<?php
namespace Pondol\Fortune\Services;
use Pondol\Fortune\Facades\Saju;
// 귀인 뜻 풀이 참조 : https://blog.naver.com/ahendufrhd/223059645477
 /**
   * 12실살을 제외한 기타 길신 및 흉신 구하기
   * 일지기준으로 하면 집안일
   * 년지기준으로 하면 바깥일
   */

class Sinsal
{

  private $trans = [
    '천을귀인'=>['ch'=>'天乙貴人'],
    '천주귀인'=>['ch'=>'天廚貴人'],
    '천관귀인'=>['ch'=>'天官貴人'],
    '천복귀인'=>['ch'=>'天福貴人'],
    '태극귀인'=>['ch'=>'太極貴人'],
    '월덕귀인'=>['ch'=>'月德貴人'],
    '천덕귀인'=>['ch'=>'天德貴人'],
    '문창귀인'=>['ch'=>'天廚貴人'], // 문창성
    '문곡성'=>['ch'=>''],
    '관귀학관'=>['ch'=>''],
    '황은대사'=>['ch'=>'皇恩大赦'],
    '암록'=>['ch'=>''],
    '금여록'=>['ch'=>'金與祿'],
    '학당'=>['ch'=>'學堂'], // 學堂貴人
    '천의성'=>['ch'=>'天醫)'],
    '괴강살'=>['ch'=>''],
    '백호살'=>['ch'=>''],
    '원진살'=>['ch'=>''],
    '귀문관살'=>['ch'=>''],
    '홍염살'=>['ch'=>'紅艶殺'],
    '양인살'=>['ch'=>''],
    '공망'=>['ch'=>''],
    '단교관살'=>['ch'=>''],
    '상문살'=>['ch'=>''],
    '상문조객살'=>['ch'=>'喪門弔客殺'],
    '수옥살'=>['ch'=>''], // 12신살의 재살
    '급각살'=>['ch'=>''],
    '고신살'=>['ch'=>'孤神殺'],
    '과숙살'=>['ch'=>'寡宿殺'],
    '상처살'=>['ch'=>'喪妻殺'],
    '상부살'=>['ch'=>'喪夫殺'],
    '천모살'=>['ch'=>'天耗殺'],
    '지모살'=>['ch'=>'地耗殺'],
    '대모소모살'=>['ch'=>'大耗小耗殺'],
    '소모살'=>['ch'=>'小耗殺'],
    '반음살'=>['ch'=>'返吟殺'],
    '병부살'=>['ch'=>'病符殺'],
    '관부살'=>['ch'=>'官符殺'],
    '세파살'=>['ch'=>'歲破殺'],
    '비염살'=>['ch'=>'飛廉殺'],
    '탕화살'=>['ch'=>'湯火殺'],
    '유하살'=>['ch'=>'流霞殺'],
    '비인살'=>['ch'=>'飛刃殺'],
    '음양착살'=>['ch'=>'陰陽錯殺'],
    '지살'=>['ch'=>'地殺'], // 12신살
    '도화살'=>['ch'=>'桃花煞'], // 12신살, 연살(年殺)
    '월살'=>['ch'=>'月殺'], // 12신살
    '망신살'=>['ch'=>'亡身殺'], // 12신살
    '장성살'=>['ch'=>'將星殺'], // 12신살
    '반안살'=>['ch'=>'攀鞍殺'], // 12신살
    '역마살'=>['ch'=>'驛馬殺'], // 12신살
    '육해살'=>['ch'=>'六害殺'], // 12신살
    '화개살'=>['ch'=>'華蓋殺'], // 12신살
    '겁살'=>['ch'=>'劫殺'], // 12신살
    '재살'=>['ch'=>'劫殺'], // 12신살, 수옥살
    '천살'=>['ch'=>'天殺'], // 12신살,
  ];

  private $gender, $hour, $day, $month, $year, $hour_h, $day_h, $month_h, $year_h, $hour_e, $day_e, $month_e, $year_e;

  public function withSaju($saju) {
    $this->hour = $saju->hour->ch;
    $this->day = $saju->day->ch;
    $this->month = $saju->month->ch;
    $this->year = $saju->year->ch;
    $this->hour_h = $saju->get_h('hour');
    $this->day_h = $saju->get_h('day');
    $this->month_h = $saju->get_h('month');
    $this->year_h = $saju->get_h('year');
    $this->hour_e = $saju->get_e('hour');
    $this->day_e = $saju->get_e('day');
    $this->month_e = $saju->get_e('month');
    $this->year_e = $saju->get_e('year');
    $this->gender = $saju->gender;

    return $this;
  }

  public function sinsal() {
    // 길신
    $this->cheneul(); // 천을귀인
    $this->taeguk(); // 태극귀인
    $this->chenguan(); // 천관귀인
    $this->wolduk(); // 월덕귀인 
    $this->chunduk(); // 천덕귀인
    $this->munchang(); // 문창성 (문창귀인)
    $this->woldukHap(); // 월덕합
    $this->chundukHap(); // 천덕합
    $this->boksung(); // 복성귀인
    $this->chenju(); // 천주귀인
    $this->chenbok(); // 천복귀인
    $this->mungok(); // 문곡성(문곡귀인)
    $this->guangui(); // 관귀학관
    $this->amrok(); // 암록
    $this->gumrok(); // 금여록
    $this->hakdang(); // 학당
    $this->chene(); // 천의성

    
    $this->guegangsal(); // 괴강살
    $this->bekhosal(); // 백호살
    $this->wonjinsal(); // 원진살(일지로 검색)

    //자유(子酉), 오축(午丑), 인미(寅未), 묘신(卯申), 진해(辰亥), 사술(巳戌)
    $this->guimunsal(); // 귀문관(일지로 검색)
    // $this->yeokmasal();
    // 
    // 단교관살
    // $this->dangyogansal']['d'] = $this->dangyogansal($month_e, $day_e);

    $this->hongsal(); // 홍염살(일간으로 검색)
    $this->yanginsal(); // 양인살(일간으로 검색)
    $this->gongmangsal(); // 공망구하기

    // 상문살(일지,년지으로 검색)=>먼저 일지를 중심으로 3지를 보고 다음 연지를 중심으로 일지만 본다(연일지를 둘다보는 경우는 이렇게 처리하도록한다.)
    $this->sangmunsal();
    
    // $this->jaesal(); // 재살(수옥살)(일지,년지으로 검색)
    // 급각살
    //1, 2, 3월생이 해(亥)나 자(子), 4, 5, 6월생이 묘(卯)나 미(未), 7, 8, 9월생이 인(寅)이나 술(戌),
    //10, 11, 12월생이 축(丑)이나 진(辰)을
    $this->gepgaksal();

    // 고신살  (상처살)\
    // 과숙살 (상부살, 과부살, 공방살, 독수공방살)
    //남자로서 인묘진(寅卯辰)년 생이 사(巳), 사오미(巳午未) 생이 신(申), 신유술(申酉戌) 생이 해(亥),
    //해자축(亥子丑) 생이 인(寅)을 만나면 곧 상처살(喪妻煞)이라 하며,
    //여자로서는 인묘진(寅卯辰) 생이 축(丑), 사오미(巳午未) 생이 진(辰),
    //신유술(申酉戌) 생이 미(未), 해자축(亥子丑) 생이 술(戌)을 만나면 상부살(喪夫煞)이라고 한다.
    if ($this->gender == 'M'){
      if($this->hasGosinsal($this->year_e, $this->month_e)) {
        $this->gosinsal['m'] = '상처살';
      }
    } else if ($gender == 'W'){
      if($this->hasGuasuksall($this->year_e, $this->month_e)) {
        $this->guasuksal['m'] = '상부살';
      }
    }

    return $this;
  }

