<?php
namespacePondol\Fortune\Services\LunarSolar\type2;
use Pondol\Fortune\Services\LunarSolar\LunarSolarBase;

class LunarSolarService extends LunarSolarBase
{
    // 그레고리안력은 1년을 365.2425일로 정하는 윤년을 포함하는 양력을 말한다.
    // 또한 세계 표준으로 사용하는 역법이다. 기본적으로 율리우스력을 그대로 따르지만 윤년을 정하는 규칙을 두가지 추가했다.
    // 1. 끝자리가 00으로 끝나는 해는 평년이다.
    // 2. 그중 400으로 나누어 떨어지는 해는 윤년이다.
    // 기존 율리우스력은 400년 동안 윤년이 약 100회지만, 그레고리안력은 97회로 줄였다.

    // 율리우스력은 그레고리안력의 기초가 되는 양력의 기준이다. 기본 구조는 1년 365일에 4년마다 한번씩 윤년(하루를 더해 366일을 1년으로 한다.)
    // 이것으로 4년마다 한번씩 2월 29일이 생기는 이유이다.

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // YYYY-MM-DD 형식의 날짜를 반환
    // $date = array ($year, $month, $day)
    public static function get_hyphen_date ($date)
    {
        list ($year, $month, $day) = $date;

        return sprintf (
            '%d-%s%d-%s%d',
            $year,
            ($month < 10) ? '0' : '',
            (int) $month,
            ($day < 10) ? '0' : '',
            (int) $day
        );
    }

    // AD/BC 타입의 연도를 반환
    /*
        input    :
            LunarSolar::get_readable_year (-2333);
        output    :
            BC 2333
    */
    public static function get_readable_year ($year)
    {
        if($year < 1)
        {
            $year = ($year * -1) + 1;
            $type = 'BC';
        }
        else
            $type = 'AD';

        return sprintf ('%s %d', $type, $year);
    }

    // YYYY-MM-DD 또는 array ((string) YYYY, (string) MM, (string) DD 입력값을 array ((int) $year, (int) $month, (int) $day))로 변환
    // $date = YYYY-MM-DD
    // $date = array ((string) YYYY, (string) MM, (stirng) DD)
    public static function get_split_date ($date)
    {
        if(is_array ($date))
            $date = self::get_hyphen_date ($date);

        $minus = ($date[0] == '-') ? true : false;
        $date = $minus ? substr ($date, 1) : $date;

        $result = preg_split ('/-/', $date);
        if($minus)
            $result[0] *= -1;

        foreach($result AS $key => $value)
        {
            $result[$key] = (int) $value;
        }

        return $result;
    }

