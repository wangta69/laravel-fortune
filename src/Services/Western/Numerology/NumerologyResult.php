<?php

namespace Pondol\Fortune\Services\Western\Numerology;

class NumerologyResult
{
    public int $life_path_number;       // 라이프 패스 넘버 (생명수: 핵심 영혼 경로)

    public int $birthday_number;        // 탄생일 수 (태어난 날짜 기반 재능)

    public int $attitude_number;        // 첫인상/태도수 (월 + 일)

    public ?int $destiny_number = null; // 표현수/운명수 (이름 영문 알파벳 전체 합)

    public ?int $soul_urge_number = null; // 영혼수 (이름 모음 합)

    public int $personal_year;          // 올해의 개인 연운수 (개인 주기 1~9)

    public string $archetype;           // 성향 원형 명칭

    public array $traits;               // 대표 키워드 및 재능

    public string $description;         // 성향 상세 풀이

    public function __construct(array $data)
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }
    }
}