  /**
   * year / month / day / hour 로 검색된 모든 신살을 배열로 처리
   */
  public function create() {
    $result = json_decode(json_encode($this));

    $return = (object)['y'=>[],'m'=>[],'d'=>[],'h'=>[]];
    foreach($result as $val) {
      foreach($val as $k => $v ) {
        if($v) {
          $ch = array_key_exists($v, $this->trans) ? $this->trans[$v]['ch']: null;
          // $ch = in_array(trim($v), $this->trans) ? ']+++++++++++++': null;

          // print_
          // if(in_array('천을귀인', $this->trans)) {
          //   echo 'adfadfadfafeds';
          // }
          // // echo $this->trans[$v]['ch'];
          // echo $v.PHP_EOL;
          // echo trim($v).PHP_EOL;
          // // print_r($this->trans);
          // echo '============='.$ch.in_array($v, $this->trans).PHP_EOL;
          array_push($return->{$k}, (object)['ko'=>$v, 'ch'=>$ch]);
        }
      }
      // print_r($v);
    }

    // print_r($return);
    return $return;
  }
 

  /**
  * 천을 귀인(天乙貴) 구하기
  *@param String $day_h 생일간
  *@param String $e : 사주중 지지
  */
  public function cheneul() {
    $this->cheneul['y'] = $this->calCheneul($this->day_h, $this->year_e);
    $this->cheneul['m'] = $this->calCheneul($this->day_h, $this->month_e);
    $this->cheneul['d']= $this->calCheneul($this->day_h, $this->day_e);
    $this->cheneul['h'] = $this->calCheneul($this->day_h, $this->hour_e);
    return $this;
  }

  private function calCheneul($day_h, $e) {
    if($this->hasCheneul($day_h, $e)) {
      return '천을귀인';
    } else {
      return null;
    }
  }

  private function hasCheneul($day_h, $e) {
    switch($day_h){
      case '甲': case '戊': case '庚': // 갑 무 경
        if (($e == '丑') || ($e == '未')) { return true;} break;// (天乙貴);
      case '乙': case '己': // 을기
        if (($e == '子') || ($e == '申')) { return true;} break;
      case '丙': case '丁': // 병정
        if (($e == '酉') || ($e == '亥')) { return true;} break;
      case '辛':
        if (($e == '寅') || ($e == '午')) { return true;} break;
      case '壬': case '癸': // 임 계
        if (($e == '卯') || ($e == '巳')) { return true;} break;
    }
    return false;
  }

