<?php

namespace Pondol\Fortune\Services\Western\Biorhythm;

use DateTime;

class Biorhythm
{
    private const PHYSICAL_CYCLE = 23;     // 신체 리듬 주기

    private const EMOTIONAL_CYCLE = 28;    // 감정 리듬 주기

    private const INTELLECTUAL_CYCLE = 33; // 지성 리듬 주기

    public function calculate(string $birthYmd, ?string $targetYmd = null): BiorhythmResult
    {
        $birthDate = new DateTime(trim($birthYmd));
        $targetDate = new DateTime($targetYmd ? trim($targetYmd) : date('Y-m-d'));

        $diff = $birthDate->diff($targetDate);
        $daysAlive = (int) $diff->format('%r%a');

        $p = $this->calculateSine($daysAlive, self::PHYSICAL_CYCLE);
        $e = $this->calculateSine($daysAlive, self::EMOTIONAL_CYCLE);
        $i = $this->calculateSine($daysAlive, self::INTELLECTUAL_CYCLE);

        // 향후 7일간 추이 데이터 산출
        $trend = [];
        for ($step = 0; $step < 7; $step++) {
            $curDays = $daysAlive + $step;
            $curDate = (clone $targetDate)->modify("+{$step} days")->format('Y-m-d');
            $trend[$curDate] = [
                'physical' => $this->calculateSine($curDays, self::PHYSICAL_CYCLE),
                'emotional' => $this->calculateSine($curDays, self::EMOTIONAL_CYCLE),
                'intellectual' => $this->calculateSine($curDays, self::INTELLECTUAL_CYCLE),
            ];
        }

        return new BiorhythmResult([
            'days_alive' => $daysAlive,
            'physical' => $p,
            'emotional' => $e,
            'intellectual' => $i,
            'average' => round(($p + $e + $i) / 3, 1),
            'is_physical_critical' => abs($p) < 5.0,
            'is_emotional_critical' => abs($e) < 5.0,
            'is_intellectual_critical' => abs($i) < 5.0,
            'trend_7days' => $trend,
        ]);
    }

    private function calculateSine(int $days, int $cycle): float
    {
        // 공식: sin(2 * pi * t / cycle) * 100
        return round(sin((2 * M_PI * $days) / $cycle) * 100, 1);
    }
}
