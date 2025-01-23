<?php
namespace Pondol\Fortune\Services;
use Carbon\Carbon;
use Pondol\Fortune\Facades\Saju;

/**
 * 12실살을 제외한 기타 길신 및 흉신 구하기
 * 일지기준으로 하면 집안일
 * 년지기준으로 하면 바깥일
 */

class DaeWoon
{


  private $gender, $birth_time, $lunar_ymd, $month_h, $year_h, $month_e, $direction;
  /**
  *
  *@param String $gender : W, M (from profile)
  *@param String $birth_ym : hhmm (from profile)
  *@param string $lunar_ymd  음력 생년월일 (from saju)
  */
  // function daeun($gender, $birth_time, $lunar_ymd, $month_h, $year_h, $month_e){ //
  function withSaju($saju){ //

    $this->month_h = $saju->get_h('month');
    $this->year_h = $saju->get_h('year');
    $this->month_e = $saju->get_e('month');
    $this->gender = $saju->gender;


    $this->direction = $this->get_direction();
    
    //$gabja_array = array('甲子','乙丑','丙寅','丁卯','戊辰','己巳','庚午','辛未','壬申','癸酉','甲戌','乙亥','丙子','丁丑','戊寅','己卯','庚辰','辛巳','壬午','癸未','甲申','乙酉','丙戌','丁亥','戊子','己丑','庚寅','辛卯','壬辰','癸巳','甲午','乙未','丙申','丁酉','戊戌','己亥','庚子','辛丑','壬寅','癸卯','甲辰','乙巳','丙午','丁未','戊申','己酉','庚戌','辛亥','壬子','癸丑','甲寅','乙卯','丙辰','丁巳','戊午','己未','庚申','辛酉','壬戌','癸亥');


    // daeun_u : 천간을 기준으로 하는 대운
    // daeun_l : 지지를 기준으로 하는 대운
    // $he = $this->month_h.$this->month_e;

    switch($this->direction) {
      case 'forward':
        // 기분배열을 천간월 만큰 로테이트 시킨다.
        $arr1 = ['乙','丙','丁','戊','己','庚','辛','壬','癸','甲'];
        $this->daeun_h = arr_forward_rotate($arr1, h_to_serial($this->month_h));
        $arr2 = ['丑','寅','卯','辰','巳','午','未','申','酉','戌','亥','子'];
        $this->daeun_e = array_slice(arr_forward_rotate($arr2, e_to_serial($this->month_e)), 0, 10);
        break;
      case 'reverse':

        $arr1 = ['癸','壬','辛','庚','己','戊','丁','丙','乙','甲'];
        $this->daeun_h = arr_reverse_rotate($arr1, h_to_serial($this->month_h));
        // 기준배열을 진간월 만큰 로테이트 시키되 앞자리 10개만 갸져온다.
        $arr2 = ['亥','戌','酉','申','未','午','巳','辰','卯','寅','丑','子'];
        $this->daeun_e = array_slice(arr_reverse_rotate($arr2, e_to_serial($this->month_e)), 0, 10);
        break;
    }

     // 대운 나이 구하기
    $this->daeunAge($saju); // $this->daeunAge($lunar_ymd, $birth_time, $direction)

    $this->sipsin_e = $this->sipsin_e($saju);
    $this->woonsung_e = $this->woonsung_e($saju);
    return $this;
  }

  // 대운의 지지 10성 구하기
  private function sipsin_e($saju) {
    $sipsin_e = [];
    foreach($this->daeun_e  as $k => $v) {
      // 지지의 10성
      $sipsin_e[$k]= SipSin::cal($saju->get_h('day'), $v, 'e');
    }
    return $sipsin_e;
  }

  // 대운의 지지 12운성 구하기
  private function woonsung_e($saju) {
    $woonsung_e = [];
    foreach($this->daeun_e  as $k => $v) {
      // 지지의 12운성
      $woonsung_e[$k]= Woonsung12::cal($saju->get_h('day'), $v);
    }
    return $woonsung_e;
  }

  /**
   * 대운은 남/녀 및 천간의 년에 따라 방향이 달라진다.
   */
  private function get_direction() {
    switch($this->gender) {
      case 'M':
        switch($this->year_h) {
          case '甲': case '丙': case '戊': case '庚': case '壬': 
            return 'forward';
          default: return 'reverse'; break;
        }
        break;
      case 'W':
        switch($this->year_h) {
          case '甲': case '丙': case '戊': case '庚': case '壬': 
            return 'reverse';
          default: return 'forward';
        }
        break;
    }
  }


