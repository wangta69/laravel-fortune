<?php

namespace Pondol\Fortune\Services\Calendar;

use Pondol\Fortune\Facades\Calendar as CalendarFacade;
use Pondol\Fortune\Traits\Calendar;
use Pondol\Fortune\Traits\SelectDay as t_selectDay;
use Pondol\Fortune\Traits\SinsalRules;

class MarriageCalendar
{
    use Calendar;

    /**
     * 결혼 택일 매니저: 신랑/신부 사주를 대조하여 병합
     */
    public function cal($saju_male, $saju_female, $yyyymm, $options = [])
    {
        // 1. 공통 기초 데이터 캐시 로드
        $calendar = CalendarFacade::lunarCalendar($yyyymm);

        foreach ($calendar->days as $week) {
            foreach ($week as $dayObject) {
                if (is_object($dayObject) && ! empty($dayObject->day)) {

                    // 2. 기초 데이터 백업
                    $baseTotal = $dayObject->total ?? 0;
                    $baseTitles = $dayObject->titles ?? [];

                    // 3. 결혼 전용 계산기 실행 (신랑, 신부 사주 주입)
                    $calculatedData = new MarriageDay;
                    $calculatedData->cal($saju_male, $saju_female, $dayObject, $options);

                    // 4. 최종 점수 합산 (남녀 평균 점수 + 기초 점수)
                    $dayObject->total = $calculatedData->total + $baseTotal;

                    // 5. 태그 병합 및 우선순위 정렬
                    $mergedTitles = array_merge($calculatedData->titles, $baseTitles);
                    uasort($mergedTitles, function ($a, $b) {
                        return ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0);
                    });
                    $dayObject->titles = $mergedTitles;

                    // 6. 속성 복사 (stdClass 에러 방지)
                    foreach (get_object_vars($calculatedData) as $key => $value) {
                        if (! in_array($key, ['titles', 'total'])) {
                            $dayObject->{$key} = $value;
                        }
                    }
                }
            }
        }

        return $calendar;
    }
}

/**
 * 결혼 택일 계산기
 */
class MarriageDay
{
    use SinsalRules, t_selectDay;

    public $total = 0;

    public $titles = [];

    public $male_total = 0;

    public $female_total = 0;

    public $taekilInfo_male = [];

    public $taekilInfo_female = [];

    /* --- MarriageDay.php 내부 cal 메서드 하단 수정 --- */

    public function cal($saju_male, $saju_female, $dayData, $options = [])
    {
        $maleRes = $this->calculateFortune($saju_male, $dayData, 'marriage', $options);
        $femaleRes = $this->calculateFortune($saju_female, $dayData, 'marriage', $options);

        $this->male_total = $maleRes['total'];
        $this->female_total = $femaleRes['total'];
        $this->total = (int) round(($this->male_total + $this->female_total) / 2);

        // [고도화된 병합 로직]
        $mergedTitles = [];
        $catBaseKeys = array_keys(config('pondol-fortune.select_day.category_base', []));

        // 1. 남성 결과 처리
        foreach ($maleRes['titles'] as $k => $v) {
            // category_base에 있거나 dae_group, gachui 같은 공통 항목인 경우
            if (in_array($k, $catBaseKeys) || in_array($k, ['dae_group', 'gachui'])) {
                $mergedTitles[$k] = $v; // 라벨 없이 저장
            } else {
                $mergedTitles['m_'.$k] = array_merge($v, ['ko' => '남:'.$v['ko']]);
            }
        }

        // 2. 여성 결과 처리
        foreach ($femaleRes['titles'] as $k => $v) {
            if (in_array($k, $catBaseKeys) || in_array($k, ['dae_group', 'gachui'])) {
                $mergedTitles[$k] = $v; // 공통은 덮어쓰기 (중복 방지)
            } else {
                $mergedTitles['f_'.$k] = array_merge($v, ['ko' => '여:'.$v['ko']]);
            }
        }

        $this->titles = $mergedTitles;
    }
}
