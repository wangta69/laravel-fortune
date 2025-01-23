<?php
namespace Pondol\Fortune\Services;

/**
 * 지장간을 찾는다
 * 지장간은 지지에 포함된 천간을 찾는 것이다.
 */

class Zizangan
{

  public $year;
  public $month;
  public $day;
  public $hour;

  private $zizangan = [
    '子' => '壬癸', // 임계
    '丑' => '癸辛己', // 계신기
    '寅' => '戊丙甲', // 무병값
    '卯' => '甲乙', // 갑을
    '辰' => '乙癸戊', // 을계무
    '巳' => '戊庚丙', // 무경병
    '午' => '丙己丁', // 병기정
    '未' => '丁乙己', // 정을기
    '申' => '戊壬庚', // 기무임경
    '酉' => '庚辛', // 경신
    '戌' => '辛丁戊', // 신정무
    '亥' => '戊甲壬', // 무갑임
  ];


  public function withSaju($saju) {
    $this->hour = $this->cal($this->zizangan[$saju->get_e('hour')],  $saju->get_h('day'));
    $this->day = $this->cal($this->zizangan[$saju->get_e('day')], $saju->get_h('day'));
    $this->month = $this->cal($this->zizangan[$saju->get_e('month')], $saju->get_h('day'));
    $this->year = $this->cal($this->zizangan[$saju->get_e('year')], $saju->get_h('day'));
    return $this;
  }

  /**
   * 사주화 정보는 e_code의 1 이 인으로 시작하는데 프로그램상 자 를 1로 변경
   */

   private function cal($str, $day_h) {
    $ret = [];
    for ($i=0; $i<mb_strlen($str); $i++){
      $he = mb_substr($str, $i, 1);
      $sipsin = Sipsin::cal($day_h, $he, 'h');
      array_push($ret, (object)['h'=>$he, 'sipsin'=>$sipsin]);
    }

    return $ret;
  }

}



