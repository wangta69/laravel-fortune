<?php
namespace Pondol\Fortune\Services\Calendar;
use Carbon\Carbon;
use Pondol\Fortune\Facades\Lunar;

/**
 * 12실살을 제외한 기타 길신 및 흉신 구하기
 * 일지기준으로 하면 집안일
 * 년지기준으로 하면 바깥일
 */

class Season24Calendar
{

  /**
   * 음력달력출력
   * @param String $yyyymm 202501 (2025년 01월)
   */
  public function cal($year) {

    $season24 = [];
    for ($i = 0; $i < 12; $i++) {
      $j = substr('0'+ ($i+1), -2);
      $j = pad_zero($i+1, 2);
      $season = Lunar::seasonal_division($year.$j.'01')->create();

      // print_r($season);
      foreach($season->seasons as $v) {
        if(!in_array($v, $season24)) { //  && $v->year == $year
          $season24[$v->name->ko] = $v;
        }
      }
    }

    return $season24;
  }


}


