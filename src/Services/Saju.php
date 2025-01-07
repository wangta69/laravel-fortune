<?php
namespace Pondol\Fortune\Services;

use Pondol\Fortune\Facades\Manse;

class Saju
{

  
  public function create($sl, $ymd, $leap) {
    // $manse = Manse::create($sl, $ymd, $leap);
    $manse = Manse::ymdhi($ymd)->sl($sl)->leap($leap)->create();

    // 오행을 더한다.
    
    $manse->oheng = new \stdclass;
    $manse->oheng->year_h = oheng(mb_substr($manse->year->ch, 0, 1));
    $manse->oheng->year_e = oheng(mb_substr($manse->year->ch, 1, 1));
    $manse->oheng->month_h = oheng(mb_substr($manse->month->ch, 0, 1));
    $manse->oheng->month_e = oheng(mb_substr($manse->month->ch, 1, 1));
    $manse->oheng->day_h = oheng(mb_substr($manse->day->ch, 0, 1));
    $manse->oheng->day_e = oheng(mb_substr($manse->day->ch, 1, 1));
    $manse->oheng->hour_h = oheng(mb_substr($manse->hour->ch, 0, 1));
    $manse->oheng->hour_e = oheng(mb_substr($manse->hour->ch, 1, 1));
    // oheng($he);

  
    // 12신살 구하기
    // 12신살은 년지(年支)나 일지(日支)을 기준으로 삼는데, 인(寅)의 경우를 남겨봅니다. 출생년의 년지(年支=띠)로 보고, 출생일의 일지(日支)를 보는 것도 참고하셔야만 합니다.
    $manse->sinsal = new \stdclass;
    $manse->sinsal->hour_e = sal12(mb_substr($manse->year->ch, 1, 1), mb_substr($manse->hour->ch, 1, 1));
    $manse->sinsal->day_e = sal12(mb_substr($manse->year->ch, 1, 1), mb_substr($manse->day->ch, 1, 1));
    $manse->sinsal->month_e = sal12(mb_substr($manse->year->ch, 1, 1), mb_substr($manse->month->ch, 1, 1));
    $manse->sinsal->year_e = sal12(mb_substr($manse->year->ch, 1, 1), mb_substr($manse->year->ch, 1, 1));
  /*
    // 12운성 구하기
    $woonsung = new \stdClass;
    $woonsung->hour_e = woonsung12(e_code_number($sajuwha->hour_e_code), h_ch_number($sajuwha->day_h));
    $woonsung->day_e = woonsung12(e_code_number($sajuwha->day_e_code), h_ch_number($sajuwha->day_h));
    $woonsung->month_e = woonsung12(e_code_number($sajuwha->month_e_code), h_ch_number($sajuwha->day_h));
    $woonsung->year_e = woonsung12(e_code_number($sajuwha->year_e_code), h_ch_number($sajuwha->day_h));
    $profile->woonsung = $woonsung;

    // 지장간 구하기
    $zizangan = new \stdClass;
    $zizangan->hour = $this->zizangan_to_array(zizangan($sajuwha->hour_e), $sajuwha->day_h);
    $zizangan->day = $this->zizangan_to_array(zizangan($sajuwha->day_e), $sajuwha->day_h);
    $zizangan->month = $this->zizangan_to_array(zizangan($sajuwha->month_e), $sajuwha->day_h);
    $zizangan->year = $this->zizangan_to_array(zizangan($sajuwha->year_e), $sajuwha->day_h);
    $profile->zizangan = $zizangan;


    // 길신 흉신 구하기
        // 오늘 날짜를 기준으로 사주화 데이타를 만듦
        // SinSalFunc 에서 공망을 구할때 사용
        $now = $this->sajuwhaSvc->sajuwha((object)['sl'=>'S', 'birth_ym'=>date('Ymd')]);
        // print_r($now);

        $goodbadsin = new \stdClass;
        $goodbadsin = \App\Services\Saju\SinSalFunc::get_sinsal(
          $sajuwha->hour_h, 
          $sajuwha->day_h, 
          $sajuwha->month_h, 
          $sajuwha->year_h, 
          $sajuwha->hour_e, 
          $sajuwha->day_e, 
          $sajuwha->month_e, 
          $sajuwha->year_e,
          $profile->gender,
          $now
          );
        $profile->goodbadsin = $goodbadsin;

        // 대운 (10년운)
        $daeun = $this->daeunSipsinSvc->daeun($profile->gender, $profile->birth_time, $sajuwha->umdate, $sajuwha->month_h, $sajuwha->year_h, $sajuwha->month_e);
        
        $daeun_h = $daeun['daeun_h']; // 천간 대운
        $daeun_e = $daeun['daeun_e']; // 지지 대운
        $daeun_age = $daeun['daeunAge']; // 대운나이
        $profile->daeun = [];

        foreach($daeun_h  as $k => $v) {
          $daeun_h[$k] =  h_code_ch($v);
          if (!isset($profile->daeun[$k])) {
            $profile->daeun[$k] = new \stdClass;
          }
          $profile->daeun[$k]->h = h_code_ch($v);
          // $profile->daeun[$k]->sipsin_h= \App\Services\Saju\SipSinFunc::sipsin2($sajuwha->day_h, $profile->daeun[$k]->h, 'h');
        }

        foreach($daeun_e  as $k => $v) {
          $daeun_e[$k] =  e_code_ch($v);
          $profile->daeun[$k]->e = e_code_ch($v);
          // 지지의 10성
          $profile->daeun[$k]->sipsin_e= \App\Services\Saju\SipSinFunc::sipsin2($sajuwha->day_h, $profile->daeun[$k]->e, 'e');
          //지지의 12운성
          $profile->daeun[$k]->woonsung = woonsung12(e_ch_number($profile->daeun[$k]->e), h_ch_number($sajuwha->day_h));
        }

        foreach($daeun_age  as $k => $v) {
          $profile->daeun[$k]->age = $v;
        }

        // 대운 (10년운) 끝
        // 세운 (1년운)
        $start = substr($sajuwha->umdate, 0, 4);
        $end = $start + 80;

        $seun = [];

        $i = 0;
        for($year = $start;  $year < $end;  $year++) {
          $result = $this->calgabja($year);
          
          $seun[$i] = $result;
          $seun[$i]->age = $i + 1;
          $seun[$i]->year = $year;
          
          // 지지의 10성
          $seun[$i]->sipsin_e= \App\Services\Saju\SipSinFunc::sipsin2($sajuwha->day_h, $result->e, 'e');
          //지지의 12운성
          $seun[$i]->woonsung = woonsung12(e_ch_number($result->e), h_ch_number($sajuwha->day_h));

          $i++;
        }

        $collection = collect($seun);

        $profile->seun = $collection->split(8);

        */

    print_r($manse);
    return $manse;
  }

}

class Oheng {

}

