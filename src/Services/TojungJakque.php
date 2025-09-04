<?php
// 작괘 구하기
namespace Pondol\Fortune\Services;
use Pondol\Fortune\Facades\Lunar;
use Pondol\Fortune\Facades\Saju;


class TojungJakque
{
  private $taewolil = 	// 태세수, 월건수, 일진수
    [
      '甲子'=>['20', '18', '18'],'甲戌'=>['22', '14', '20'],'甲申'=>['21', '16', '19'],'甲午'=>['18', '18', '16'],'甲辰'=>['22', '14', '20'],'甲寅'=>['19', '16', '17'],
      '乙丑'=>['21', '16', '19'],'乙亥'=>['19', '12', '17'],'乙酉'=>['20', '14', '18'],'乙未'=>['21', '16', '19'],'乙巳'=>['17', '12', '15'],'乙卯'=>['18', '14', '16'],
      '丙寅'=>['17', '14', '15'],'丙子'=>['18', '16', '16'],'丙戌'=>['20', '12', '18'],'丙申'=>['19', '14', '17'],'丙午'=>['16', '16', '14'],'丙辰'=>['20', '12', '18'],
      '丁卯'=>['16', '12', '14'],'丁丑'=>['19', '14', '17'],'丁亥'=>['17', '10', '15'],'丁酉'=>['18', '12', '16'],'丁未'=>['19', '14', '17'],'丁巳'=>['15', '10', '13'],
      '戊辰'=>['18', '10', '16'],'戊寅'=>['15', '12', '13'],'戊子'=>['16', '14', '14'],'戊戌'=>['18', '10', '16'],'戊申'=>['17', '12', '15'],'戊午'=>['14', '14', '12'],
      '己巳'=>['18', '13', '16'],'己卯'=>['19', '15', '17'],'己丑'=>['22', '17', '20'],'己亥'=>['20', '13', '18'],'己酉'=>['21', '15', '19'],'己未'=>['22', '17', '20'],
      '庚午'=>['17', '17', '15'],'庚辰'=>['21', '13', '19'],'庚寅'=>['18', '15', '16'],'庚子'=>['19', '17', '17'],'庚戌'=>['21', '13', '19'],'庚申'=>['20', '15', '18'],
      '辛未'=>['20', '15', '18'],'辛巳'=>['16', '11', '14'],'辛卯'=>['17', '13', '15'],'辛丑'=>['20', '15', '18'],'辛亥'=>['18', '11', '16'],'辛酉'=>['19', '13', '17'],
      '壬申'=>['18', '13', '16'],'壬午'=>['15', '15', '13'],'壬辰'=>['19', '11', '17'],'壬寅'=>['16', '13', '14'],'壬子'=>['17', '15', '15'],'壬戌'=>['19', '11', '17'],
      '癸酉'=>['17', '11', '15'],'癸未'=>['18', '13', '16'],'癸巳'=>['14', '9', '12'],'癸卯'=>['15', '11', '13'],'癸丑'=>['18', '13', '16'],'癸亥'=>['16', '9', '14']
    ];

    //
  private $년천간수치표 = "";
  public $age, $now, $now_year; // $now_lunar_year, 


  /**
   * 토정비결을 보기위한 해 
   * 정의되지 않은 경우 date('Y');
   */
  public function set_year($year) {
    $this->now_year = $year;
  }

  /**
  *@param Number $umdate 음력 생일
  *@param String $umdate  : 유저의 음력 데이타 yyyymmdd
  */
  public function create($manse) {

    $this->now_year == $this->now_year ?? date('Y');


    // 오늘의 만세력을 구한다. 입춘을 기준으로 띠가 변경되므로 대략 3월을 올해의 기준점으로 잡는다.
    // $this->now = Saju::ymdhi(date('Y').'0301')->create();
    // $this->now_lunar_year = substr($this->now->lunar, 0, 4);

    // 올해(토정비결을 보는해)의 년도에  음력생월일을 대입하여 올해생월일에 대햔 만세력을 구한다.
    list ($year, $month, $day)  = explode('-', $manse->lunar);
    // $this_year_ymd = $this->now_year.substr($manse->lunar, 4, 2).substr($manse->lunar, 6, 2);
    $this_year_ymd = $this->now_year.'-'.$month.'-'.$day;

    $this->now = Saju::ymdhi($this_year_ymd)->sl('lunar')->create();
    // print_r($this->now);

    ## 상괘 
    $this->que[0] = $this->sangque($manse);
    ## 중괘
    $this->que[1] = $this->jungque($manse);
    ## 하괘
    $this->que[2] = $this->haque($manse);

    // 토탈 구하기
    $this->total = $this->que[0].$this->que[1].$this->que[2];

    return $this;

  }

  /**
   * 상괘 : (나이수 + 태세 수 ) ÷ 8 = 답
   * 
   * 태세 수는 토정비결 1쪽에서 무조건 본인이 보고자 하는 해의 태세를 찾는다. 즉, 그해의 태세 수는 모든 사람이 같다
   * 현재 본인의 나이에 1에서 나온 태세 수를 더해서 8로 나눈 후 그 나머지 값을 상괘라 한다.
   * 단, 8로 나눠서 나머지 수가 0 이 나올 경우에는 상괘를 8로 한다
   */
  private function sangque($manse) {

    // 나이 구하기
    $manse_year = substr($manse->solar, 0, 4); 
    $this->age = $this->now_year - $manse_year + 1; // 올해의 한국 나이

    // 토정을 보는 해의 60갑자를 가져와 태세수를 구하고 나이와 더한후 8로 나눈다.
    $gabja = $this->taewolil[$this->now->get_he('year')];

    // echo "this->now->get_he('year')".$this->now->get_he('year').PHP_EOL;
    $this->taese_su = $gabja[0];  // 태세수 구하기
    return mod_zero_to_mod(($this->age + $this->taese_su), 8);
  }

