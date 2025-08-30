// resources/js/llm-advice.js
import { pipeline } from '@huggingface/transformers';

let _pipe;

/** Deteksi device terbaik */
function pickDevice() {
  if (typeof navigator !== 'undefined' && navigator.gpu) return 'webgpu';
  return 'wasm';
}

/** Prompt builder: minta JSON 3 saran/kategori */
function buildPrompt(ctx) {
  const { umur_bulan, jenis_kelamin, status_tbu, status_bbu, status_bbtb } = ctx ?? {};
  return `
Anda adalah asisten gizi anak. Berikan 3 saran singkat & spesifik untuk orang tua pada 4 kategori: 1) makanan, 2) pola_asuh, 3) lingkungan, 4) kegiatan.

Konteks anak:
- umur_bulan: ${umur_bulan}
- jenis_kelamin: ${jenis_kelamin}
- status_tbu (tinggi/umur): ${status_tbu}
- status_bbu (berat/umur): ${status_bbu}
- status_bbtb (berat/tinggi): ${status_bbtb}

Aturan:
- Bahasa Indonesia, 1 kalimat pendek per butir.
- Hindari istilah medis/klaim diagnosis.
- Sesuaikan bila berisiko (MP-ASI padat gizi, protein hewani terjangkau, kebersihan air & jamban).
- Kembalikan HANYA JSON:
{
  "makanan": ["...","...","..."],
  "pola_asuh": ["...","...","..."],
  "lingkungan": ["...","...","..."],
  "kegiatan": ["...","...","..."]
}
`.trim();
}

/** Fallback parsing kalau model tidak mengeluarkan JSON rapi */
function fallbackParse(text) {
  const lines = (text || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean);
  const take = (kw) => lines.filter(l => l.toLowerCase().includes(kw)).slice(0, 3)
    .map(l => l.replace(/^\-|\d+\.\s*/,'').trim());
  return {
    makanan: take('makanan'),
    pola_asuh: take('pola'),
    lingkungan: take('lingkungan'),
    kegiatan: take('kegiatan'),
  };
}

/** Load pipeline sekali lalu pakai ulang */
async function ensurePipe() {
  if (_pipe) return _pipe;
  _pipe = await pipeline(
    'text-generation',
    // Model kecil & instruksi-friendly, open:
    'HuggingFaceTB/SmolLM2-135M-Instruct',
    {
      device: pickDevice(),      // 'webgpu' atau 'wasm'
      dtype: 'q4',               // kuantisasi ringan agar muat di memori
    }
  );
  return _pipe;
}

/** API utama yang bisa kamu panggil dari Blade/Livewire */
export async function getAdvice(ctx) {
  const pipe = await ensurePipe();
  const prompt = buildPrompt(ctx);
  const out = await pipe(prompt, {
    max_new_tokens: 220,
    temperature: 0.6,
    top_p: 0.9,
    repetition_penalty: 1.1,
    return_full_text: false,
  });

  const text = Array.isArray(out) ? out[0]?.generated_text : out?.generated_text ?? String(out ?? '');
  try {
    const json = JSON.parse(text);
    return {
      makanan: (json.makanan ?? []).slice(0,3),
      pola_asuh: (json.pola_asuh ?? []).slice(0,3),
      lingkungan: (json.lingkungan ?? []).slice(0,3),
      kegiatan: (json.kegiatan ?? []).slice(0,3),
      _raw: text,
    };
  } catch {
    const parsed = fallbackParse(text);
    return { ...parsed, _raw: text };
  }
}
