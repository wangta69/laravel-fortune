<?php
namespace Pondol\Fortune\Services\LunarSolar\type2;

class LunarCalendarService
{
    var $lunarMonthType = array();
    //  var $accumulateLunarDate = array();

    var $SolarToLunar = array();
    var $LunarToSolar = array();
    var $error = "";
    var $solar_start = "1881-01-30";
    var $lunar_start = '18810101';

    // 십간 상수정의
    const KOR_GAN = array ('갑', '을', '병', '정', '무', '기', '경', '신', '임', '계');
    const HAN_GAN = array ('甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸');

    // 십이지 상수정의
    const KOR_JI = array ('자', '축', '인', '묘', '진', '사', '오', '미', '신', '유', '술', '해');
    const HAN_JI = array ('子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥');

    // 띠 상수정의
    const ZODIAC = array ('쥐', '소', '호랑이', '토끼', '용', '뱀', '말', '양', '원숭이', '닭', '개', '돼지');

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

    function __construct()
    {
        // 음력 달력의 달형태를 저장한다.
        // 각 해는 13월로 표현되고,  1 작은달, 2 큰달, 3 작은 윤달, 4 큰 윤달 이다. 0 은 윤달이 없는 해에 자리를 채우는 것이다.
        // 1881년 1월 30일은 음력 1881년 1월 1일 임으로 이를 기준으로 계산한다.
        $monthTypeMark = "1212122322121" . "1212121221220" . "1121121222120" . "2112132122122" . "2112112121220" . "2121211212120" . "2212321121212" . "2122121121210" . "2122121212120"
            . "1232122121212" . "1212121221220" . "1121123221222" . "1121121212220" . "1212112121220" . "2121231212121" . "2221211212120" . "1221212121210" . "2123221212121" . "2121212212120"
            . "1211212232212" . "1211212122210" . "2121121212220" . "1212132112212" . "2212112112210" . "2212211212120" . "1221412121212" . "1212122121210" . "2112212122120" . "1231212122212"
            . "1211212122210" . "2121123122122" . "2121121122120" . "2212112112120" . "2212231212112" . "2122121212120" . "1212122121210" . "2132122122121" . "2112121222120" . "1211212322122"
            . "1211211221220" . "2121121121220" . "2122132112122" . "1221212121120" . "2121221212110" . "2122321221212" . "1121212212210" . "2112121221220" . "1231211221222" . "1211211212220"
            . "1221123121221" . "2221121121210" . "2221212112120" . "1221241212112" . "1212212212120" . "1121212212210" . "2114121212221" . "2112112122210" . "2211211412212" . "2211211212120"
            . "2212121121210" . "2212214112121" . "2122122121120" . "1212122122120" . "1121412122122" . "1121121222120" . "2112112122120" . "2231211212122" . "2121211212120" . "2212121321212"
            . "2122121121210" . "2122121212120" . "1212142121212" . "1211221221220" . "1121121221220" . "2114112121222" . "1212112121220" . "2121211232122" . "1221211212120" . "1221212121210"
            . "2121223212121" . "2121212212120" . "1211212212210" . "2121321212221" . "2121121212220" . "1212112112210" . "2223211211221" . "2212211212120" . "1221212321212" . "1212122121210"
            . "2112212122120" . "1211232122212" . "1211212122210" . "2121121122210" . "2212312112212" . "2212112112120" . "2212121232112" . "2122121212110" . "2212122121210" . "2112124122121"
            . "2112121221220" . "1211211221220" . "2121321122122" . "2121121121220" . "2122112112322" . "1221212112120" . "1221221212110" . "2122123221212" . "1121212212210" . "2112121221220"
            . "1211231212222" . "1211211212220" . "1221121121220" . "1223212112121" . "2221212112120" . "1221221232112" . "1212212122120" . "1121212212210" . "2112132212221" . "2112112122210"
            . "2211211212210" . "2221321121212" . "2212121121210" . "2212212112120" . "1232212122112" . "1212122122120" . "1121212322122" . "1121121222120" . "2112112122120" . "2211231212122"
            . "2121211212120" . "2122121121210" . "2124212112121" . "2122121212120" . "1212121223212" . "1211212221220" . "1121121221220" . "2112132121222" . "1212112121220" . "2121211212120"
            . "2122321121212" . "1221212121210" . "2121221212120" . "1232121221212" . "1211212212210" . "2121123212221" . "2121121212220" . "1212112112220" . "1221231211221" . "2212211211220"
            . "1212212121210" . "2123212212121" . "2112122122120" . "1211212322212" . "1211212122210" . "2121121122120" . "2212114112122" . "2212112112120" . "2212121211210" . "2212232121211"
            . "2122122121210" . "2112122122120" . "1231212122212" . "1211211221220" . "2121121321222" . "2121121121220" . "2122112112120" . "2122141211212" . "1221221212110" . "2121221221210"
            . "2114121221221";

        //      $monthTypeMark = "1212122322121" . "1212121221220"; // 디버깅용 데이터.

        // $monthTypeMark 에 대응하는 날의수
        $dateCount = array(
            0,
            29,
            30,
            29,
            30
        );
        //문자열 입력을 배열로 컷팅.
        $perYear = str_split($monthTypeMark, 13);
        foreach ($perYear as $yearData)
        {
            $arr = str_split($yearData);
            $lunarMonthType[] = $arr;
        }

        //인덱스 구축.
        $solarDate = new \DateTime($this->solar_start);
        $lastSol = $solarDate->format('Ymd');
        $lastLuna = $this->lunar_start;
        $lunarYear = (int) substr($this->lunar_start, 0, 4);
        foreach ($lunarMonthType as $yearArr)
        {
            $accArr = array();

            $lunarMonth = 0;
            foreach ($yearArr as $monthType)
            {
                if ($monthType == '0')
                    continue;
                $dcnt = $dateCount[$monthType];

                $isLeapMonth = false;
                if ($monthType == '3' || $monthType == '4')
                    $isLeapMonth = true;
                else
                    $lunarMonth++;

                $lunarYMD = sprintf('%d%02d%02d%s', $lunarYear, $lunarMonth, 1, $isLeapMonth ? 'L' : ' ');

                if (isset($this->SolarToLunar[$solarDate->format('Ym')]) == false)
                {
                    $this->SolarToLunar[$solarDate->format('Ym')][$lastSol] = $lastLuna;
                }

                $this->SolarToLunar[$solarDate->format('Ym')][$solarDate->format('Ymd')] = $lunarYMD;
                $this->LunarToSolar[$lunarYMD] = $solarDate->format('Ymd');

                $lastSol = $solarDate->format('Ymd');
                $lastLuna = $lunarYMD;

                $solarDate->add(new \DateInterval('P' . $dcnt . 'D'));
            }
            $lunarYear++;
        }

    }