  /**
  * 천주귀인(天廚貴人) 구하기
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function chenju() {
    $this->chenju['y'] = $this->calChenju($this->day_h, $this->year_e);
    $this->chenju['m'] = $this->calChenju($this->day_h, $this->month_e);
    $this->chenju['d'] = $this->calChenju($this->day_h, $this->day_e);
    $this->chenju['h'] = $this->calChenju($this->day_h, $this->hour_e);
  }

  private function calChenju($day_h, $e) {
    if($this->hasChenju($day_h, $e)) {
      return '천주귀인';
    } else {
      return null;
    }
  }

  private function hasChenju($my_h, $e) {
    $str = $my_h.$e;
    $arr = ['甲巳','乙午','丙巳','丁午','戊申','己酉','庚亥','辛子','壬寅','癸卯'];
    return in_array($str, $arr);
  }

  /**
  * 천관귀인구하기
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function chenguan() {
    $this->chenguan['y'] = $this->calChenguan($this->day_h, $this->year_e);
    $this->chenguan['m'] = $this->calChenguan($this->day_h, $this->month_e);
    $this->chenguan['d'] = $this->calChenguan($this->day_h, $this->day_e);
    $this->chenguan['h'] = $this->calChenguan($this->day_h, $this->hour_e);
  }

  private function calChenguan($day_h, $e) {
    if($this->hasChenguan($day_h, $e)) {
      return '천관귀인';
    } else {
      return null;
    }
  }

  private function hasChenguan($day_h, $e) {
    $str = $day_h.$e;
    $arr = ['甲未','乙辰','丙巳','丁寅','戊卯','己酉','庚亥','辛申','壬酉','癸午'];
    return in_array($str, $arr);
  }

  /**
  * 천복귀인구하기
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function chenbok() {
    $this->chenbok['y'] = $this->calChenbok($this->day_h, $this->year_e);
    $this->chenbok['m'] = $this->calChenbok($this->day_h, $this->month_e);
    $this->chenbok['d'] = $this->calChenbok($this->day_h, $this->day_e);
    $this->chenbok['h'] = $this->calChenbok($this->day_h, $this->hour_e);
  } 

  private function calChenbok($day_h, $e) {
    if($this->hasChenbok($day_h, $e)) {
      return '천복귀인';
    } else {
      return null;
    }
  }

  private function hasChenbok($day_h, $e) {
    $str = $day_h.$e;
    $arr = ['甲酉','乙申','丙子','丁亥','戊卯','己寅','庚午','辛巳','壬午','癸巳'];
    return in_array($str, $arr);
  }

  /**
  * 태극귀인구하기
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function taeguk() {
    $this->taeguk['y'] = $this->calTaeguk($this->day_h, $this->year_e);
    $this->taeguk['m'] = $this->calTaeguk($this->day_h, $this->month_e);
    $this->taeguk['d'] = $this->calTaeguk($this->day_h, $this->day_e);
    $this->taeguk['h'] = $this->calTaeguk($this->day_h, $this->hour_e);
  }

  private function calTaeguk($day_h, $e) {
    if($this->hasTaeguk($day_h, $e)) {
      return '태극귀인';
    } else {
      return null;
    }
  }

  private function hasTaeguk($day_h, $e) {
    switch($day_h){
      case '甲': case '乙': // 갑 을
        if ($e == '子' || $e == '午' ) { return true;} break;// (太極貴)
      case '丙': case '丁': // 병 정
        if ($e == '卯' || $e == '酉' ) { return true;} break;//
      case '戊': case '己': // 무 기
        if ($e == '辰' || $e == '戌' || $e == '丑' || $e == '未' ) { return true;} break;//
      case '庚': case '辛': // 경신
        if ($e == '寅' || $e == '亥' ) { return true;} break;//
      case '壬': case '癸': // 임계
        if ($e == '巳' || $e == '申' ) { return true;} break;//
    }
    return false;
  }



  /**
  * 복성귀인 구하기
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function boksung() {
    $this->boksung['y'] = $this->calBoksung($this->day_h, $this->year_e);
    $this->boksung['m'] = $this->calBoksung($this->day_h, $this->month_e);
    $this->boksung['d'] = $this->calBoksung($this->day_h, $this->day_e);
    $this->boksung['h'] = $this->calBoksung($this->day_h, $this->hour_e);
  }

  private function calBoksung($day_h, $e) {
    if($this->hasBoksung($day_h, $e)) {
      return '복성귀인';
    } else {
      return null;
    }
  }

  private function hasBoksung($day_h, $e) {
    $str = $day_h.$e;
    $arr = ['甲寅','乙丑','丙子','丁酉','戊申','己未','庚午','辛巳','壬辰','癸卯'];
    return in_array($str, $arr);
  }



/**
  * 문장구하기(문창귀인)
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function munchang() {
    $this->munchang['y'] = $this->calMunchang($this->day_h, $this->year_e);
    $this->munchang['m'] = $this->calMunchang($this->day_h, $this->month_e);
    $this->munchang['d'] = $this->calMunchang($this->day_h, $this->day_e);
    $this->munchang['h'] = $this->calMunchang($this->day_h, $this->hour_e);
  }

  private function calMunchang($day_h, $e) {
    if($this->hasMunchang($day_h, $e)) {
      return '문창';
    } else {
      return null;
    }
  }

  private function hasMunchang($day_h, $e) {
    $str = $day_h.$e;
    $arr = ['甲巳','乙午','丙申','丁酉','戊申','己酉','庚亥','辛子','壬寅','癸卯'];
    return in_array($str, $arr);
  }



  /**
  * 문곡구하기
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function mungok() {
    $this->mungok['y'] = $this->calMungok($this->day_h, $this->year_e);
    $this->mungok['m'] = $this->calMungok($this->day_h, $this->month_e);
    $this->mungok['d'] = $this->calMungok($this->day_h, $this->day_e);
    $this->mungok['h'] = $this->calMungok($this->day_h, $this->hour_e);
  }

  private function calMungok($day_h, $e) {
    if($this->hasMungok($day_h, $e)) {
      return '문곡';
    } else {
      return null;
    }
  }

  private function hasMungok($day_h, $e) {
    $str = $day_h.$e;
    $arr = ['甲亥','乙子','丙寅','丁卯','戊寅','己卯','庚巳','辛午','壬申','癸酉'];
    return in_array($str, $arr);
  }


  /**
  * 관귀학관구하기
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function guangui() {
    $this->guangui['y'] = $this->calGuangui($this->day_h, $this->year_e);
    $this->guangui['m'] = $this->calGuangui($this->day_h, $this->month_e);
    $this->guangui['d'] = $this->calGuangui($this->day_h, $this->day_e);
    $this->guangui['h'] = $this->calGuangui($this->day_h, $this->hour_e);
  }

  private function calGuangui($day_h, $e) {
    if($this->hasGuangui($day_h, $e)) {
      return '관귀학관';
    } else {
      return null;
    }
  }

  private function hasGuangui($day_h, $e) {
    $str = $day_h.$e;
    $arr = ['甲巳','乙巳','丙申','丁申','戊亥','己亥','庚寅','辛寅','壬申','癸申'];
    return in_array($str, $arr);
  }

  /**
  * 암록구하기
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function amrok() {
    $this->amrok['y'] = $this->calAmrok($this->day_h, $this->year_e);
    $this->amrok['m'] = $this->calAmrok($this->day_h, $this->month_e);
    $this->amrok['d'] = $this->calAmrok($this->day_h, $this->day_e);
    $this->amrok['h'] = $this->calAmrok($this->day_h, $this->hour_e);
  }

  private function calAmrok($day_h, $e) {
    if($this->hasAmrok($day_h, $e)) {
      return '암록';
    } else {
      return null;
    }
  }

  private function hasAmrok($day_h, $e) {
    $str = $day_h.$e;
    $arr = ['甲亥','乙戌','丙申','丁未','戊申','己未','庚巳','辛辰','壬寅','癸丑'];
    return in_array($str, $arr);
  }

  /**
  * 금여록(金與祿)구하기
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function gumrok() {
    $this->gumrok['y'] = $this->calGumrok($this->day_h, $this->year_e);
    $this->gumrok['m'] = $this->calGumrok($this->day_h, $this->month_e);
    $this->gumrok['d'] = $this->calGumrok($this->day_h, $this->day_e);
    $this->gumrok['h'] = $this->calGumrok($this->day_h, $this->hour_e);
  }

  private function calGumrok($day_h, $e) {
    if($this->hasGumrok($day_h, $e)) {
      return '금여록';
    } else {
      return null;
    }
  }

  private function hasGumrok($my_h, $e) {
    $str = $my_h.$e;
    $arr = ['甲辰','乙巳','丙未','丁申','戊未','己申','庚戌','辛亥','壬丑','癸寅'];
    return in_array($str, $arr);
  }

  /**
   * 황은대사(皇恩大赦)
   * @my_e : 생월지
   */
  private function hasWhangeungDaesa($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子申','丑未','寅戌','卯丑','辰寅','巳巳','午酉','未卯','申子','酉午','戌亥','亥辰'];
    return in_array($str, $arr);
  }

  /**
  * 학당귀인(學堂貴人)구하기
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function hakdang() {
    $this->hakdang['y'] = $this->calHakdang($this->day_h, $this->year_e);
    $this->hakdang['m'] = $this->calHakdang($this->day_h, $this->month_e);
    $this->hakdang['d'] = $this->calHakdang($this->day_h, $this->day_e);
    $this->hakdang['h'] = $this->calHakdang($this->day_h, $this->hour_e);     
  }
  private function calHakdang($day_h, $e) {
    if($this->hasHakdang($day_h, $e)) {
      return '학당';
    } else {
      return null;
    }       
  }

  private function hasHakdang($my_h, $e) {
    $str = $my_h.$e;
    $arr = ['甲亥','乙午','丙寅','丁酉','戊寅','己酉','庚巳','辛子','壬申','癸卯'];
    return in_array($str, $arr);
  }

  /**
  * 월덕귀인구하기
  *@param String $month_e 생월 월지
  *@param String $h : 사주중 천간
  */
  public function wolduk() {
    $this->wolduk['y'] = $this->calWolduk($this->month_e, $this->year_h);
    $this->wolduk['m'] = $this->calWolduk($this->month_e, $this->month_h);
    $this->wolduk['d'] = $this->calWolduk($this->month_e, $this->day_h);
    $this->wolduk['h'] = $this->calWolduk($this->month_e, $this->hour_h);
  }

  private function calWolduk($month_e, $h) {
    if($this->hasWolduk($month_e, $h)) {
      return '월덕귀인';
    } else {
      return null;
    }
  }

  /**
   * $my_e 생월지
   */
  static function hasWolduk($my_e, $h) {
    // (($my_e == '卯')&&($day_e == '申')) 도 월덕합으로 보년 경향이 있음
    switch($my_e){
      case '亥': case '卯': case '未': // 해 묘 미
        if ($h == '甲') { return true;} break; // (月德貴)break;
      case '寅': case '午': case '戌': // 인 오 술
        if ($h == '丙') { return true;}  break;
      case '巳': case '酉': case '丑': // 사 유 축
        if ($h == '庚') { return true;} break; //
      case '申': case '子': case '辰': // 신 자 진
        if ($h == '壬') { return true;} break; //
    }
    return false;
  }

  /**
   * 월덕합
   * 월지에 연월일시 천간을 대조하여 판단
   */
  public function woldukHap() {
    $this->wolduk['y'] = $this->calWoldukHap($this->month_e, $this->year_h);
    $this->wolduk['m'] = $this->calWoldukHap($this->month_e, $this->month_h);
    $this->wolduk['d'] = $this->calWoldukHap($this->month_e, $this->day_h);
    $this->wolduk['h'] = $this->calWoldukHap($this->month_e, $this->hour_h);
  }

