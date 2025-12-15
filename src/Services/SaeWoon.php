<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Facades\Saju;

/**
 * 12실살을 제외한 기타 길신 및 흉신 구하기
 * 일지기준으로 하면 집안일
 * 년지기준으로 하면 바깥일
 */
class SaeWoon
{
    private $gender;

    private $birth_time;

    private $lunar_ymd;

    private $month_h;

    private $year_h;

    private $month_e;

    private $direction;

    /**
     * @param  string  $gender  : W, M (from profile)
     * @param  string  $birth_ym  : hhmm (from profile)
     * @param  string  $lunar_ymd  음력 생년월일 (from saju)
     */
    // function daeun($gender, $birth_time, $lunar_ymd, $month_h, $year_h, $month_e){ //
    public function withSaju($saju) //
    {
        $this->month_h = $saju->get_h('month');
        $this->year_h = $saju->get_h('year');
        $this->month_e = $saju->get_e('month');
        $this->gender = $saju->gender;

        $start = substr($saju->lunar, 0, 4);
        $end = $start + 80;
        $i = 0;
        $this->saewoon = [];
        for ($year = $start; $year < $end; $year++) {
            $result = calgabja($year);

            $this->saewoon_h[$i] = $result->h;
            $this->saewoon_e[$i] = $result->e;
            $this->age[$i] = $i + 1;
            $this->year[$i] = $year;
            $i++;
        }

        $this->sipsin_e = $this->sipsin_e($saju);
        $this->woonsung_e = $this->woonsung_e($saju);

        return $this;
    }

    //  세운의 지지 10성 구하기
    private function sipsin_e($saju)
    {
        $sipsin_e = [];
        foreach ($this->saewoon_e as $k => $v) {
            // 지지의 10성
            $sipsin_e[$k] = SipSin::cal($saju->get_h('day'), $v, 'e');
        }

        return $sipsin_e;
    }

    // 세운의 지지 12운성 구하기
    private function woonsung_e($saju)
    {
        $woonsung_e = [];
        foreach ($this->saewoon_e as $k => $v) {
            // 지지의 12운성
            $woonsung_e[$k] = Woonsung12::cal($saju->get_h('day'), $v);
        }

        return $woonsung_e;
    }
}