    /**
     * 디버깅용 인덱스 출력함수
     *
     */
    function print_index()
    {
        //      foreach ($this->SolarToLunar as $k=> $l)
        //      {
        //          if(count($l) >1)
        //          {
        //              print_r("$k => ");
        //              print_r($l);
        //          }
        //      }
        print_r($this->SolarToLunar);
    }

    /**
     * getLunarDate의 반환값을 포맷팅 하기 위한 함수
     * 아래 포맷을 지원함
     *  *Y-m-d  :  2010-02-03  형태
     *  *YmdL : 20100203L 형태, L이 붙으면 윤달 그렇지 않으면 윤달 아님.
     * @param unknown_type $lunarDate
     * @param unknown_type $fmt
     */
    static function formatLunar($lunar, $fmt = 'Y-m-d')
    {
        $lunarYear = $lunar['year'];
        $lunarMonth = $lunar['month'];
        $lunarDate = $lunar['date'];
        $isLeapMonth = $lunar['is_leap_month']; // 윤달

        return LunarCalendarService::formatLunar2($lunarYear, $lunarMonth, $lunarDate, $isLeapMonth, $fmt);
    }

    /**
     * 포맷팅 지원함수.
     * @param unknown_type $lunarYear
     * @param unknown_type $lunarMonth
     * @param unknown_type $lunarDate
     * @param unknown_type $isLeapMonth
     * @param unknown_type $fmt
     */
    static function formatLunar2($lunarYear, $lunarMonth, $lunarDate, $isLeapMonth, $fmt)
    {
        switch ($fmt)
        {
            case 'Y-m-d':
                $lunarYMD = sprintf('%04d-%02d-%02d', $lunarYear, $lunarMonth, $lunarDate);
                break;
            case 'YmdL':
                $lunarYMD = sprintf('%04d%02d%02d%s', $lunarYear, $lunarMonth, $lunarDate, $isLeapMonth ? 'L' : ' ');
                break;
        }
        return $lunarYMD;
    }

