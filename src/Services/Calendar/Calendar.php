<?php
namespace Pondol\Fortune\Services\Calendar;
use Carbon\Carbon;
use Pondol\Fortune\Facades\Lunar;
use Pondol\Fortune\Facades\Manse;
// use Pondol\Fortune\Facades\Manse;

/**
 * 12실살을 제외한 기타 길신 및 흉신 구하기
 * 일지기준으로 하면 집안일
 * 년지기준으로 하면 바깥일
 */

class Calendar
{

  /**
   * 음력달력출력
   * @param String $yyyymm 202501 (2025년 01월)
   */
  public function lunarCalendar($yyyymm) {
    $lunarCalendar = new LunarCalendar;
    return $lunarCalendar->cal($yyyymm);
  }

  /**
   * 특정년의 절기 출력
   */
  public function season24Calendar($yyyy) {
    $season24Calendar = new Season24Calendar;
    return $season24Calendar->cal($yyyy);
  }

  /**
   * 월별 이사택일
   */
  public function moveCalendar($manse, $yyyymm) {
    $moveCalendar = new MoveCalendar;
    return $moveCalendar->cal($manse, $yyyymm);
  }

  /**
   * 월별 결혼 택일
   */
  public function marriage($manse, $p_manse, $yyyymm) {
    $marriageCalendar = new MarriageCalendar;
    return $marriageCalendar->cal($manse, $p_manse, $yyyymm);
  }
  
  /**
   *  특정년의 3재 출력
   */
  public function samjae($yyyy) {
    $samjae = new Samjae;
    return $samjae->cal($yyyy);
    

  }
}