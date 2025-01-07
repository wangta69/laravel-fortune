<?php
namespace Pondol\Fortune\Services;

/**
 * 12 신살 구하기
 * 12신살은 년지(年支)나 일지(日支)을 기준으로 삼는데, 인(寅)의 경우를 남겨봅니다. 
 * 출생년의 년지(年支=띠)로 보고, 
 * 출생일의 일지(日支)를 보는 것도 참고하셔야만 합니다.  
 */

class Woonsung12
{

  public $year_e;
  public $month_e;
  public $day_e;
  public $hour_e;

  private static $woonsung = [
    '甲' => ['목욕', '관대', '건록', '제왕', '쇠', '병', '사', '묘', '절', '태', '양', '장생'], // 갑
    '乙' => ['병', '쇠', '제왕', '건록', '관대', '목욕', '장생', '양', '태', '절', '묘', '사'], // 을
    '丙' => ['태', '양', '장생', '목욕', '관대', '건록', '제왕', '쇠', '병', '사', '묘', '절'], // 병
    '丁' => ['절', '묘', '사', '병', '쇠', '제왕', '건록', '관대', '목욕', '장생', '양', '태'], // 정
    '戊' => ['태', '양', '장생', '목욕', '관대', '건록', '제왕', '쇠', '병', '사', '묘', '절'], // 무
    '己' => ['절', '묘', '사', '병', '쇠', '제왕', '건록', '관대', '목욕', '장생', '양', '태'], // 기
    '庚' => ['사', '묘', '절', '태', '양', '장생', '목욕', '관대', '건록', '제왕', '쇠', '병'], // 경
    '辛' => ['장생', '양', '태', '절', '묘', '사', '병', '쇠', '제왕', '건록', '관대', '목욕'], // 신
    '壬' => ['제왕', '쇠', '병', '사', '묘', '절', '태', '양', '장생', '목욕', '관대', '건록'], // 임
    '癸' => ['건록', '관대', '목욕', '장생', '양', '태', '절', '묘', '사', '병', '쇠', '제왕'] // 계
  ];
 
  public function withManse($manse) {
    $this->hour_e = self::cal($manse->get_h('day'), $manse->get_e('hour'));
    $this->day_e = self::cal($manse->get_h('day'), $manse->get_e('day'));
    $this->month_e = self::cal($manse->get_h('day'), $manse->get_e('month'));
    $this->year_e = self::cal($manse->get_h('day'), $manse->get_e('year'));
    return $this;
  }


  static function cal($h,  $e) {
    return self::$woonsung[$h][e_to_serial($e)];
  }

  /**
   * 사주화 정보는 e_code의 1 이 인으로 시작하는데 프로그램상 자 를 1로 변경
   */

  public function trans_to_ch($code) {
    switch($code) {
      case '장생': return '長生';
      case '목욕': return '沐浴';
      case '관대': return '冠帶';
      case '건록': return '乾祿';
      case '제왕': return '帝旺';
      case '쇠': return '衰'; //쇄
      case '병': return '病';
      case '사': return '死';
      case '묘': return '墓';
      case '절': return '絶'; // 포
      case '태': return '胎';
      case '양': return '養';
    }
  }

}



