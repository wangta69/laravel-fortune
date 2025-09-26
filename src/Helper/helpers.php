<?php

function pad_zero1() {
  return ji;
}

if (!function_exists('pad_zero')) {
  function pad_zero($no, $digit=2) {
    return str_pad($no, $digit, '0', STR_PAD_LEFT);
  }
}

/**
 * 현재 나누는 값보다 클경우는 나누고 아니면 입력값을 입력
 * 모드를 사용할 경우 0이 나오는 것을 피하기 위해
 */
// mod 를 보정하여 값 넘기기
if (!function_exists('mod_zero_to_mod')) {
  function mod_zero_to_mod($number, $mod) {
    $result = $number % $mod;
    $result = $result ? $result : $mod;
    return $result;
  }
}


if (!function_exists('user_date_format')) {
  /**
   * @params String $date 2020-20, 202020, 2020-20-20, 2020.20
   * @params String $format []: return as array,, -, null, . 
   */
  function str_date_format($date, $format=null) {

    if(gettype($date) == 'array') {
      
      foreach($date as $v) {
        $v = pad_zero($v, 2);
      }

      $val = $date;
    } else if ( preg_match ('|^([0-9]{1,4})[-.]?([0-9]{1,2})[-.]?([0-9]{1,2})?$|', trim ($date), $match) ) {
      @list(,$y,$m,$d) = $match;
      
      $val = [];
      array_push($val, $y);
      array_push($val, pad_zero($m, 2));
      $d ? array_push($val, pad_zero($d, 2)) : null;
    } else {
      return false;
    }

    switch($format) {
      case '[]': return $val;
      default: return implode($format, $val);
    }
  }
}
/**
 * 맨 앞을 맨뒤로 보낸다.
 */
if (!function_exists('arr_forward_rotate')) {
  function arr_forward_rotate($array, $distance) {
    for ($i = 0; $i < $distance; $i++) {
      array_push($array, array_shift($array));
    }
    return $array;
  }
}

/**
 * 맨 뒤를 맨 앞으로 보낸다.
 */
if (!function_exists('arr_reverse_rotate')) {
  function arr_reverse_rotate($array, $distance) {
    for ($i = 0; $i < $distance; $i++) {
      array_unshift($array, array_pop($array));
    }
    return $array;
  }
}

// 년도의의 뒷자리를 기준으로 색상 가져오기
if (!function_exists('zodiac_color')) {
  function zodiac_color($h) {
    switch($h) {
      case '甲': case '乙':// 갑 을'', '', '', '', '', '', '', ', '', ''
        return '푸른'; // 청
      case '丙': case '丁': // 병정
        return '붉은'; // 적
      case '戊': case '己': // 무기
        return '황금'; // 황
      case '庚': case '辛': // 경신
        return '하얀'; // 백
      case '壬': case '癸': // 임계
        return '검은'; // 흑
    }
    
  }
}

// 특정글자의 위치를 얻어와서 다른 언어의 동일 위치의 값을 가져온다.  
if (!function_exists('tr_code')) {
  function tr_code($from, $to, $val) {

    $val_type = gettype($val);

    if($val_type == 'array') {
      $rtn = [];
      foreach($val as $v) {
        // echo $v;
        $key = array_keys($from, $v)[0];
        if($key) {
          array_push($rtn, $to[$key]);
        } else {
          array_push($rtn, null);
        }
      }
      return $rtn;
    } else { // string
      $key = array_keys($from, $val)[0];
      if($key) {
        return $to[$key];
      }
    }
    
    return null;
  }
}
/**
 * 지지를 시리얼로 변경 (배열처리시)
 */
if (!function_exists('e_to_serial')) {
  function e_to_serial($e, $pad=false) {

    switch($e) {
      case '子': $no = 0; break;// 자
      case '丑': $no = 1; break;// 축
      case '寅': $no = 2; break; // 인
      case '卯': $no = 3; break; // 묘
      case '辰': $no = 4; break; // 진
      case '巳': $no = 5; break; // 사
      case '午': $no = 6; break; // 오
      case '未': $no = 7; break; // 미
      case '申': $no = 8; break; // 신
      case '酉': $no = 9; break; //유
      case '戌': $no = 10; break; // 술
      case '亥': $no = 11; break; //해
    }

    if ($pad == true) {
      $no = str_pad($no, 2, '0', STR_PAD_LEFT);
    }

    return $no;
  }
}

  /**
   * 월건을 볼때는 11월이 자 가 되고 1월이 인이 된다.
   */
