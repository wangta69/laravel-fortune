<?php

namespace Pondol\Fortune\Services\Western\Tarot;

class TarotCard
{
    public int $id;                 // 0 ~ 77 (고유 순번)

    public string $name_ko;        // 한글 카드명 (예: '바보', '마법사', '완드 에이스')

    public string $name_en;        // 영문 카드명 (예: 'The Fool', 'Ace of Wands')

    public string $arcana;         // 'major' 또는 'minor'

    public ?string $suit;          // 'wands', 'cups', 'swords', 'pentacles', null(메이저)

    public int $number;            // 카드 번호 (메이저: 0~21, 마이너: 1~14)

    public bool $is_reversed = false; // 역방향 여부

    public array $keywords_upright;   // 정방향 키워드

    public array $keywords_reversed;  // 역방향 키워드

    public string $meaning_upright;   // 정방향 상세 풀이

    public string $meaning_reversed;  // 역방향 상세 풀이

    public function __construct(array $data, bool $isReversed = false)
    {
        foreach ($data as $key => $val) {
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
        $this->is_reversed = $isReversed;
    }

    public function getOrientationText(): string
    {
        return $this->is_reversed ? '역방향' : '정방향';
    }

    public function getActiveKeywords(): array
    {
        return $this->is_reversed ? $this->keywords_reversed : $this->keywords_upright;
    }

    public function getActiveMeaning(): string
    {
        return $this->is_reversed ? $this->meaning_reversed : $this->meaning_upright;
    }
}
