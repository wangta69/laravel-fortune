<?php
namespace Pondol\Fortune\Services\LunarSolar\type2;
    // 적용 가능 기간: -9999 ~ +9999년

class LunarSolarBase
{
    const MONTH = array (
        0, 21355, 42843, 64498, 86335, 108366, 130578, 152958,
        175471, 198077, 220728, 243370, 265955, 288432, 310767,
        332928, 354903, 376685, 398290, 419736, 441060, 462295,
        483493, 504693, 525949
    );

    // 십간 상수정의
    const KOR_GAN = array ('갑', '을', '병', '정', '무', '기', '경', '신', '임', '계');
    const HAN_GAN = array ('甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸');

    // 십이지 상수정의
    const KOR_JI = array ('자', '축', '인', '묘', '진', '사', '오', '미', '신', '유', '술', '해');
    const HAN_JI = array ('子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥');

    // 띠 상수정의
    const ZODIAC = array ('쥐', '소', '호랑이', '토끼', '용', '뱀', '말', '양', '원숭이', '닭', '개', '돼지');

    // 10간의 색상
    const COLORS = array ('청', '청', '홍', '홍', '황', '황', '백', '백', '흑', '흑');

    // 병자년 경인월 신미일 기해시 입춘 데이터
    const UNIT_YEAR = 1996;
    const UNIT_MONTH = 2;
    const UNIT_DAY = 4;
    const UNIT_HOUR = 22;
    const UNIT_MIN = 8;
    const UNIT_SEC = 0;

    // 병자년 데이터
    const UY_GAN = 2;
    const UY_JI = 0;
    const UY_SU = 12;

    // 경인년 데이터
    const UM_GAN = 6;
    const UM_JI = 2;
    const UM_SU = 26;

    // 신미일 데이터
    const UH_GAN = 5;
    const UH_JI = 11;
    const UH_SU = 35;

    // 정원 초하루 합삭 시간
    const UNIT_M_YEAR = 1996;
    const UNIT_M_MONTH = 2;
    const UNIT_M_DAY = 19;
    const UNIT_M_HOUR = 8;
    const UNIT_M_MIN = 30;
    const UNIT_M_SEC = 0;
    const MOON_LENGTH = 42524;

    // 절기 데이터
    const KOR_MONTH_STR = array (
        '입춘', '우수', '경칩', '춘분', '청명', '곡우',
        '입하', '소만', '망종', '하지', '소서', '대서',
        '입추', '처서', '백로', '추분', '한로', '상강',
        '입동', '소설', '대설', '동지', '소한', '대한',
        '입춘'
    );
    const HAN_MONTH_STR = array (
        '立春', '雨水', '驚蟄', '春分', '淸明', '穀雨',
        '立夏', '小滿', '芒種', '夏至', '小暑', '大暑',
        '立秋', '處暑', '白露', '秋分', '寒露', '霜降',
        '立冬', '小雪', '大雪', '冬至', '小寒', '大寒',
        '立春'
    );

    // 60간지 데이터
    const KOR_GANJI = array (
        '갑자', '을축', '병인', '정묘', '무진', '기사', '경오', '신미', '임신', '계유', '갑술', '을해',
        '병자', '정축', '무인', '기묘', '경진', '신사', '임오', '계미', '갑신', '을유', '병술', '정해',
        '무자', '기축', '경인', '신묘', '임진', '계사', '갑오', '을미', '병신', '정유', '무술', '기해',
        '경자', '신축', '임인', '계묘', '갑진', '을사', '병오', '정미', '무신', '기유', '경술', '신해',
        '임자', '계축', '갑인', '을묘', '병진', '정사', '무오', '기미', '경신', '신유', '임술', '계해'
    );
    const HAN_GANJI = array (
        '甲子','乙丑','丙寅','丁卯','戊辰','己巳','庚午','辛未','壬申','癸酉','甲戌','乙亥',
        '丙子','丁丑','戊寅','己卯','庚辰','辛巳','壬午','癸未','甲申','乙酉','丙戌','丁亥',
        '戊子','己丑','庚寅','辛卯','壬辰','癸巳','甲午','乙未','丙申','丁酉','戊戌','己亥',
        '庚子','辛丑','壬寅','癸卯','甲辰','乙巳','丙午','丁未','戊申','己酉','庚戌','辛亥',
        '壬子','癸丑','甲寅','乙卯','丙辰','丁巳','戊午','己未','庚申','辛酉','壬戌','癸亥'
    );

    // 요일 데이터
    const KOR_WEEK = array ('일','월','화','수','목','금','토');
    const HAN_WEEK = array ('日','月','火','水','木','金','土');

    // 28일 데이터
    const KOR_28_DAYS    = array (
        '각', '항', '저', '방', '심', '미', '기',
        '두', '우', '녀', '허', '위', '실', '벽',
        '규', '수', '위', '묘', '필', '자', '삼',
        '정', '귀', '류', '성', '장', '익', '진'
    );
    const HAN_28_DAYS    = array (
        '角','亢','氐','房','心','尾','箕',
        '斗','牛','女','虛','危','室','壁',
        '奎','婁','胃','昴','畢','觜','參',
        '井','鬼','柳','星','張','翼','軫'
    );

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // 정수 몫을 반환
    public static function get_integer_share ($value_1, $value_2)
    {
        return (int) ($value_1 / $value_2);
    }

