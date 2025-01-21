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
    // echo 'ymdhi:'.$ymdhi.PHP_EOL;
    $ymdhi = str_replace(['-', ':'], '', $ymdhi);
    $len = strlen($ymdhi);
    $typeof = gettype($ymdhi);
    // echo 'typeof: '.$typeof;
    switch($len) {
      case 8: $ymd = $ymdhi; break;
      case 12: 
        preg_match ('/^([0-9]{8})([0-9]{4})$/', trim ($ymdhi), $match);
        list (, $ymd, $hi) = $match;
        $this->hi = $hi;
        break;
    }
    preg_match ('/^([0-9]{4})([0-9]{2})([0-9]{2})$/', trim ($ymd), $match);
    list (, $y, $m, $d) = $match;
    $this->ymd = $y.'-'.$m.'-'.$d;

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
        $manse = Lunar::ymd($this->ymd)->hi($this->hi)->tolunar()->gabja()->create();
        $this->lunar = $manse->lunar; 
        break;
      case 'lunar': 
        $this->lunar = $this->ymd; 
        $manse = Lunar::ymd($this->ymd)->hi($this->hi)->tosolar($this->leap)->gabja()->create();
        $this->solar = $manse->solar; 
        
        break;
    }
    

    $this->year = $manse->gabja->year;
    $this->month = $manse->gabja->month;
    $this->day = $manse->gabja->day;
    $this->hour = $manse->gabja->hour;

    $this->korean_age = date('Y') - substr($this->solar, 0, 4) + 1;
    return $this;
  }


  public function seasonal_division($ymd) {
    return Lunar::seasonal_division($ymd)->create();
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
   * 만세력에서 60갑자 가져오기
   */
  public function get_he($str) {
    return $this->{$str}->ch;
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
  public function sipsin() {
    $sipsin = new Sipsin();
    $this->sipsin = $sipsin->withManse($this);
    return $this;
  }

  /**
   * 지장간 구하기
   */
  public function zizangan() {
    $zizangan = new Zizangan();
    $this->zizangan = $zizangan->withManse($this);
    return $this;
  }

   /**
   * 신살 구하기
   */
  // public function sinsal() {
  //   $sinsal = new Sinsal();
  //   // $this->sinsal = $sinsal->withManse($this)->cheneul();
  //   $this->sinsal = $sinsal->withManse($this)->all()->create();
  //   return $this;
  // }

  /**
   * 길신/흉신 구하기
   * 위의 신살 구하기에서 결과를 받아와서 년월일시로 배열을 재정리
   */
  public function goodbadsin() {
    $sinsal = new Sinsal();
    $this->goodbadsin = $sinsal->withManse($this)->goodbadsin()->create();
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

  /**
   * 세운구하기
   */
  public function saewoon() {
    $saewoon = new SaeWoon();
    $this->saewoon = $saewoon->withManse($this);
    return $this;
  }

  /**
   *  토정비결용 작괘 구하기
   */
  public function jakque($callback=null) {

    $jakque = new TojungJakque();
    if($callback) {
      $callback($jakque);
    }
    $this->jakque = $jakque->create($this);
    return $this;
  }
}