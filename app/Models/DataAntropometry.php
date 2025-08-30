<?php

namespace App\Models;

use App\Services\GroqAdviceClient as AdviceClient;
use App\Support\GrowthCalculator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataAntropometry extends Model
{
    use HasFactory;

    protected $table = 'data_antropometry';

    protected $fillable = [
        'id_anak',
        'id_user',
        'umur_bulan',
        'tipe_ukur',
        'tinggi',
        'berat',
        'lingkar_lengan_atas',
        'lingkar_kepala',
        'tanggal_ukur'
    ];

    // Relasi ke Anak
    public function anak()
    {
        return $this->belongsTo(Anak::class, 'id_anak');
    }

    // Relasi ke User (jika pakai auth bawaan Laravel)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relasi ke Prediksi
    public function prediksi()
    {
        return $this->hasOne(Prediksi::class, 'id_dataAntropometry');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->id_user ??= $model->anak?->id_user ?? auth()->id();
            $model->tanggal_ukur ??= now()->toDateString();
        });

        static::saved(function (self $model) {
            try {

                $model->loadMissing('anak');
                $jkText = $model->anak?->jenis_kelamin; // "Laki-laki" / "Perempuan"

                if (
                    !$jkText ||
                    is_null($model->umur_bulan) ||
                    is_null($model->berat) ||
                    is_null($model->tinggi)
                ) {
                    return;
                }

                $gender = ($jkText === 'Laki-laki') ? 'L' : 'P';

                $hasil = GrowthCalculator::hitungSemua(
                    gender: $gender,
                    umurBulan: (int) $model->umur_bulan,
                    berat: (float) $model->berat,
                    tinggi: (float) $model->tinggi,
                );

                $prediksi = $model->prediksi()->updateOrCreate(
                    ['id_dataAntropometry' => $model->id],
                    [
                        'status_tbu' => $hasil['status_tbu'] ?? null,
                        'status_bbu' => $hasil['status_bbu'] ?? null,
                        'status_tbbb' => $hasil['status_bbtb'] ?? null,
                    ]
                );

                try {
                    /** @var AdviceClient $client */
                    $client = app(AdviceClient::class);
                    Log::debug('GroqAdviceClient invoked');
                    $rec = $client->generate([
                        'umur_bulan' => (int) $model->umur_bulan,
                        'jenis_kelamin' => $jkText,
                        'status_tbu' => $hasil['status_tbu'] ?? null,
                        'status_bbu' => $hasil['status_bbu'] ?? null,
                        'status_tbbb' => $hasil['status_bbtb'] ?? null,
                    ]) ?? [];

                    // defensive casts
                    $makanan = array_filter((array) ($rec['makanan'] ?? []));
                    $pola_asuh = array_filter((array) ($rec['pola_asuh'] ?? []));
                    $lingkungan = array_filter((array) ($rec['lingkungan'] ?? []));
                    $kegiatan = array_filter((array) ($rec['kegiatan'] ?? []));

                    $markdown =
                        "**Saran Makanan**\n" . ($makanan ? '- ' . implode("\n- ", $makanan) : '- (tidak ada saran)') .
                        "\n\n**Pola Asuh & Kebiasaan**\n" . ($pola_asuh ? '- ' . implode("\n- ", $pola_asuh) : '- (tidak ada saran)') .
                        "\n\n**Lingkungan & Sanitasi**\n" . ($lingkungan ? '- ' . implode("\n- ", $lingkungan) : '- (tidak ada saran)') .
                        "\n\n**Kegiatan/Aktivitas**\n" . ($kegiatan ? '- ' . implode("\n- ", $kegiatan) : '- (tidak ada saran)') .
                        "\n\n> Catatan: Saran bersifat edukasi umum, bukan pengganti nasihat tenaga kesehatan.";

                    $prediksi->update(['rekomendasi' => $markdown]);
                    $justSaved = $model->prediksi()->first(['id', 'rekomendasi', 'updated_at']);
                    Log::info('After LLM update, rekomendasi is:', [
                        'prediksi_id' => $justSaved?->id,
                        'updated_at' => (string) $justSaved?->updated_at,
                        'rekomendasi_sample' => mb_substr((string) $justSaved?->rekomendasi, 0, 120),
                    ]);
                    Log::info('Prediksi and rekomendasi updated', [
                        'data_antropometry_id' => $model->id,
                        'prediksi_id' => $prediksi->id ?? null,
                    ]);

                } catch (\Throwable $e) {
                    Log::warning('LLM rekomendasi gagal', [
                        'data_antropometry_id' => $model->id,
                        'message' => $e->getMessage(),
                    ]);
                }

            } catch (\Throwable $e) {
                Log::error('Prediksi auto-create failed', [
                    'data_antropometry_id' => $model->id,
                    'message' => $e->getMessage(),
                ]);
            }
        });
    }


}
