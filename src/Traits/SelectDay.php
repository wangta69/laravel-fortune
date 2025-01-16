<?php
namespace Pondol\Fortune\Traits;
use Pondol\Fortune\Services\Sinsal;

// https://m.blog.naver.com/coreaking76/140191614574 참조하기
trait SelectDay{

  /**
   * 지간과 합이 좋은 갑자를 구한다.
   */
  public function _good_he($e) {
    #갑자 을축 병인 정묘 무진 기사 경오 신미 임신 계유
    #갑술 을해 병자 정축 무인 기묘 경진 신사 임오 계미
    #갑신 을유 병술 정해 무자 기축 경인 신묘 임진 계사
    #갑오 을미 병신 정유 무술 기해 경자 신축 임인 계묘
    #갑진 을사 병오 정미 무신 기유 경술 신해 임자 계축
    #갑인 을묘 병진 정사 무오 기미 경신 신유 임술 계해
    switch($e) {
      case '子': case '午': case '卯': case '酉':
        return [
          '甲子','乙丑','丙寅','丁卯','戊辰','己巳','庚午','辛未','壬申','癸酉',
          '甲午','乙未','丙申','丁酉','戊戌','己亥','庚子','辛丑','壬寅','癸卯'
        ];
      case '辰': case '戌': case '丑': case '未':
        return [
          '甲辰','乙巳','丙午','丁未','戊申','己酉','庚戌','辛亥','壬子','癸丑',
          '甲戌','乙亥','丙子','丁丑','戊寅','己卯','庚辰','辛巳','壬午','癸未'
        ];
      case '寅': case '申': case '巳': case '亥':
        return [
          '甲寅','乙卯','丙辰','丁巳','戊午','己未','庚申','辛酉','壬戌','癸亥',
          '甲申','乙酉','丙戌','丁亥','戊子','己丑','庚寅','辛卯','壬辰','癸巳'
        ];
    }

  }

  /**
  * 황도 구하기
  * 가장 길한 날의 하나로 청룡황도, 명당황도, 금궤황도, 대덕황도, 옥당황도, 사명황도가 있음. 
   * (천강,하괴등의 흉신을 제할수 있음) 
  */
  public function _whangdo($month_e, $day_e, &$titles, &$scores){
    $has_whangdo = $this->has_whangdo($month_e, $day_e);
    if($has_whangdo) {
      $titles['whangdo'] = $has_whangdo;
      $scores['whangdo'] = 30;
    }
  }
  private function has_whangdo($month_e, $day_e){ // , &$titles, &$scores
    switch ($month_e) {

      case '巳': case '亥':
        switch($day_e) {
          case '午': return '청룡황도';
          case '未': return '명당황도';
          case '戌': return '금궤황도';
          case '亥': return '천덕황도';
          case '丑': return '옥당황도';
          case '辰': return '사명황도';
        }
        break;
      case '午': case '子':
        switch($day_e) {
          case '申': return '청룡황도';
          case '酉': return '명당황도';
          case '子': return '금궤황도';
          case '丑': return '천덕황도';
          case '卯': return '옥당황도';
          case '午': return '사명황도';
        }
        break;
      case '未': case '丑':
        switch($day_e) {
          case '戌': return '청룡황도';
          case '亥': return '명당황도';
          case '寅': return '금궤황도';
          case '卯': return '천덕황도';
          case '巳': return '옥당황도';
          case '申': return '사명황도';
        }
        break;
      case '寅': case '申':
        switch($day_e) {
          case '子': return '청룡황도';
          case '丑': return '명당황도';
          case '辰': return '금궤황도';
          case '巳': return '천덕황도';
          case '未': return '옥당황도';
          case '戌': return '사명황도';
        }
        break;
      case '卯': case '酉':
        switch($day_e) {
          case '寅': return '청룡황도';
          case '卯': return '명당황도';
          case '午': return '금궤황도';
          case '未': return '천덕황도';
          case '酉': return '옥당황도';
          case '子': return '사명황도';
        }
        break;
      case '辰': case '戌':
        switch($day_e) {
          case '辰': return '청룡황도';
          case '巳': return '명당황도';
          case '申': return '금궤황도';
          case '酉': return '천덕황도';
          case '亥': return '옥당황도';
          case '寅': return '사명황도';
        }
        break;
      return null;
    }
  }