    // 특정년의 1월 1일부터 해당 날짜(date)까지의 날짜의 수
    // date = $year, $month, $day
    public static function count_day_from_first_day ($year, $month, $day)
    {
        $day_count = $i = 0;

        for($i = 1; $i < $month; $i ++)
        {
            $day_count += 31;
            if($i == 2 || $i == 4 || $i == 6 || $i == 9 || $i == 11)
                $day_count --;

            if($i == 2)
            {
                $day_count -= 2;
                if(($year % 4) == 0)
                    $day_count ++;
                if(($year % 100) == 0)
                    $day_count --;
                if(($year % 400) == 0)
                    $day_count ++;
                if(($year % 4000) == 0)
                    $day_count --;
            }
        }
        $day_count += $day;

        return $day_count;
    }

    // 특정 날짜($start_date)부터 특정 날짜($end_date)까지의 일수를 계산
    // start_date = $start_year, $start_month, $start_day
    // end_date = $end_year, $end_month, $end_day
    public static function count_day_from_start_to_end ($start_year, $start_month, $start_day, $end_year, $end_month, $end_day)
    {
        $a = $b = $c = $d = $e = $f = $g = $h = $i = $day_count_result = 0;

        if($end_year > $start_year)
        {
            $a = self::count_day_from_first_day ($start_year, $start_month, $start_day);
            $c = self::count_day_from_first_day ($start_year, 12, 31);
            $b = self::count_day_from_first_day ($end_year, $end_month, $end_day);
            $d = $start_year;
            $e = $end_year;
            $f = -1;
        }
        else
        {
            $a = self::count_day_from_first_day ($end_year, $end_month, $end_day);
            $c = self::count_day_from_first_day ($end_year, 12, 31);
            $b = self::count_day_from_first_day ($start_year, $start_month, $start_day);
            $d = $end_year;
            $e = $start_year;
            $f = 1;
        }

        if($end_year == $start_year)
            $day_count_result = $b - $a;
        else
        {
            $day_count_result = $c - $a;
            $g = $d + 1;
            $h = $e - 1;

            for($i = $g; $i <= $h; $i ++)
            {
                if($i == -2000 && $h > 1990)
                {
                    $day_count_result += 1457682;
                    $i = 1991;
                }
                else if($i == -1750 && $h > 1990)
                {
                    $day_count_result += 1366371;
                    $i = 1991;
                }
                else if($i == -1500 && $h > 1990)
                {
                    $day_count_result += 1275060;
                    $i = 1991;
                }
                else if($i == -1250 && $h > 1990)
                {
                    $day_count_result += 1183750;
                    $i = 1991;
                }
                else if($i == -1000 && $h > 1990)
                {
                    $day_count_result += 1092439;
                    $i = 1991;
                }
                else if($i == -750 && $h > 1990)
                {
                    $day_count_result += 1001128;
                    $i = 1991;
                }
                else if($i == -500 && $h > 1990)
                {
                    $day_count_result += 909818;
                    $i = 1991;
                }
                else if($i == -250 && $h > 1990)
                {
                    $day_count_result += 818507;
                    $i = 1991;
                }
                else if($i == 0 && $h > 1990)
                {
                    $day_count_result += 727197;
                    $i = 1991;
                }
                else if($i == 250 && $h > 1990)
                {
                    $day_count_result += 635887;
                    $i = 1991;
                }
                else if($i == 500 && $h > 1990)
                {
                    $day_count_result += 544576;
                    $i = 1991;
                }
                else if($i == 750 && $h > 1990)
                {
                    $day_count_result += 453266;
                    $i = 1991;
                }
                else if($i == 1000 && $h > 1990)
                {
                    $day_count_result += 361955;
                    $i = 1991;
                }
                else if($i == 1250 && $h > 1990)
                {
                    $day_count_result += 270644;
                    $i = 1991;
                }
                else if($i == 1500 && $h > 1990)
                {
                    $day_count_result += 179334;
                    $i = 1991;
                }
                else if($i == 1750 && $h > 1990)
                {
                    $day_count_result += 88023;
                    $i = 1991;
                }

                // $dis += self::count_day_from_first_day ($i, 12, 31);
            }

            $day_count_result += $b;
            $day_count_result *= $f;
        }

        return $day_count_result;
    }

    // start_date와 end_date사이의 시간(분)을 계산
    // start_date = $start_year, $start_month, $start_day, $start_hour, $start_min
    // end_date = $end_year, $end_month, $end_day, $end_hour, $end_min
    public static function count_min_from_start_to_end ($start_year, $start_month, $start_day, $start_hour, $start_min, $end_year, $end_month, $end_day, $end_hour, $end_min)
    {
        $count_min = 0;

        $count_day = self::count_day_from_start_to_end ($start_year, $start_month, $start_day, $end_year, $end_month, $end_day);
        $count_min = $count_day * 24 * 60 + ($start_hour - $end_hour) * 60 + ($start_min - $end_min);

        return $count_min;
    }