  /**
   * 중괘 : (낳은 달수 + 월건수) ÷ 6 = 답
   * 
   * 낳은 달수는 본인이 보고자 하는 해의 음력 생일이 큰 달(大)일 경우에는 30이, 작은 달(小)일 경우에는 29가 된다.
   * 월건수는 2쪽의 '월건법'에서 본인이 보고자 하는 해에 해당되는 생월의 간지를 찾은 후, 1쪽에서 그 간지에 나와있는 수를 월건수로 한다.
   * 위의 1번과 2번에서 나온 수를 더해 6으로 나눈 그 나머지 값을 중괘라 한다.
   */
  private function jungque($manse) {

    // 달수 구하기 (태어날 달의 대소(30, 29) 구하기)
    // echo "this->now->solar:".$this->now->solar.PHP_EOL;
    $lunar = Lunar::ymd($this->now->solar)->tolunar()->create();

    if ($lunar->largemonth) { // 없으면 29
      $this->dal_su = 30;
    }else{
      $this->dal_su = 29;
    }

    // echo $this->dal_su;

    // 월건수 구하기 (올해에서 내 생월이 포함된 것을 절기를 기준)
    // 1. monthgun 을 이용하는 방법
    // $month_he = $this->monthgun($this_year_mnase->get_h('year'), substr($this_year_mnase->lunar, 4, 2));
    // 2. 만세력에서 바로가져오기
    $month_he = $this->now->get_he('month');
    
    $gabja = $this->taewolil[$month_he];
    $this->wolgeon_su = $gabja[1]; // 월건수
    return mod_zero_to_mod(($this->dal_su + $this->wolgeon_su), 6);
  }

  /**
   * 하괘 : (그해 생일수 + 일진수) ÷ 3 = 답
   * 
   * 일진수는 본인이 보고자 하는 해의 음력 생일에 나와 있는 간지를 찾은 후 1쪽에서 그 간지에서 해당되는 수를 일진수로 한다.(간지는 달력에서 손쉽게 찾아 볼 수 있다.)
   * 그 해의 생일 수에 1의 값을 더해서 3으로 나눈 그 나머지 값을 하괘라 한다.
   */
  private function haque($manse) {
    // 일진수 구하기

    $gabja = $this->taewolil[$this->now->get_he('day')];
    $this->iljin_su = $gabja[2];// 일진수 구하기

    list (,,$day) = explode('-', $manse->lunar);
    return mod_zero_to_mod(((int)$day + $this->iljin_su), 3);
  }

  /**
   * https://blog.naver.com/dd00oo/20202489643 참고하여 월건 구함
   */
  private function monthgun($month, $user_lunar_month) {
    switch($month) {
      case '甲': case '己':
        switch($user_lunar_month) {
          case '01': return '丙寅';
          case '02': return '丁卯';
          case '03': return '戊辰';
          case '04': return '己巳';
          case '05': return '庚午';
          case '06': return '辛未';
          case '07': return '壬申';
          case '08': return '癸酉';
          case '09': return '甲戌';
          case '10': return '乙亥';
          case '11': return '丙子';
          case '12': return '丁丑';
        }

        break;
      case '乙': case '庚':
        switch($user_lunar_month) {
          case '01': return '戊寅';
          case '02': return '己卯';
          case '03': return '庚辰';
          case '04': return '辛巳';
          case '05': return '壬午';
          case '06': return '癸未';
          case '07': return '甲申';
          case '08': return '乙酉';
          case '09': return '丙戌';
          case '10': return '丁亥';
          case '11': return '戊子';
          case '12': return '己丑';
        }
        break;
      case '丙': case '辛':
        switch($user_lunar_month) {
          case '01': return '庚寅';
          case '02': return '辛卯';
          case '03': return '壬辰';
          case '04': return '癸巳';
          case '05': return '甲午';
          case '06': return '乙未';
          case '07': return '丙申';
          case '08': return '丁酉';
          case '09': return '戊戌';
          case '10': return '己亥';
          case '11': return '庚子';
          case '12': return '辛丑';
        }
        break;
      case '丁': case '壬':
        switch($user_lunar_month) {
          case '01': return '壬寅';
          case '02': return '癸卯';
          case '03': return '甲辰';
          case '04': return '乙巳';
          case '05': return '丙午';
          case '06': return '丁未';
          case '07': return '戊申';
          case '08': return '己酉';
          case '09': return '庚戌';
          case '10': return '辛亥';
          case '11': return '壬子';
          case '12': return '癸丑';
        }
        break;
      case '戊': case '癸':
        switch($user_lunar_month) {
          case '01': return '甲寅';
          case '02': return '乙卯';
          case '03': return '丙辰';
          case '04': return '丁巳';
          case '05': return '戊午';
          case '06': return '己未';
          case '07': return '庚申';
          case '08': return '辛酉';
          case '09': return '壬戌';
          case '10': return '癸亥';
          case '11': return '甲子';
          case '12': return '乙丑';
        }
        break;
    }
  }
}