<?php
namespace Pondol\Fortune\Services\Calendar;
use Carbon\Carbon;
use Pondol\Fortune\Facades\Lunar;

/**
 * 12실살을 제외한 기타 길신 및 흉신 구하기
 * 일지기준으로 하면 집안일
 * 년지기준으로 하면 바깥일
 */

class Samjae
{

  /**
   * 음력달력출력
   * @param String $yyyymm 202501 (2025년 01월)
   */
  public function cal($year) {

    $lunar = Lunar::ymd($year.'0301')
      ->tolunar()
      ->sajugabja()
      ->setAttributes(function($lunar){
        $lunar->zodiac = mb_substr($lunar->gabja->year->ch, 1, 1);
      })
      ->create();

    $type = $this->type($lunar->zodiac);
    $samjaes = [];
    $samjaes['ch'] = $this->yearsofsamjae($lunar->zodiac);

  
    $samjaes['ko'] = tr_code(JI['ch'], ZODIAC['ko'], $samjaes['ch']);
    
    return compact('type', 'samjaes');
  }

  private function type($e) {
    switch($e){
      
      case '寅':
      case '巳':
      case '申':
      case '亥':
        return '들';
      case '卯':
      case '午':
      case '酉':
      case '子':
        return '눌';
      case '辰':
      case '未':
      case '戌':
      case '丑':
      return '날'; 
    }
  }

  private function yearsofsamjae($e) {
    switch($e){
      
      case '寅':
      case '卯':
      case '辰':
        return ['申', '子',  '辰'];
      case '巳':
      case '午':
      case '未':
        return ['亥', '卯', '未'];
  
      case '申':
      case '酉':
      case '戌':
        return ['寅','午','戌'];
    
      case '亥':
      case '子':
      case '丑':
        return ['巳','酉','丑']; 
    }
  }


}