  private function calWoldukHap($month_e, $h) {
    if($this->hasWoldukHap($month_e, $h)) {
      return '월덕합';
    } else {
      return null;
    }
  }

  static function hasWoldukHap($month_e, $h) {
    //  (($month_e == '卯')&&($day_e == '巳'))||
    // (($month_e == '未')&&($day_e == '巳'))||
    // (($month_e == '亥')&&($day_e == '巳'))||
    switch($month_e){
      case '亥': case '卯': case '未': // 해 묘 미
        if ($h == '己') { return true;} break; // (月德貴)break;
      case '寅': case '午': case '戌': // 인 오 술
        if ($h == '辛') { return true;}  break;
      case '巳': case '酉': case '丑': // 사 유 축
        if ($h == '乙') { return true;} break; //
      case '申': case '子': case '辰': // 신 자 진
        if ($h == '丁') { return true;} break; //
    }
    return false;
  }

  /**
  * 천덕귀인구하기
  *@param String $month_e 생월 월지
  *@param String $h : 사주중 천간
  */
  public function chunduk() {
    $this->chunduk['y'] = $this->calChunduk($this->month_e, $this->year_h, $this->year_e);
    $this->chunduk['m'] = $this->calChunduk($this->month_e, $this->month_h, $this->month_e);
    $this->chunduk['d'] = $this->calChunduk($this->month_e, $this->day_h, $this->day_e);
    $this->chunduk['h'] = $this->calChunduk($this->month_e, $this->hour_h, $this->hour_e);
  }

  private function calChunduk($month_e, $h, $e) {
    if ($this->hasChunduk($month_e, $h, $e)) {
      return '천덕귀인';
    } else {
      return null;
    }
  }

  /**
   * @$my_e : 생월지 
   */
  static function hasChunduk($my_e, $h, $e) {
    switch($my_e) {
      case '子':
        if ($e == '巳') { return true;} break;
      case '丑':
        if ($h == '庚') { return true;} break;
      case '寅':
        if ($h == '丁') { return true;} break;
      case '卯':
        if ($e == '申') { return true;} break;
      case '辰':
        if ($h == '壬') { return true;} break;
      case '巳':
        if ($e == '申') { return true;} break;
      case '午':
        if ($e == '亥') { return true;} break;
      case '未':
        if ($h == '甲') { return true;} break;
      case '申':
        if ($h == '癸') { return true;} break;
      case '酉':
        if ($e== '寅') { return true;} break;
      case '戌':
        if ($h == '丙') { return true;} break;
      case '亥':
        if ($h == '乙') { return true;}  break;
    }
    return false;
  }

  /**
   * 천덕합 (월지에 연월일시의 지지를 대조하여 판단)
   */
  public function chundukHap() {
    $this->chunduk['y'] = $this->calChundukHap($this->month_e, $this->year_e);
    $this->chunduk['m'] = $this->calChundukHap($this->month_e, $this->month_e);
    $this->chunduk['d'] = $this->calChundukHap($this->month_e, $this->day_e);
    $this->chunduk['h'] = $this->calChundukHap($this->month_e, $this->hour_e);
  }

  private function calChundukHap($month_e, $e) {
    if ($this->hasChendukhap($month_e, $e)) {
      return '천덕합';
    } else {
      return null;
    }
  }
  static function hasChendukhap($month_e, $e) {
    // 아래와 같이 e도 고려하는 경우가 있음
    // (($month_e == '卯')&&($day_e == '巳'))||
    // (($month_e == '午')&&($day_e == '寅'))||
    // (($month_e == '申')&&($day_e == '戌'))||
    //  (($month_e == '酉')&&($day_e == '亥'))||
    $str = $month_e.$e;
    $arr = ['子申','丑乙','寅壬','卯巳','辰丁','巳丙','午寅','未己','申"戊','酉亥','戌辛','亥庚'];
    return in_array($str, $arr);
  }

  /**
  * 천의(天醫)성구하기 : 길신
  *@param String $month_e 생월 월지
  *@param String $e : 월지를 제외한 지지
  */
  public function chene() {
    $this->chene['y'] = $this->calChene($this->month_e, $this->year_e);
    $this->chene['d'] = $this->calChene($this->month_e, $this->day_e);
    $this->chene['h'] = $this->calChene($this->month_e, $this->hour_e);
  }

  private function calChene($month_e, $e) {
    if($this->hasChene($month_e, $e)) {
      return '천의';
    } else {
      return null;
    }
  }

  static function hasChene($month_e, $e) {
    $str = $month_e.$e;
    $arr = ['寅丑','卯寅','辰卯','巳辰','午巳','未午','申未','酉甲','戌酉','亥戌','子亥','丑子'];
    return in_array($str, $arr);
  }

  /**
  * 원진살구하기 (day_e 에 관한 원진살은 $e 가 $year_e 일때와 동일)
  * 일지로 검색
  *@param String $day_e 생일 일지
  *@param String $e : 월지를 제외한 지지
  */
  public function wonjinsal() {
    $this->wonjinsal['y'] = $this->calWonjinsal($this->day_e, $this->year_e);
    $this->wonjinsal['m'] = $this->calWonjinsal($this->day_e, $this->month_e);
    $this->wonjinsal['d'] = $this->calWonjinsal($this->day_e, $this->year_e);
    $this->wonjinsal['h'] = $this->calWonjinsal($this->day_e, $this->hour_e);
  }

  private function calWonjinsal($day_e, $e) {
    $wonjinsal = null;
    if ((($day_e == '子') && ($e ==  '未')) || (($day_e == '未') && ($e ==  '子'))) {$wonjinsal = '원진살';} // (怨嗔殺)
    if ((($day_e == '丑') && ($e ==  '午')) || (($day_e == '午') && ($e ==  '丑'))) {$wonjinsal = '원진살';}
    if ((($day_e == '寅') && ($e ==  '酉')) || (($day_e == '酉') && ($e ==  '寅'))) {$wonjinsal = '원진살';}
    if ((($day_e == '卯') && ($e ==  '申')) || (($day_e == '申') && ($e ==  '卯'))) {$wonjinsal = '원진살';}
    if ((($day_e == '辰') && ($e ==  '亥')) || (($day_e == '亥') && ($e ==  '辰'))) {$wonjinsal = '원진살(';}
    if ((($day_e == '巳') && ($e ==  '戌')) || (($day_e == '戌') && ($e ==  '巳'))) {$wonjinsal = '원진살';}
    return $wonjinsal;
  }

  /**
  * 귀문관살 구하기 (day_e 에 관한 원진살은 $e 가 $year_e 일때와 동일)
  * 일지로 검색
  *@param String $day_e 생일 일지
  *@param String $e : 월지를 제외한 지지
  */
  public function guimunsal() {
    $this->guimunsal['y'] = $this->calGuimunsal($this->day_e, $this->year_e);
    $this->guimunsal['m'] = $this->calGuimunsal($this->day_e, $this->month_e);
    $this->guimunsal['d'] = $this->calGuimunsal($this->day_e, $this->year_e);
    $this->guimunsal['h'] = $this->calGuimunsal($this->day_e, $this->hour_e);
  }

