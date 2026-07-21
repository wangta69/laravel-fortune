<?php

namespace Pondol\Fortune\Services\Calendar;

use Pondol\Fortune\Facades\Calendar as CalendarFacade;
use Pondol\Fortune\Traits\Calendar;
use Pondol\Fortune\Traits\SelectDay as t_selectDay;
use Pondol\Fortune\Traits\SinsalRules;

class PetAdoptionCalendar
{
    use Calendar;

    public function cal($saju, $yyyymm, $options)
    {
        $calendar = CalendarFacade::lunarCalendar($yyyymm);

        foreach ($calendar->days as $week) {
            foreach ($week as $dayObject) {
                if (is_object($dayObject) && ! empty($dayObject->day)) {
                    $baseTotal = $dayObject->total ?? 0;
                    $baseTitles = $dayObject->titles ?? [];

                    $calculatedData = new AdoptionDay;
                    $calculatedData->cal($saju, $dayObject, $options);

                    $dayObject->total = $calculatedData->total + $baseTotal;
                    $mergedTitles = array_merge($calculatedData->titles, $baseTitles);
                    uasort($mergedTitles, fn ($a, $b) => ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0));
                    $dayObject->titles = $mergedTitles;

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

class AdoptionDay
{
    use SinsalRules, t_selectDay;

    public $total = 0;

    public $titles = [];

    public function cal($saju, $dayData, $options)
    {
        // ★ 여기서 'adoption'을 명시적으로 넣어줘야 config의 wonjin을 읽어옵니다.
        $result = $this->calculateFortune($saju, $dayData, 'pet_adoption', $options);
        $this->total = $result['total'];
        $this->titles = $result['titles'];
    }
}