    // distinct_date으로 부터 $target_min(분) 떨어진 시점의 년/월/일/시/분을 계산
    // distinct_date = $distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min
    // $target_min = (int)
    public static function get_date_by_target_min ($target_min, $distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min)
    {
        $year = $month = $day = $hour = $min = $time = 0;
        $year = $distinct_year - self::get_integer_share ($target_min, 525949);

        if($target_min > 0)
        {
            $year += 2;
            do
            {
                $year --;
                $time = self::count_min_from_start_to_end ($distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min, $year, 1, 1, 0, 0);
            }
            while($time < $target_min);

            $month = 13;
            do
            {
                $month --;
                $time = self::count_min_from_start_to_end ($distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min, $year, $month, 1, 0, 0);
            }
            while($time < $target_min);

            $day = 32;
            do
            {
                $day --;
                $time = self::count_min_from_start_to_end ($distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min, $year, $month, $day, 0, 0);
            }
            while($time < $target_min);

            $hour = 24;
            do
            {
                $hour --;
                $time = self::count_min_from_start_to_end ($distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min, $year, $month, $day, $hour, 0);
            }
            while($time < $target_min);

            $time = self::count_min_from_start_to_end ($distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min, $year, $month, $day, $hour, 0);
            $min = $time - $target_min;
        }
        else
        {
            $year -= 2;
            do
            {
                $year ++;
                $time = self::count_min_from_start_to_end ($distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min, $year, 1, 1, 0, 0);
            }
            while($time >= $target_min);

            $year --;
            $month = 0;
            do
            {
                $month ++;
                $time = self::count_min_from_start_to_end ($distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min, $year, $month, 1, 0, 0);
            }
            while($time >= $target_min);

            $month --;
            $day = 0;
            do
            {
                $day = $day + 1;
                $time = self::count_min_from_start_to_end ($distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min, $year, $month, $day, 0, 0);
            }
            while($time >= $target_min);

            $day --;
            $hour = -1;
            do
            {
                $hour ++;
                $time = self::count_min_from_start_to_end ($distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min, $year, $month, $day, $hour, 0);
            }
            while($time >= $target_min);

            $hour --;
            $time = self::count_min_from_start_to_end ($distinct_year, $distinct_month, $distinct_day, $distinct_hour, $distinct_min, $year, $month, $day, $hour, 0);
            $min = $time - $target_min;
        }

        return array ($year, $month, $day, $hour, $min);
    }

    // 그래고리력의 년/월/시/분으로 60년의 배수, 세차, 월건, 일진, 시주를 구함
    /*
    Array (
        [0] => -17    // 60년의 배수
        [1] => 29    // 60간지의 년 배열 index
        [2] => 55    // 60간지의 월 배열 index
        [3] => 11    // 60간지의 일 배열 index
        [4] => 20    // 60간지의 시 배열 index
    )
    */
    public static function get_sexagenary_by_gregorian ($solar_year, $solar_month, $solar_day, $solar_hour, $solar_min)
    {
        $count_min = self::count_min_from_start_to_end (
            self::UNIT_YEAR, self::UNIT_MONTH, self::UNIT_DAY, self::UNIT_HOUR, self::UNIT_MIN,
            $solar_year, $solar_month, $solar_day, $solar_hour, $solar_min
        );
        $count_day = self::count_day_from_start_to_end (
            self::UNIT_YEAR, self::UNIT_MONTH, self::UNIT_DAY,
            $solar_year, $solar_month, $solar_day
        );

        // 무인년(1996) 입춘부터 해당일시까지의 경과년수
        $sexagenary = self::get_integer_share ($count_min, 525949);

        if($count_min >= 0)
            $sexagenary ++;

        // 년주 계산
        $sexagenary_year = ($sexagenary % 60) * -1;
        $sexagenary_year += 12;

        if($sexagenary_year < 0)
            $sexagenary_year += 60;
        else if($sexagenary_year > 59)
            $sexagenary_year -= 60;

        $k = $count_min % 525949;
        $k = 525949 - $k;

        if($k < 0)
            $k += 525949;
        else if($k >= 525949)
            $k -= 525949;

        for($i = 0; $i <= 11; $i ++)
        {
            $j = $i * 2;
            if(self::MONTH[$j] <= $k && $k < self::MONTH[$j+2])
            {
                $sexagenary_month = $i;
            }
        };

        // 월주 구하기
        $i = $sexagenary_month;
        $j = $sexagenary_year % 10;
        $j %= 5;
        $j = $j * 12 + 2 + $i;

        $sexagenary_month = $j;

        if($sexagenary_month > 50)
            $sexagenary_month -= 60;

        $sexagenary_day = $count_day % 60;

        // 일주 구하기
        $sexagenary_day *= -1;
        $sexagenary_day += 7;

        if($sexagenary_day < 0)
            $sexagenary_day += 60;
        else if($sexagenary_day > 59)
            $sexagenary_day -= 60;

        if(($solar_hour == 0 || $solar_hour == 1 && $solar_min < 30))
            $i = 0;

        else if(($solor_hour == 1 && $solor_min >= 30) || $solor_hour == 2 || ($solor_hour == 3 && $solor_min < 30))
            $i = 1;

        else if(($solor_hour == 3 && $solor_min >= 30) || $solor_hour == 4 || ($solor_hour == 5 && $solor_min < 30))
            $i = 2;

        else if(($solor_hour == 5 && $solor_min >= 30) || $solor_hour == 6 || ($solor_hour == 7 && $solor_min < 30))
            $i = 3;

        else if(($solor_hour == 7 && $solor_min >= 30) || $solor_hour == 8 || ($solor_hour == 9 && $solor_min < 30))
            $i = 4;

        else if(($solor_hour == 9 && $solor_min >= 30) || $solor_hour == 10 || ($solor_hour == 11 && $solor_min < 30))
            $i = 5;

        else if(($solor_hour == 11 && $solor_min >= 30) || $solor_hour == 12 || ($solor_hour == 13 && $solor_min < 30))
            $i = 6;

        else if(($solor_hour == 13 && $solor_min >= 30) || $solor_hour == 14 || ($solor_hour == 15 && $solor_min < 30))
            $i = 7;

        else if(($solor_hour == 15 && $solor_min >= 30) || $solor_hour == 16 || ($solor_hour == 17 && $solor_min < 30))
            $i = 8;

        else if(($solor_hour == 17 && $solor_min >= 30) || $solor_hour == 18 || ($solor_hour == 19 && $solor_min < 30))
            $i = 9;

        else if(($solor_hour == 19 && $solor_min >= 30) || $solor_hour == 20 || ($solor_hour == 21 && $solor_min < 30))
            $i = 10;

        else if(($solor_hour == 21 && $solor_min >= 30) || $solor_hour == 22 || ($solor_hour == 23 && $solor_min < 30))
            $i = 11;

        else if($solar_hour == 23 && $solar_min >= 30)
        {
            $sexagenary_day ++;

            if($solar_day == 60)
                $sexagenary_day = 0;

            $i = 0;
        }

        $j = $sexagenary_day % 10;
        $j %= 5;
        $j = $j * 12 + $i;

        $sexagenary_hour = $j;

        return array ($sexagenary, $sexagenary_year, $sexagenary_month, $sexagenary_day, $sexagenary_hour);
    }

