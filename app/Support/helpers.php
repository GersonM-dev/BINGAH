<?php

use Carbon\Carbon;

if (! function_exists('age_months_rounded')) {
    /**
     * Hitung umur (bulan) dibulatkan:
     * - hitung bulan penuh (floor)
     * - tambah 1 jika sisa hari >= $thresholdDays (default 30)
     *
     * @param  \DateTimeInterface|string|null  $birth
     * @param  \DateTimeInterface|string|null  $measure
     * @param  int  $thresholdDays
     * @return int|null
     */
    function age_months_rounded(\DateTimeInterface|string|null $birth, \DateTimeInterface|string|null $measure, int $thresholdDays = 30): ?int
    {
        if (! $birth || ! $measure) {
            return null;
        }

        $b = $birth instanceof \DateTimeInterface ? Carbon::instance($birth) : Carbon::parse($birth);
        $m = $measure instanceof \DateTimeInterface ? Carbon::instance($measure) : Carbon::parse($measure);

        $b = $b->startOfDay();
        $m = $m->startOfDay();

        if ($m->lessThan($b)) {
            return 0;
        }

        $months = $b->diffInMonths($m); // floor
        $anchor = $b->copy()->addMonths($months);
        $leftoverDays = $anchor->diffInDays($m);

        if ($leftoverDays >= $thresholdDays) {
            $months += 1;
        }

        return $months;
    }
}