  private function calGuimunsal($day_e, $e) {
      $wonjinsal = null;
      if ((($day_e == '子') && ($e ==  '酉')) || (($day_e == '酉') && ($e ==  '子'))) {$wonjinsal = '귀문살';} // (鬼門關)
      if ((($day_e == '丑') && ($e ==  '午')) || (($day_e == '午') && ($e ==  '丑'))) {$wonjinsal = '귀문살';}
      if ((($day_e == '寅') && ($e ==  '未')) || (($day_e == '未') && ($e ==  '寅'))) {$wonjinsal = '귀문살';}
      if ((($day_e == '卯') && ($e ==  '申')) || (($day_e == '申') && ($e ==  '卯'))) {$wonjinsal = '귀문살';}
      if ((($day_e == '辰') && ($e ==  '亥')) || (($day_e == '亥') && ($e ==  '辰'))) {$wonjinsal = '귀문살';}
      if ((($day_e == '巳') && ($e ==  '戌')) || (($day_e == '戌') && ($e ==  '巳'))) {$wonjinsal = '귀문살';}
      return $wonjinsal;
  }

  /**
  * 홍염살 구하기(일간으로 검색)
  *@param String $day_h 생일 일간
  *@param String $e : 사주중 지지
  */
  public function hongsal() {
    $this->hongsal['y'] = $this->calHongsal($this->day_h, $this->year_e);
    $this->hongsal['m'] = $this->calHongsal($this->day_h, $this->month_e);
    $this->hongsal['d'] = $this->calHongsal($this->day_h, $this->day_e);
    $this->hongsal['h'] = $this->calHongsal($this->day_h, $this->hour_e);
  }

  private function calHongsal($day_h, $e) {
    if($this->hasHongsal($day_h, $e)) {
      return '홍염살'; // 紅艶殺
    } else {
      return null;
    }
  }

  private function hasHongsal($day_h, $e) {
    // Z申 홈영삼
    $str = $day_h.$e;
    $arr = ['甲午','乙午','丙寅','丁未','戊辰','己辰','庚戌','辛酉','壬子','癸申'];
    return in_array($str, $arr);
  }

  /**
  * 양인살 구하기(일간으로 검색)
  *@param String $day_h 생일간
  *@param String $e : 사주중 지지
  */
  public function yanginsal() {
    $this->yanginsal['y'] = $this->calYanginsal($this->day_h, $this->year_e);
    $this->yanginsal['m'] = $this->calYanginsal($this->day_h, $this->month_e);
    $this->yanginsal['d'] = $this->calYanginsal($this->day_h, $this->day_e);
    $this->yanginsal['h'] = $this->calYanginsal($this->day_h, $this->hour_e);
  }

  private function calYanginsal($day_h, $e) {
    if($this->hasYanginsal($day_h, $e)) {
      return '양인살';
    } else {
      return null;
    }
  }

  private function hasYanginsal($my_h, $e) {
    $str = $my_h.$e;
    $arr = ['甲卯','丙午','戊午','庚酉','壬子'];
    return in_array($str, $arr);
  }
  /**
   * 양인살은 판별법이 두가지 이다. 하나는 위처럼, 하나는 현재 처럼
   */
  private function hasYanginsal1($my_h, $e) {
    $str = $my_h.$e;
    $arr = ['甲卯','乙辰','丙午','丁未','戊午','己未','庚酉','辛戌','壬子','癸丑'];
    return in_array($str, $arr);
  }



  /**
  * 괴강살 구하기
  *@param String $gabja 갑자
  *@param String $e : 사주중 지지
  */
  public function guegangsal() {
    $this->guegangsal['y'] = $this->calGuegangsal($this->year);
    $this->guegangsal['m'] = $this->calGuegangsal($this->month);
    $this->guegangsal['d'] = $this->calGuegangsal($this->day);
    $this->guegangsal['h'] = $this->calGuegangsal($this->hour);
  }

  private function calGuegangsal($gabja) {
    $arr = ['壬辰','壬戌','戊戌','庚辰','庚戌'];
    // 임진, 임술, 무술 (무술년을 괘강살로 안하는 경우도 있음), 경진, 경술
    return in_array($gabja, $arr);

    // switch($gabja){
    //   case '壬辰': // 임진
    //     return '괴강살'; // 魁罡殺
    //   case '壬戌': // 임술
    //     return '괴강살';
    //   case '戊戌': // 무술 (무술년을 괘강살로 안하는 경우도 있음)
    //     return '괴강살';
    //   case '庚辰': //경진
    //     return '괴강살';
    //   case '庚戌': //경술
    //     return '괴강살';
    // }
    // return null;
  }

  /**
   * 괴강살(魁강殺)
   * $my_h 생일간
   * $e 현재의 년지
   */
  private function hasGuegangsal($my_h, $e) {
    switch($my_h){
      case '庚': 
        if ($e == '辰' || $e == '戌') return true; 
        break;
      case '壬': 
        if ($e == '辰' || $e == '戌') return true;
        break;
    }
    return false;
  }

  /**
  * 백호살 구하기 / 
  *@param String $gabja 갑자
  *@param String $e : 사주중 지지
  */
  public function bekhosal() {
    $this->bekhosal['y'] = $this->calBekhosal($this->year);
    $this->bekhosal['m'] = $this->calBekhosal($this->month);
    $this->bekhosal['d'] = $this->calBekhosal($this->day);
    $this->bekhosal['h'] = $this->calBekhosal($this->hour);
  }

  private function calBekhosal($gabja) {
    switch($gabja){
      case '甲辰': // 갑진
          return '백호살'; // 白虎殺)
      case '丙戌': //병술
          return '백호살';
      case '丁丑': //정축
          return '백호살';
      case '戊辰': // 무진
          return '백호살';
      case '壬戌'://임술
          return '백호살';
      case '癸丑': //계축
          return '백호살';
    }
    return null;
  }

  /**
   * 백호대살(白虎大殺)
   * $my_h 생일간
   * $e: 현재의 년지
   */
  private function hasBekhosal($my_h, $e) {
    $str = $my_h.$e;
    $arr = ['甲辰', '乙未', '丙戌', '丁丑', '戊辰', '壬戌', '癸丑'];
    // 갑진, 을미, 변술, 정축, 무진, 임술, 계축
    return in_array($str, $arr);
  }


  /**
  * 공망살 구하기 (일주를 이용하여 구하기)
  *@param String $day_h 일간
  *@param String $day_e 일지
  */