    // 입력된 날짜 형식을 연/월/일의 배열로 반환
    /*
        input    :
            LunarSolar::convert_to_args (
                2013-07-13    or
                2013-7-13    or
                20130713    or
                1373641200    or
                NULL
            );
        output    :
            Array
            (
                [0]    => 2013,
                [1]    => 7,
                [2]    => 13,
            );
    */
    public static function convert_to_args (&$date, $lunar_date = false)
    {
        if($date == null)
        {
            $year = (int) date ('Y');
            $month = (int) date ('m');
            $day = (int) date ('d');
        }
        else
        {
            if(is_numeric($date) && $date > 30000000)
            {
                $year = (int) date ('Y', $date);
                $month = (int) date ('m', $date);
                $day = (int) date ('d', $date);
            }
            else
            {
                if(preg_match ('/^(-?[0-9]{1,4})[\/-]?([0-9]{1,2})[\/-]?([0-9]{1,2})$/', trim ($date), $match))
                {
                    array_shift ($match);
                    list ($year, $month, $day) = $match;
                }
                else
                {
                    throw new Exception('Invalid Date Format');
                    return false;
                }
            }

            // 날짜가 음력일 경우 아래가 실행이 되면 측정되는 날짜가 달라질 수 있음
            if(!$lunar_date && $year > 1969 && $year < 2038)
            {
                $fixed_date = mktime (0, 0, 0, $month, $day, $year);
                $year = (int) date ('Y', $fixed_date);
                $month = (int) date ('m', $fixed_date);
                $day = (int) date ('d', $fixed_date);
            }
            else
            {
                if($month > 12 || $day > 31)
                {
                    throw new Exception('Invalid Date Format');
                    return false;
                }
            }
        }
        $date = self::get_hyphen_date (array ($year, $month, $day));

        return array ($year, $month, $day);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // 윤년인지 체크
    /*
        input    :
            LunarSolar::is_leap(1992);
        output    :
            true
    */
    // 1582년 이전은 율리우스 달력으로 판단, 또한 false도 율리우스력으로 간주해 판단
    public function is_leap ($year, $julian = false)
    {
        if($julian || $year < 1583)
            return ($year % 4) ? false : true;

        if(($year % 400) == 0)
            return true;

        if(($year % 4) == 0 && ($year % 100) != 0)
            return true;

        return false;
    }

    // 해당 날짜가 그레고리안 범위인지 체크
    public function is_gregorian ($year, $month, $day = 1)
    {
        if((int) $month < 10)
            $month = '0'.(int) $month;
        if((int) $day < 10)
            $day = '0'.(int) $day;

        $check = $year.$month.$day;

        if($check < 15821015)
            return false;

        return true;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // 그레고리안력을 율리우스력으로 변환
    /*
    stdClass Object
    (
        [julian(fmt)]    => 2013-06-09    // YYYY-MM-DD 형식의 율리우스 날짜
        [year]            => 2013            // 연도
        [month]            => 6            // 월
        [day]            => 9            // 일
        [week]            => 화            // 요일
    );
    */
    // $date = 그레고리안 연/월/일 배열 또는 Julian date count
    public static function convert_gregorian_to_julian ($date)
    {
        if(is_array ($date))
        {
            $hyphen_date = self::get_hyphen_date ($date);
            list ($year, $month, $day) = self::get_split_date($hyphen_date);

            $date = self::convert_gregorian_date_to_julian_date(array ($year, $month, $day));
        }

        if(extension_loaded ('calendar'))
        {
            $result_date = (object) cal_from_jd ($date, CAL_JULIAN);
            if($result_date -> year < 0)
                $result_date -> year ++;

            return (object) array (
                'fmt'    => self::get_hyphen_date(
                                                array (
                                                    $result_date -> year,
                                                    $result_date -> month,
                                                    $result_date -> day
                                                    )
                                            ),
                'year'    => $result_date -> year,
                'month'    => $result_date -> month,
                'day'    => $result_date -> day,
                'week'    => $result_date -> dow
            );
        }

        if(is_float ($date))
            list ($X, $Y) = preg_split ('/\./', $date);
        else
        {
            $X = $date;
            $Y = 0;
        }

        if($date < 2299161)
            $A = $X;
        else
        {
            $alpha = (int) ($X - 1867216.25 / 36524.25);
            $A = $X + 1 + $alpha - (int) ($alpha / 4);
        }

        $B = $A + 1524;
        $C = (int) (($B - 122.1) / 365.25);
        $D = (int) (365.25 * $C);
        $E = (int) (($B - $D) / 30.6001);

        $day    = $B - $D - (int) (30.6001 *$E) + $F;
        $month    = ($E < 14) ? $E - 1 : $E - 13;
        $year    = $C - 4715;
        if($month > 2)
            $year --;

        $week = ($date + 1.5) % 7;

        return (object) array (
            'fmt'    => self::get_hyphen_date (array ($year, $month, $day)),
            'year'    => $year,
            'month'    => $month,
            'day'    => $day,
            'week'    => $week
        );
    }

    public static function get_modulus ($value_1, $value_2)
    {
        return ($value_1 % $value_2 + $value_2) % $value_2;
    }

    // 율리우스력을 그레고리안력으로 변환
    /*
    stdClass Object
    (
        [gregorian(fmt)]    => 2013-06-09    // YYYY-MM-DD 형식의 Julian 날짜
        [year]                => 2013            // 연도
        [month]                => 6            // 월
        [day]                => 9            // 일
        [week]                => 화            // 요일
    );
    */
    // $julian_date = 율리우스 연/월/일 배열 또는 Julian date count
    public static function convert_julian_to_gregorian ($julian_date, $pure = false)
    {
        if(is_array ($julian_date))
        {
            list ($year, $month, $day) = self::get_split_date ($julian_date);
            $julian_date = self::calculate_to_julian_date (array ($year, $month, $day), true);
        }

        if(extension_loaded ('calendar') && $pure == false)
        {
            $result_date = (object) cal_from_jd ($julian_date, CAL_GREGORIAN);
            if($result_date -> year < 0)
                $result_date -> year ++;

            return (object) array (
                'fmt'    => self::get_hyphen_date (
                                                array (
                                                    $result_date -> year,
                                                    $result_date -> month,
                                                    $result_date -> day
                                                    )
                                            ),
                'year'    => $result_date -> year,
                'month'    => $result_date -> month,
                'day'    => $result_date -> day,
                'week'    => $result_date -> dow,
            );
        }
        /* 01-01-02 부터 이전은 맞지 않음 */
        // $a = (int) $julian_date + 1401;
        // $a = (int) ($a + (((4 * $julian_date + 274277) / 146097) * 3) / 4 - 38);
        // $b = 4 * $a + 3;
        // $c = (int) (($b % 1461) / 4);
        // $d = 5 * $c + 2;
        // $day        = (int) (($d % 153) / 5 + 1);
        // $month    = (int) ((($d / 153 + 2) % 12) + 1);
        // $year    = (int) ($b / 1461 - 4716 + (12 + 2 - $month) / 12);

        $re_julian_date = floor ($julian_date - 0.5) + 0.5;
        // GREGORIAN_EPOCH 1721425.5
        $depoch = $re_julian_date - 1721425.5;
        $quadricent = floor ($depoch / 146097);
        $dqc = self::get_modulus ($depoch, 146097);
        $cent = floor ($dqc / 36524);
        $decent = self::get_modulus ($dqc, 36524);
        $quad = floor ($decent / 1461);
        $dquad = self::get_modulus ($dcent, 1461);
        $index = floor ($dquad / 365);

        $year = ($quadricent * 400) + ($cent * 100) + ($quad * 4) + $index;
        if(!($cent == 4 || $index == 4))
            $year ++;

        $year_day = $re_julian_date - self::calculate_to_julian_date (array ($year, 1, 1));
        $leap = $re_julian_date < self::calculate_to_julian_date (array ($year, 3, 1)) ? 0 : (self::is_leap ($year) ? 1 : 2);
        $month = floor (((($year_day + $leap) * 12) + 373) / 367);
        $day = ceil ($re_julian_date - self::calculate_to_julian_date (array ($year, $month, 1))) + 1;

        $week = ($julian_date + 1.5) % 7;

        return (object) array (
            'fmt'    => self::get_hyphen_date (array ($year, $month, $day)),
            'year'    => $year,
            'month'    => $month,
            'day'    => $day,
            'week'    => $week
        );
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // 그레고리안 날짜를 Julian date로 변환 (by PURE PHP CODE)
    /*
    1. Y = 해당년도, M = 월(1월 = 1, 2월 = 2), D = 해당 월의 날짜.
        D는 시간값도 포함한 소수값을 생각해야한다. 예시로 3일 12시 UT라면 D = 3.5 이다.
    2. M > 2인 경우 year, M 변경하지 않는다. M = 1 or 2인경우 Y = Y - 1, M = M + 12로 계산한다.
    3. 그레고리안력의 경우는 아래와 같이 계산한다.
        A = INT(Y / 100), B = 2 - A + INT(A / 4)
        여기서 INT는 ()안에 들어간 값을 넘지 않는 가장 큰 정수이다.
    4. Julian date의 계산은 아래와 같다.
        JULIAN_DATE = INT(365.25 (Y + 4716)) + INT(30.6001 (M + 1)) + D + B - 1524.5
        여기서 30.6001은 30.6을 써야한다. 하지만 컴퓨터 계산기 10.6이여 하는데 10.5999...로 표현되는 경우가 발생시에는
        INT(10.6)과 INT(10.5999...)의 결과가 달라진다. 따라서 이 문제에 대처하기 위해 30.6001을 사용한 것이다.
    */
    // $date = array($year, $month, $day)
    public static function calculate_to_julian_date_pure ($date, $julian = false)
    {
        list ($year, $month, $day) = $date;

        if($month <= 2)
        {
            $year --;
            $month += 12;
        }

        $A = (int) ($year / 100);
        $B = $julian ? 0 : 2 - $A + (int) ($A / 4);
        $C = (int) (365.25 * ($year + 4716));
        $D = (int) (30.6001 * ($month + 1));

        return ceil ($C + $D + $day + $B - 1524.5);
    }

    // 그레고리안 날짜를 Julian date로 변환 (by Calendar Extendsion)
    // $date = array ($year, $month, $day)
    public static function calculate_to_julian_date_ext ($date, $julian = false)
    {
        list ($year, $month, $day) = $date;

        $timezone = date_default_timezone_get ();
        date_default_timezone_set ('UTC');

        $correct_julian = $julian ? 'JulianToJulianDate' : 'GregorianToJulianDate';
        if($year < 1)
            $year --;

        $julian_result = $correct_julian ((int) $month, (int) $day, (int) $year);

        date_default_timezone_set ($timezone);
        return $julian_result;
    }

    // 그레고리안 날짜를 Julian date로 변환
    // $date = array ($year, $month, $day)
    public static function calculate_to_julian_date ($date, $julian = false)
    {
        if(extension_loaded ('calendar'))
            return self::calculate_to_julian_date_ext ($date, $julian);

        return self::calculate_to_julian_date_pure ($date, $julian);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Localtime을 UTC로 변환
    // $date = dateformat (YYYY-MM-DD HH:II:SS)
    public static function convert_local_time_to_UTC ($date)
    {
        $time = strtotime ($date);
        $timezone = date_default_timezone_get ();
        date_default_timezone_set ('UTC');

        $time_result = date ('Y-m-d-H-i-s', $time);
        date_default_timezone_set ($timezone);

        return $time_result;
    }

    // 합삭/망 절기 시간을 UTC로 변환 후, Julian date로 반환
    // $date = dateformat (YYYY-MM-DD HH:II:SS)
    public static function convert_date_to_utc_julian ($date)
    {
        $utc_date = self::convert_local_time_to_UTC ($date);
        list ($year, $month, $day, $hour, $min, $sec) = self::get_split_date ($utc_date);

        $check = $year.$month.$day;
        $julian = ($check < 18451015) ? true : false;
        $julian_result = self::calculate_to_julian_date (array ($year, $month, $day), $julian);

        if(($hour - 12) < 0)
        {
            $hour = 11 - $hour;
            $min = 60 - $min;
            $utc_date = (($hour * 3600 + $min * 60) / 86400) * -1;
        }
        else
            $utc_date = (($hour - 12) * 3600 + $min * 60) / 86400;

        return $julian_result + $utc_date;
    }

    // 1582년 10월 15일 이전의 date를 Julian date로 변환
    public static function fix_calendar ($year, $month, $day)
    {
        if($month < 10)
            $month = '0'.$month;
        if($day < 10)
            $day = '0'.$day;

        // 15821005 ~ 15821014 까지는 그레고리안 달력에서 존재하지 않는다.
        // 따라서 이 기간의 날짜는 율리우스 달력과 같은 날짜로 변경한다. (10씩 빼준다.)
        $check = $year.$month.$day;
        if($check > 15821004 && $check < 15821015)
        {
            $julian = self::calculate_to_julian_date (array ((int) $year, (int) $month, (int) $day));
            $julian -= 10;
            $julian_result = self::convert_julian_to_gregorian ($julian);
            list ($year, $month, $day) = array (
                $julian_result -> year,
                $julian_result -> month,
                $julian_result -> day
            );
        }

        // 15821005 보다 과거의 날짜는 그레고리안 달력이 없기 때문에 율리우스 달력으로 표현한다.
        if(self::is_gregorian ((int) $year, (int) $month, (int) $day) === false)
        {
            $julian_result = self::convert_julian_to_gregorian (array ((int) $year, (int) $month, (int) $day));
            list ($year, $month, $day) = array (
                $julian_result -> year,
                $julian_result -> month,
                $julian_result -> day
            );
        }

        return array ($year, $month, $day);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // 양력 -> 음력 변환
    /*
        input    :
            LunarSolar::convert_to_lunar (
                2013-07-16    or
                2013-7-16    or
                20130716    or
                1373900400    or
                NULL
            );

        output    :
            stdClass Object
            (
                [fmt]            => 2013-06-09    // YYYY-MM-DD 형식의 음력 날짜
                [dangi]            => 4346            // 단기(단군기원, 檀君紀元) 서기 2020년 = 단기 4353년
                [hyear]            => AD 2013        // AD/BC 형식의 연도
                [year]            => 2013            // 연도
                [month]            => 6            // 월
                [day]            => 9            // 일
                [leap]            => (boolean)    // 음력 윤달 여부
                [large_month]    => 1            // 평달(소월, 小月) / 큰달(대월, 大月) 여부
                [kor_week]        => 화            // 요일
                [han_week]        => 火            // 한자 요일
                [unixstamp]        => 1373900400    // unixstamp (양력)
                [kor_ganji]        => 계사            // 세차(년)
                [han_ganji]        => 癸巳            // 한자 세차(년)
                [kor_gan]        => 계            // 10간
                [han_gan]        => 癸            // 한자 십간
                [kor_ji]        => 사            // 십이지
                [han_ji]        => 巳            // 한자 십이지
                [zodiac]        => 뱀            // 띠
            );
    */
    // $date의 형식(int 또는 string)
    // 1. unixstmap (1970년 12월 15일 이후부터만 가능)
    // 2. Ymd or Y-m-d
    // 3. null date (현재 시간)
    // 4. 1582년 10월 15일 이전의 날짜는 율리우스력의 날짜로 취급
    public static function convert_to_lunar ($date = null)
    {

        list ($year, $month, $day) = self::convert_to_args ($date);
        list ($year, $month, $day) = self::fix_calendar ($year, $month, $day);

        $lunar_result = LunarSolarBase::convert_solar_to_lunar ($year, $month, $day);
        list ($year, $month, $day, $leap, $large_month) = $lunar_result;

        $week = LunarSolarBase::get_week_day_by_gregorian ($year, $month, $day);

        $count_value_1 = ($year + 6) % 10;
        $count_value_2 = ($year + 8) % 12;

        if($count_value_1 < 0)
            $count_value_1 += 10;
        if($count_value_2 < 0)
            $count_value_2 += 12;

        return (object) array (
            'fmt' => self::get_hyphen_date ($lunar_result),
            'dangi' => $year + 2333,
            'hyear' => self::get_readable_year ($year),
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'leap' => $leap,
            'large_month' => $large_month,
            'kor_week' => LunarSolarBase::KOR_WEEK[$week],
            'han_week' => LunarSolarBase::HAN_WEEK[$week],
            'unixstamp' => mktime (0, 0, 0, $month, $day, $year),
            'kor_ganji' => LunarSolarBase::KOR_GAN[$count_value_1].LunarSolarBase::KOR_JI[$count_value_2],
            'han_ganji' => LunarSolarBase::HAN_GAN[$count_value_1].LunarSolarBase::HAN_JI[$count_value_2],
            'kor_gan' => LunarSolarBase::KOR_GAN[$count_value_1],
            'han_gan' => LunarSolarBase::HAN_GAN[$count_value_1],
            'kor_ji' => LunarSolarBase::KOR_JI[$count_value_2],
            'han_ji' => LunarSolarBase::HAN_JI[$count_value_2],
            'zodiac' => LunarSolarBase::ZODIAC[$count_value_2]
        );
    }

    // 음력 -> 양력 변환
    /*
        input    :
            LunarSolar::convert_to_solar (
                2013-06-09    or
                2013-6-9    or
                20130609    or
                NULL
            );
        output    :
            stdClass Object
            (
                [jd] => 2456527 // Julian Date Count
                [fmt] => 2013-07-16 // YYYY-MM-DD 형식의 날짜 (15821015 이전은 율리우스력)
                [gregory] => 2013-07-16 // 그레고리안 달력
                [julian] => 2013-08-09 // 율리우스 달력
                [dangi] => 4346 // 단기(단군기원, 檀君紀元) 서기 2020년 = 단기 4353년
                [hyear] => AD 2013 // AD/BC 형식의 연도
                [year] => 2013 // 연도
                [month] => 7 // 월
                [day] => 16 // 일
                [kor_week] => 화 // 요일
                [han_week] => 火 // 한자 요일
                [unixstamp] => 1373900400 // unixstamp (양력)
                [kor_ganji] => 계사 // 세차(년)
                [han_ganji] => 癸巳 // 한자 세차(년)
                [kor_gan] => 계 // 10간
                [han_gan] => 癸 // 한자 십간
                [kor_ji] => 사 // 십이지
                [han_ji] => 巳 // 한자 십이지
                [zodiac] => 뱀 // 띠
            );
        만약 구하려는 음력월의 윤달 여부를 모른다면 아래와 같은 확인 과정이 필요하다.
            $lunar = '2013-06-09';
            $solar_date = LunarSolar::convert_to_solar ($lunar);
            $lunar_date = LunarSolar::convert_to_lunar ($solar -> fmt);
            if($lunar != $lunar_date -> fmt)
                $solar_date = LunarSolar::convert_to_solar ($lunar, true);
    */
    // $date의 형식(int 또는 string)
    // 1. unixstmap (1970년 12월 15일 이후부터만 가능)
    // 2. Ymd or Y-m-d
    // 3. null date (현재 시간)
    // $leap = boolean (윤달 여부)
    public static function convert_to_solar ($date = null, $leap = false)
    {
        list ($year, $month, $day) = self::convert_to_args ($date, true);

        $solar_result = LunarSolarBase::convert_lunar_to_solar ($year, $month, $day, $leap);
        list ($year, $month, $day) = $solar_result;

        $week = LunarSolarBase::get_week_day_by_gregorian ($year, $month, $day);

        $julian_date = self::calculate_to_julian_date ($solar_result);
        $julian_result = self::convert_gregorian_to_julian ($julian_date);
        $julian_fmt = $julian -> fmt;
        $gregorian_fmt = self::get_hyphen_date ($solar_result);
        $fmt = ($julian_date < 2299161) ? $julian_fmt : $gregorian_fmt;

        $count_value_1 = ($year + 6) % 10;
        $count_value_2 = ($year + 8) % 12;

        if($count_value_1 < 0)
            $count_value_1 += 10;
        if($count_value_2 < 0)
            $count_value_2 += 12;

        return (object) array (
            'jd' => $julian_date,
            'fmt' => $fmt,
            'gregory' => $gregorian_fmt,
            'julian' => $julian_fmt,
            'dangi' => $year + 2333,
            'hyear' => self::get_readable_year ($year),
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'kor_week' => LunarSolarBase::KOR_WEEK[$week],
            'han_week' => LunarSolarBase::HAN_WEEK[$week],
            'unixstamp' => mktime (0, 0, 0, $month, $day, $year),
            'kor_ganji' => LunarSolarBase::KOR_GAN[$count_value_1].LunarSolarBase::KOR_JI[$count_value_2],
            'han_ganji' => LunarSolarBase::HAN_GAN[$count_value_1].LunarSolarBase::HAN_JI[$count_value_2],
            'kor_gan' => LunarSolarBase::KOR_GAN[$count_value_1],
            'han_gan' => LunarSolarBase::HAN_GAN[$count_value_1],
            'kor_ji' => LunarSolarBase::KOR_JI[$count_value_2],
            'han_ji' => LunarSolarBase::HAN_JI[$count_value_2],
            'zodiac' => LunarSolarBase::ZODIAC[$count_value_2]
        );
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // 세차(년), 월건(월), 일진(일) 데이터를 얻는다.
    /*
        input    :
            LunarSolar::get_day_fortune (
                '2013-07-16'    or
                '2013-7-16'        or
                '20130716'        or
                '1373900400'    or
                NULL
            );

        output    :
            stdClass Object
            (
                [data]        => stdClass Object
                    (
                        [year]    => 29    // 세차 index
                        [month]    => 55    // 월건 index
                        [day]    => 19    // 일진 index
                    )
                [kor_year]    => 계사    // 세차(년) 값
                [kor_month]    => 기미    // 월건(월) 값
                [kor_day]    => 계미    // 일진(일) 값
                [han_year]    => 癸巳    // 한자 세차(년) 값
                [han_month]    => 己未    // 한자 월건(월) 값
                [han_day]    => 癸未    // 한자 일진(일) 값
            );
    */
    // $date의 형식
    // 1. unixstmap (1970년 12월 15일 이후부터만 가능)
    // 2. Ymd or Y-m-d
    // 3. null date (현재 시간)
    // 4. 1582년 10월 15일 이전의 날짜는 율리우스력의 날짜로 취급
    public static function get_day_fortune ($date = null)
    {
        list ($year, $month, $day) = self::convert_to_args ($date);
        list ($year, $month, $day) = self::fix_calendar ($year, $month, $day);

        list ($sexagenary, $year, $month, $day, $hour) = LunarSolarBase::get_sexagenary_by_gregorian ($year, $month, $day, 1, 0);

        return (object) array (
            'data'        => (object) array (
                                'year'    => $year,
                                'month'    => $month,
                                'day'    => $day
                            ),
            'kor_year'    => LunarSolarBase::KOR_GANJI[$year],
            'kor_month'    => LunarSolarBase::KOR_GANJI[$month],
            'kor_day'    => LunarSolarBase::KOR_GANJI[$day],
            'han_year'    => LunarSolarBase::HAN_GANJI[$year],
            'han_month'    => LunarSolarBase::HAN_GANJI[$month],
            'han_day'    => LunarSolarBase::HAN_GANJI[$day]
        );
    }

    // 특정일의 28수를 구한다.
    /*
        input    :
            LunarSolar::get_28_day (
                '2013-07-16'    or
                '2013-7-16'        or
                '20130716'        or
                '1373900400'    or
                NULL
            );

        output    :
            stdClass Object
            (
                [data]            => 5    // 28일 데이터 index
                [kor_28_days]    => 미    // 28일 데이터 한글
                [han_28_days]    => 尾    // 28일 데이터 한자
            );
    */
    // $date의 형식
    // 1. unixstmap (1970년 12월 15일 이후부터만 가능)
    // 2. Ymd or Y-m-d
    // 3. null date (현재 시간)
    // 4. 1582년 10월 15일 이전의 날짜는 율리우스력의 날짜로 취급
    public static function get_28_day ($date = null)
    {
        if(is_object ($date))
        {
            $result = $date -> data + 1;
            if($result >= 28)
                $result %= 28;

            goto skip_work;
        }

        list ($year, $month, $day) = self::convert_to_args ($date);
        list ($year, $month, $day) = self::fix_calendar ($year, $month, $day);
        $result = LunarSolarBase::get_lunar_mansions_by_gregorian ($year, $month, $day);

        skip_work:

        return (object) array (
            'data'            => $result,
            'kor_28_days'    => LunarSolarBase::KOR_28_DAYS[$result],
            'han_28_days'    => LunarSolarBase::HAN_28_DAYS[$result]
        );
    }

    // 금월(이번달) 초입/중기와 익월(다음달) 초입 데이터 반환
    /*
        input    :
            LunarSolar::get_seasonal_date (
                '2013-07-16'    or
                '2013-7-16'        or
                '20130716'        or
                '1373900400'    or
                NULL
            );

        output    :
            stdClass Object
            (
                [this_month_entry]    => stdClass Object
                (
                    [kor_name]    => 소서
                    [han_name]    => 小暑
                    [hyear]        => AD 2013
                    [year]        => 2013
                    [month]        => 7
                    [day]        => 7
                    [hour]        => 7
                    [min]        => 49
                )
                [this_month_middle]    => stdClass Object
                (
                    [kor_name]    => 대서
                    [han_name]    => 大暑
                    [hyear]        => AD 2013
                    [year]        => 2013
                    [month]        => 7
                    [day]        => 23
                    [hour]        => 1
                    [min]        => 11
                )
                [next_month_entry]    => stdClass Object
                (
                    [kor_name]    => 입추
                    [han_name]    => 立秋
                    [hyear]        => AD 2013
                    [year]        => 2013
                    [month]        => 8
                    [day]        => 7
                    [hour]        => 17
                    [min]        => 36
                )
            );
    */
    // $date의 형식
    // 1. unixstmap (1970년 12월 15일 이후부터만 가능)
    // 2. Ymd or Y-m-d
    // 3. null date (현재 시간)
    // 4. 1582년 10월 15일 이전의 날짜는 율리우스력의 날짜로 취급
    public static function get_seasonal_date ($date = null)
    {
        list ($year, $month, $day) = self::convert_to_args ($date);
        list ($year, $month, $day) = self::fix_calendar ($year, $month, $day);

        list (
            $ingi_name, $ingi_year, $ingi_month, $ingi_day, $ingi_hour, $ingi_min,
            $mid_name, $mid_year, $mid_month, $mid_day, $mid_hour, $mid_min,
            $outgi_name, $outgi_year, $outgi_month, $outgi_day, $outgi_hour, $outgi_min
        ) = LunarSolarBase::get_season_by_gregorian ($year, $month, 20, 1, 0);

        // 금월 초입
        $julian_this_month_entry = self::convert_date_to_utc_julian (
            sprintf (
                '%s %s:%s:00',
                self::get_hyphen_date (array ($ingi_year, $ingi_month, $ingi_day)),
                $ingi_hour < 10 ? '0'.$ingi_hour : $ingi_hour,
                $ingi_min < 10 ? '0'.$ingi_min : $ingi_min
            )
        );

        // 1852-10-15 이전이면 julian으로 변경
        if(self::is_gregorian ($ingi_year, $ingi_month, $ingi_day) === false)
        {
            $result        = self::convert_gregorian_to_julian (array ($ingi_year, $ingi_month, $ingi_day));
            $ingi_year    = $result -> year;
            $ingi_month    = $result -> month;
            $ingi_day    = $result -> day;
        }

        // 금월 중기
        $julian_this_month_middle = self::convert_date_to_utc_julian (
            sprintf (
                '%s %s:%s:00',
                self::get_hyphen_date (array ($mid_year, $mid_month, $mid_day)),
                $mid_hour < 10 ? '0'.$mid_hour : $mid_hour,
                $mid_min < 10 ? '0'.$mid_min : $mid_min
            )
        );

        // 1852-10-15 이전이면 julian으로 변경
        if(self::is_gregorian ($ingi_year, $ingi_month, $ingi_day) === false)
        {
            $result        = self::convert_gregorian_to_julian (array ($ingi_year, $ingi_month, $ingi_day));
            $ingi_year    = $result -> year;
            $ingi_month    = $result -> month;
            $ingi_day    = $result -> day;
        }

        // 익월 초입
        $julian_next_month_entry = self::convert_date_to_utc_julian (
            sprintf (
                '%s %s:%s:00',
                self::get_hyphen_date (array ($outgi_year, $outgi_month, $outgi_day)),
                $outgi_hour < 10 ? '0'.$outgi_hour : $outgi_hour,
                $outgi_min < 10 ? '0'.$outgi_min : $outgi_min
            )
        );

        // 1852-10-15 이전이면 julian으로 변경
        if(self::is_gregorian ($outgi_year, $outgi_month, $outgi_day) === false)
        {
            $result            = self::convert_gregorian_to_julian (array ($outgi_year, $outgi_month, $outgi_day));
            $outgi_year        = $result -> year;
            $outgi_month    = $result -> month;
            $outgi_day        = $result -> day;
        }

        return (object) array (
            'this_month_entry' => (object) array (
                'kor_name' => LunarSolarBase::KOR_MONTH_STR[$ingi_name],
                'han_name' => LunarSolarBase::HAN_MONTH_STR[$ingi_name],
                'hyear' => self::get_readable_year ($ingi_year),
                'year' => $ingi_year,
                'month' => $ingi_month,
                'day' => $ingi_day,
                'hour' => $ingi_hour,
                'min' => $ingi_min,
                'julian' => $julian_this_month_entry
            ),
            'this_month_middle' => (object) array (
                'kor_name' => LunarSolarBase::KOR_MONTH_STR[$mid_name],
                'han_name' => LunarSolarBase::HAN_MONTH_STR[$mid_name],
                'hyear' => self::get_readable_year ($mid_year),
                'year' => $mid_year,
                'month' => $mid_month,
                'day' => $mid_day,
                'hour' => $mid_hour,
                'min' => $mid_min,
                'julian' => $julian_this_month_middle
            ),
            'next_month_entry' => (object) array (
                'kor_name' => LunarSolarBase::KOR_MONTH_STR[$outgi_name],
                'han_name' => LunarSolarBase::HAN_MONTH_STR[$outgi_name],
                'hyear' => self::get_readable_year ($outgi_year),
                'year' => $outgi_year,
                'month' => $outgi_month,
                'day' => $outgi_day,
                'hour' => $outgi_hour,
                'min' => $outgi_min,
                'julian' => $julian_next_month_entry
            )
        );
    }

    // 양력일에 대한 음력월의 합삭/망 데이터 반환
    /*
        input    :
            LunarSolar::get_moon_status (
                '2013-07-16'    or
                '2013-7-16'        or
                '20130716'        or
                '1373900400'    or
                NULL
            );

        output    :
            stdClass Object
            (
                [new_moon]    => stdClass Object
                (
                    [hyear]        => AD 2013
                    [year]        => 2013
                    [month]        => 7
                    [day]        => 8
                    [hour]        => 16
                    [min]        => 15
                )
                [full_moon]    => stdClass Object
                (
                    [hyear]        => AD 2013
                    [year]        => 2013
                    [month]        => 7
                    [day]        => 23
                    [hour]        => 2
                    [min]        => 59
                )
            )

        합삭/망 정보의 경우, 한달에 음력월이 2개가 있으므로,
        1일의 정보만 얻어서는 합삭/망 중에 1개의 정보만 나올 수 있다.
        따라서, 1일의 데이터를 얻은 다음에 음력 1일의 정보까지
        구하면 한달의 합삭/망 정보를 모두 표현 가능하다.
        예시    :
            $lunar = LunarSolar::get_moon_status ('2013-07-01');
            if($lunar -> large_month)    // 평달의 경우 마지막이 29일, 큰달은 30일이다.
                $plus = 29 - $lunar -> day;
            else
                $plus = 30 - $lunar -> day;

            $result_1 = LunarSolar::get_moon_status ('2013-07-01');            // 음력 2013-05-23
            $result_2 = LunarSolar::get_moon_status ('2013-07-'.1 + $plus)    // 음력 2013-06-01
    */
    // $date의 형식
    // 1. unixstmap (1970년 12월 15일 이후부터만 가능)
    // 2. Ymd or Y-m-d
    // 3. null date (현재 시간)
    // 4. 1582년 10월 15일 이전의 날짜는 율리우스력의 날짜로 취급
    public static function get_moon_status ($date = null)
    {
        list ($year, $month, $day) = self::convert_to_args ($date);
        list ($year, $month, $day) = self::fix_calendar ($year, $month, $day);

        list (
            $year_start, $month_start, $day_start, $hour_start, $min_start,
            $year_mid, $month_mid, $day_mid, $hour_mid, $min_mid,
            $year_end, $month_end, $day_end, $hour_end, $min_end,
        ) = LunarSolarBase::get_conjunction_full_moon ($year, $month, $day);

        // 합삭(New moon)
        $new_moon = self::convert_date_to_utc_julian (
            sprintf (
                '%s %s:%s:00',
                self::get_hyphen_date (array ($year_start, $month_start, $day_start)),
                $hour_start < 10 ? '0'.$hour_start : $hour_start,
                $min_start < 10 ? '0'.$min_start : $min_start
            )
        );

        // 1852-10-15 이전이면 율리우스로 변경
        if(self::is_gregorian ($year_start, $month_start, $day_start) === false)
        {
            $result = self::convert_gregorian_to_julian (array ($year_start, $month_start, $day_start));
            $year_start = $result -> year;
            $month_start = $result -> month;
            $day_start = $result -> day;
        }

        // 망(Full moon)
        $full_moon = self::convert_date_to_utc_julian (
            sprintf (
                '%s %s:%s:00',
                self::get_hyphen_date (array ($year_mid, $month_mid, $day_mid)),
                $hour_mid < 10 ? '0'.$hour_mid : $hour_mid,
                $min_mid < 10 ? '0'.$min_mid : $min_mid
            )
        );

        // 1852-10-15 이전이면 율리우스로 변경
        if(self::is_gregorian ($year_mid, $month_mid, $day_mid) === false)
        {
            $result = self::convert_gregorian_to_julian (array ($year_mid, $month_mid, $day_mid));
            $year_mid = $result -> year;
            $month_mid = $result -> month;
            $day_mid = $result -> day;
        }

        return (object) array (
            // 합삭(New moon)
            'new_moon' => (object) array (
                'hyear' => self::get_readable_year ($year_start),
                'year' => $year_start,
                'month' => $month_start,
                'day' => $day_start,
                'hour' => $hour_start,
                'min' => $min_start,
                'julian' => $new_moon,
            ),
            // 망(Full moon)
            'full_moon' => (object) array (
                'hyear' => self::get_readable_year ($year_mid),
                'year' => $year_mid,
                'month' => $month_mid,
                'day' => $day_mid,
                'hour' => $hour_mid,
                'min' => $min_mid,
                'julian' => $full_moon,
            ),
        );
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // get_day_fortune () 메서드의 간지 인덱스 반환값을 이용하여, 간지 값을 반환한다.
    // $ganji_count = get_day_fortune () 메서드의 간지 인덱스 번호
    // $language = 출력모드이며 boolean 형태(false => 한글 간지명, true => 한자 간지명)
    public static function get_ganji_value ($ganji_count, $language = false)
    {
        if($ganji_count > 59)
            $ganji_count -= 60;

        $mode = $language ? 'HAN_GANJI' : 'KOR_GANJI';
        return LunarSolarBase::$mode[$ganji_count];
    }
}