    // 그래고리력의 년/월/시/분이 들어있는 절기(season)의 이름번호, 년/월/일/시/분을 얻는다.
    public static function get_season_by_gregorian ($solar_year, $solar_month, $solar_day, $solar_hour, $solar_min)
    {
        list ($season, $season_year, $season_month, $season_day, $season_hour) = self::get_sexagenary_by_gregorian (
            $solar_year, $solar_month, $solar_day, $solar_hour, $solar_min
        );

        $count_min = self::count_min_from_start_to_end (
            self::UNIT_YEAR, self::UNIT_MONTH, self::UNIT_DAY, self::UNIT_HOUR, self::UNIT_MIN,
            $solar_year, $solar_month, $solar_day, $solar_hour, $solar_min
        );

        // $k = $count_min % 525949;
        // $k = 525949 - $k;
        $k = ($count_min % 525949) * -1;

        if($k < 0)
            $k += 525949;
        else if($k >= 525949)
            $k = $k - 525949;

        $i = $season_month % 12 - 2;
        if($i == -2)
            $i = 10;
        else if($i == -1)
            $i = 11;

        $ingi_name = $i * 2;
        $mid_name = $i * 2 + 1;
        $outgi_name = $i * 2 + 2;

        $j = $i * 2;
        $target_min = $count_min + ($k - self::MONTH[$j]);

        list ($year, $month, $day, $hour, $min) = self::get_date_by_target_min (
            $target_min,
            self::UNIT_YEAR,
            self::UNIT_MONTH,
            self::UNIT_DAY,
            self::UNIT_HOUR,
            self::UNIT_MIN
        );

        $ingi_year = $year;
        $ingi_month = $month;
        $ingi_day = $day;
        $ingi_hour = $hour;
        $ingi_min = $min;

        $target_min = $count_min + ($k - self::MONTH[$j+1]);

        list ($year, $month, $day, $hour, $min) = self::get_date_by_target_min (
            $target_min,
            self::UNIT_YEAR,
            self::UNIT_MONTH,
            self::UNIT_DAY,
            self::UNIT_HOUR,
            self::UNIT_MIN
        );

        $mid_year = $year;
        $mid_month = $month;
        $mid_day = $day;
        $mid_hour = $hour;
        $mid_min = $min;

        $tmin = $count_min + ($k - self::MONTH[$j + 2]);

        list ($year, $month, $day, $hour, $min) = self::get_date_by_target_min (
            $target_min,
            self::UNIT_YEAR,
            self::UNIT_MONTH,
            self::UNIT_DAY,
            self::UNIT_HOUR,
            self::UNIT_MIN
        );

        $outgi_year = $year;
        $outgi_month = $month;
        $outgi_day = $day;
        $outgi_hour = $hour;
        $outgi_min = $min;

        // print_r(array (
        //     $ingi_year, $ingi_month, $ingi_day, $ingi_hour, $ingi_min,
        //     $mid_year, $mid_month, $mid_day, $mid_hour, $mid_min,
        //     $outgi_year, $outgi_month, $outgi_day, $outgi_hour, $outgi_min
        // ));

        return array (
            $ingi_name, $ingi_year, $ingi_month, $ingi_day, $ingi_hour, $ingi_min,
            $mid_name, $mid_year, $mid_month, $mid_day, $mid_hour, $mid_min,
            $outgi_name, $outgi_year, $outgi_month, $outgi_day, $outgi_hour, $outgi_min
        );
    }

