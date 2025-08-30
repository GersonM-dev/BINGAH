<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class GroqAdviceClient
{
    public function __construct(
        private ?Client $http = null
    ) {
        $this->http ??= new Client([
            'base_uri' => config('services.groq.base'),
            'timeout' => 20,
        ]);
    }

    /**
     * @param array{
     *   umur_bulan:int,
     *   jenis_kelamin:string,
     *   status_tbu:?string,
     *   status_bbu:?string,
     *   status_bbtb:?string
     * } $payload
     * @return array{makanan:array,pola_asuh:array,lingkungan:array,kegiatan:array}
     */
    public function generate(array $payload): array
    {
        $model = config('services.groq.model', 'qwen/qwen3-32b');

        $system = <<<SYS
You are a pediatric growth & nutrition assistant for Indonesia.
Return JSON ONLY with keys: makanan, pola_asuh, lingkungan, kegiatan.
Each key value is an array of 3–6 concise bullet points (no numbering).
Keep advice general, safe, and educational; no medical diagnosis.
Language: Indonesian.
SYS;

        $user = sprintf(
            <<<USR
Data anak:
- Umur (bulan): %d
- Jenis kelamin: %s
- Status TBU: %s
- Status BBU: %s
- Status BB/TB: %s

Instruksi:
Berdasarkan data, berikan saran terstruktur dalam JSON dengan 4 kunci: 
"makanan", "pola_asuh", "lingkungan", "kegiatan". 
Jangan sertakan penjelasan di luar JSON.
USR,
            (int) $payload['umur_bulan'],
            (string) $payload['jenis_kelamin'],
            (string) ($payload['status_tbu'] ?? '-'),
            (string) ($payload['status_bbu'] ?? '-'),
            (string) ($payload['status_bbtb'] ?? '-')
        );

        try {
            $res = $this->http->post('openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.groq.api_key'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'temperature' => 0.6,
                    'top_p' => 0.95,
                    'stream' => false,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'max_tokens' => 800,
                ],
            ]);

            $data = json_decode((string) $res->getBody(), true);
            $text = Arr::get($data, 'choices.0.message.content', '');
            $json = json_decode($text, true);

            // ✅ Success log
            Log::info('GroqAdviceClient success', [
                'status_code' => $res->getStatusCode(),
                'usage' => $data['usage'] ?? null,
                'output_text' => $text, // log only first 200 chars
            ]);

            return [
                'makanan' => array_values((array) ($json['makanan'] ?? [])),
                'pola_asuh' => array_values((array) ($json['pola_asuh'] ?? [])),
                'lingkungan' => array_values((array) ($json['lingkungan'] ?? [])),
                'kegiatan' => array_values((array) ($json['kegiatan'] ?? [])),
            ];
        } catch (\Throwable $e) {
            // ❌ Failure log
            Log::error('GroqAdviceClient failed', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
            return ['makanan' => [], 'pola_asuh' => [], 'lingkungan' => [], 'kegiatan' => []];
        }

    }
}
