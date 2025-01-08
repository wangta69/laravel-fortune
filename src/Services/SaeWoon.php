<?php
namespace Pondol\Fortune\Services;
use Carbon\Carbon;
use Pondol\Fortune\Facades\Manse;

/**
 * 12실살을 제외한 기타 길신 및 흉신 구하기
 * 일지기준으로 하면 집안일
 * 년지기준으로 하면 바깥일
 */

class SaeWoon
{


  private $gender, $birth_time, $lunar_ymd, $month_h, $year_h, $month_e, $direction;
  /**
  *
  *@param String $gender : W, M (from profile)
  *@param String $birth_ym : hhmm (from profile)
  *@param string $lunar_ymd  음력 생년월일 (from manse)
  */
  // function daeun($gender, $birth_time, $lunar_ymd, $month_h, $year_h, $month_e){ //
  function withManse($manse){ //

    $this->month_h = $manse->get_h('month');
    $this->year_h = $manse->get_h('year');
    $this->month_e = $manse->get_e('month');
    $this->gender = $manse->gender;

    $start = substr($manse->lunar, 0, 4);
    $end = $start + 80;
    $i = 0;
    $this->saewoon = [];
    for($year = $start;  $year < $end;  $year++) {
      $result = calgabja($year);
          
      $this->saewoon_h[$i] = $result->h;
      $this->saewoon_e[$i] = $result->e;
      $this->age[$i] = $i + 1;
      $this->year[$i] = $year;
      $i++;
    }

    $this->sipsin_e = $this->sipsin_e($manse);
    $this->woonsung_e = $this->woonsung_e($manse);

    return $this;
  }

  //  세운의 지지 10성 구하기
  private function sipsin_e($manse) {
    $sipsin_e = [];
    foreach($this->saewoon_e  as $k => $v) {
      // 지지의 10성
      $sipsin_e[$k]= SipSin::cal($manse->get_h('day'), $v, 'e');
    }
    return $sipsin_e;
  }

  // 세운의 지지 12운성 구하기
  private function woonsung_e($manse) {
    $woonsung_e = [];
    foreach($this->saewoon_e  as $k => $v) {
      // 지지의 12운성
      $woonsung_e[$k]= Woonsung12::cal($manse->get_h('day'), $v);
    }
    return $woonsung_e;
  }

  /**
   * 대운은 남/녀 및 천간의 년에 따라 방향이 달라진다.
   */
  // private function get_direction() {
  //   switch($this->gender) {
  //     case 'M':
  //       switch($this->year_h) {
  //         case '甲': case '丙': case '戊': case '庚': case '壬': 
  //           return 'forward';
  //         default: return 'reverse'; break;
  //       }
  //       break;
  //     case 'W':
  //       switch($this->year_h) {
  //         case '甲': case '丙': case '戊': case '庚': case '壬': 
  //           return 'reverse';
  //         default: return 'forward';
  //       }
  //       break;
  //   }
  // }


  /**
  * 대운은 10년마다 한번씩 온다.
  */
  // private function daeunAge($manse) { // $lunar_ymd, $birth_time, $direction

  //   // 절기는 태양력을 기준으로 처리한다.
  //   // 생월의 절입시간을 구한다.
  //   $seasonal_division = $manse->seasonal_division($manse->solar);
  //   $enter_sd = $seasonal_division->ccenter; // 절입 (입력된 날짜 기준 이전 절입)
  //   $next_sd = $seasonal_division->nenter; // 절입 (입력된 날짜 기준 이후 절입)

  //   // 입력된 날짜가 절기와 같은 경우 시간에 따라서 절입이 endter, next에 존재할 수 있으므로 두개를 동시에 비교해 준다.
  //   $enter_jeolip_ymd = $enter_sd->year.pad_zero($enter_sd->month).pad_zero($enter_sd->day);
  //   $enter_jeolip_hm = pad_zero($enter_sd->hour).pad_zero($enter_sd->min);
  //   $enter_has_jeolip = $manse->solar == $enter_jeolip_ymd ? true : false;

  //   $next_jeolip_ymd = $next_sd->year.pad_zero($next_sd->month).pad_zero($next_sd->day);
  //   $next_jeolip_hm = pad_zero($next_sd->hour).pad_zero($next_sd->min);

  //   $next_has_jeolip = $manse->solar == $next_jeolip_ymd ? true : false;

  //   $has_jeolip = false;
  //   $jeolip_hm = '';
  //   if($enter_has_jeolip) { // next와 비교한다.
  //     $has_jeolip = true;
  //     $jeolip_hm = $enter_jeolip_hm;
  //   } else if($next_has_jeolip) { // next와 비교한다.
  //     $has_jeolip = true;
  //     $jeolip_hm = $next_jeolip_hm;
  //   }

  //   // 나의 양력 생일이 절입에 포한된 경우 절입시간까지 고려한다.
  //   if (
  //     ($has_jeolip && ($manse->hi < $jeolip_hm) && ($this->direction == 'forward')) ||
  //     ($has_jeolip && ($manse->hi > $jeolip_hm) && ($this->direction == 'reverse'))) 
  //   {
  //     $mok = 1;
  //   } else {
  //     $solar_ymd = Carbon::createFromFormat('Ymd', $manse->solar);
  //     if ($this->direction == 'forward') {
  //       $jeolip_ymd = Carbon::createFromFormat('Ymd', $next_jeolip_ymd);
  //       $gap = $jeolip_ymd->diffInDays($solar_ymd);;
  //     } elseif ($this->direction == 'reverse') {
  //       $jeolip_ymd = Carbon::createFromFormat('Ymd', $enter_jeolip_ymd);
  //       $gap = $solar_ymd->diffInDays($jeolip_ymd);;
  //     }

  //     $mok = intval($gap / 3);       ////////////대운 숫자
  //     $nam = $gap % 3;
  //     if ($nam == 2) {$mok = $mok + 1;}
  //   }

  //   $age[0] = $mok;
  //   $year[0] = substr($manse->solar, 0, 4) + $mok - 1;

  //   for($i=1; $i < 10; $i++) {
  //     $j = $i * 10;
  //     $age[$i] = $age[0] + $j;
  //     $year[$i] = $year[0] + $j;
  //   }
  //   $this->age = $age;
  //   $this->year = $year;
  // }
}