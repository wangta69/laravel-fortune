<?php

namespace Pondol\Fortune\Services\Western\Zodiac;

class ZodiacSign
{
    public int $id;              // 1: 양자리 ~ 12: 물고기자리 (정통 춘분점 순번)

    public string $code;        // DB 연동용 2자리 코드 ('01' ~ '12')

    public string $name_ko;     // 한글 명칭 ('양자리')

    public string $name_en;     // 영문 명칭 ('Aries')

    public string $symbol;      // 천문 기호 ('♈')

    public string $start_date;  // 시작일 ('03-21')

    public string $end_date;    // 종료일 ('04-19')

    public string $element_en;  // 4원소 영문 ('Fire', 'Earth', 'Air', 'Water')

    public string $element_ko;  // 4원소 한글 ('불', '흙', '공기', '물')

    public string $modality;    // 3성질 ('활동궁', '고정궁', '변통궁')

    public string $ruler;       // 수호천체/지배성 ('화성', '금성' 등)

    public string $polarity;    // 극성 ('양성', '음성')

    public array $keywords;     // 성향 대표 키워드

    public int $calendar_id;    // 레거시 달력 1월 기준 순번 (물병=1 ~ 염소=12 호환용)

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        $this->code = str_pad((string) $this->id, 2, '0', STR_PAD_LEFT);
    }

    public function getPeriodText(): string
    {
        [$sm, $sd] = explode('-', $this->start_date);
        [$em, $ed] = explode('-', $this->end_date);

        return sprintf('%d월 %d일 - %d월 %d일', (int) $sm, (int) $sd, (int) $em, (int) $ed);
    }
}
