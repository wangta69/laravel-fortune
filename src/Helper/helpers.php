<?php

function pad_zero1()
{
    return ji;
}

if (! function_exists('pad_zero')) {
    function pad_zero($no, $digit = 2)
    {
        return str_pad($no, $digit, '0', STR_PAD_LEFT);
    }
}

/**
 * 현재 나누는 값보다 클경우는 나누고 아니면 입력값을 입력
 * 모드를 사용할 경우 0이 나오는 것을 피하기 위해
 */
// mod 를 보정하여 값 넘기기
if (! function_exists('mod_zero_to_mod')) {
    function mod_zero_to_mod($number, $mod)
    {
        $result = $number % $mod;
        $result = $result ? $result : $mod;

        return $result;
    }
}

if (! function_exists('user_date_format')) {
    /**
     * @params String $date 2020-20, 202020, 2020-20-20, 2020.20
     * @params String $format []: return as array,, -, null, .
     */
    function str_date_format($date, $format = null)
    {

        if (gettype($date) == 'array') {

            foreach ($date as $v) {
                $v = pad_zero($v, 2);
            }

            $val = $date;
        } elseif (preg_match('|^([0-9]{1,4})[-.]?([0-9]{1,2})[-.]?([0-9]{1,2})?$|', trim($date), $match)) {
            @[, $y, $m, $d] = $match;

            $val = [];
            array_push($val, $y);
            array_push($val, pad_zero($m, 2));
            $d ? array_push($val, pad_zero($d, 2)) : null;
        } else {
            return false;
        }

        switch ($format) {
            case '[]': return $val;
            default: return implode($format, $val);
        }
    }
}
/**
 * 맨 앞을 맨뒤로 보낸다.
 */
if (! function_exists('arr_forward_rotate')) {
    function arr_forward_rotate($array, $distance)
    {
        for ($i = 0; $i < $distance; $i++) {
            array_push($array, array_shift($array));
        }

        return $array;
    }
}

/**
 * 맨 뒤를 맨 앞으로 보낸다.
 */
if (! function_exists('arr_reverse_rotate')) {
    function arr_reverse_rotate($array, $distance)
    {
        for ($i = 0; $i < $distance; $i++) {
            array_unshift($array, array_pop($array));
        }

        return $array;
    }
}

