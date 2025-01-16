<?php
namespace Pondol\Fortune\Traits;

trait Calendar {
  public $days = [];

  public function _create($yyyymm) {
    preg_match('/^([0-9]{4})([0-9]{2})$/', trim ($yyyymm), $match);
    list (, $year, $month) = $match;

    $c_date = mktime(0, 0, 0, $month, 1, $year);
    $start_week = date('w', $c_date); // 1. 시작 요일
    $total_day = date('t', $c_date); // 2. 현재 달의 총 날짜
    // $this->total_week = ceil(($total_day + $start_week) / 7);  // 3. 현재 달의 총 주차
    $w = $start_week;

    $this->days = [];
    // 시작하는 요일 이전의 것은 공백처리
    for ($i=0; $i < $w; $i++) {
      array_push($this->days, new Day);
    }
    for($i = 0; $i<$total_day; $i++) {
      array_push($this->days, new Day($i+1));
    }
    // 날짜가 끝나는 마지막 요일 까지 공백 처리
    $mod = 7 - count($this->days) % 7;
    for ($i = 0; $i < $mod; $i++) {
      array_push($this->days, new Day);
    }

    return $this;
  }

  // 1주 7일 단위로 배열을 만들어 리턴
  public function splitPerWeek() {
    $collection = collect($this->days);
    $split = count($this->days) / 7; // 데이타를 7일 씩 자름
    return $collection->split($split);
  }

}

class Day {
  public $day;

  public function __construct(
    $day=null
  ){
    $this->day = $day;
  }

  public function setObject($obj) {
    $result = json_decode(json_encode($obj));
    foreach($result as $k => $v) {
      $this->{$k} = $v;
    }
  }
  public function __set($name, $value) {
    $this->{$name} = $value;
  }


}