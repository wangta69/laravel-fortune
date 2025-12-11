<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Services\Oheng;
use Pondol\Fortune\Facades\Lunar;

class Saju
{
    public $sl = 'solar'; // $lunar
    public $solar; // 양력
    public $lunar; // 음력
    public $leap = false; // 윤달여부
    public $ymd; // 생년워일 yyyymmdd
    public $hi = '9999'; // 생시 hhmm (예 1330, 13시 30분)
    public $hourKnown = true; // 시간 정보 유무를 나타내는 플래그
    public $year = ['ch' => '', 'ko' => ''];
    public $month = ['ch' => '', 'ko' => ''];
    public $day = ['ch' => '', 'ko' => ''];
    public $hour = ['ch' => '', 'ko' => ''];
    public $gender = 'M'; //M(Man) | W(Woman)
    public $korean_age; // 한국나이


    public $oheng; // 오행
    public $sipsin; // 10신
    public $zizangan; // 지장간
    public $daewoon; // 대운
    public $woonsung12; // 12운성
    public $sinsal; // 신살
    public $sinsal12; // 12신살
    public $unse;  // 운세(기타 신살 포함)
    public $taekil; // 택일

    public function __construct()
    {
        // 객체가 생성될 때 기본값으로 현재 시간을 설정합니다.
        // 기존 ymdhi() 메소드를 재사용하여 코드 중복을 피합니다.
        $this->ymdhi(now()->format('YmdHi'));

        // 다른 기본값들도 여기서 설정할 수 있습니다.
        $this->sl = 'solar';
        $this->leap = false;
        $this->gender = 'M'; // 기본 성별
    }