    // 특정한 각도를 0도 ~ 360도 이내로 계산
    public static function get_degree_between_0_to_360 ($degree)
    {
        $degree_result = $degree;
        $i = self::get_integer_share ((int) $degree_result, 360);
        $degree_result = $degree - ($i * 360);

        while($degree_result >= 360 || $degree < 0)
        {
            if($degree_result > 0)
                $degree_result -= 360;
            else
                $degree_result += 360;
        }

        return $degree_result;
    }

    // 1996년 기준 태양황경과 달황경의 차이
    public static function get_sun_moon_longitude_gap ($day)
    {
        // 태양황경
        $sun_celestial_longitude = (float) ($day * 0.98564736 + 278.956807); // 평균 황경
        $sun_perihelion = 282.869498 + 0.00004708 * $day; // 근일점 황경
        $sun_anomaly = 3.14159265358979 * ($sun_celestial_longitude - $sun_perihelion_ecliptic) / 180; // 근점이각
        $sun_diff_celestial_longitude = 1.919 * sin ($sun_anomaly) + 0.02 * sin (2 * $sun_anomaly); // 황경차
        $sun_true_celestial_longitude = self::get_degree_between_0_to_360 ($sun_celestial_longitude + $sun_diff_celestial_longitude); // 진황경

        // 달황경
        $moon_celestial_longitude = 27.836584 + 13.17639648 * $day; // 평균 황경
        $moon_perigee = 280.425774 + 0.11140356 * $day; // 근지점 황경
        $moon_anomaly = 3.14159265358979 * ($moon_celestial_longitude - $moon_perihelion_ecliptic) / 180; // 근점이각
        $moon_node_celestial_longitude = 202.489407 - 0.05295377 * $day; // 교점황경
        $moon_longitude = 3.14159265358979 * ($moon_celestial_longitude - $moon_anomaly) / 180;
        $moon_diff_celestial_longitude    = 5.06889 * sin ($moon_anomaly)
                                        + 0.146111 * sin (2 * $moon_anomaly)
                                        + 0.01 * sin (3 * $moon_anomaly)
                                        - 0.238056 * sin ($sun_anomaly)
                                        - 0.087778 * sin ($moon_anomaly + $sun_anomaly)
                                        + 0.048889 * sin ($moon_anomaly - $sun_anomaly)
                                        - 0.129722 * sin (2 * $moon_longitude)
                                        - 0.011111 * sin (2 * $moon_longitude - $moon_anomaly)
                                        - 0.012778 * sin (2 * $moon_longitude + $moon_anomaly);                                                // 황경차
        $moon_true_celestial_longitude = self::get_degree_between_0_to_360 ($moon_celestial_longitude + $moon_diff_celestial_longitude);    // 진황경

        // 결과
        $celestial_longitude_result = self::get_degree_between_0_to_360 ($moon_true_celestial_longitude - $sun_true_celestial_longitude);

        return $celestial_longitude_result;
    }