if (!function_exists('e_to_wolgun')) {
  function e_to_wolgun($g, $pad=false) {
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
}

if (!function_exists('h_to_serial')) {
  function h_to_serial($h, $digit=0) {

    switch($h) {
      case '甲': $no = 0; break;// 갑
      case '乙': $no = 1; break;// 을
      case '丙': $no = 2; break; // 병
      case '丁': $no = 3; break; // 정
      case '戊': $no = 4; break; // 무
      case '己': $no = 5; break; // 기
      case '庚': $no = 6; break; // 경
      case '辛': $no = 7; break; // 신
      case '壬': $no = 8; break; // 임
      case '癸': $no = 9; break; // 계
    }

    if ($digit != 0 ){
      $no = str_pad($no, $digit, '0', STR_PAD_LEFT);
    }

    return $no;
  }
}

 /**
   * 년월을 이용해서 단순하게 계산
   */
if (!function_exists('calgabja')) {
  function calgabja($year) {
    // 0 ~ 11
    $h = ['庚','辛','壬','癸','甲','乙','丙','丁','戊','己'];
    $e = ['申','酉','戌','亥', '子','丑','寅','卯','辰','巳','午','未'];
    $rtn = new \stdClass;
    $remain = $year % 10;
    $rtn->h = $h[$remain];
    $remain = $year % 12;
    $rtn->e = $e[$remain];
    
    return $rtn;
  }
  }


/**
* 양력 연도로 내 나이를 계산
*@param $yyyy 양력 생년
*/
if (!function_exists('korean_age')) {
  function korean_age($yyyy) {
    return  date('Y') - $yyyy + 1;
  }
}

/**
 * 현재 나누는 값보다 클경우는 나누고 아니면 입력값을 입력
 * 모드를 사용할 경우 0이 나오는 것을 피하기 위해
 */
// mod 를 보정하여 값 넘기기
if (!function_exists('correctMod')) {
function correctMod($mod, $number) {
  $result = $number % $mod;
  $result = $result ? $result : $mod;
  return $result;
}
}


  // function array_rotate($array, $distance = 1) {
  //   settype($array, 'array');
  //   $distance %= count($array);
  //   return array_merge(
  //       array_splice($array, $distance), // Last elements  - moved to the start
  //       $array                          //  First elements - appended to the end
  //   );
  // }

  // function array_rotate_assoc($array, $distance = 1) {
  //   $keys = array_keys((array)$array);
  //   $values = array_values((array)$array);
  //   return array_combine(
  //     array_rotate($keys, $distance),   // Rotated keys
  //     array_rotate($values, $distance) //  Rotated values
  //   );
  // }

// if (!function_exists('oheng')) {
//   // 천간 지지를 이용하여 오행 처리
//   function oheng($he){
//     $Oheng = [];
//     switch($he){
//       case '甲' : case '寅' :
//         $Oheng= oheng_lang(0);
//         $Oheng['flag'] = '+';
//         break;
//       case '乙' :  case '卯' :
//         $Oheng= oheng_lang(0);
//         $Oheng['flag'] = '-';
//         break;
//       case '丙' : case '巳' :
//         $Oheng= oheng_lang(1);
//         $Oheng['flag'] = '+';
//         break;
//       case '丁' : case '午' :
//         $Oheng= oheng_lang(1);
//         $Oheng['flag'] = '-';
//         break;
//       case '戊' : case '辰' : case '戌' :
//         $Oheng= oheng_lang(2);
//         $Oheng['flag'] = '+';
//         break;
//       case '己' : case '未' : case '丑' :
//         $Oheng= oheng_lang(2);
//         $Oheng['flag'] = '-';
//         break;
//       case '庚' : case '申' :
//         $Oheng= oheng_lang(3);
//         $Oheng['flag'] = '+';
//         break;
//       case '辛' : case '酉' :
//         $Oheng= oheng_lang(3);
//         $Oheng['flag'] = '-';
//         break;
//       case '壬' : case '亥' :
//         $Oheng= oheng_lang(4);
//         $Oheng['flag'] = '+';
//         break;
//       case '癸' : case '子' :
//         $Oheng= oheng_lang(4);
//         $Oheng['flag'] = '-';
//         break;
//     }

//     return (object)$Oheng;
//   }
// }

// if (!function_exists('oheng_lang')) {
//   // 천간 지지를 이용하여 오행 처리
//   function oheng_lang($serial){
//     $ch = ['木', '火', '土', '金', '水'];
//     $ko = ['목', '화', '토', '금', '수'];
//     $en = ['thu', 'tue', 'sat', 'fri', 'wed'];

//     return ['ch'=>$ch[$serial], 'ko'=>$ko[$serial], 'en'=>$en[$serial]];
//   }

// }


/*
// 한글을 한자로 변경
function kor_to_ch($kor) {
  switch($kor) {
      // 12 살
      case '지살': return '地殺';
      case '도화': return '桃花';
      case '월살': return '月殺';
      case '망신': return '亡身';
      case '장성': return '將星';
      case '반안': return '攀鞍';
      case '역마': return '驛馬';
      case '육해': return '六害';
      case '화개': return '華蓋';
      case '겁살': return '劫殺';
      case '재살': return '災殺';
      case '천살': return '天殺';
  }
}

*/