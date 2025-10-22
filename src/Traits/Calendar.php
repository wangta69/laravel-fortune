<?php

namespace Pondol\Fortune\Traits;

use Pondol\Fortune\Facades\Lunar;

trait Calendar
{
    public $date = '';
    public $days = [];

    // 각각의 새해를 보는 입장이 다르므로 년도는 양력기준으로 월/일은 명리학을 기준으로 표기

    public function _create($yyyymm)
    {
        preg_match('/^([0-9]{4})([0-9]{2})$/', trim($yyyymm), $match);
        list(, $year, $month) = $match;

        $this->date = mktime(0, 0, 0, $month, 1, $year);


        $start_week = date('w', $this->date); // 1. 시작 요일
        $total_day = date('t', $this->date); // 2. 현재 달의 총 날짜

        $this->days = [];
        // 시작하는 요일 이전의 것은 공백처리
        for ($i = 0; $i < $start_week; $i++) {
            $this->days[] = new Day();
        }
        // 실제 날짜 채우기
        for ($i = 1; $i <= $total_day; $i++) {
            // Day 객체를 생성할 때 날짜와 함께 모든 정보를 전달
            $current_date_str = $year . '-' . $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $dayInfo = Lunar::ymd($current_date_str)->tolunar()->sajugabja()->create();

            $this->days[] = new Day($i, $dayInfo);
        }
        // 날짜가 끝나는 마지막 요일 까지 공백 처리
        $remaining = 7 - (count($this->days) % 7);
        if ($remaining < 7) {
            for ($i = 0; $i < $remaining; $i++) {
                $this->days[] = new Day();
            }
        }

        return $this;
    }

    // 1주 7일 단위로 배열을 만들어 리턴
    public function splitPerWeek()
    {
        $collection = collect($this->days);
        $split = count($this->days) / 7; // 데이타를 7일 씩 자름
        $this->days = $collection->split($split);
        return $this;
    }

}

class Day
{
    public $day;
    public $solar;
    public $lunar;
    public $gabja;

    public function __construct($day = null, $dayInfo = null)
    {
        $this->day = $day;

        // 생성자에서 기본 정보를 프로퍼티에 할당
        if ($dayInfo) {
            $this->solar = $dayInfo->solar;
            $this->lunar = $dayInfo->lunar;
            $this->gabja = $dayInfo->gabja;
            // 필요하다면 $dayInfo의 다른 정보들도 여기에 할당
        }
    }

    public function setObject($obj)
    {
        // 객체의 모든 public 프로퍼티를 현재 객체에 복사
        foreach (get_object_vars($obj) as $key => $value) {
            $this->{$key} = $value;
        }
    }


    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }


}
