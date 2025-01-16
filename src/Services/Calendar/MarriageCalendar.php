<?php
namespace Pondol\Fortune\Services\Calendar;

use Pondol\Fortune\Traits\SelectDay as t_selectDay;
use Pondol\Fortune\Traits\Calendar;
use Pondol\Fortune\Facades\Manse;
// 택일 카렌다 모양 참조 : https://lancer.tistory.com/323
// http://saju.sajuplus.net/?acmode=b_s&curjong=saju001001&no=41586&page=11&orgcstyle=&cstyle=4
/**
* 모든 계산법이 이사택일과 같으나 이사택일은 singu_jumsu  가 결혼택일은 dae_jumsu 가 마지막에 드러간다.
*/

class MarriageCalendar
{

  use Calendar;



  /**
   * 결혼택일은 본인과 상대방의 manse가 들어가야 한다.
   */
  // public function marrage() {
  //   $this->calMarrageDay($targetdate, $sajuwha, $profile, $p_sajuwha, $p_profile); //
  // }

  /**
   * 이사택일 구하기
   */
  public function cal($manse, $yyyymm) {
    
    // Calendar._create
    $calendar = $this->_create($yyyymm);

    foreach($calendar->days as $c) {
      if($c->day) {
        $data = new Day;
        $data->cal($manse, $yyyymm.pad_zero($c->day));
        $c->setObject($data);       
      }
    }
    return $calendar->splitPerWeek();
    // $this->calMoveDay($manse, $yyyymm); //
  }

  /**
   * @param $yymmdd : 이사일
   */
  // public function calMoveDay($manse, $yyyymm) {
  // }
}

class Day {
  use t_selectDay;

  public function cal($manse, $yyyymmdd) {

    $now = Manse::refresh()->ymdhi($yyyymmdd)->create();

    // 1. 이사하는 시기의 나이 구하기
    $selected_year = substr($yyyymmdd, 0, 4);
    $my_age = $selected_year - $manse->solar + 1 ;

    $direction = $this->_direction($my_age, $manse->gender);

    $titles = [];
    $scores = [];

    // $good_he = _good_he($toyear_e);
    // 당해의 지간과 합이 좋은 갑자를 구한다.
    $good_he = $this->_good_he($now->get_e('year'));
    $titles['color'] = 'black';


    if(in_array($now->get_he('day'), $good_he))
    {
        $titles['color'] = 'blue';
        $scores['color'] = 20;
    }

    // 생기복덕 구하기

    $senggiBokdukCheneu = $this->_senggiBokdukCheneu($my_age, $manse->gender);
    if (in_array($now->get_e('day'), $senggiBokdukCheneu['senggi'])) {$titles['senggi'] = '생기'; $scores['sbc'] = 30;}
    if (in_array($now->get_e('day'), $senggiBokdukCheneu['bokduk'])) {$titles['bokduk'] = '복덕'; $scores['sbc'] = 30;}
    if (in_array($now->get_e('day'), $senggiBokdukCheneu['cheneu'])) {$titles['cheneu'] = '천의'; $scores['sbc'] = 30;}


    // 황도구하기
    $this->_whangdo($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 십악대패
    $this->_sipak($now->get_h('year'), $now->get_he('day'), $now->get_e('month'), $titles, $scores);

    // 길신 >> 천덕
    $this->_chenduk($now->get_e('month'), $now->get_e('day'), $now->get_h('day'), $titles, $scores);

    // 길신 >> 월덕
    // $this->_wolduk($now->get_e('month'), $now->get_e('day'), $now->get_h('day'), $titles, $scores);
    $this->_wolduk($now->get_e('month'), $now->get_h('day'), $titles, $scores);
    // 길신 >> 천덕합
    $this->_chendukhap($now->get_e('month'), $now->get_h('day'), $titles, $scores);

    // 길신 >> 월덕합
   $this-> _woldukhap($now->get_e('month'), $now->get_e('day'), $now->get_h('day'), $titles, $scores);

    // 길신 >> 생기
   $this-> _seng($now->get_e('month'), $now->get_e('day'), $now->get_h('day'), $titles, $scores);

    // 길신 >> 천의
    $this->_chen($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 흉신 >> 천강
    $this->_chengang($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 흉신 >> 하괴
    $this->_hague($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 흉신 >> 지파
   $this-> _jipa($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 흉신 >> 나망
    $this->_namang($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 흉신 >> 멸몰
    $this->_myelmol($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 흉신 >> 중상
    $this->_jungsang($now->get_e('month'), $now->get_h('day'), $titles, $scores);

    // 흉신 >> 천구
    $this->_chengu($now->get_e('month'), $now->get_e('day'), $titles, $scores);


    // 살구하기 >> 천살
    $this->_chensal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 피마살
    $this->_pamasal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 수사살
    $this->_susasal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 망라살
    $this->_mangrasal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 천적살
    $this->_chenjeoksal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 고초살
    $this->_gochosal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 귀기살
   $this-> _gueguesal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 왕망살
   $this-> _wangmangsal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 십악살
    $this->_sipaksal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 월압살
    $this->_wolapsal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 월살
    $this->_wolsal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 황사살
    $this->_hwangsasal($now->get_e('month'), $now->get_e('day'), $titles, $scores);

    // 살구하기 >> 홍사살
    $this->_hongsasal($now->get_e('month'), $now->get_e('day'), $titles, $scores);


    // 축음양불장길일
    $this->_chuk($now->get_e('month'), $now->get_he('day'), $titles, $scores);

    //  헌집/새집 길일
    $this->_singu($now->get_he('day'), $titles, $scores);


    // 월기일 매월 초5일 14일 23 일
    $this->_wolgi(substr($now->lunar, 6, 2), $titles, $scores);

    // 인동일
   $this-> _indong(substr($now->lunar, 6, 2), $titles, $scores);


    // 가취대흉일
    $this->_gachui($now->get_e('month'), $now->get_he('day'), $titles, $scores);

    // 매달해일
    $this->_haeil($now->get_e('day'), $titles, $scores);



    // print_r($titles);
    // print_r($scores);
    // 총점수
    $this->total = 0;
    $this->titles = $titles;
    foreach($scores as $k => $v) {
      $this->total = $this->total + $v;
    }

  }


}