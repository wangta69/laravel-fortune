<?php
namespace Pondol\Fortune\Services;

/**
 * 12 신살 구하기
 * 12신살은 년지(年支)나 일지(日支)을 기준으로 삼는데, 인(寅)의 경우를 남겨봅니다. 
 * 출생년의 년지(年支=띠)로 보고, 
 * 출생일의 일지(日支)를 보는 것도 참고하셔야만 합니다.  
 */

class Sinsal12
{

  public $year_e;
  public $month_e;
  public $day_e;
  public $hour_e;
 
  public function withManse($manse) {
    $this->hour_e = $this->cal($manse->get_e('year'), $manse->get_e('hour'));
    $this->day_e = $this->cal($manse->get_e('year'), $manse->get_e('day'));
    $this->month_e = $this->cal($manse->get_e('year'), $manse->get_e('month'));
    $this->year_e = $this->cal($manse->get_e('year'), $manse->get_e('year'));

    return $this;
  }

  private  function cal($default_e, $e) {
    switch($default_e) {
      case '巳': case '酉': case '丑':
        switch($e) {
          case '寅': $sal = '겁살'; break;
          case '卯': $sal = '재살'; break;
          case '辰': $sal = '천살'; break;
          case '巳': $sal = '지살'; break;
          case '午': $sal = '도화'; break;
          case '未': $sal = '월살'; break;
          case '申': $sal = '망신'; break;
          case '酉': $sal = '장성'; break;
          case '戌': $sal = '반안'; break;
          case '亥': $sal = '역마'; break;
          case '子': $sal = '육해'; break;
          case '丑': $sal = '화개'; break;
        }
        break;
      case '申': case '子': case '辰':
        switch($e) {
          case '寅': $sal = '역마'; break;
          case '卯': $sal = '육해'; break;
          case '辰': $sal = '화개'; break;
          case '巳': $sal = '겁살'; break;
          case '午': $sal = '재살'; break;
          case '未': $sal = '천살'; break;
          case '申': $sal = '지살'; break;
          case '酉': $sal = '도화'; break;
          case '戌': $sal = '월살'; break;
          case '亥': $sal = '망신'; break;
          case '子': $sal = '장성'; break;
          case '丑': $sal = '반안'; break;
        }
        break;
      case '亥': case '卯': case '未':
        switch($e) {
          case '寅': $sal = '망신'; break;
          case '卯': $sal = '장성'; break;
          case '辰': $sal = '반안'; break;
          case '巳': $sal = '역마'; break;
          case '午': $sal = '육해'; break;
          case '未': $sal = '화개'; break;
          case '申': $sal = '겁살'; break;
          case '酉': $sal = '재살'; break;
          case '戌': $sal = '천살'; break;
          case '亥': $sal = '지살'; break;
          case '子': $sal = '도화'; break;
          case '丑': $sal = '월살'; break;
        }
        break;
      case '寅': case '午': case '戌':
        switch($e) {
          case '寅': $sal = '지살'; break;
          case '卯': $sal = '도화'; break;
          case '辰': $sal = '월살'; break;
          case '巳': $sal = '망신'; break;
          case '午': $sal = '장성'; break;
          case '未': $sal = '반안'; break;
          case '申': $sal = '역마'; break;
          case '酉': $sal = '육해'; break;
          case '戌': $sal = '화개'; break;
          case '亥': $sal = '겁살'; break;
          case '子': $sal = '재살'; break;
          case '丑': $sal = '천살'; break;
        }
      break;
    }
    
    return $sal;
  }

  function kor_to_ch($kor) {
    switch($kor) {
        // 12 살
        case '지살': return '地殺';
        case '도화': return '桃花';
        case '월살': return '月殺';
        case '망신': return '亡身';
        case '장성': return '將星';
        case '반안': return '攀鞍';
        case '역마': return '驛馬';
        case '육해': return '六害';
        case '화개': return '華蓋';
        case '겁살': return '劫殺';
        case '재살': return '災殺';
        case '천살': return '天殺';
    }
  }

}



