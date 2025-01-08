<?php
if (!function_exists('pad_zero')) {
  function pad_zero($no, $digit=2) {
    return str_pad($no, $digit, '0', STR_PAD_LEFT);
  }
}

if (!function_exists('arr_forward_rotate')) {
  function arr_forward_rotate($array, $distance) {
    for ($i = 0; $i < $distance; $i++) {
      array_push($array, array_shift($array));
    }
    return $array;
  }
}

if (!function_exists('arr_reverse_rotate')) {
  function arr_reverse_rotate($array, $distance) {
    for ($i = 0; $i < $distance; $i++) {
      array_unshift($array, array_pop($array));
    }
    return $array;
  }
}
/**
 * 지지를 시리얼로 변경 (배열처리시)
 */
if (!function_exists('e_to_serial')) {
  function e_to_serial($e, $digit=0) {

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

    if ($digit != 0 ){
      $no = str_pad($no, $digit, '0', STR_PAD_LEFT);
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
// if (!function_exists('korean_age')) {
//   function korean_age($yyyy) {
//     return  date('Y') - $yyyy + 1;
//   }
// }


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


/**
* 12신살
* 12신살은 보통 년지나 일지를 중심으로 보는 편이나 근래에는 년지를 위주로 한다. (년지 : 외부일, 일지: 집안일)
*@param String $default_e ($day_e or $year_e)
*/
/*
if (!function_exists('sal12')) {
  function sal12($default_e, $e) {
    switch($default_e) {
      case '巳': case '酉': case '丑':
        switch($e) {
          case '寅': $sal = '겁살'; break;
          case '卯': $sal = '재살'; break;
          case '辰': $sal = '천살'; break;
          case '巳': $sal = '지살'; break;
          case '午': $sal = '도화'; break;
          case '未': $sal = '월살'; break;
          case '申': $sal = '망신'; break;
          case '酉': $sal = '장성'; break;
          case '戌': $sal = '반안'; break;
          case '亥': $sal = '역마'; break;
          case '子': $sal = '육해'; break;
          case '丑': $sal = '화개'; break;
        }
        break;
      case '申': case '子': case '辰':
        switch($e) {
          case '寅': $sal = '역마'; break;
          case '卯': $sal = '육해'; break;
          case '辰': $sal = '화개'; break;
          case '巳': $sal = '겁살'; break;
          case '午': $sal = '재살'; break;
          case '未': $sal = '천살'; break;
          case '申': $sal = '지살'; break;
          case '酉': $sal = '도화'; break;
          case '戌': $sal = '월살'; break;
          case '亥': $sal = '망신'; break;
          case '子': $sal = '장성'; break;
          case '丑': $sal = '반안'; break;
        }
        break;
      case '亥': case '卯': case '未':
        switch($e) {
          case '寅': $sal = '망신'; break;
          case '卯': $sal = '장성'; break;
          case '辰': $sal = '반안'; break;
          case '巳': $sal = '역마'; break;
          case '午': $sal = '육해'; break;
          case '未': $sal = '화개'; break;
          case '申': $sal = '겁살'; break;
          case '酉': $sal = '재살'; break;
          case '戌': $sal = '천살'; break;
          case '亥': $sal = '지살'; break;
          case '子': $sal = '도화'; break;
          case '丑': $sal = '월살'; break;
        }
        break;
      case '寅': case '午': case '戌':
        switch($e) {
          case '寅': $sal = '지살'; break;
          case '卯': $sal = '도화'; break;
          case '辰': $sal = '월살'; break;
          case '巳': $sal = '망신'; break;
          case '午': $sal = '장성'; break;
          case '未': $sal = '반안'; break;
          case '申': $sal = '역마'; break;
          case '酉': $sal = '육해'; break;
          case '戌': $sal = '화개'; break;
          case '亥': $sal = '겁살'; break;
          case '子': $sal = '재살'; break;
          case '丑': $sal = '천살'; break;
        }
      break;
    }
    
    return $sal;
    

  }
}
*/
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