  private function gongmang($day_h, $day_e) {
      $ilju = $day_h.$day_e;
      $gongmang = [];
      switch($ilju) {
        case '甲子': case '乙丑': case '丙寅': case '丁卯': case '戊辰': 
        case '己巳': case '庚午': case '辛未': case '壬申': case '癸酉':
          $gongmang = ['戌', '亥'];
          break;
        case '甲戌': case '乙亥': case '丙子': case '丁丑': case '戊寅':
        case '己卯': case '庚辰': case '辛巳': case '壬午': case '癸未':
          $gongmang = ['申', '酉'];
          break;
        case '甲申': case '乙酉': case '丙戌': case '丁亥': case '戊子':
        case '己丑': case '庚寅': case '辛卯': case '壬辰': case '癸巳':
          $gongmang = ['午', '未'];
          break;
        case '甲午': case '乙未': case '丙申': case '丁酉': case '戊戌':
        case '己亥': case '庚子': case '辛丑': case '壬寅': case '癸卯':
          $gongmang = ['辰', '巳'];
          break;
        case '甲辰': case '乙巳': case '丙午': case '丁未': case '戊申':
        case '己酉': case '庚戌': case '辛亥': case '壬子': case '癸丑':
          $gongmang = ['寅', '卯'];
          break;
        case '甲寅': case '乙卯': case '丙辰': case '丁巳': case '戊午':
        case '己未': case '庚申': case '辛酉': case '壬戌': case '癸亥':
          $gongmang = ['子', '丑'];
          break;
    }

    return $gongmang;
  }

  public function gongmangsal() {
    // 오늘의 만세력을 구한다.
    $now = Saju::ymdhi(date('YmdHi'))->create();

    $this->gongmangsal['y'] = $this->calGongmangsal($this->year_h, $this->year_e, $now->get_e('year'));
    $this->gongmangsal['m'] = $this->calGongmangsal($this->month_h, $this->month_e, $now->get_e('month'));
    $this->gongmangsal['d'] = $this->calGongmangsal($this->day_h, $this->day_e, $now->get_e('day'));
    $this->gongmangsal['h'] = $this->calGongmangsal($this->hour_h, $this->hour_e, $now->get_e('hour'));
  }

  private function calGongmangsal($day_h, $day_e, $e) {
    $gongmang = $this->gongmang($day_h, $day_e);

    if(in_array($e, $gongmang)) {
      return '공망살'; // (空亡殺)
    }

    return null;
  }

  /**
  * 상문살 구하기
  * 상문살(일지,년지으로 검색)=>먼저 일지를 중심으로 3지를 보고 다음 연지를 중심으로 일지만 본다(연일지를 둘다보는 경우는 이렇게 처리하도록한다.)
  *@param String $day_e 생월 일지 (일지에 대한 상문살을 구할경우 $day_e 에 $year_e가 온다)
  *@param String $e : 일지를 제외한 지지
  */
  public function sangmunsal() {
    $this->sangmunsal['y'] = $this->calSangmunsal($this->day_e, $this->year_e);
    $this->sangmunsal['m'] = $this->calSangmunsal($this->day_e, $this->month_e);
    $this->sangmunsal['d'] = $this->calSangmunsal($this->year_e, $this->day_e);
    $this->sangmunsal['h'] = $this->calSangmunsal($this->day_e, $this->hour_e);      
  }

  private function calSangmunsal($my_e, $e) {
    $result = $this->hasSangmunsal($my_e, $e);
    if ($result) {
      return '상문살';
    } else {
      return null;
    }       
  }

  /**
   * 상문조객살(喪門弔客殺)
   * 상문살(喪門殺)과 격각살((隔角殺)는 구하는 공식이 동일하다. (보통 격각살은 일지를 기준으로 하고 상문살은 연지를 기준으로 한다.)
   *@param $my_e 생년지
   *@param $e 현재의 년지
   */
  private function hasSangmunsal($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子寅','丑卯','寅辰','卯巳','辰午','巳未','午申','未酉','申戌','酉亥','戌子','亥丑'];
    return in_array($str, $arr);
  }

  /**
   * 고신살孤神殺 (남자용)
   * 남자로서 인묘진(寅卯辰)년 생이 사(巳), 사오미(巳午未) 생이 신(申), 신유술(申酉戌) 생이 해(亥),
   * 해자축(亥子丑) 생이 인(寅)을 만나면 곧 상처살(喪妻煞),
   * $my_e: 생월지(생년지나 생일지를 이용해서 많이 볾)
   * $e: 현재의 년지
   * 
   */
  private function hasGosinsal($my_e, $e) {
    switch($my_e){
      case '寅': case '卯': case '辰':
        if ($e == '巳') { return true;} break;
      case '巳': case '午': case '未':
        if ($e == '申') { return true;} break;
      case '申': case '酉': case '戌':
        if ($e == '亥') { return true;} break;
      case '亥': case '子': case '丑':
        if ($e == '寅') { return true;} break;// (喪門殺)
    }
    return false;
  }

  /**
   * 과숙살寡宿殺(여자용)
   * 여자로서는 인묘진(寅卯辰) 생이 축(丑), 사오미(巳午未) 생이 진(辰),
  * 신유술(申酉戌) 생이 미(未), 해자축(亥子丑) 생이 술(戌)을 만나면 상부살(喪夫煞).
   * $my_e: 생일지
   * $e: 현재의 년지
   */
  private function hasGuasuksall($my_e, $e) {
    switch($e){
      case '寅': case '卯': case '辰':
        if ($e == '丑') { return true;} break;
      case '巳': case '午': case '未':
        if ($e == '辰') { return true;} break;
      case '申': case '酉': case '戌':
        if ($e == '未') { return true;} break;
      case '亥': case '子': case '丑':
        if ($e == '戌') { return true;} break;// (喪門殺)
    }
    return false;
  }

  /**
   * 조객 (상문살과 의미는 같다)
   * @param $my_e : 생년지
   * @param $e : 오늘의 년지
   */
  private function hasJogeak($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子戌','丑亥','寅子','卯丑','辰寅','巳卯','午辰','未巳','申午','酉未','戌申','亥酉'];
    return in_array($str, $arr);
  }

  /**
   * 천모살(天耗殺)
   * $my_e : 생년지
   * $e: 현재의 년지
   */
  private function hasChenmo($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子申','丑戌','寅子','卯寅','辰辰','巳午','午申','未戌','申子','酉寅','戌辰','亥午'];
    return in_array($str, $arr);
  }

  /**
   * 지모살(地耗殺)
   * $my_e : 생년지
   * $e: 현재의 년지
   */
  private function hasJimo($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子巳','丑未','寅酉','卯亥','辰丑','巳卯','午巳','未未','申酉','酉亥','戌丑','亥卯'];
    return in_array($str, $arr);
  }

  /**
   * 대모소모살(大耗小耗殺) 대모/소모는 의미가 같음
   * 대모살 大耗殺, 복음살(伏吟殺) 은 구하는 방식이 동일
   * @param $my_e : 생년지
   * @param $e : 오늘의 년지
   */
  private function hasDaemo($my_e, $e) {
    $str = $my_h.$e;
    $arr = ['子午','丑未','寅申','卯酉','辰戌','巳亥','午子','未丑','申寅','酉卯','戌辰','亥巳'];
    return in_array($str, $arr);
  }

  /**
   * 소모살 小耗殺, 사부살(死符殺)는 구하는 방식이 동일
   * @param $my_e : 생년지
   * @param $e : 오늘의 년지
   */
  private function hasSomo($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子巳','丑午','寅未','卯申','辰酉','巳戌','午亥','未子','申丑','酉寅','戌卯','亥辰'];
    return in_array($str, $arr);
  }

