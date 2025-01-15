<?php
namespace Pondol\Fortune\Services\Calendar;
use Carbon\Carbon;
use Pondol\Fortune\Facades\Lunar;

/**
 * 12실살을 제외한 기타 길신 및 흉신 구하기
 * 일지기준으로 하면 집안일
 * 년지기준으로 하면 바깥일
 */

class LunarCalendar
{

  /**
   * 음력달력출력
   * @param String $yyyymm 202501 (2025년 01월)
   */
  public function cal($yyyymm) {

    preg_match('/^([0-9]{4})([0-9]{2})$/', trim ($yyyymm), $match);
    list (, $year, $month) = $match;

    $c_date = mktime(0, 0, 0, $month, 1, $year);
    $start_week = date('w', $c_date); // 1. 시작 요일
    $total_day = date('t', $c_date); // 2. 현재 달의 총 날짜
    // $this->total_week = ceil(($total_day + $start_week) / 7);  // 3. 현재 달의 총 주차
    $w = $start_week;

    $season_24 = Lunar::seasonal_division($yyyymm.'20');
    // echo $yyyymm.'20';
    // print_r($season_24);
    $season_24->seasonArr = $this->season_24_to_array($season_24);
    // print_r($season_24);

    $daily = [];
    // 시작하는 요일 이전의 것은 공백처리
    for ($i=0; $i < $w; $i++) {
      array_push($daily, new Day);
    }
    for($i = 0; $i<$total_day; $i++) {
      $data = new Day;

      $data->fillInfo($yyyymm, $i + 1, $season_24);
      array_push($daily, $data);
    }

    // 날짜가 끝나는 마지막 요일 까지 공백 처리
    $mod = 7 - count($daily) % 7;
    for ($i = 0; $i < $mod; $i++) {
      array_push($daily, new Day);
    }


    // 데이타를 주별로 나누기
    $collection = collect($daily);
    $split = count($daily) / 7; // 데이타를 7일 씩 자름
    $this->daily = $collection->split($split);

    return $this->daily;
  }


  private function season_24_to_array($season_24) {

    return [
      $season_24->center->year.pad_zero($season_24->center->month).pad_zero($season_24->center->day) => $season_24->center->name,
      $season_24->ccenter->year.pad_zero($season_24->ccenter->month).pad_zero($season_24->ccenter->day) => $season_24->ccenter->name,
      $season_24->nenter->year.pad_zero($season_24->nenter->month).pad_zero($season_24->nenter->day) => $season_24->nenter->name
    ];

  }
}

class Day {
  public $day;
  public $solar;
  public function fillInfo($yyyymm, $day, $season_24) {
    $this->day = $day;
    $this->solar = $yyyymm.pad_zero($day);
    $lunar = Lunar::tolunar($this->solar);

    $this->lunar = $lunar->year.pad_zero($lunar->month).pad_zero($lunar->day);

    $this->leap = $lunar->leap; // 윤달여부
    $this->largemonth = $lunar->largemonth; // 큰달(30일) 작은달(29일)
    $this->week = $lunar->week;
    $this->ganji = $lunar->ganji;
    $this->ddi = $lunar->ddi;
    $this->season24 = $this->season24($season_24);
    $this->tune = Lunar::dayfortune($this->solar);

    preg_match('/^([0-9]{4})([0-9]{2})$/', trim ($yyyymm), $match);
    list (, $year, $month) = $match;

    if ( // 현재의 접입일을 계산
      $season_24->center->year == $year &&
      $season_24->center->month == $month &&
      ($season_24->center->day + 1) == $this->day
    ) {
      $this->tune->lunar = $this->lunar;
      // $this->tune->s28  = Lunar::s28day ($ymd);
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

  private function season24($season_24) {
    if(array_key_exists($this->solar, $season_24->seasonArr)) {
      return $season_24->seasonArr[$this->solar];
    }
    return null;
  }
}