  /**
  * 대운은 10년마다 한번씩 온다.
  */
  private function daeunAge($saju) { // $lunar_ymd, $birth_time, $direction

    // 절기는 태양력을 기준으로 처리한다.
    // 생월의 절입시간을 구한다.
    $seasonal_division = $saju->seasonal_division($saju->solar);
    $enter_sd = $seasonal_division->ccenter; // 절입 (입력된 날짜 기준 이전 절입)
    $next_sd = $seasonal_division->nenter; // 절입 (입력된 날짜 기준 이후 절입)

    // 입력된 날짜가 절기와 같은 경우 시간에 따라서 절입이 endter, next에 존재할 수 있으므로 두개를 동시에 비교해 준다.
    $enter_jeolip_ymd = $enter_sd->year.pad_zero($enter_sd->month).pad_zero($enter_sd->day);
    $enter_jeolip_hm = pad_zero($enter_sd->hour).pad_zero($enter_sd->min);
    $enter_has_jeolip = $saju->solar == $enter_jeolip_ymd ? true : false;

    $next_jeolip_ymd = $next_sd->year.pad_zero($next_sd->month).pad_zero($next_sd->day);
    $next_jeolip_hm = pad_zero($next_sd->hour).pad_zero($next_sd->min);

    $next_has_jeolip = $saju->solar == $next_jeolip_ymd ? true : false;

    $has_jeolip = false;
    $jeolip_hm = '';
    if($enter_has_jeolip) { // next와 비교한다.
      $has_jeolip = true;
      $jeolip_hm = $enter_jeolip_hm;
    } else if($next_has_jeolip) { // next와 비교한다.
      $has_jeolip = true;
      $jeolip_hm = $next_jeolip_hm;
    }

    // 나의 양력 생일이 절입에 포한된 경우 절입시간까지 고려한다.
    if (
      ($has_jeolip && ($saju->hi < $jeolip_hm) && ($this->direction == 'forward')) ||
      ($has_jeolip && ($saju->hi > $jeolip_hm) && ($this->direction == 'reverse'))) 
    {
      $mok = 1;
    } else {
      $solar_ymd = Carbon::createFromFormat('Ymd', $saju->solar);
      if ($this->direction == 'forward') {
        $jeolip_ymd = Carbon::createFromFormat('Ymd', $next_jeolip_ymd);
        $gap = $jeolip_ymd->diffInDays($solar_ymd);;
      } elseif ($this->direction == 'reverse') {
        $jeolip_ymd = Carbon::createFromFormat('Ymd', $enter_jeolip_ymd);
        $gap = $solar_ymd->diffInDays($jeolip_ymd);;
      }

      $mok = intval($gap / 3);       ////////////대운 숫자
      $nam = $gap % 3;
      if ($nam == 2) {$mok = $mok + 1;}
    }

    $age[0] = $mok;
    $year[0] = substr($saju->solar, 0, 4) + $mok - 1;

    for($i=1; $i < 10; $i++) {
      $j = $i * 10;
      $age[$i] = $age[0] + $j;
      $year[$i] = $year[0] + $j;
    }
    $this->age = $age;
    $this->year = $year;
  }

  /**
   * 내 생일(양력)이 절립과 같으면 시분을 리턴하고 아니면 false return 
   

  public function sinsal12($my_day_e, $td1_umyear_e) {

// 도화, 
      $sinsals = ['지살', '도화', '월살', '망신', '장성', '반안', '역마', '육해', '화개', '겁살', '재살', '천살'];
      $unsungs = ['장생', '목욕', '관대', '건록', '제왕', '쇠', '병', '사', '묘', '절', '태', '양'];

      $start_index = 0;
      switch($my_day_e) {
          case '寅': case '午': case '戌': // 인오술
              switch($td1_umyear_e) {
                  case '寅': $start_index = 0; break;
                  case '卯': $start_index = 1; break;
                  case '辰': $start_index = 2; break;
                  case '巳': $start_index = 3; break;
                  case '午': $start_index = 4; break;
                  case '未': $start_index = 5; break;
                  case '申': $start_index = 6; break;
                  case '酉': $start_index = 7; break;
                  case '戌': $start_index = 8; break;
                  case '亥': $start_index = 9; break;
                  case '子': $start_index = 10; break;
                  case '丑': $start_index = 11; break;
              }
              break;
          case '巳': case '酉': case '丑': // 사유축
              switch($td1_umyear_e) {
                  case '寅': $start_index = 9; break;
                  case '卯': $start_index = 10; break;
                  case '辰': $start_index = 11; break;
                  case '巳': $start_index = 0; break;
                  case '午': $start_index = 1; break;
                  case '未': $start_index = 2; break;
                  case '申': $start_index = 3; break;
                  case '酉': $start_index = 4; break;
                  case '戌': $start_index = 5; break;
                  case '亥': $start_index = 6; break;
                  case '子': $start_index = 7; break;
                  case '丑': $start_index = 8; break;
              }
              break;
          case '申': case '子': case '辰': // 신자진
              switch($td1_umyear_e) {
                  case '寅': $start_index = 6; break;
                  case '卯': $start_index = 7; break;
                  case '辰': $start_index = 8; break;
                  case '巳': $start_index = 9; break;
                  case '午': $start_index = 10; break;
                  case '未': $start_index = 11; break;
                  case '申': $start_index = 0; break;
                  case '酉': $start_index = 1; break;
                  case '戌': $start_index = 2; break;
                  case '亥': $start_index = 3; break;
                  case '子': $start_index = 4; break;
                  case '丑': $start_index = 5; break;
              }
              break;
          case '亥': case '卯': case '未': // 해묘미
              switch($td1_umyear_e) {
                  case '寅': $start_index = 3; break;
                  case '卯': $start_index = 4; break;
                  case '辰': $start_index = 5; break;
                  case '巳': $start_index = 6; break;
                  case '午': $start_index = 7; break;
                  case '未': $start_index = 8; break;
                  case '申': $start_index = 9; break;
                  case '酉': $start_index = 10; break;
                  case '戌': $start_index = 11; break;
                  case '亥': $start_index = 0; break;
                  case '子': $start_index = 1; break;
                  case '丑': $start_index = 2; break;
              }
              break;

      }
      $rtn = ['sal'=>[], 'unsung'=>[]];
      for($i = 0; $i < 12; $i++) {
          array_push( $rtn['sal'], $sinsals[$start_index]);
          array_push( $rtn['unsung'], $unsungs[$start_index]);
          $start_index = ($start_index + 1) % 12;
      }

      return $rtn;
  }
*/

  



}