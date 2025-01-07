<?php
namespace Pondol\Fortune\Services;

class Oheng
{

  public $year_h;
  public $year_e;
  public $month_h;
  public $month_e;
  public $day_h;
  public $day_e;
  public $hour_h;
  public $hour_e;
 
  public function withManse($manse) {
    $this->year_h = $this->oheng($manse->get_h('year'));
    $this->year_e = $this->oheng($manse->get_e('year'));
    $this->month_h = $this->oheng($manse->get_h('month'));
    $this->month_e = $this->oheng($manse->get_e('month'));
    $this->day_h = $this->oheng($manse->get_h('day'));
    $this->day_e = $this->oheng($manse->get_e('day'));
    $this->hour_h = $this->oheng($manse->get_h('hour'));
    $this->hour_e = $this->oheng($manse->get_e('hour'));

    return $this;
  }

  private function oheng($he){
    $Oheng = [];
    switch($he){
      case '甲' : case '寅' :
        $Oheng= $this->oheng_lang(0);
        $Oheng['flag'] = '+';
        break;
      case '乙' :  case '卯' :
        $Oheng= $this->oheng_lang(0);
        $Oheng['flag'] = '-';
        break;
      case '丙' : case '巳' :
        $Oheng= $this->oheng_lang(1);
        $Oheng['flag'] = '+';
        break;
      case '丁' : case '午' :
        $Oheng= $this->oheng_lang(1);
        $Oheng['flag'] = '-';
        break;
      case '戊' : case '辰' : case '戌' :
        $Oheng= $this->oheng_lang(2);
        $Oheng['flag'] = '+';
        break;
      case '己' : case '未' : case '丑' :
        $Oheng= $this->oheng_lang(2);
        $Oheng['flag'] = '-';
        break;
      case '庚' : case '申' :
        $Oheng= $this->oheng_lang(3);
        $Oheng['flag'] = '+';
        break;
      case '辛' : case '酉' :
        $Oheng= $this->oheng_lang(3);
        $Oheng['flag'] = '-';
        break;
      case '壬' : case '亥' :
        $Oheng= $this->oheng_lang(4);
        $Oheng['flag'] = '+';
        break;
      case '癸' : case '子' :
        $Oheng= $this->oheng_lang(4);
        $Oheng['flag'] = '-';
        break;
    }

    return (object)$Oheng;
  }


  private function oheng_lang($serial){
    $ch = ['木', '火', '土', '金', '水'];
    $ko = ['목', '화', '토', '금', '수'];
    $en = ['thu', 'tue', 'sat', 'fri', 'wed'];

    return ['ch'=>$ch[$serial], 'ko'=>$ko[$serial], 'en'=>$en[$serial]];
  }

}



