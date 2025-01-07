<?php
namespace Pondol\Fortune\Services;

use Pondol\Fortune\Facades\Lunar;
class Manse {
  public $sl = 'solar'; // $lunar
  public $solar; // 양력
  public $lunar; // 음력
  public $leap = false; // 윤달여부
  public $ymd; // 생년워일 yyyymmdd
  public $hi = '0100'; // 생시 hhmm (예 1330, 13시 30분)
  public $year = ['ch'=>'', 'ko'=>''];
  public $month = ['ch'=>'', 'ko'=>''];
  public $day = ['ch'=>'', 'ko'=>''];
  public $hour = ['ch'=>'', 'ko'=>''];
  public $gender = 'M'; //M(Man) | W(Woman)
  public $korean_age; // 한국나이

  /**
   * 생년월일생시
   * @param $ymdhi = yyyymmddhhii
   */
  public function ymdhi($ymdhi) {
    $len = strlen($ymdhi);
    switch($len) {
      case 8: $this->ymd = $ymd; break;
      case 12: 
        preg_match ('/^([0-9]{8})([0-9]{4})$/', trim ($ymdhi), $match);
        list (, $ymd, $hi) = $match;
        $this->ymd = $ymd;
        $this->hi = $hi;
        break;
    }
    return $this;
  }

  /**
   * 양|음력
   * @param String $sl : solar | lunar
   */
  public function sl($sl) {
    $this->sl = $sl;
    return $this;
  }

  /**
   * 윤달여부
   *@param Boolean $leap : true | false 
   */
  public function leap($leap) {
    $this->leap = $leap;
    return $this;
  }

  public function gender($gender) {
    $this->gender = $gender;
    return $this;
  }

  public function create() {
    switch($this->sl) {
      case 'solar': 
        $this->solar = $this->ymd; 
        $manse = Lunar::tolunar($this->ymd);
        break;
      case 'lunar': 
        $this->lunar = $this->ymd; 
        $manse = Lunar::toSolar($this->ymd, $this->leap);
        break;
    }
    // manse를 이용하여 양/음력 날짜 세팅
    $this->set_solar_or_lunar($manse);
    // $this->year($manse->year)->month($manse->month)->day($manse->day);

   
    // echo 'this->solar: '.$this->solar.PHP_EOL;
    $dayfortune = Lunar::dayfortune ($this->solar, $this->hi);

    // print_r($dayfortune);
    $this->year = (object)['ch'=>$dayfortune->hyear, 'ko'=>$dayfortune->year];
    $this->month = (object)['ch'=>$dayfortune->hmonth, 'ko'=>$dayfortune->month];
    $this->day = (object)['ch'=>$dayfortune->hday, 'ko'=>$dayfortune->day];
    $this->hour = (object)['ch'=>$dayfortune->hhour, 'ko'=>$dayfortune->hour];
    $this->korean_age = date('Y') - substr($this->solar, 0, 4) + 1;
    return $this;
  }

  public function set_solar_or_lunar($manse) {
    $ymd = str_pad($manse->year, 4, '0', STR_PAD_LEFT)
      .str_pad($manse->month, 2, '0', STR_PAD_LEFT)
      .str_pad($manse->day, 2, '0', STR_PAD_LEFT);

    switch($this->sl) {
      case 'solar': $this->lunar = $ymd ; break;
      case 'lunar': $this->solar = $ymd ; break;
    }
  }

  public function seasonal_division($ymd) {
    return Lunar::seasonal_division($ymd);
  }

  /** 만세에서  천간 가져오기 
  *@param String $str hour | day | month | year 
  */
  public function get_h($str) {

    return mb_substr($this->{$str}->ch, 0, 1);
  }

  /**
   * 만세에서 지지 가져오기
   */
  public function get_e($str) {
    return mb_substr($this->{$str}->ch, 1, 1);
  }


  /**
   * oheng 구하기
   */
  public function oheng() {
    $oheng = new Oheng();
    $this->oheng = $oheng->withManse($this);
    // $callback($oheng);
    return $this;
  }

  /**
   * 12신살 구하기
   */
  public function sinsal12() {
    $sinsal12 = new Sinsal12();
    $this->sinsal12 = $sinsal12->withManse($this);
    return $this;
  }

  /**
   * 12운성 구하기
   */
  public function woonsung12() {
    $woonsung12 = new Woonsung12();
    $this->woonsung12 = $woonsung12->withManse($this);
    return $this;
  }

  /**
   * 10신 구하기
   */
  public function zizangan() {
    $zizangan = new Zizangan();
    $this->zizangan = $zizangan->withManse($this);
    return $this;
  }

   /**
   * 신살 구하기
   */
  public function sinsal() {
    $sinsal = new Sinsal();
    // $this->sinsal = $sinsal->withManse($this)->cheneul();
    $this->sinsal = $sinsal->withManse($this)->all();
    return $this;
  }

  /**
   * 대운구하기
   */
  public function daewoon() {
    $daewoon = new DaeWoon();
    $this->daewoon = $daewoon->withManse($this);
    return $this;
  }
}