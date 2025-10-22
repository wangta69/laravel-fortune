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
    use Calendar {
        _create as calendar_create;
    }
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
        $season_24 = $this->info->seasons;
        $season_24->seasonArr = $this->season_24_to_array($season_24);

        // print_r($season_24->seasonArr );
        $calendar = $this->calendar_create($yyyymm);

        // print_r($calendar);
        // list ($so24, $so24year, $so24month, $so24day, $so24hour) =
        // list ($so24, $so24year, $so24month, $so24day, $so24hour) = Lunar::test (2025, 1, 1, 12, 30);
        // echo GANJI['ko'][$so24year].','.GANJI['ko'][$so24month].','.GANJI['ko'][$so24day];
        // 시작하는 요일 이전의 것은 공백처리
        foreach ($calendar->days as $c) {
            if ($c->day) {
                $data = new Day();
                $data->cal($yyyymm, $c->day, $season_24);
                $c->setObject($data);
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
          $season_24->center->year.pad_zero($season_24->center->month).pad_zero($season_24->center->day) => $season_24->center->name,
          $season_24->ccenter->year.pad_zero($season_24->ccenter->month).pad_zero($season_24->ccenter->day) => $season_24->ccenter->name,
          $season_24->nenter->year.pad_zero($season_24->nenter->month).pad_zero($season_24->nenter->day) => $season_24->nenter->name
        ];

    }
}

class Day
{
    public $day;
    public $solar;
    public function cal($yyyymm, $day, $season_24)
    {



        $this->day = $day;
        $lunar = Lunar::ymd($yyyymm.pad_zero($day))->tolunar()->sajugabja()->create();

        // $this->lunar = $lunar->year.pad_zero($lunar->month).pad_zero($lunar->day);
        $this->solar = $lunar->solar;
        $this->lunar = $lunar->lunar;
        $this->leap = $lunar->leap; // 윤달여부
        $this->largemonth = $lunar->largemonth; // 큰달(30일) 작은달(29일)
        $this->week = $lunar->week;
        // $this->ganji = $lunar->ganji;
        // $this->ddi = $lunar->ddi;
        $this->season24 = $this->season24($season_24);

        // print_r($this->season24);
        $this->gabja = $lunar->gabja;


        preg_match('/^([0-9]{4})([0-9]{2})$/', trim($yyyymm), $match);
        list(, $year, $month) = $match;

        if ( // 현재의 접입일을 계산
            $season_24->center->year == $year &&
            $season_24->center->month == $month &&
            ($season_24->center->day + 1) == $this->day
        ) {
            $this->gabja->lunar = $this->lunar;
            // $this->gabja->s28  = Lunar::s28day ($ymd);
            // $this->ymd_tune = $v->tune;
        }

        // # 1일의 음력월에 대한 합삭/망 정보
        // $v->moon = $this->lunarSvc->moonstatus ($ymd);
        // # 1일의 28수 정보
        // $v->s28  = $this->lunarSvc->s28day ($ymd);
        // # 이번달의 절기 정보

        // $v->yoon = $v->lunar->leap ? ', 윤달' : '';
        // $v->bmon = $v->lunar->largemonth ? '큰달' : '평달';

        // $v->gabja = calgabja($year);
    }

    private function season24($season_24)
    {
        $solar = str_replace("-", "", $this->solar);
        if (array_key_exists($solar, $season_24->seasonArr)) {
            return $season_24->seasonArr[$solar];
        }
        return null;
    }
}