    /**
     * 생년월일생시
     * @param $ymdhi = yyyymmddhhii
     */
    public function ymdhi($ymdhi)
    {
        $ymdhi = str_replace(['-', ':'], '', trim($ymdhi));
        $len = strlen($ymdhi);
        $typeof = gettype($ymdhi);

        switch ($len) {
            case 8:
                $ymd = $ymdhi;
                $this->hi = '9999'; // 8자리 입력은 시간이 없는 것으로 간주
                $this->hourKnown = false;
                break;
            case 12:
                preg_match('/^([0-9]{8})([0-9]{4})$/', trim($ymdhi), $match);
                list(, $ymd, $hi) = $match;
                // '시간 모름' 값(예: 9999)을 받았을 때 처리
                if ($hi === '9999' || substr($hi, 0, 2) === '99') {
                    $this->hi = '9999';
                    $this->hourKnown = false;
                } else {
                    $this->hi = $hi;
                    $this->hourKnown = true;
                }
                break;
            default: // 8자리나 12자리가 아닌 모든 경우를 처리
                throw new \Exception("Invalid date length. Expected 8 or 12 characters, but got " . $len);
        }
        preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})$/', trim($ymd), $match);
        if (count($match) < 4) { // preg_match가 실패한 경우에 대한 방어 코드
            throw new \Exception("Failed to parse ymd: " . $ymd);
        }
        list(, $y, $m, $d) = $match;
        $this->ymd = $y.'-'.$m.'-'.$d;

        return $this;
    }

    public function ymd($ymd)
    {
        return $this->ymdhi($ymd);
    }

    /**
     * 양|음력
     * @param String $sl : solar | lunar
     */
    public function sl($sl)
    {
        $this->sl = $sl;
        return $this;
    }

    /**
     * 윤달여부
     *@param Boolean $leap : true | false
     */
    public function leap($leap)
    {
        $this->leap = $leap;
        return $this;
    }

    public function gender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    public function create()
    {

        switch ($this->sl) {
            case 'solar':
                $this->solar = $this->ymd;
                // [수정] hourKnown 플래그에 따라 분기 처리
                if ($this->hourKnown) {
                    $saju = Lunar::ymd($this->ymd)->hi($this->hi)->tolunar()->sajugabja()->create();
                } else {
                    $saju = Lunar::ymd($this->ymd)->tolunar()->sajugabja()->create(); // hi() 호출 제외
                    // $this->hour = (object)['ko' => '알수없음', 'ch' => '時柱不明'];
                }
                $this->lunar = $saju->lunar;
                break;
            case 'lunar':
                $this->lunar = $this->ymd;
                // [수정] hourKnown 플래그에 따라 분기 처리
                if ($this->hourKnown) {
                    $saju = Lunar::ymd($this->ymd)->hi($this->hi)->tosolar($this->leap)->sajugabja()->create();
                } else {
                    $saju = Lunar::ymd($this->ymd)->tosolar($this->leap)->sajugabja()->create(); // hi() 호출 제외
                    // $this->hour = (object)['ko' => '알수없음', 'ch' => '時柱不明'];
                }
                $this->solar = $saju->solar;
                break;
        }


        $this->year = (object)$saju->gabja->year;
        $this->month = (object)$saju->gabja->month;
        $this->day = (object)$saju->gabja->day;
        $this->hour = (object)$saju->gabja->hour;

        // gabja 프로퍼티도 객체로 유지 (하위 호환성)
        $this->gabja = (object)[
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'hour' => $this->hour,
        ];

        $this->korean_age = date('Y') - substr($this->solar, 0, 4) + 1;
        return $this;
    }


    public function seasonal_division($ymd)
    {
        return Lunar::seasonal_division($ymd)->create();
    }

    /** 만세에서  천간 가져오기
    *@param String $str hour | day | month | year
    */
    public function get_h($str)
    {

        return mb_substr($this->{$str}->ch, 0, 1);
    }

    public function get_h_serial($str)
    {
        return h_to_serial($this->get_h($str));
    }

    /**
     * 만세에서 지지 가져오기
     */
    public function get_e($str)
    {
        return mb_substr($this->{$str}->ch, 1, 1);
    }

    public function get_e_serial($str)
    {
        return e_to_serial($this->get_e($str));
    }

    public function get_e_wolgun($str)
    {
        return e_to_wolgun($this->get_e($str));
    }

    /**
     * 만세력에서 60갑자 가져오기
     */
    public function get_he($str)
    {
        return $this->{$str}->ch;
    }


    /**
     * oheng 구하기
     */
    public function oheng()
    {
        if (!isset($this->oheng)) {
            $ohengCalculator  = new Oheng();
            $this->oheng = $ohengCalculator ->withSaju($this);
        }
        // $callback($oheng);
        return $this->oheng;
    }

    public function get_oheng(string $pillar, string $type = 'h'): string
    {
        if (!isset($this->oheng)) {
            $this->oheng();
        }

        $property = $pillar . '_' . $type;
        return $this->oheng->{$property}->ch ?? '';
    }

    /**
     * 길신/흉신 구하기
     * 위의 신살 구하기에서 결과를 받아와서 년월일시로 배열을 재정리
     */
    public function sinsal()
    {
        if (!isset($this->sinsal)) {
            $sinsal = new Sinsal();
            $this->sinsal = $sinsal->withSaju($this)->sinsal()->create();
        }
        return $this->sinsal;
    }

    /**
     * 12신살 구하기
     */
    public function sinsal12()
    {
        if (!isset($this->sinsal12)) {
            $this->sinsal12 = (new Sinsal12())->withSaju($this);
        }
        return $this->sinsal12;
    }

    /**
     * 운세 분석기 (년운, 월운 등)
     * 이 메서드는 Unse 객체 자체를 반환하여, 외부에서 checkYear() 등을 호출할 수 있게 합니다.
     */
    public function unse()
    {
        if (!isset($this->unse)) {
            $this->unse = (new Unse())->withSaju($this);
        }
        return $this->unse;
    }

    /**
     * 택일 분석기 (특정 날짜의 길흉)
     * Taekil 객체를 생성하고 사주 정보를 주입하여 반환합니다.
     * 외부에서 checkDate()를 호출하여 사용합니다.
     */
    public function taekil()
    {
        if (!isset($this->taekil)) {
            $this->taekil = (new Taekil())->withSaju($this);
        }
        return $this->taekil;
    }

    /**
     * 기타 특수 신살 분석기
     */
    public function gita()
    {
        if (!isset($this->gita)) {
            $this->gita = (new Gita())->withSaju($this);
        }
        return $this->gita;
    }

    /**
     * 12운성 구하기
     */
    public function woonsung12()
    {
        if (!isset($this->woonsung12)) {
            $woonsung12 = new Woonsung12();
            $this->woonsung12 = $woonsung12->withSaju($this);
        }
        return $this->woonsung12;
    }

    /**
    * 10신 구하기
    */
    public function sipsin()
    {
        if (!isset($this->sipsin)) {
            $sipsin = new Sipsin();
            $this->sipsin = $sipsin->withSaju($this);
        }
        return $this->sipsin;
    }

    /**
     * 지장간 구하기
     */
    public function zizangan()
    {
        if (!isset($this->zizangan)) {
            $zizangan = new Zizangan();
            $this->zizangan = $zizangan->withSaju($this);
        }
        return $this->zizangan;
    }

    /**
     * 대운구하기
     */
    public function daewoon()
    {
        if (!isset($this->daewoon)) {
            $daewoon = new DaeWoon();
            $this->daewoon = $daewoon->withSaju($this);
        }
        return $this->daewoon;
    }

    /**
     * 세운구하기
     */
    public function saewoon()
    {
        if (!isset($this->saewoon)) {
            $saewoon = new SaeWoon();
            $this->saewoon = $saewoon->withSaju($this);
        }
        return $this->saewoon;
    }

    /**
     * 신약신강구하기
     */
    public function sinyaksingang()
    {
        if (!isset($this->sinyaksingang)) {
            $sinyaksingang = new SinyakSingang();
            $this->sinyaksingang = $sinyaksingang->withSaju($this);
        }
        return $this->sinyaksingang;
    }

    /**
     *  토정비결용 작괘 구하기
     */
    public function tojeong()
    {
        if (!isset($this->tojung)) {
            $this->tojeong = (new TojeongJakgwae())->withSaju($this);
        }

        return $this->tojeong;
    }

}