// 년도의의 뒷자리를 기준으로 색상 가져오기
if (! function_exists('zodiac_color')) {
    function zodiac_color($h)
    {
        switch ($h) {
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
if (! function_exists('tr_code')) {
    function tr_code($from, $to, $val)
    {

        $val_type = gettype($val);

        if ($val_type == 'array') {
            $rtn = [];
            foreach ($val as $v) {
                // echo $v;
                $key = array_keys($from, $v)[0];
                if ($key) {
                    array_push($rtn, $to[$key]);
                } else {
                    array_push($rtn, null);
                }
            }

            return $rtn;
        } else { // string
            $key = array_search($val, $from);

            // 키를 찾았는지 확인합니다 (0도 유효한 키로 처리).
            if ($key !== false) {
                return $to[$key];
            }
        }

        return null;
    }
}
/**
 * 지지를 시리얼로 변경 (배열처리시)
 */
if (! function_exists('e_to_serial')) {
    function e_to_serial($e, $pad = false)
    {

        switch ($e) {
            case '子': $no = 0;
                break; // 자
            case '丑': $no = 1;
                break; // 축
            case '寅': $no = 2;
                break; // 인
            case '卯': $no = 3;
                break; // 묘
            case '辰': $no = 4;
                break; // 진
            case '巳': $no = 5;
                break; // 사
            case '午': $no = 6;
                break; // 오
            case '未': $no = 7;
                break; // 미
            case '申': $no = 8;
                break; // 신
            case '酉': $no = 9;
                break; // 유
            case '戌': $no = 10;
                break; // 술
            case '亥': $no = 11;
                break; // 해
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
if (! function_exists('e_to_wolgun')) {
    function e_to_wolgun($g, $pad = false)
    {
        switch ($g) {
            case '子': $no = 11;
                break; // 자
            case '丑': $no = 12;
                break; // 축
            case '寅': $no = 1;
                break; // 인
            case '卯': $no = 2;
                break; // 묘
            case '辰': $no = 3;
                break; // 진
            case '巳': $no = 4;
                break; // 사
            case '午': $no = 5;
                break; // 오
            case '未': $no = 6;
                break; // 미
            case '申': $no = 7;
                break; // 신
            case '酉': $no = 8;
                break; // 유
            case '戌': $no = 9;
                break; // 술
            case '亥': $no = 10;
                break; // 해
        }

        if ($pad == true) {
            $no = str_pad($no, 2, '0', STR_PAD_LEFT);
        }

        return $no;
    }
}

if (! function_exists('h_to_serial')) {
    function h_to_serial($h, $pad = false)
    {
        switch ($h) {
            case '甲': $no = 0;
                break;
            case '乙': $no = 1;
                break;
            case '丙': $no = 2;
                break;
            case '丁': $no = 3;
                break;
            case '戊': $no = 4;
                break;
            case '己': $no = 5;
                break;
            case '庚': $no = 6;
                break;
            case '辛': $no = 7;
                break;
            case '壬': $no = 8;
                break;
            case '癸': $no = 9;
                break;
        }
        if ($pad == true) {
            $no = str_pad($no, 2, '0', STR_PAD_LEFT);
        }

        return $no;
    }
}

/**
 * 년월을 이용해서 단순하게 계산
 */
if (! function_exists('calgabja')) {
    function calgabja($year)
    {
        // 0 ~ 11
        $h = ['庚', '辛', '壬', '癸', '甲', '乙', '丙', '丁', '戊', '己'];
        $e = ['申', '酉', '戌', '亥', '子', '丑', '寅', '卯', '辰', '巳', '午', '未'];
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
 *
 * @param  $yyyy  양력 생년
 */
if (! function_exists('korean_age')) {
    function korean_age($yyyy)
    {
        return date('Y') - $yyyy + 1;
    }
}

/**
 * 현재 나누는 값보다 클경우는 나누고 아니면 입력값을 입력
 * 모드를 사용할 경우 0이 나오는 것을 피하기 위해
 */
// mod 를 보정하여 값 넘기기
if (! function_exists('correctMod')) {
    function correctMod($mod, $number)
    {
        $result = $number % $mod;
        $result = $result ? $result : $mod;

        return $result;
    }
}

if (! function_exists('get_yeonji_from_year')) {
    /**
     * 양력 년도를 해당 해의 년지(띠)로 변환합니다.
     */
    function get_yeonji_from_year(int $year): string
    {
        // 12지신 배열 (자, 축, 인, 묘, ...)
        $jiji = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];

        // 서기 4년(갑자년)을 기준으로 계산합니다.
        // (년도 - 4)를 12로 나눈 나머지를 인덱스로 사용합니다.
        return $jiji[($year - 4) % 12];
    }
}

if (! function_exists('format_ganji')) {
    /** '乙巳' -> '을사(乙巳)' */
    function format_ganji($hanja)
    {
        if (! $hanja) {
            return '';
        }
        $idx = array_search($hanja, GANJI['ch']);

        return ($idx !== false) ? GANJI['ko'][$idx]."({$hanja})" : $hanja;
    }
}

if (! function_exists('format_gan')) {
    /** '乙' -> '을(乙)' */
    function format_gan($hanja)
    {
        if (! $hanja) {
            return '';
        }
        $idx = array_search($hanja, GAN['ch']);

        return ($idx !== false) ? GAN['ko'][$idx]."({$hanja})" : $hanja;
    }
}

if (! function_exists('format_ji')) {
    /** '巳' -> '사(巳)' */
    function format_ji($hanja)
    {
        if (! $hanja) {
            return '';
        }
        $idx = array_search($hanja, JI['ch']);

        return ($idx !== false) ? JI['ko'][$idx]."({$hanja})" : $hanja;
    }
}

if (! function_exists('get_jiji_ohaeng_str')) {
    /**
     * "오(午)에 있는 화(火)의 기운" 형태의 문자열 반환
     *
     * @param  string  $ji_ch  (한자 지지: 子, 丑, 寅...)
     */
    function get_jiji_ohaeng_str($ji_ch)
    {
        $ohaeng_ko = JI_OHAENG[$ji_ch] ?? '';

        // 오행 한자 매핑 (이미 GAN 상수에 있는 한자 활용)
        $ohaeng_map = ['목' => '木', '화' => '火', '토' => '土', '금' => '金', '수' => '水'];
        $ohaeng_ch = $ohaeng_map[$ohaeng_ko] ?? '';

        if (! $ohaeng_ko) {
            return $ji_ch;
        }

        return "{$ji_ch}에 있는 {$ohaeng_ko}({$ohaeng_ch})의 기운";
    }
}

if (! function_exists('get_month_from_season')) {
    /**
     * 절기 이름을 넣으면 해당 월(寅, 卯...)을 반환
     *
     * @param  string  $season_ko  (입춘, 경칩...)
     */
    function get_month_from_season($season_ko)
    {
        $index = array_search($season_ko, SEASON24['ko']);
        if ($index === false) {
            return null;
        }

        $ji_ch = SEASON_MONTH_MAP[$index];
        $ji_ko = JI['ko'][array_search($ji_ch, JI['ch'])];

        return "{$ji_ch}월({$ji_ko}월)";
    }
}

if (! function_exists('get_jiji_description')) {
    /**
     * 지지 한자를 넣으면 "오(午)에 있는 화(火)의 기운" 형태의 감성적 문구 반환
     *
     * @param  string  $ji_ch  (子, 丑, 寅...)
     * @return string
     */
    function get_jiji_description($ji_ch)
    {
        // 1. 해당 지지의 오행 한글명을 가져옴 (예: 화)
        $ohaeng_ko = JI_OHAENG[$ji_ch] ?? null;
        if (! $ohaeng_ko) {
            return $ji_ch;
        }

        // 2. 오행의 한자명을 가져옴 (예: 火)
        $ohaeng_ch = OHAENG_CH[$ohaeng_ko] ?? '';

        // 3. 지지의 한글명을 가져옴 (예: 오)
        $ji_ko = JI['ko'][array_search($ji_ch, JI['ch'])] ?? '';

        // 4. 최종 문장 조립
        return "{$ji_ko}({$ji_ch})에 있는 {$ohaeng_ko}({$ohaeng_ch})의 기운";
    }
}
