<?php

namespace App\Observers;

use App\Models\DataAntropometry;
use App\Support\GrowthCalculator;

class DataAntropometryObserver
{
    public function saved(DataAntropometry $model): void
    {
        // Pastikan field yang dibutuhkan ada
        if (!$model->umur_bulan || !$model->berat || !$model->tinggi || !$model->anak?->jenis_kelamin) {
            return;
        }

        // Map gender 'Laki-laki' / 'Perempuan' → 'L'/'P'
        $gender = $model->anak->jenis_kelamin === 'Laki-laki' ? 'L' : 'P';

        $hasil = GrowthCalculator::hitungSemua(
            gender: $gender,
            umurBulan: (int) $model->umur_bulan,
            berat: (float) $model->berat,
            tinggi: (float) $model->tinggi,
        );

        // Upsert ke relasi hasOne prediksi
        $model->prediksi()->updateOrCreate(
            ['id_dataAntropometry' => $model->id],
            [
                'status_tbu'   => $hasil['status_tbu'],
                'status_bbu'   => $hasil['status_bbu'],
                'status_tbbb'  => $hasil['status_bbtb'],
                'rekomendasi'  => self::buatRekomendasi($hasil),
            ]
        );
    }

    protected static function buatRekomendasi(array $hasil): ?string
    {
        // Contoh sederhana: rangkum tiga status
        return sprintf(
            "TBU: %s; BBU: %s; BB/TB: %s.",
            $hasil['status_tbu'],
            $hasil['status_bbu'],
            $hasil['status_bbtb'],
        );
    }
}