  /**
  * 생기복덕천의 산출(81세까지)
  *  $my_age 생기[$senggi_01 $senggi_02]  복덕[$bokduk_01  $bokduk_02 ] 천의[$cheneu_01  $cheneu_02]
  * _cal2
  */
  public function _senggiBokdukCheneu($my_age, $gender) {
    switch($gender) {
      case 'M':
        switch($my_age) {
          case 2: case 10: case 18: case 26: case 34: case 42: case 50: case 58: case 66: case 74:
            return ['senggi'=>['戌', '亥'], 'bokduk'=>['未', '申'], 'cheneu'=>['午']];
          case 9: case 17: case 25: case 33: case 41: case 49: case 57: case 65: case 73: case 81:
            return ['senggi'=>['丑', '寅'], 'bokduk'=>['酉'], 'cheneu'=>['辰', '巳']];
          case 1: case 8: case 16: case 24: case 32: case 40: case 48: case 56: case 64: case 72: case 80:
            return ['senggi'=>['卯'], 'bokduk'=>['辰', '巳'], 'cheneu'=>['酉']];
          case 7: case 15: case 23: case 31: case 39: case 47: case 55: case 63: case 71: case 79:
            return ['senggi'=>['子'], 'bokduk'=>['午'], 'cheneu'=>['未', '申']];
          case 6: case 14: case 22: case 30: case 38: case 46: case 54: case 62: case 70: case 78:
            return ['senggi'=>['午'], 'bokduk'=>['戌', '亥'], 'cheneu'=>['子']];
          case 5: case 13: case 21: case 29: case 37: case 45: case 53: case 61: case 69: case 77:
            return ['senggi'=>['未', '申'], 'bokduk'=>['戌', '亥'], 'cheneu'=>['子']];
          case 4: case 12: case 20: case 28: case 36: case 44: case 52: case 60: case 68: case 76:
            return ['senggi'=>['辰', '巳'], 'bokduk'=>['卯'], 'cheneu'=>['丑', '寅']];
          case 3: case 11: case 19: case 27: case 35: case 43: case 51: case 59: case 67: case 75:
            return ['senggi'=>['酉'], 'bokduk'=>['丑', '寅'], 'cheneu'=>['卯']];
        }
        break;
      case 'W':
        switch($my_age) {
          case 3: case 10: case 18: case 26: case 34: case 42: case 50: case 58: case 66: case 74:
            return ['senggi'=>['戌', '亥'], 'bokduk'=>['未', '申'], 'cheneu'=>['午', '']];
          case 4: case 11: case 19: case 27: case 35: case 43: case 51: case 59: case 67: case 75:
            return ['senggi'=>['丑', '寅'], 'bokduk'=>['酉', ''], 'cheneu'=>['辰', '巳']];
          case 5: case 12: case 20: case 28: case 36: case 44: case 52: case 60: case 68: case 76:
            return ['senggi'=>['卯', ''], 'bokduk'=>['辰', '巳'], 'cheneu'=>['酉', '']];
          case 6: case 13: case 21: case 29: case 37: case 45: case 53: case 61: case 69: case 77:
            return ['senggi'=>['子', ''], 'bokduk'=>['午', ''], 'cheneu'=>['未', '申']];
          case 7: case 14: case 22: case 30: case 38: case 46: case 54: case 62: case 70: case 78:
            return ['senggi'=>['午'], 'bokduk'=>['戌', '亥'], 'cheneu'=>['子']];
          case 15: case 23: case 31: case 39: case 47: case 55: case 63: case 71: case 79:
            return ['senggi'=>['未', '申'], 'bokduk'=>['戌', '亥'], 'cheneu'=>['子']];
          case 1: case 8: case 16: case 24: case 32: case 40: case 48: case 56: case 64: case 72: case 80:
            return ['senggi'=>['辰', '巳'], 'bokduk'=>['卯'], 'cheneu'=>['丑', '寅']];
          case 2: case 9: case 17: case 25: case 33: case 41: case 49: case 57: case 65: case 73: case 81:
            return ['senggi'=>['酉'], 'bokduk'=>['丑', '寅'], 'cheneu'=>['卯']];
        }
        break;
    }

    return ['senggi'=>[], 'bokduk'=>[], 'cheneu'=>[]];

  } 

  /**
   * 이사 방향 구하기
   */
  public function _direction($my_age, $gender) {
    switch($gender) {
      case 'M':
        $directions = ['동','동남','중','서북','서','동북','남','북','서남'];
        $mod = mod_zero_to_mod($my_age, 9) - 1;
        return arr_reverse_rotate($directions, $mod);
      case 'W':
        $directions = ['동남','중','서북','서','동북','남','북','서남','동'];
        $mod = mod_zero_to_mod($my_age, 9) - 1;
        return arr_reverse_rotate($directions, $mod);
    }

    return $directions;
  }


  /**
  * 십악대패 구하기
  * 십악대패살(十惡大敗煞)은 악할 악(惡), 깨뜨릴 패(敗)를 쓰며,
  * 10가지의 악한 기운으로 인해 크게 실패하게 된다는 흉살을 의미합니다.
  */
  public function _sipak($year_h, $day_he, $month_e, &$titles, &$scores){
    $has_sipak = $this->has_sipak($year_h, $day_he, $month_e);
    if($has_sipak) {
      $titles['sipak'] = '십악대패';
      $scores['sipak'] = 30;
    }
  }
  public function has_sipak($year_h, $day_he, $month_e){
    // echo 'has_sipak:'.$year_h.','.$day_he.','.$month_e.PHP_EOL;
    if ((($year_h == '甲')||($year_h == '己'))&&($month_e == '辰')&&($day_he == '戊戌')) {return true;}
    if ((($year_h == '甲')||($year_h == '己'))&&($month_e == '申')&&($day_he == '癸亥')) {return true;}
    if ((($year_h == '甲')||($year_h == '己'))&&($month_e == '亥')&&($day_he == '丙申')) {return true;}
    if ((($year_h == '甲')||($year_h == '己'))&&($month_e == '子')&&($day_he == '辛亥')) {return true;}
    
    if ((($year_h == '乙')||($year_h == '庚'))&&($month_e == '巳')&&($day_he == '壬申')) {return true;}
    if ((($year_h == '乙')||($year_h == '庚'))&&($month_e == '戌')&&($day_he == '乙巳')) {return true;}
    if ((($year_h == '丙')||($year_h == '辛'))&&($month_e == '辰')&&($day_he == '辛巳')) {return true;}
    if ((($year_h == '丙')||($year_h == '辛'))&&($month_e == '戌')&&($day_he == '庚辰')) {return true;}
    if ((($year_h == '戊')||($year_h == '癸'))&&($month_e == '未')&&($day_he == '己丑')) {return true;}

    return false;
  }

