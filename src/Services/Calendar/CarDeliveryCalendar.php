<?php

namespace Pondol\Fortune\Services\Calendar;

use Pondol\Fortune\Facades\Calendar as CalendarFacade;
use Pondol\Fortune\Traits\Calendar;
use Pondol\Fortune\Traits\SelectDay as t_selectDay;
use Pondol\Fortune\Traits\SinsalRules;

class CarDeliveryCalendar
{
    use Calendar;

    /**
     * 신차 택일 매니저
     */
    public function cal($saju, $yyyymm, $options = [])
    {
        // 1. 공통 기초 데이터 캐시 로드
        $calendar = CalendarFacade::lunarCalendar($yyyymm);

        foreach ($calendar->days as $week) {
            foreach ($week as $dayObject) {
                if (is_object($dayObject) && ! empty($dayObject->day)) {

                    // 2. 기초 데이터 백업
                    $baseTotal = $dayObject->total ?? 0;
                    $baseTitles = $dayObject->titles ?? [];

                    // 3. 차량 전용 개인화 계산기 실행
                    $calculatedData = new CarDay;
                    $calculatedData->cal($saju, $dayObject, $options);

                    // 4. 최종 점수 합산
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
 * 신차 택일 계산기
 */
class CarDay
{
    use SinsalRules, t_selectDay;

    public $total = 0;

    public $titles = [];

    public function cal($saju, $dayData, $options)
    {
        $result = $this->calculateFortune($saju, $dayData, 'car', $options);
        $this->total = $result['total'];
        $this->titles = $result['titles'];
    }
}
