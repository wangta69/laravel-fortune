<?php

namespace Pondol\Fortune\Services\Calendar;

use Carbon\Carbon;
use Pondol\Fortune\Facades\Lunar;
use Pondol\Fortune\Traits\Calendar;

/**
 * 12실살을 제외한 기타 길신 및 흉신 구하기
 * 일지기준으로 하면 집안일
 * 년지기준으로 하면 바깥일
 */

class LunarCalendar
{
    use Calendar;
    /**
     * 음력달력출력
     * @param String $yyyymm 202501 (2025년 01월)
     */
    public function cal($yyyymm)
    {

        // 중간일정도를 대입하여 이전/이후 절의 중간점을 찾는다
        // 편의를위해 양력 1월 1일의 갑자를 음력 1월 1일의 갑자로 본다.
        // 양력의 갑자년도 구하기

        // $this->month = Lunar::gabja_month(substr($yyyymm, 0, 4), substr($yyyymm, 4, 2));
        $this->info = Lunar::ymd($yyyymm.'01')->tolunar()->sajugabja()
          ->seasonal_division($yyyymm.'20')
          ->create();

        // 양력에 대한 갑자 년월 구하기
        list($year, $month) = Lunar::to_gabja($yyyymm);
        $this->info->solarInfo = (object)['year' => $year, 'month' => $month];

        // 음력에 대한 갑자 년월 구하기
        $lunar_yyyymm = date('Ym', strtotime($this->info->lunar));
        list($year, $month) = Lunar::to_gabja($lunar_yyyymm);
        $this->info->lunarInfo = (object)['year' => $year, 'month' => $month];

        // 현재 달의 절기 정보를 가져와서 배열로 맵핑
        //  _create() 호출로 모든 날짜 계산을 끝냅니다.
        $calendar = $this->_create($yyyymm);

        // 루프를 돌며 절기 정보만 추가합니다.
        $seasonArr = $this->season_24_to_array($this->info->seasons);
        foreach ($calendar->days as $dayObject) {
            if ($dayObject && $dayObject->day) {
                $solar_yyyymmdd = str_replace('-', '', $dayObject->solar);
                if (isset($seasonArr[$solar_yyyymmdd])) {
                    $dayObject->season24 = $seasonArr[$solar_yyyymmdd]; // 뷰와 형식을 맞추기 위해 배열로 저장
                }
            }
        }

        return $calendar->splitPerWeek();
    }

    /**
     * 데이타 비료를 위해 날짜(yyyymmdd) 및 절기명으로 데이타 리턴
     */
    private function season_24_to_array($season_24)
    {
        return [
          $season_24->center->year.str_pad($season_24->center->month, 2, '0', STR_PAD_LEFT).str_pad($season_24->center->day, 2, '0', STR_PAD_LEFT)
            => ['ko' => $season_24->center->name->ko, 'ch' => $season_24->center->name->ch],

          $season_24->ccenter->year.str_pad($season_24->ccenter->month, 2, '0', STR_PAD_LEFT).str_pad($season_24->ccenter->day, 2, '0', STR_PAD_LEFT)
            => ['ko' => $season_24->ccenter->name->ko, 'ch' => $season_24->ccenter->name->ch],

          $season_24->nenter->year.str_pad($season_24->nenter->month, 2, '0', STR_PAD_LEFT).str_pad($season_24->nenter->day, 2, '0', STR_PAD_LEFT)
            => ['ko' => $season_24->nenter->name->ko, 'ch' => $season_24->nenter->name->ch]
        ];
    }
}