  /**
  * 길신 > 천덕
  */
  public function _chenduk($month_e, $day_e, $day_h, &$titles, &$scores){
    $result = Sinsal::hasChunduk($month_e, $day_h, $day_e);
    if ($result) {
      $titles['chenduk'] = '천덕'; $scores['chenduk'] = 10;
    }
  }

  /**
  * 길신 > 월덕
  */
  public function _wolduk($month_e, $day_h, &$titles, &$scores){
    $result = Sinsal::hasWolduk($month_e, $day_h);
    if ($result) {
        $titles['wolduk'] = '월덕'; $scores['wolduk'] = 10;
    }
  }

  /**
  * 길신 > 천덕합
  */
  public function _chendukhap($month_e, $day_h, &$titles, &$scores){
    $result = Sinsal::hasChendukhap($month_e, $day_h);
    if ($result) {
      $titles['chendukhap'] = '천덕합'; $scores['chendukhap'] = 10;
    }
  }

  /**
  * 길신 > 월덕합
  */
  public function _woldukhap($month_e, $day_e, $day_h, &$titles, &$scores){
    $result = Sinsal::hasWoldukHap($month_e, $day_h);
    if ($result) {
      $titles['woldukhap'] = '월덕합'; $scores['woldukhap'] = 10;
    }
  }

  /**
  * 길신 > 생기
  */
  public function _seng($month_e, $day_e, $day_h, &$titles, &$scores){
    $result = Sinsal::hasSengi($month_e, $day_e);
    if ($result) {
      $titles['seng'] = '생기'; $scores['seng'] = 10;
    }
  }

  /**
  * 길신 > 천의
  */
  public function _chen($month_e, $day_e, &$titles, &$scores){
    $result = Sinsal::hasChene($month_e, $day_e);
    if ($result) {
      $titles['chen'] = '천의'; $scores['chen'] = 10;
    }
  }

  /**
  * 흉신 > 천강
  */
  public function _chengang($month_e, $day_e, &$titles, &$scores){
    $result = Sinsal::hasChengang($month_e, $day_e);
    if ($result) {
      $titles['chengang'] = '천강'; $scores['chengang'] = -20;
    }
  }

  /**
  * 흉신 > 하괴
  */
  public function _hague($month_e, $day_e, &$titles, &$scores){
    $result = Sinsal::hasHague($month_e, $day_e);
    if ($result) {
      $titles['hague'] = '하괴'; $scores['hague'] = -20;
    }
  }

