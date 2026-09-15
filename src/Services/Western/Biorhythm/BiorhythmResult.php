<?php

namespace Pondol\Fortune\Services\Western\Biorhythm;

class BiorhythmResult
{
    public int $days_alive;             // 출생 후 경과 일수

    public float $physical;             // 신체 지수 (-100.0 ~ +100.0)

    public float $emotional;            // 감정 지수 (-100.0 ~ +100.0)

    public float $intellectual;         // 지성 지수 (-100.0 ~ +100.0)

    public float $average;              // 종합 평균 활력도 (-100.0 ~ +100.0)

    public bool $is_physical_critical;  // 신체 위험일 여부 (0점 전이일)

    public bool $is_emotional_critical; // 감정 위험일 여부

    public bool $is_intellectual_critical; // 지성 위험일 여부

    public array $trend_7days = [];     // 향후 7일간의 지수 추이 배열

    public function __construct(array $data)
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }
    }

    public function getPhysicalStatus(): string
    {
        return $this->is_physical_critical ? '위험일' : ($this->physical > 0 ? '고조기' : '저조기');
    }

    public function getEmotionalStatus(): string
    {
        return $this->is_emotional_critical ? '위험일' : ($this->emotional > 0 ? '고조기' : '저조기');
    }

    public function getIntellectualStatus(): string
    {
        return $this->is_intellectual_critical ? '위험일' : ($this->intellectual > 0 ? '고조기' : '저조기');
    }
}