    /**
     * getLunarDate의 쓰기 편한 형태
     * 2010-03-18 형태로 아규먼트를 넣을수 있음.
     *
     * @param unknown_type $Y_m_d
     */
    function getLunarDateYmd($Y_m_d)
    {
        //      print_r('$Y_m_d' .$Y_m_d);
        $format = "Y-m-d";
        $tm = date_parse_from_format($format, $Y_m_d);
        $year = $tm["year"];
        $month = $tm["month"];
        $date = $tm["day"];
        //      print_r($tm);
        return $this->getLunarDate($year, $month, $date);
    }
    /**
     *
     * 음력으로 돌려줌.
     * 반환은 아래 형태
     *        Array
     *        (
     *            [year] => 2050
     *            [month] => 03
     *            [date] => 9
     *            [is_leap_month] => 0 // 윤달 여부, 1이면 윤달.
     *        )
     * 계산 범위 초과시 null
     * @param unknown_type $year
     * @param unknown_type $month
     * @param unknown_type $date
     */
    function getLunarDate($year, $month, $date)
    {
        $this->error = "";
        list($nearSol, $nearLuna) = $this->_getNearData($year, $month, $date);

        if (empty($nearSol))
            return null;

        //키와 입력과의 날짜 차이만금, lunarPinDate에 더한다.
        $targetJD = cal_to_jd(CAL_GREGORIAN, $month, $date, $year);
        $keyJD = cal_to_jd(CAL_GREGORIAN, substr($nearSol, 4, 2), substr($nearSol, 6, 2), substr($nearSol, 0, 4));

        $diff = $targetJD - $keyJD;

        $lunarYear = substr($nearLuna, 0, 4);
        $lunarMonth = substr($nearLuna, 4, 2);
        $lunarDate = substr($nearLuna, 6, 2);
        $lunarLeapMonth = substr($nearLuna, 8, 1);

        $lunarDate += $diff;

        ## 년간 년지 구하기
        $ganji_1y = ($lunarYear + 6) % 10; // 당 년에서 7을 더하여 10으로 나눈 나머지 (배열이므로 0부터 시작하므로 -1)
        $ganji_2y = ($lunarYear + 8) % 12; // 9를 더한 값을 12로 나눈 나머지

        ## 월간 월지 구하기 (https://echotop.tistory.com/entry/%EC%97%B0%EC%9B%94%EC%9D%BC-%EA%B0%84%EC%A7%80-%EA%B3%84%EC%82%B0%EB%B2%95)
        $ganji_1m = (2 * $lunarYear + $lunarMonth + 2) % 10;
        $ganji_2m = ($lunarMonth) % 12;


        ## 일간 일지 구하기 (https://echotop.tistory.com/entry/%EC%97%B0%EC%9B%94%EC%9D%BC-%EA%B0%84%EC%A7%80-%EA%B3%84%EC%82%B0%EB%B2%95)
        $y = $lunarYear;
        $m = $lunarMonth;
        $d = $lunarDate;
        if ($m == 1 || $m == 2) {
            $y = $y - 1;
            if ($m == 1) {
                $m = 13;
            } else if ($m == 2) {
                $m = 14;
            }

        }
        // 1)    1월,2월 에는 y에서 1을 빼고 이 값을 y로 한다.
        // 2)    1월은 m=13,2월은 m=14로 한다.

        $c = substr($y, 0, 2);
        $n = substr($y, -2);

        $ganji_1d = ( 4 * $c + floor($c / 4) + 5 * $n + floor($n / 4) + floor((3 * $m + 3) / 5 ) + $d + 6 ) % 10;
        // p≡4c+[c/4]+5n+[n/4]+[(3m+3)/5]+d+7 (mod10)
        $ganji_2d = (8 * $c + floor($c / 4) + 5 * $n + floor($n / 4 ) + 6 * $m + floor(( 3 * $m + 3 ) / 5 ) + $d ) % 12;
        // q≡8c+[c/4]+5n+[n/4]+6m+[(3m+3)/5]+d+1 (mod12)

        // https://m.cafe.daum.net/klland/3va3/8
        // http://manse.sajuplus.net/ 하고 결과값이 다름
        // http://lifesci.net/pod/plugin/calendar/
        return array(
            'year' => $lunarYear,
            'month' => $lunarMonth,
            'date' => $lunarDate,
            'kor_ganji_y' => LunarCalendarService::KOR_GAN[$ganji_1y].LunarCalendarService::KOR_JI[$ganji_2y],
            'kor_ganji_m' => LunarCalendarService::KOR_GAN[$ganji_1m].LunarCalendarService::KOR_JI[$ganji_2m],
            'kor_ganji_d' => LunarCalendarService::KOR_GAN[$ganji_1d].LunarCalendarService::KOR_JI[$ganji_2d],
            'han_ganji_y' => LunarCalendarService::HAN_GAN[$ganji_1y].LunarCalendarService::HAN_JI[$ganji_2y],
            'han_ganji_m' => LunarCalendarService::HAN_GAN[$ganji_1m].LunarCalendarService::HAN_JI[$ganji_2m],
            'han_ganji_d' => LunarCalendarService::HAN_GAN[$ganji_1d].LunarCalendarService::HAN_JI[$ganji_2d],
            'zodiac' => LunarCalendarService::ZODIAC[$ganji_2y],
            'is_leap_month' => $lunarLeapMonth == 'L' ? 1 : 0,
        );
    }