  /**
   * 파군
   */
  private function hasPagun($my_e, $e) {
    switch($my_e){
      case '子': case '辰': case '申':
        if ($e == '申') { return true;} break;
      case '丑': case '巳': case '酉':
        if ($e == '巳') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '寅') { return true;} break;
      case '卯': case '未': case '丑':
        if ($e == '亥') { return true;} break;
    }
    return false;
  }

  /**
   * 구신
   */
  private function hasGusin($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子卯','丑辰','寅巳','卯午','辰未','巳申','午酉','未戌','申亥','酉子','戌丑','亥寅'];
    return in_array($str, $arr);
  }

  /**
   * 교신
   */
  private function hasGuyosin($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子酉','丑戌','寅亥','卯子','辰丑','巳寅','午卯','未辰','申巳','酉午','戌未','亥申'];
    return in_array($str, $arr);
  }

  /**
   * 반음살(返吟殺)
   * @param $my_e :  생년지
   * @param $e : 오늘의 년지
   */
  private function hasBanum($my_e, $e) {
    if ($my_e == $e) {
      return true;
    }
    return false;
  }

  /**
   * 병부살(病符殺), 태음살(太陰殺)은 구하는 공식이 같음
   * $my_e : 생년지
   * @e: 오늘의 년지
   */
  private function hasBengbu($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子亥','丑子','寅丑','卯寅','辰卯','巳辰','午巳','未午','申未','酉申','戌酉','亥戌'];
    return in_array($str, $arr);
  }

  /**
   * 관부살(官符殺)
   * $my_e : 생년지
   * @e: 오늘의 년지
   */
  private function hasGuanbu($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子辰','丑巳','寅午','卯未','辰申','巳酉','午戌','未亥','申子','酉丑','戌寅','亥卯'];
    return in_array($str, $arr);
  }

  /**
   * 세파살(歲破殺)
    * $my_e : 생년지
   * @e: 오늘의 년지
   */
  private function hasSepa($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子酉','丑辰','寅亥','卯午','辰丑','巳申','午卯','未戌','申巳','酉子','戌未','亥寅'];
    return in_array($str, $arr);
  }

  /**
   * 비염살(飛廉殺)
   * $my_e : 생년지
   * @e: 오늘의 년지
   */
  private function hasByem($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子申','丑酉','寅戌','卯亥','辰子','巳丑','午寅','未卯','申辰','酉巳','戌午','亥未'];
    return in_array($str, $arr);
  }


  /**
   * 탕화살(湯火殺)
   * $my_e : 생년지
   * @e: 오늘의 년지
   */
  private function hasTangwha($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子午','丑未','寅寅','卯午','辰未','巳寅','午午','未未','申寅','酉午','戌未','亥寅'];
    return in_array($str, $arr);
  }

  /**
   * 하괴 (흉신)
   * $my_e : 생년지
   * @e: 오늘의 년지
   */
  static function hasHague($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['寅亥','卯午','辰丑','巳申','午卯','未戌','申巳','酉子','戌未','亥寅','子酉','丑辰'];
    return in_array($str, $arr);
  }

  /**
   * 생기 (길신)
   */
  static function hasSengi($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['寅戌','卯亥','辰子','巳丑','午寅','未卯','申辰','酉巳','戌午','亥未','子申','丑酉'];
    return in_array($str, $arr);
  }


  /**
   * 단교관살
   * 월지파를 기준으로 일시를 비교하는 경우도 있으나 일지를 기준으로 보는 경향이 많습니다.
   * 월지 일지 비교
   * 일지를 기준
   */
  public function dangyogansal() {
    $this->dangyogansal['d']= $this->calDangyogansal($this->day_e, $this->day_e);
  }
  private function calDangyogansal($day_e, $e) {
    $result = $this->hasDangyogansal($day_e, $e);
    if ($result) {
      return '단교관살';
    } else {
      return null;
    }
  }
  /**
   * $my_e 생월지
   * $e : 올해의 년지
   */
  private function hasDangyogansal($my_e, $e) {
    $str = $my_e.$e;
    $arr = ['子亥','丑子','寅寅','卯卯','辰申','巳丑','午戌','未酉','申辰','酉巳','戌午','亥未'];
    return in_array($str, $arr);
  }

  /**
   * 유하살(流霞殺)
   * $my_h 생일간
   * $e 올해의 년지
   */
  private function hasUhasal($my_h, $e) {
    $str = $my_h.$e;
    $arr = ['甲酉','乙戌','丙未','丁申','戊巳','己午','庚辰','辛卯','壬亥','癸寅'];
    return in_array($str, $arr);
  }

  /**
   * 비인살(飛刃殺)
   *  * $my_h 생일간
   * $e 올해의 년지
   */
  private function hasBeeinsal($my_h, $e) {
    $str = $my_h.$e;
    $arr = ['甲酉','乙戌','丙子','丁丑','戊子','己丑','庚卯','辛辰','壬午','癸未'];
    return in_array($str, $arr);
  }

  /**
   * 음양착살(陰陽錯殺)
   * $my_h 생일간
   * $e 올해의 년지
   */
  private function hasEumyangsal($my_h, $e) {
    switch($my_h){
      case '丙':
        if ($e == '子' || $e == '午') { return true;} break;
      case '丁':
        if ($e == '丑' || $e == '未') { return true;} break;
      case '戊':
        if ($e == '寅' || $e == '申') { return true;} break;
      case '辛':
        if ($e == '卯' || $e == '酉') { return true;} break;
      case '壬':
        if ($e == '辰' || $e == '戌') { return true;} break;
      case '癸':
        if ($e == '巳' || $e == '亥') { return true;} break;
    }
    return false;
  }


  

  /**
  * 급각살 구하기
  * 1, 2, 3월생이 해(亥)나 자(子), 4, 5, 6월생이 묘(卯)나 미(未), 7, 8, 9월생이 인(寅)이나 술(戌),10, 11, 12월생이 축(丑)이나 진(辰)
  *@param String $month_e 생월 월지
  *@param String $e : 일지를 제외한 지지
  */

  public function gepgaksal() {
    $this->gepgaksal['y'] = $this->calGepgaksal($this->month_e, $this->year_e);
    $this->gepgaksal['d'] = $this->calGepgaksal($this->month_e, $this->day_e);
    $this->gepgaksal['h'] = $this->calGepgaksal($this->month_e, $this->hour_e);
  }

  private function calGepgaksal($month_e, $e) {
    switch($month_e){
      case '寅': case '卯': case '辰':
        if ($e == '亥' || $e == '子') { return '급각살';} break;
      case '巳': case '午': case '未':
        if ($e == '卯' || $e == '未') { return '급각살';} break;
      case '申': case '酉': case '戌':
        if ($e == '寅' || $e == '戌') { return '급각살';} break;
      case '亥': case '子': case '丑':
        if ($e == '丑' || $e == '辰') { return '급각살';} break;
    }
    return null;
  }

  /**
  * 지살(地殺) 구하기  (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */
  private function hasJisal($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '申') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '寅') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '巳') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '亥') { return true;} break;
    }
    return false;
  }

  /**
  * 연살(年殺)/도화살 구하기  (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */

  private function hasYeunsall($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '酉') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '卯') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '午') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '子') { return true;} break;
    }
    return false;
  }

  /**
  * 월살(月殺) 구하기 (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */
  private function hasWolsal($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰': // 신자진
        if ($e == '戌') { return true;} break;
      case '巳': case '酉': case '丑': // 사유축
        if ($e == '未') { return true;} break;
      case '寅': case '午': case '戌': // 인오술
        if ($e == '辰') { return true;} break;
      case '亥':case '卯': case '未': // 해묘미
        if ($e == '丑') { return true;} break;
    }
    return false;
  }

  /**
  * 망신살(亡身殺) 구하기 (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */
  private function hasMangsinsal($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '亥') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '巳') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '申') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '寅') { return true;} break;
    }
    return false;
  }

  /**
  * 장성살(將星殺) 구하기 (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */
  private function hasJangsungsal($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '子') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '午') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '酉') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '卯') { return true;} break;
    }
    return false;
  }

  /**
  * 반안살(攀鞍殺) 구하기  (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */
  private function hasBanansal($my_e, $date_e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($date_e == '丑') { return true;} break;
      case '寅': case '午': case '戌':
        if ($date_e == '未') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($date_e == '戌') { return true;} break;
      case '亥':case '卯': case '未':
        if ($date_e == '辰') { return true;} break;
    }
    return false;
  }

  /**
  * 역마살(驛馬殺) 구하기 (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */
  public function yeokmasal() {
    $this->yeokmasal['y'] = $this->calYeokmasal($this->year_e, $this->year_e);
    $this->yeokmasal['m'] = $this->calYeokmasal($this->year_e, $this->month_e);
    $this->yeokmasal['d'] = $this->calYeokmasal($this->year_e, $this->year_e);
    $this->yeokmasal['h'] = $this->calYeokmasal($this->year_e, $this->hour_e);
  }

  private function calYeokmasal($my_e, $e) {
    if ($this->hasYeokmasal($my_e, $e)) {
      return '역마살';
    } else {
      return null;
    }
  }
  private function hasYeokmasal($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰': // 신자진
        if ($e == '寅') { return true;} break;
      case '寅': case '午': case '戌': // 인오술
        if ($e == '申') { return true;} break;
      case '巳': case '酉': case '丑':  // 사유축
        if ($e == '亥') { return true;} break;
      case '亥':case '卯': case '未': // 해묘미
        if ($e == '巳') { return true;} break;
    }
    return false;
  }

  /**
  * 육해살(六害살) 구하기  (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */
  private function hasYukhaesal($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '卯') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '酉') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '子') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '午') { return true;} break;
    }
    return false;
  }

  /**
  * 화개살(華蓋살) 구하기 (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */
  private function hasWhagaesal($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '辰') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '戌') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '丑') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '未') { return true;} break;
    }
    return false;
  }

  /**
  * 겁살(劫殺) 구하기 (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 올해의 년지
  * 삼살은 겁살, 재살, 천살을 의미
  */

  private function hasGubsal($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '巳') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '亥') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '寅') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '申') { return true;} break;
    }
    return false;
  }

  /**
  * 재살(劫殺){수옥살} 구하기 災殺 (12신살)
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */

  public function jaesal() {
    $this->jaesal['y'] = $this->calJaesal($this->day_e, $this->year_e);
    $this->jaesal['m'] = $this->calJaesal($this->day_e, $this->month_e);
    $this->jaesal['d'] = $this->calJaesal($this->year_e, $this->day_e);
    $this->jaesal['h'] = $this->calJaesal($this->day_e, $this->hour_e);
  }

  private function calJaesal($day_e, $e) {
    if($this->hasJaesal($day_e, $e)) {
      return '재살'; // 수옥살
    } else {
      return null;
    }
  }

  private function hasJaesal($day_e, $e) {
    switch($day_e){
      case '申': case '子': case '辰':
        if ($e == '午') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '子') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '卯') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '酉') { return true;} break;
    }
    return false;
  }

  /**
  * 수옥살 구하기 (위의 재살과 동일)
  * @deprecated
  * 수옥살(일지,년지으로 검색)=>먼저 일지를 중심으로 3지를 보고 다음 연지를 중심으로 일지만 본다(연일지를 둘다보는 경우는 이렇게 처리하도록한다.)
  *@param String $day_e 생월 일지 (일지에 대한 수옥살을 구할경우 $day_e 에 $year_e가 온다)
  *@param String $e : 일지를 제외한 지지
  */
  /*
  public function suoksal() {
    $this->suoksal['y'] = $this->calSuoksal($this->day_e, $this->year_e);
    $this->suoksal['m'] = $this->calSuoksal($this->day_e, $this->month_e);
    $this->suoksal['d'] = $this->calSuoksal($this->year_e, $this->day_e);
    $this->suoksal['h'] = $this->calSuoksal($this->day_e, $this->hour_e);
  }

  private function calSuoksal($day_h, $e) {
    if($this->hasSuoksal($day_h, $e)) {
      return '수옥살';
    } else {
      return null;
    }
  }

  private function hasSuoksal($day_e, $e) {
    $str = $day_e.$e;
    $arr = ['子午','申午','辰午','寅子','午子','戌子','酉卯','丑卯','巳卯','未酉','亥酉','卯酉'];
    return in_array($str, $arr);
  }
  */
  
  /**
  * 천살(天殺) 구하기 (12신살) // select day chensal 과 다름 나중에 확인 요망
  *@param String $my_e 생년지(혹은 생일지)
  *@param String $e : 현재의 년지
  */
  private function hasCheunsal($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '未') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '丑') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '辰') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '戌') { return true;} break;
    }
    return false;
  }
  

  /**
  * 삼재살(三災殺)들삼재
  *@param String $my_e 생년지
  *@param String $e : 오늘의 년지
  */
  private function hasSamjae1($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '寅') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '巳') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '申') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '亥') { return true;} break;
    }
    return false;
  }

  /**
  * 삼재살(三災殺)누울삼재
  *@param String $my_e 생년지
  *@param String $e : 오늘의 년지
  */
  private function hasSamjae2($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '卯') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '午') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '酉') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '子') { return true;} break;
    }
    return false;
  }

  /**
  * 삼재살(三災殺)날삼재
  *@param String $my_e 생년지
  *@param String $e : 오늘의 년지
  */
  private function hasSamjae3($my_e, $e) {
    switch($my_e){
      case '申': case '子': case '辰':
        if ($e == '辰') { return true;} break;
      case '亥':case '卯': case '未':
        if ($e == '未') { return true;} break;
      case '寅': case '午': case '戌':
        if ($e == '戌') { return true;} break;
      case '巳': case '酉': case '丑': 
        if ($e == '丑') { return true;} break;
    }
    return false;
  }

/**
  * 도화살(桃花殺) (년살)
  *@param String $month_e 생월 월지
  *@param String $e : 일지를 제외한 지지
  */
  // private function hasDowhasal($my_e, $date_e) {
  //   switch($my_e){
  //     case '申': case '子': case '辰':
  //       if ($date_e == '酉') { return true;} break;
  //     case '亥':case '卯': case '未':
  //       if ($date_e == '子') { return true;} break;
  //     case '寅': case '午': case '戌':
  //       if ($date_e == '卯') { return true;} break;
  //     case '巳': case '酉': case '丑': 
  //       if ($date_e == '午') { return true;} break;
  //   }
  //   return false;
  // }

 /**
   * 천강(흉신)
   */
  static function hasChengang($month_e, $e) {
    $str = $month_e.$e;
    $arr = ['寅巳','卯子','辰未','巳寅','午酉','未辰','申亥','酉午','戌丑','亥申','子卯','丑戌'];
    return in_array($str, $arr);
  }

}