  /**
  * 흉신 > 지파
  */
  public function _jipa($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '亥'))||
      (($month_e == '卯')&&($day_e == '子'))||
      (($month_e == '辰')&&($day_e == '丑'))||
      (($month_e == '巳')&&($day_e == '寅'))||
      (($month_e == '午')&&($day_e == '卯'))||
      (($month_e == '未')&&($day_e == '辰'))||
      (($month_e == '申')&&($day_e == '巳'))||
      (($month_e == '酉')&&($day_e == '午'))||
      (($month_e == '戌')&&($day_e == '未'))||
      (($month_e == '亥')&&($day_e == '申'))||
      (($month_e == '子')&&($day_e == '酉'))||
      (($month_e == '丑')&&($day_e == '戌'))
    ) {
      $titles['jipa'] = '지파'; $scores['jipa'] = -20;
    }
  }

  /**
  * 흉신 > 나망
  */
  public function _namang($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '子'))||
      (($month_e == '卯')&&($day_e == '申'))||
      (($month_e == '辰')&&($day_e == '巳'))||
      (($month_e == '巳')&&($day_e == '辰'))||
      (($month_e == '午')&&($day_e == '戌'))||
      (($month_e == '未')&&($day_e == '亥'))||
      (($month_e == '申')&&($day_e == '丑'))||
      (($month_e == '酉')&&($day_e == '申'))||
      (($month_e == '戌')&&($day_e == '未'))||
      (($month_e == '亥')&&($day_e == '子'))||
      (($month_e == '子')&&($day_e == '巳'))||
      (($month_e == '丑')&&($day_e == '申'))
    ) {
        $titles['namang'] = '나망'; $scores['namang'] = -20;
    }
  }

  /**
  * 흉신 > 멸몰
  */
  public function _myelmol($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '丑'))||
      (($month_e == '卯')&&($day_e == '子'))||
      (($month_e == '辰')&&($day_e == '亥'))||
      (($month_e == '巳')&&($day_e == '戌'))||
      (($month_e == '午')&&($day_e == '酉'))||
      (($month_e == '未')&&($day_e == '申'))||
      (($month_e == '申')&&($day_e == '未'))||
      (($month_e == '酉')&&($day_e == '午'))||
      (($month_e == '戌')&&($day_e == '巳'))||
      (($month_e == '亥')&&($day_e == '辰'))||
      (($month_e == '子')&&($day_e == '卯'))||
      (($month_e == '丑')&&($day_e == '寅'))
    ) {
      $titles['myelmol'] = '멸몰'; $scores['myelmol'] = -20;
    }
  }

  /**
  * 흉신 > 중상
  */
  public function _jungsang($month_e, $day_h, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_h == '甲'))||
      (($month_e == '卯')&&($day_h == '乙'))||
      (($month_e == '辰')&&($day_h == '己'))||
      (($month_e == '巳')&&($day_h == '丙'))||
      (($month_e == '午')&&($day_h == '丁'))||
      (($month_e == '未')&&($day_h == '己'))||
      (($month_e == '申')&&($day_h == '庚'))||
      (($month_e == '酉')&&($day_h == '辛'))||
      (($month_e == '戌')&&($day_h == '己'))||
      (($month_e == '亥')&&($day_h == '壬'))||
      (($month_e == '子')&&($day_h == '癸'))||
      (($month_e == '丑')&&($day_h == '己'))
    ) {
      $titles['jungsang'] = '중상'; $scores['jungsang'] = -20;
    }
  }

  /**
  * 흉신 > 천구
  */
  public function _chengu($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '子'))||
      (($month_e == '卯')&&($day_e == '丑'))||
      (($month_e == '辰')&&($day_e == '寅'))||
      (($month_e == '巳')&&($day_e == '卯'))||
      (($month_e == '午')&&($day_e == '辰'))||
      (($month_e == '未')&&($day_e == '巳'))||
      (($month_e == '申')&&($day_e == '午'))||
      (($month_e == '酉')&&($day_e == '未'))||
      (($month_e == '戌')&&($day_e == '申'))||
      (($month_e == '亥')&&($day_e == '酉'))||
      (($month_e == '子')&&($day_e == '戌'))||
      (($month_e == '丑')&&($day_e == '亥'))
    ) {
      $titles['chengu'] = '천구'; $scores['chengu'] = -20;
    }
  }

  /**
  * 살구하기 > 천살
  */
  public function _chensal($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '戌'))||
      (($month_e == '卯')&&($day_e == '酉'))||
      (($month_e == '辰')&&($day_e == '申'))||
      (($month_e == '巳')&&($day_e == '未'))||
      (($month_e == '午')&&($day_e == '午'))||
      (($month_e == '未')&&($day_e == '巳'))||
      (($month_e == '申')&&($day_e == '辰'))||
      (($month_e == '酉')&&($day_e == '卯'))||
      (($month_e == '戌')&&($day_e == '寅'))||
      (($month_e == '亥')&&($day_e == '丑'))||
      (($month_e == '子')&&($day_e == '子'))||
      (($month_e == '丑')&&($day_e == '亥'))
    ) {
      $titles['chensal'] = '천살'; $scores['chensal'] = -15;
    }
  }

  /**
  * 살구하기 > 피마살
  */
  public function _pamasal($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '子'))||
      (($month_e == '卯')&&($day_e == '酉'))||
      (($month_e == '辰')&&($day_e == '午'))||
      (($month_e == '巳')&&($day_e == '卯'))||
      (($month_e == '午')&&($day_e == '子'))||
      (($month_e == '未')&&($day_e == '酉'))||
      (($month_e == '申')&&($day_e == '午'))||
      (($month_e == '酉')&&($day_e == '卯'))||
      (($month_e == '戌')&&($day_e == '子'))||
      (($month_e == '亥')&&($day_e == '酉'))||
      (($month_e == '子')&&($day_e == '午'))||
      (($month_e == '丑')&&($day_e == '卯'))
    ) {
        $titles['pamasal'] = '피마살'; $scores['pamasal'] = -15;
    }
  }

  /**
  * 살구하기 > 수사살
  */
  public function _susasal($month_e, $day_e, &$titles, &$scores){
    if (
        (($month_e == '寅')&&($day_e == '戌'))||
        (($month_e == '卯')&&($day_e == '辰'))||
        (($month_e == '辰')&&($day_e == '亥'))||
        (($month_e == '巳')&&($day_e == '巳'))||
        (($month_e == '午')&&($day_e == '子'))||
        (($month_e == '未')&&($day_e == '午'))||
        (($month_e == '申')&&($day_e == '丑'))||
        (($month_e == '酉')&&($day_e == '未'))||
        (($month_e == '戌')&&($day_e == '寅'))||
        (($month_e == '亥')&&($day_e == '申'))||
        (($month_e == '子')&&($day_e == '卯'))||
        (($month_e == '丑')&&($day_e == '酉'))
    ) {
        $titles['susasal'] = '수사살'; $scores['susasal'] = -15;
    }
  }

  /**
  * 살구하기 > 망라살
  */
  public function _mangrasal($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '子'))||
      (($month_e == '卯')&&($day_e == '申'))||
      (($month_e == '辰')&&($day_e == '巳'))||
      (($month_e == '巳')&&($day_e == '辰'))||
      (($month_e == '午')&&($day_e == '戌'))||
      (($month_e == '未')&&($day_e == '亥'))||
      (($month_e == '申')&&($day_e == '丑'))||
      (($month_e == '酉')&&($day_e == '申'))||
      (($month_e == '戌')&&($day_e == '未'))||
      (($month_e == '亥')&&($day_e == '子'))||
      (($month_e == '子')&&($day_e == '巳'))||
      (($month_e == '丑')&&($day_e == '申'))
    ) {
      $titles['mangrasal'] = '망라살'; $scores['mangrasal'] = -15;
    }
  }

  /**
  * 살구하기 > 천적살
  */
  public function _chenjeoksal($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '辰'))||
      (($month_e == '卯')&&($day_e == '酉'))||
      (($month_e == '辰')&&($day_e == '寅'))||
      (($month_e == '巳')&&($day_e == '未'))||
      (($month_e == '午')&&($day_e == '子'))||
      (($month_e == '未')&&($day_e == '巳'))||
      (($month_e == '申')&&($day_e == '戌'))||
      (($month_e == '酉')&&($day_e == '卯'))||
      (($month_e == '戌')&&($day_e == '申'))||
      (($month_e == '亥')&&($day_e == '丑'))||
      (($month_e == '子')&&($day_e == '午'))||
      (($month_e == '丑')&&($day_e == '亥'))
    ) {
      $titles['chenjeoksal'] = '천적살'; $scores['chenjeoksal'] = -15;
    }
  }

  /**
  * 살구하기 > 고초살
  */
  public function _gochosal($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '辰'))||
      (($month_e == '卯')&&($day_e == '丑'))||
      (($month_e == '辰')&&($day_e == '戌'))||
      (($month_e == '巳')&&($day_e == '未'))||
      (($month_e == '午')&&($day_e == '卯'))||
      (($month_e == '未')&&($day_e == '子'))||
      (($month_e == '申')&&($day_e == '酉'))||
      (($month_e == '酉')&&($day_e == '午'))||
      (($month_e == '戌')&&($day_e == '寅'))||
      (($month_e == '亥')&&($day_e == '亥'))||
      (($month_e == '子')&&($day_e == '申'))||
      (($month_e == '丑')&&($day_e == '巳'))
    ) {
      $titles['gochosal'] = '고초살'; $scores['gochosal'] = -15;
    }
  }

  /**
  * 살구하기 > 귀기살
  */
  public function _gueguesal($month_e, $day_e, &$titles, &$scores){
    if (
        (($month_e == '寅')&&($day_e == '丑'))||
        (($month_e == '卯')&&($day_e == '寅'))||
        (($month_e == '辰')&&($day_e == '子'))||
        (($month_e == '巳')&&($day_e == '丑'))||
        (($month_e == '午')&&($day_e == '寅'))||
        (($month_e == '未')&&($day_e == '子'))||
        (($month_e == '申')&&($day_e == '丑'))||
        (($month_e == '酉')&&($day_e == '寅'))||
        (($month_e == '戌')&&($day_e == '子'))||
        (($month_e == '亥')&&($day_e == '丑'))||
        (($month_e == '子')&&($day_e == '寅'))||
        (($month_e == '丑')&&($day_e == '子'))
    ) {
        $titles['gueguesal'] = '귀기살'; $scores['gueguesal'] = -15;
    }
  }

  /**
  * 살구하기 > 왕망살
  */
  public function _wangmangsal($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '寅'))||
      (($month_e == '卯')&&($day_e == '巳'))||
      (($month_e == '辰')&&($day_e == '申'))||
      (($month_e == '巳')&&($day_e == '亥'))||
      (($month_e == '午')&&($day_e == '卯'))||
      (($month_e == '未')&&($day_e == '午'))||
      (($month_e == '申')&&($day_e == '酉'))||
      (($month_e == '酉')&&($day_e == '子'))||
      (($month_e == '戌')&&($day_e == '辰'))||
      (($month_e == '亥')&&($day_e == '未'))||
      (($month_e == '子')&&($day_e == '戌'))||
      (($month_e == '丑')&&($day_e == '丑'))
    ) {
        $titles['wangmangsal'] = '왕망살'; $scores['wangmangsal'] = -15;
    }
  }

  /**
  * 살구하기 > 십악살
  */
  public function _sipaksal($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '卯'))||
      (($month_e == '卯')&&($day_e == '寅'))||
      (($month_e == '辰')&&($day_e == '丑'))||
      (($month_e == '巳')&&($day_e == '子'))||
      (($month_e == '午')&&($day_e == '辰'))||
      (($month_e == '未')&&($day_e == '子'))||
      (($month_e == '申')&&($day_e == '丑'))||
      (($month_e == '酉')&&($day_e == '寅'))||
      (($month_e == '戌')&&($day_e == '卯'))||
      (($month_e == '亥')&&($day_e == '辰'))||
      (($month_e == '子')&&($day_e == '巳'))||
      (($month_e == '丑')&&($day_e == '辰'))
    ) {
      $titles['sipaksal'] = '십악살'; $scores['sipaksal'] = -15;
    }
  }

  /**
  * 살구하기 > 월압살
  */
  public function _wolapsal($month_e, $day_e, &$titles, &$scores){
    if (
        (($month_e == '寅')&&($day_e == '戌'))||
        (($month_e == '卯')&&($day_e == '酉'))||
        (($month_e == '辰')&&($day_e == '申'))||
        (($month_e == '巳')&&($day_e == '未'))||
        (($month_e == '午')&&($day_e == '午'))||
        (($month_e == '未')&&($day_e == '巳'))||
        (($month_e == '申')&&($day_e == '辰'))||
        (($month_e == '酉')&&($day_e == '卯'))||
        (($month_e == '戌')&&($day_e == '寅'))||
        (($month_e == '亥')&&($day_e == '丑'))||
        (($month_e == '子')&&($day_e == '子'))||
        (($month_e == '丑')&&($day_e == '亥'))
    ) {
        $titles['wolapsal'] = '월압살'; $scores['wolapsal'] = -15;
    }
  }

  /**
  * 살구하기 > 월살
  */
  public function _wolsal($month_e, $day_e, &$titles, &$scores){
    if (
      (($month_e == '寅')&&($day_e == '丑'))||
      (($month_e == '卯')&&($day_e == '戌'))||
      (($month_e == '辰')&&($day_e == '未'))||
      (($month_e == '巳')&&($day_e == '辰'))||
      (($month_e == '午')&&($day_e == '丑'))||
      (($month_e == '未')&&($day_e == '戌'))||
      (($month_e == '申')&&($day_e == '未'))||
      (($month_e == '酉')&&($day_e == '辰'))||
      (($month_e == '戌')&&($day_e == '丑'))||
      (($month_e == '亥')&&($day_e == '戌'))||
      (($month_e == '子')&&($day_e == '未'))||
      (($month_e == '丑')&&($day_e == '辰'))
    ) {
        $titles['wolsal'] = '월살'; $scores['wolsal'] = -15;
    }
  }

  /**
  * 살구하기 > 황사살
  */
  public function _hwangsasal($month_e, $day_e, &$titles, &$scores){
    if (
        (($month_e == '寅')&&($day_e == '午'))||
        (($month_e == '卯')&&($day_e == '寅'))||
        (($month_e == '辰')&&($day_e == '子'))||
        (($month_e == '巳')&&($day_e == '午'))||
        (($month_e == '午')&&($day_e == '寅'))||
        (($month_e == '未')&&($day_e == '子'))||
        (($month_e == '申')&&($day_e == '午'))||
        (($month_e == '酉')&&($day_e == '寅'))||
        (($month_e == '戌')&&($day_e == '子'))||
        (($month_e == '亥')&&($day_e == '午'))||
        (($month_e == '子')&&($day_e == '寅'))||
        (($month_e == '丑')&&($day_e == '子'))
    ) {
        $titles['hwangsasal'] = '황사살'; $scores['hwangsasal'] = -15;
    }
  }

  /**
  * 살구하기 > 홍사살
  */
  public function _hongsasal($month_e, $day_e, &$titles, &$scores){
      if (
        (($month_e == '寅')&&(($day_e == '申')||($day_e == '酉')))||
        (($month_e == '卯')&&(($day_e == '辰')||($day_e == '巳')))||
        (($month_e == '辰')&&(($day_e == '子')||($day_e == '丑')))||
        (($month_e == '巳')&&(($day_e == '申')||($day_e == '酉')))||
        (($month_e == '午')&&(($day_e == '辰')||($day_e == '巳')))||
        (($month_e == '未')&&(($day_e == '子')||($day_e == '丑')))||
        (($month_e == '申')&&(($day_e == '申')||($day_e == '酉')))||
        (($month_e == '酉')&&(($day_e == '辰')||($day_e == '巳')))||
        (($month_e == '戌')&&(($day_e == '子')||($day_e == '丑')))||
        (($month_e == '亥')&&(($day_e == '申')||($day_e == '酉')))||
        (($month_e == '子')&&(($day_e == '辰')||($day_e == '巳')))||
        (($month_e == '丑')&&(($day_e == '子')||($day_e == '丑')))
    ) {
        $titles['hongsasal'] = '홍사살'; $scores['hongsasal'] = -15;
    }
  }

  /**
  * 축음양불장길일
  */
  public function _chuk($month_e, $day_he, &$titles, &$scores){
    #인월 : 병인 정묘 병자 무인 기묘 무자 기축 경인 신묘 경자 신축
    #묘월 : 을축 병인 병자 무인 무자 기축 경인 무술 경자 경술
    #진월 : 갑자 을축 갑술 병자 을유 무자 기축 정유 무술 기유
    #사월 : 갑자 갑술 병자 갑신 을유 무자 병신 정유 무술 무신 기유
    #오월 : 계유 갑술 계미 갑신 을유 병신 무술 무신
    #미월 : 임신 계유 갑술 임오 계미 갑신 을유 갑오
    #신월 : 임신 계유 임오 계미 갑신 을유 계사 갑오 을사
    #유월 : 신미 임신 신사 임오 계미 갑신 임진 계사 갑오
    #술월 : 경오 신미 경진 신사 임오 계미 신묘 임진 계사 계묘
    #해월 : 경오 경진 신사 임오 경인 신묘 임진 계사 임인 계묘
    #자월 : 정묘 기사 기묘 경진신사 기축 경인 신묘 임진 신축 임인 정사
    #축월 : 병인 정묘 무진 병자 무인 기묘 경진 무자 기축 경인 신묘 경자 신축 병진 정사 기사 신사

    // 음양
    switch($month_e) {
      case '寅':
        $arr = ['丙寅','丁卯','丙子','戊寅','己卯','戊子','己丑','庚寅','辛卯','庚子','辛丑'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '卯':
        $arr = ['乙丑','丙寅','丙子','戊寅','戊子','己丑','庚寅','戊戌','庚子','庚戌'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '辰':
        $arr = ['甲子','乙丑','甲戌','丙子','乙酉','戊子','己丑','丁酉','戊戌','己酉'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '巳':
        $arr = ['甲子','甲戌','丙子','甲申','乙酉','戊子','丙申','丁酉','戊戌','戊申','己酉'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '午':
        $arr = ['癸酉','甲戌','癸未','甲申','乙酉','丙申','戊戌','戊申'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '未':
        $arr = [ '壬申','癸酉','甲戌','壬午','癸未','甲申','乙酉','甲午'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '申':
        $arr = ['壬申','癸酉','壬午','癸未','甲申','乙酉','癸巳','甲午','乙巳'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '酉':
        $arr = ['辛未','壬申','辛巳','壬午','癸未','甲申','壬辰','癸巳','甲午'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '酉':
        $arr = ['庚午','辛未','庚辰','辛巳','壬午','癸未','辛卯','壬辰','癸巳','癸卯'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '亥':
        $arr = ['庚午','庚辰','辛巳','壬午','庚寅','辛卯','壬辰','癸巳','壬寅','癸卯'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '子':
        $arr = ['丁卯','己巳','己卯','庚辰','辛巳','己丑','庚寅','辛卯','壬辰','辛丑','壬寅','丁巳'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
      case '丑':
        $arr = [$day_he == '丙寅','丁卯','戊辰','丙子','戊寅','己卯','庚辰','戊子','己丑','庚寅','辛卯','庚子','辛丑','丙辰','丁巳','己巳','辛巳'];
        if (in_array($day_he, $arr)){
          $titles['chuk'] = '축음양불장길'; $scores['chuk'] = 24;
        }
        break;
    }
  }

  /**
  * 신구 헌집/새집 길일
  */
  public function _singu($day_he, &$titles, &$scores){
    if ($day_he == '甲子') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '乙丑') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '丙寅') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '庚午') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '乙酉') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '庚寅') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '壬辰') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '癸巳') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '壬寅') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '癸卯') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '丙午') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '庚戌') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '乙卯') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '丙辰') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '丁巳') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '己未') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}
    if ($day_he == '庚申') {$titles['singu1'] = '신가'; $titles['singu2'] = '구가'; $scores['singu'] = 24;}


    if ($day_he == '丁卯') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}
    if ($day_he == '己巳') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}
    if ($day_he == '辛未') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}
    if ($day_he == '甲戌') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}
    if ($day_he == '乙亥') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}
    if ($day_he == '癸未') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}
    if ($day_he == '甲申') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}
    if ($day_he == '庚子') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}
    if ($day_he == '丁未') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}
    if ($day_he == '甲寅') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}
    if ($day_he == '辛酉') {$titles['singu1'] = '신가'; $scores['singu'] = 24;}

  }

  /**
  * 대리월 방모씨 방옹고 방녀부모 방부주 방녀신
  -가취월(家取月) : 결혼하기에 좋은 달을 가리는 방법
 .대이월 : 혼레가 아주 길한 달
 .방매씨 : 결혼을 해도 무방한 달
 .방옹고 : 시부모에게 불리합니다 - 단,시부모가 없음면 결혼할 수 있는 달
 .방녀부모 : 친정부모에게 불리한 달
 .방부주 : 신랑에게 불리한 달
 .방녀신 : 신부에게 나쁜 달
  */
  public function _dae($year_e, $month_e, &$titles, &$scores){
    switch($year_e) {
      case '子': case '午':
        switch($month_e) {
          case '丑': case '未': $titles['dae'] = '대리월'; $scores['dae'] = 20; break;
          case '申': case '寅': $titles['dae'] = '방모씨'; $scores['dae'] = 0; break;
          case '酉': case '卯': $titles['dae'] = '방옹고'; $scores['dae'] = 0; break;
          case '戌': case '辰': $titles['dae'] = '방녀부모'; $scores['dae'] = 0; break;
          case '亥': case '巳': $titles['dae'] = '방부주'; $scores['dae'] = -20; break;
          case '子': case '午': $titles['dae'] = '방녀신'; $scores['dae'] = -20; break;
        }
      break;
    case '丑': case '未':
      switch($month_e) {
        case '丑': case '未': $titles['dae'] = '방녀신'; $scores['dae'] = -20; break;
        case '申': case '寅': $titles['dae'] = '방부주'; $scores['dae'] = -20; break;
        case '酉': case '卯': $titles['dae'] = '방녀부모'; $scores['dae'] = 0; break;
        case '戌': case '辰': $titles['dae'] = '방옹고'; $scores['dae'] = 0; break;
        case '亥': case '巳': $titles['dae'] = '방모씨'; $scores['dae'] = 0; break;
        case '子': case '午': $titles['dae'] = '대리월'; $scores['dae'] = 20; break;
      }
      break;
    case '寅': case '申':
      switch($month_e) {
        case '丑': case '未': $titles['dae'] = '방부주'; $scores['dae'] = -20; break;
        case '申': case '寅': $titles['dae'] = '방녀신'; $scores['dae'] = -20; break;
        case '酉': case '卯': $titles['dae'] = '대리월'; $scores['dae'] = 20; break;
        case '戌': case '辰': $titles['dae'] = '방모씨'; $scores['dae'] = 0; break;
        case '亥': case '巳': $titles['dae'] = '방옹고'; $scores['dae'] = 0; break;
        case '子': case '午': $titles['dae'] = '방녀부모'; $scores['dae'] = 0; break;
      }
      break;
    case '卯': case '酉':
      switch($month_e) {
        case '丑': case '未': $titles['dae'] = '방모씨'; $scores['dae'] = 0; break;
        case '申': case '寅': $titles['dae'] = '대리월'; $scores['dae'] = 20; break;
        case '酉': case '卯': $titles['dae'] = '방녀신'; $scores['dae'] = -20; break;
        case '戌': case '辰': $titles['dae'] = '방부주'; $scores['dae'] = -20; break;
        case '亥': case '巳':  $titles['dae'] = '방녀부모'; $scores['dae'] = 0; break;
        case '子': case '午': $titles['dae'] = '방옹고'; $scores['dae'] = 0; break;
      }
      break;
    case '辰': case '戌':
      switch($month_e) {
        case '丑': case '未': $titles['dae'] = '방옹고'; $scores['dae'] = 0; break;
        case '申': case '寅': $titles['dae'] = '방녀부모'; $scores['dae'] = 0; break;
        case '酉': case '卯': $titles['dae'] = '방부주'; $scores['dae'] = -20; break;
        case '戌': case '辰': $titles['dae'] = '방녀신'; $scores['dae'] = -20; break;
        case '亥': case '巳': $titles['dae'] = '대리월'; $scores['dae'] = 20; break;
        case '子': case '午': $titles['dae'] = '방모씨'; $scores['dae'] = 0; break;
      }
      break;
    case '巳': case '亥':
      switch($month_e) {
        case '丑': case '未': $titles['dae'] = '방녀부모'; $scores['dae'] = 0; break;
        case '申': case '寅': $titles['dae'] = '방옹고'; $scores['dae'] = 0; break;
        case '酉': case '卯': $titles['dae'] = '방모씨'; $scores['dae'] = 0; break;
        case '戌': case '辰': $titles['dae'] = '방옹고'; $scores['dae'] = 0; break;
        case '亥': case '巳': $titles['dae'] = '방녀신'; $scores['dae'] = -20; break;
        case '子': case '午': $titles['dae'] = '방부주'; $scores['dae'] = -20; break;
      }
      break;
    }

  }


  /**
  * 월기일
  * 월기일 매월 초5일 14일 23 일
  */
  public function _wolgi($lunarday, &$titles, &$scores){
    if (($lunarday == '05')||($lunarday == '14')||($lunarday == '23')) {
      $titles['wolgi'] = '월기일'; $scores['wolgi'] = -21;
    }
  }

  /**
  * 인동일
  * 인동일과 인격일에는 외부사람을 집에 들이지 말아야 한다.

    그날에 사람들을 집에 들이면 까닭모를 질병,재난이 올 수 있다.
  */
  public function _indong($lunarday, &$titles, &$scores){
    if (($lunarday == '01')||($lunarday == '08')||($lunarday == '13')||($lunarday == '18')||($lunarday == '23')||($lunarday == 24)||($lunarday == '28')) {
      $titles['indong'] = '인동일';  $scores['indong'] = -21;
    }
  }

  /**
  * 가취대흉일
  */
  public function _gachui($month_e, $day_he, &$titles, &$scores){
    if (
      (($month_e == '寅')||($month_e == '卯')||($month_e == '辰')) &&
      (($day_he == '甲子')||($day_he == '乙丑'))) {
        $titles['gachui'] = '가취대흉일'; $scores['gachui'] = -21;
    } else if (
      (($month_e == '巳')||($month_e == '午')||($month_e == '未')) &&
      (($day_he == '丙子')||($day_he == '丁丑'))) {
        $titles['gachui'] = '가취대흉일'; $scores['gachui'] = -21;
    } else if (
      (($month_e == '申')||($month_e == '酉')||($month_e == '戌')) &&
      (($day_he == '庚子')||($day_he == '辛丑'))) {
        $titles['gachui'] = '가취대흉일'; $scores['gachui'] = -21;
    } else if (
      (($month_e == '亥')||($month_e == '子')||($month_e == '丑')) &&
      (($day_he == '壬子')||($day_he == '癸丑'))) {
        $titles['gachui'] = '가취대흉일'; $scores['gachui'] = -21;
      }
  }

  /**
  * 해일
  */
  public function _haeil($day_e, &$titles, &$scores){
    if ($day_e == '亥') {
      $titles['haeil'] = '해일'; $scores['haeil'] = -11;
    }
  }
}