    function _getNearData($year, $month, $date)
    {
        $ym = sprintf('%d%02d', $year, $month);
        $ymd = sprintf('%d%02d%02d', $year, $month, $date);

        if (false == isset($this->SolarToLunar[$ym]))
        {
            $this->error = '계산할수 있는 범위가 아닙니다.';
            return null;
        }
        $pair = $this->SolarToLunar[$ym];
        $lastLuna = '';
        $lastSol = "";
        //      print_r($pair);
        foreach ($pair as $sol => $luna)
        {
            //          print_r('$ymd < $keys[$i]    ' . "$ymd < $keys[$i]\n");
            //      print_r("$ymd $sol  $luna");
            if ($ymd < $sol)
            {
                return array(
                    $lastSol,
                    $lastLuna
                );
            }
            else if ($ymd == $sol)
            {
                return array(
                    $sol,
                    $luna
                );
            }
            $lastSol = $sol;
            $lastLuna = $luna;
        }
        return array(
            $lastSol,
            $lastLuna
        );
    }
    /**
     * 음력에 대응하는 양력 날짜 구하기.
     * @param unknown_type $lunarYear
     * @param unknown_type $lunarMonth
     * @param unknown_type $lunarDate
     * @param unknown_type $isLeapMonth
     */
    function getSolarDate($lunarYear, $lunarMonth, $lunarDate, $isLeapMonth = false)
    {
        $this->error = "";

        $nearKey = sprintf('%d%02d%02d%s', $lunarYear, $lunarMonth, 1, $isLeapMonth ? 'L' : ' ');

        if (false == isset($this->LunarToSolar[$nearKey]))
        {
            $this->error = '계산할수 있는 범위가 아닙니다.';
            return null;
        }

        $solarPinDate = $this->LunarToSolar[$nearKey];

        //키와 입력과의 날짜 차이만금, $solarPinDate 더한다.
        $keyDate = substr($nearKey, 6, 2);
        $keyIsLeapMonth = ('L' == substr($nearKey, 8, 1) ? true : false);
        if ($keyIsLeapMonth != $isLeapMonth)
        {
            $this->error = ($isLeapMonth ? "윤달" : "평달") . "$lunarYear-$lunarMonth-$lunarDate" . '는 없음.';
            return null;
        }

        $diff = $lunarDate - $keyDate;

        $date = \DateTime::createFromFormat('Ymd', $solarPinDate);
        //      print_r($date);
        $date->add(new \DateInterval('P' . $diff . 'D'));
        //      print_r($date);

        return $date->format('Y-m-d');
    }
}
//
// /* 테스트 용 소스 */
// $Y = 2012;
// $D = 04;
// $LunarCalendar = new LunarCalendar();
//
// echo "Sol -> Moon Test<br />";
// for($i=1;$i<=30;$i++) {
//     $rst = $LunarCalendar->getLunarDate($Y, $D, $i);
//     $rst = $LunarCalendar->formatLunar($rst);
//     echo "{$Y}-{$D}-{$i} -> ".$rst."<br />";
// }
// echo "<br />";
// echo "<br />";
// echo "Moon -> Sol Test<br />";
// for($i=1;$i<=31;$i++) {
//     $rst = $LunarCalendar->getSolarDate($Y, $D, $i, false);
//     echo "{$Y}-{$D}-{$i} -> ".$rst."<br />";
// }
