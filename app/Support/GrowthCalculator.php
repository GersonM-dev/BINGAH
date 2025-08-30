<?php

namespace App\Support;

use InvalidArgumentException;

class GrowthCalculator
{
    public static function roundHeightHalf(float $cm): float
    {
        // pembulatan ke 0.5 cm terdekat
        return round($cm * 2) / 2;
    }

    public static function hitungZ(float $nilai, float $median, float $sd): float
    {
        if ($sd == 0.0) {
            throw new InvalidArgumentException('SD tidak boleh 0');
        }
        return ($nilai - $median) / $sd;
    }

    public static function statusUnderweight(float $z): string
    {
        if ($z < -3) return 'Berat badan sangat kurang';
        if ($z >= -3 && $z < -2) return 'Berat badan kurang';
        if ($z >= -2 && $z <= 2) return 'Berat badan normal';
        return 'Risiko berat badan lebih';
    }

    public static function statusStunting(float $z): string
    {
        if ($z < -3) return 'Sangat pendek';
        if ($z >= -3 && $z < -2) return 'Pendek';
        if ($z >= -2 && $z <= 2) return 'Normal';
        return 'Tinggi';
    }

    public static function statusWasting(float $z): string
    {
        if ($z < -3) return 'Gizi buruk';
        if ($z >= -3 && $z < -2) return 'Gizi kurang';
        if ($z >= -2 && $z <= 1) return 'Gizi baik';
        if ($z > 1 && $z <= 2) return 'Berisiko gizi lebih';
        if ($z > 2 && $z <= 3) return 'Gizi lebih';
        return 'Obesitas';
    }

    /**
     * @param 'L'|'P' $gender
     * @param int $umurBulan 0..60
     * @param float $berat kg
     * @param float $tinggi cm
     * @return array{bb_u_z: float, tb_u_z: float, bb_tb_z: float, status_bbu: string, status_tbu: string, status_bbtb: string}
     */
    public static function hitungSemua(string $gender, int $umurBulan, float $berat, float $tinggi): array
    {
        $ref = config('who_zscore');

        // BBU
        $bbU = $ref['BBU'][$gender][$umurBulan] ?? null;
        // TBU
        $tbU = $ref['TBU'][$gender][$umurBulan] ?? null;

        // BBTB: tinggi dibulatkan ke 0.5cm → string key dengan 1 desimal
        $tinggiKey = number_format(self::roundHeightHalf($tinggi), 1, '.', '');
        $bbTb = $ref['BBTB'][$gender][$tinggiKey] ?? null;

        if (!$bbU || !$tbU || !$bbTb) {
            throw new InvalidArgumentException("Referensi WHO tidak lengkap untuk gender=$gender umur=$umurBulan tinggi=$tinggiKey");
        }

        $bb_u_z = self::hitungZ($berat,  (float)$bbU['median'], (float)$bbU['sd']);
        $tb_u_z = self::hitungZ($tinggi, (float)$tbU['median'], (float)$tbU['sd']);
        $bb_tb_z = self::hitungZ($berat,  (float)$bbTb['median'], (float)$bbTb['sd']);

        return [
            'bb_u_z' => $bb_u_z,
            'tb_u_z' => $tb_u_z,
            'bb_tb_z' => $bb_tb_z,
            'status_bbu'  => self::statusUnderweight($bb_u_z),
            'status_tbu'  => self::statusStunting($tb_u_z),
            'status_bbtb' => self::statusWasting($bb_tb_z),
        ];
    }
}