    // 그레고리력 년/월/일이 들어있는 태음월의 시작합삭일시, 망일시, 끝합삭일시를 계산
    /*
    Array (
        [0] => 2013 // 시작 합삭 년도
        [1] => 7 // 시작 합삭 월
        [2] => 8 // 시작 합삭 일
        [3] => 16 // 시작 합삭 시
        [4] => 15 // 시작 합삭 분
        [5] => 2013 // 망 연도
        [6] => 7 // 망 월
        [7] => 23 // 망 일
        [8] => 2 // 망 시
        [9] => 59 // 망 분
        [10] => 2013 // 끝 합삭 년도
        [11] => 8 // 끝 합삭 월
        [12] => 7 // 끝 합삭 일
        [13] => 6 // 끝 합삭 시
        [14] => 50 // 끝 합삭 분
    )
    */
    // 시작 합삭(Start Conjunction)    = $start_conjunction_year,    $start_conjunction_month,    $start_conjunction_day,    $start_conjunction_hour,    $start_conjunction_min
    // 망월(Full Moon)                = $full_moon_year,            $full_moon_month,            $full_moon_day,            $full_moon_hour,            $full_moon_min
    // 끝 합삭(End Conjunction)        = $end_conjunction_year,    $end_conjunction_month,        $end_conjunction_day,    $end_conjunction_hour,        $end_conjunction_min
    public static function get_conjunction_full_moon ($solar_year, $solar_month, $solar_day)
    {
        $count_day = self::count_day_from_start_to_end ($solar_year, $solar_month, $solar_day, 1995, 12, 31);
        $longitude_gap = self::get_sun_moon_longitude_gap ($count_day);

        $j = $count_day;
        $k = $longitude_gap;

        while($k > 13.5)
        {
            $j --;
            $k = self::get_sun_moon_longitude_gap ($j);
        };

        while($k > 1)
        {
            $j -= 0.04166666666;
            $k = self::get_sun_moon_longitude_gap ($j);
        };

        while($j < 359.99)
        {
            $j -= 0.000694444;
            $k = self::get_sun_moon_longitude_gap ($j);
        };

        $j += 0.375;
        $j *= 1440;
        $i = (int) $j * -1;
        list ($start_year, $start_month, $start_day, $start_hour, $start_min) = self::get_date_by_target_min ($i, 1995, 12, 31, 0, 0);

        $j = $count_day;
        $k = $longitude_gap;

        while($k < 346.5)
        {
            $j ++;
            $k = self::get_sun_moon_longitude_gap ($j);
        };

        while($k < 359)
        {
            $j += 0.04166666666;
            $k = self::get_sun_moon_longitude_gap ($j);
        };

        while($k > 0.01)
        {
            $j += 0.000694444;
            $k = self::get_sun_moon_longitude_gap ($j);
        };

        $l    = $j;
        $j += 0.375;
        $j *= 1440;
        $i = (int) $j * -1;
        list ($end_conjunction_year, $end_conjunction_month, $end_conjunction_day, $end_conjunction_hour, $end_conjunction_min) = self::get_date_by_target_min ($i, 1995, 12, 31, 0, 0);

        if($solar_month == $end_conjunction_month && $solar_day == $end_conjunction_day)
        {
            $start_conjunction_year = $end_conjunction_year;
            $start_conjunction_month = $end_conjunction_month;
            $start_conjunction_day = $end_conjunction_day;
            $start_conjunction_hour = $end_conjunction_hour;
            $start_conjunction_min = $end_conjunction_min;

            $j = $l + 26;

            $k = self::get_sun_moon_longitude_gap ($j);
            while($k < 346.5)
            {
                $j ++;
                $k = self::get_sun_moon_longitude_gap ($j);
            };

            while ($k < 359)
            {
                $j += 0.04166666666;
                $k = self::moonsundegree ($j);
            };

            while ($k > 0.01) {
                $j += 0.000694444;
                $k = self::moonsundegree ($j);
            };

            $j += 0.375;
            $j *= 1440;
            $i = (int) $j * -1;
            list ($end_conjunction_year, $end_conjunction_month, $end_conjunction_day, $end_conjunction_hour, $end_conjunction_min) = self::get_date_by_target_min ($i, 1995, 12, 31, 0, 0);
        };

        $j = self::count_day_from_start_to_end ($start_conjunction_year, $start_conjunction_month, $start_conjunction_day, 1995, 12, 31);
        $j += 12;

        $k    = self::get_sun_moon_longitude_gap ($j);
        while($k < 166.5)
        {
            $j ++;
            $k = self::get_sun_moon_longitude_gap ($j);
        };

        while($k < 179)
        {
            $j += 0.04166666666;
            $k = self::get_sun_moon_longitude_gap ($j);
        };

        while($k < 179.999)
        {
            $j += 0.000694444;
            $k = self::get_sun_moon_longitude_gap ($j);
        };

        $j += 0.375;
        $j *= 1440;
        $i = (int) $d * -1;
        list ($full_moon_year, $full_moon_month, $full_moon_day, $full_moon_hour, $full_moon_min) = self::get_date_by_target_min ($i, 1995, 12, 31, 0, 0);

        return array (
            $start_conjunction_year, $start_conjunction_month, $start_conjunction_day, $start_conjunction_hour, $start_conjunction_min,
            $full_moon_year, $full_moon_month, $full_moon_day, $full_moon_hour, $full_moon_min,
            $end_conjunction_year, $end_conjunction_month, $end_conjunction_day, $end_conjunction_hour, $end_conjunction_min
        );
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // 양력 -> 음력 변환
    /*
    Array (
        [0] => 2013    // 음력 연도
        [1] => 6    // 음력 월
        [2] => 9    // 음력 일
        [3] =>        // 음력 윤달 여부(boolean)
        [4] => 1    // 평달(false)/큰달(true) 여부(boolean)
    )
    */
    // 윤달 : 1삭 망월은 29.53059일, 음력에서 달이 지구를 도는 데 354일이 걸린다. 따라서 양력의 기준인 365.23일과 비교해 한 주기마다 11일이 빨라진다.
    // 따라서 음력과 양력간의 차이가 1달이상 벌어지지 않도록 날짜를 밀어주는 것을 윤달이라고 한다.

    // 19 양력과 235 삭망월의 날수가 거이 일치하는 '메톤 주기'에 따라 시헌력(중국 청나라의 달력 = 현재의 음력)에 19년간 총 7개의 윤달을 넣으며 2~3년 주기이다.

    // 윤년 : 양력에서 자연의 흐음에 대해 생길 수 있는 오차를 보정하기 위해 날이나 주, 달을 인위적으로 삽입하는 해를 말한다. 영어로 Leap year라고 한다.
    // 한국법에서 윤력은 그래고리력에서 여분의 하루인 2월 29일을 추가해 1년동한 날짜의 수를 366일이 되는 해를 말한다.
    // 한국에서는 2월이 29일인 해를 윤년으로 지정하지만 세계력에서는 윤년이 6월 31일이 있는 해로 정한다.

    // 윤년의 계산 : 4로 나누어 떨어지지만 100으로도 나누어 떨어지는 해를 윤년으로 그 외에는 평년으로 지정한다.
    // 단 400으로 나누어 떨어지는 해도 윤년으로 지정된다. 예시로 2000년, 2400년이 있다.
    // 보통 4년에 한번 씩 추가되는 하루날은 날수가 가장 적은 2월에 추가된다. 4년마다 2월 29일이 돌아오는 이유이다.

    // 대월 / 소월 : 대월은 음력 달의 일수가 31일인 달을 말하고, 소월은 음력 달의 일수가 29일인 달을 말한다.
    public function convert_solar_to_lunar ($solar_year, $solar_month, $solar_day)
    {
        list (
            $smoyear, $smomonth, $smoday, $smohour, $smomin,
            $year_0, $month_0, $day_0, $hour_0, $min_0,
            $year_1, $month_1, $day_1, $hour_1, $min_1
        ) = self::get_conjunction_full_moon ($solar_year, $solar_month, $solar_day);

        $lunar_day = self::count_day_from_start_to_end ($solar_year, $solar_month, $solar_day, $smoyear, $smomonth, $smoday) + 1;

        $i = abs (self::count_day_from_start_to_end ($smoyear, $smomonth, $smoday, $year_1, $month_1, $day_1));
        if($i == 30)
            $large_month = 1;    // 대월
        if($i == 29)
            $large_month = 0;    // 소월

        list (
            $ingi_name, $ingi_year, $ingi_month, $ingi_day, $ingi_hour, $ingi_min,
            $mid_name_1, $mid_year_1, $mid_month_1, $mid_day_1, $mid_hour_1, $mid_min_1,
            $outgi_name, $outgi_year, $outgi_month, $outgi_day, $outgi_hour, $outgi_min
        ) = self::get_season_by_gregorian ($smoyear, $smomonth, $smoday, $smohour, $smomin);

        $mid_name_2 = $mid_name_1 + 2;
        if($mid_name_2 > 24)
            $mid_name_2 = 1;

        $s0 = self::MONTH[$mid_name_2] - self::MONTH[$mid_name_1];
        if($s0 < 0)
            $s0 += 525949;

        $s0 *= -1;

        list ($mid_year_2, $mid_month_2, $mid_day_2, $mid_hour_2, $mid_min_2) = self::get_date_by_target_min ($s0, $mid_year_1, $mid_month_1, $mid_day_1, $mid_hour_1, $mid_min_1);

        if(($mid_month_1 == $smomonth && $mid_day_1 >= $smoday) || ($mid_month_1 == $month_1 && $mid_day_1 < $day_1))
        {
            $lunar_month = ($mid_name_1 - 1) / 2 + 1;
            $leap = 0;
        }
        else
        {
            if(($mid_month_2 == $month_1 && $mid_day_2 < $day_1) || ($mid_month_2 == $smomonth && $mid_day_2 >= $smoday))
            {
                $lunar_month = ($mid_day_2 - 1) / 2 + 1;
                $leap = 0;
            }
            else{
                if($smomonth < $mid_month_2 && $mid_month_2 < $month_1)
                {
                    $lunar_month = ($mid_name_2 - 1) / 2 + 1;
                    $leap = 0;
                }
                else
                {
                    $lunar_month = ($mid_name_1 - 1) / 2 + 1;
                    $leap = 1;
                }
            }
        }

        $lunar_year = $smoyear;
        if($lunar_month == 12 && $smomonth == 1)
            $lunar_year --;

        if(($lunar_month == 11 && $leap == 1) || $lunar_month == 12 || $lunar_month < 6)
        {
            list ($mid_year_1, $mid_month_1, $mid_day_1, $mid_hour_1, $mid_min_1)    = self::get_date_by_target_min (2880, $smoyear, $smomonth, $smoday, $smohour, $smomin);
            list ($outgi_year, $outgi_month, $outgi_day, $lnp_1, $lnp_2)            = self::convert_solar_to_lunar ($mid_year_1, $mid_month_1, $mid_day_1);

            $outgi_day = $lunar_month - 1;
            if($outgi_day == 0)
                $outgi_day = 12;

            if($outgi_day == $outgi_month)
            {
                if($leap == 1)
                    $leap = 0;
                else
                {
                    if($leap == 1)
                    {
                        if($lunar_month != $outgi_month)
                        {
                            $lunar_month --;
                            if($lunar_month == 0)
                            {
                                $lunar_year        --;
                                $lunar_month    = 12;
                            };
                            $leap = 0;
                        };
                    }
                    else
                    {
                        if($lunar_month == $outgi_month)
                            $leap = 1;
                        else
                        {
                            $lunar_month --;
                            if($lunar_month == 0)
                            {
                                $lunar_year        --;
                                $lunar_month    = 12;
                            }
                        }
                    }
                }
            }
        }

        return array (
            $lunar_year,
            $lunar_month,
            $lunar_day,
            $leap ? true : false,
            $large_month ? true : false
        );
    }

    // 음력 -> 양력 변환
    /*
    Array (
        [0] => 2013    // 양력 연도
        [1] => 6    // 양력 월
        [2] => 9    // 양력 일
    )
    */
    public static function convert_lunar_to_solar ($lunar_year_1, $lunar_month_1, $lunar_day_1, $leap = false)
    {
        list (
            $ingi_name, $ingi_year, $ingi_month, $ingi_day, $ingi_hour, $ingi_min,
            $mid_name, $mid_year, $mid_month, $mid_day, $mid_hour, $mid_min,
            $outgi_name, $outgi_year, $outgi_month, $outgi_day, $outgi_hour, $outgi_min
        ) = self::get_season_by_gregorian ($lunar_year_1, 2, 15, 0, 0);

        $tmin = -1440 * $lunar_day_1 + 10;
        list (
            $mid_year, $mid_month, $mid_day, $mid_hour, $mid_min
        ) = self::get_date_by_target_min ($tmin, $ingi_year, $ingi_month, $ingi_day, $ingi_hour, $ingi_min);

        list (
            $outgi_year, $outgi_month, $outgi_day, $hour, $min,
            $yearm, $monthm, $daym, $hourm, $minm,
            $year_1, $month_1, $day_1, $hour_1, $min_1
        ) = self::get_conjunction_full_moon ($mid_year, $mid_month, $mid_day);

        list (
            $lunar_year_2, $lunar_month_2, $lunar_day_2, $lnp_1, $lnp_2
        ) = self::convert_solar_to_lunar ($outgi_year, $outgi_month, $outgi_day);

        if($lunar_year_1 == $lunar_year_2 && $lunar_month_1 == $lunar_month_2)
        {
            $tmin = -1440 * $lunar_day_1 + 10;
            list (
                $solar_year, $solar_month, $solar_day, $hour, $min
            ) = self::get_date_by_target_min ($tmin, $year_1, $month_1, $day_1, 0, 0);

            if($leap)
            {
                list (
                    $lunar_year_2, $lunar_month_2, $lunar_day_2, $lnp_1, $lnp_2
                ) = self::convert_solar_to_lunar ($year_1, $month_1, $day_1);
                if( $lunar_year_1 == $lunar_year_2 && $lunar_month_1 == $lunar_month_2)
                {
                    $tmin = -1440 * $lunar_day_1 + 10;
                    list (
                        $solar_year, $solar_month, $solar_day, $hour, $min
                    ) = self::get_date_by_target_min ($tmin, $year_1, $month_1, $day_1, 0, 0);
                }
            }
        }
        else
        {
            list (
                $lunar_year_2, $lunar_month_2, $lunar_day_2, $lnp_1, $lnp_2
            ) = self::convert_solar_to_lunar ($year_1, $month_1, $day_1);
            if($lunar_year_1 == $lunar_year_2 && $lunar_month_1 == $lunar_month_2)
            {
                $tmin = -1440 * $lunar_day_1 + 10;
                list (
                    $solar_year, $solar_month, $solar_day, $hour, $min
                ) = self::get_date_by_target_min ($tmin, $year_1, $month_1, $day_1, 0, 0);
            }
        }

        return array (
            $solar_year,
            $solar_month,
            $solar_day
        );
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // 그레고리력 날짜를 요일의 배열 번호로 변환
    public static function get_week_day_by_gregorian ($solar_year, $solar_month, $solar_day)
    {
        $d = self::count_day_from_start_to_end (
            $solar_year, $solar_month, $solar_day,
            self::UNIT_YEAR, self::UNIT_MONTH, self::UNIT_DAY
        );

        $i = self::get_integer_share ($d, 7);
        $d -= $i * 7;

        while($d > 6 || $d < 0)
        {
            if($d > 6)
                $d -= 7;
            else
                $d += 7;
        }

        if($d < 0)
            $d += 7;

        return $d;
    }

    // 그레고리력의 날짜에 대한 28수를 계산
    public static function get_lunar_mansions_by_gregorian ($solar_year, $solar_month, $solar_day)
    {
        $d = self::count_day_from_start_to_end (
            $solar_year, $solar_month, $solar_day,
            self::UNIT_YEAR, self::UNIT_MONTH, self::UNIT_DAY
        );

        $i = self::get_integer_share ($d, 28);
        $d -= $i * 28;

        while($d > 27 || $d < 0)
        {
            if($d > 27)
                $d -= 28;
            else
                $d += 28;
        }

        if($d < 0)
            $d += 7;

        $d -= 11;

        if($d < 0)
            $d += 28;

        return $d;
    }
}
