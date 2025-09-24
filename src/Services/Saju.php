<?php
namespace Pondol\Fortune\Services;

use Pondol\Fortune\Facades\Lunar;
class Saju {
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
    $ymdhi = str_replace(['-', ':'], '', trim($ymdhi));
    $len = strlen($ymdhi);
    $typeof = gettype($ymdhi);

    
    switch($len) {
      case 8: $ymd = $ymdhi; break;
      case 12: 
        preg_match ('/^([0-9]{8})([0-9]{4})$/', trim ($ymdhi), $match);
        list (, $ymd, $hi) = $match;
        $this->hi = $hi;
        break;
      default: // 8자리나 12자리가 아닌 모든 경우를 처리
        throw new \Exception("Invalid date length. Expected 8 or 12 characters, but got " . $len);
    }
    preg_match ('/^([0-9]{4})([0-9]{2})([0-9]{2})$/', trim ($ymd), $match);
    if (count($match) < 4) { // preg_match가 실패한 경우에 대한 방어 코드
      throw new \Exception("Failed to parse ymd: " . $ymd);
    }
    list (, $y, $m, $d) = $match;
    $this->ymd = $y.'-'.$m.'-'.$d;

    return $this;
  }

  public function ymd($ymd) {
    return $this->ymdhi($ymd);
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
        $saju = Lunar::ymd($this->ymd)->hi($this->hi)->tolunar()->sajugabja()->create();
        $this->lunar = $saju->lunar; 
        break;
      case 'lunar': 
        $this->lunar = $this->ymd; 
        $saju = Lunar::ymd($this->ymd)->hi($this->hi)->tosolar($this->leap)->sajugabja()->create();
        $this->solar = $saju->solar; 
        
        break;
    }
    

    $this->year = $saju->gabja->year;
    $this->month = $saju->gabja->month;
    $this->day = $saju->gabja->day;
    $this->hour = $saju->gabja->hour;

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

   public function get_h_serial($str) {
    return $this->h_to_serial($this->get_h($str));
  }

  /**
   * 만세에서 지지 가져오기
   */
  public function get_e($str) {
    return mb_substr($this->{$str}->ch, 1, 1);
  }

  public function get_e_serial($str) {
    return $this->e_to_serial($this->get_e($str));
  }

  public function get_e_wolgun($str) {
    return $this->e_to_wolgun($this->get_e($str));
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
    $this->oheng = $oheng->withSaju($this);
    // $callback($oheng);
    return $this;
  }

  /**
   * 12신살 구하기
   */
  public function sinsal12() {
    $sinsal12 = new Sinsal12();
    $this->sinsal12 = $sinsal12->withSaju($this);
    return $this;
  }

  /**
   * 12운성 구하기
   */
  public function woonsung12() {
    $woonsung12 = new Woonsung12();
    $this->woonsung12 = $woonsung12->withSaju($this);
    return $this;
  }

   /**
   * 10신 구하기
   */
  public function sipsin() {
    $sipsin = new Sipsin();
    $this->sipsin = $sipsin->withSaju($this);
    return $this;
  }

  /**
   * 지장간 구하기
   */
  public function zizangan() {
    $zizangan = new Zizangan();
    $this->zizangan = $zizangan->withSaju($this);
    return $this;
  }

  

  /**
   * 길신/흉신 구하기
   * 위의 신살 구하기에서 결과를 받아와서 년월일시로 배열을 재정리
   */
  public function sinsal() {
    $sinsal = new Sinsal();
    $this->sinsal = $sinsal->withSaju($this)->sinsal()->create();
    return $this;
  }

  /**
   * 대운구하기
   */
  public function daewoon() {
    $daewoon = new DaeWoon();
    $this->daewoon = $daewoon->withSaju($this);
    return $this;
  }

  /**
   * 세운구하기
   */
  public function saewoon() {
    $saewoon = new SaeWoon();
    $this->saewoon = $saewoon->withSaju($this);
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

  private function e_to_serial($g, $pad=false) {
    switch($g) {
      case '子': $no = 1; break;// 자
      case '丑': $no = 2; break;// 축
      case '寅': $no = 3; break; // 인
      case '卯': $no = 4; break; // 묘
      case '辰': $no = 5; break; // 진
      case '巳': $no = 6; break; // 사
      case '午': $no = 7; break; // 오
      case '未': $no = 8; break; // 미
      case '申': $no = 9; break; // 신
      case '酉': $no = 10; break; //유
      case '戌': $no = 11; break; // 술
      case '亥': $no = 12; break; //해
    }

    if ($pad == true) {
      $no = str_pad($no, 2, '0', STR_PAD_LEFT);
    }
    return $no;
  }

  /**
   * 월건을 볼때는 11월이 자 가 되고 1월이 인이 된다.
   */
  private function e_to_wolgun($g, $pad=false) {
    switch($g) {
      case '子': $no = 11; break;// 자
      case '丑': $no = 12; break;// 축
      case '寅': $no = 1; break; // 인
      case '卯': $no = 2; break; // 묘
      case '辰': $no = 3; break; // 진
      case '巳': $no = 4; break; // 사
      case '午': $no = 5; break; // 오
      case '未': $no = 6; break; // 미
      case '申': $no = 7; break; // 신
      case '酉': $no = 8; break; //유
      case '戌': $no = 9; break; // 술
      case '亥': $no = 10; break; //해
    }

    if ($pad == true) {
      $no = str_pad($no, 2, '0', STR_PAD_LEFT);
    }
    return $no;
  }


  private function h_to_serial($h, $pad=false){
    switch($h){
      case '甲' : $no = 1; break;
      case '乙' : $no = 2; break;
      case '丙' : $no = 3; break;
      case '丁' : $no = 4; break;
      case '戊' : $no = 5; break;
      case '己' : $no = 6; break;
      case '庚' : $no = 7; break;
      case '辛' : $no = 8; break;
      case '壬' : $no = 9; break;
      case '癸' : $no = 10; break;
    }
    if ($pad == true) {
      $no = str_pad($no, 2, '0', STR_PAD_LEFT);
    }
    return $no;
  }

}