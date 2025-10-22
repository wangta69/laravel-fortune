<?php

namespace Pondol\Fortune\Services\Calendar;

use Pondol\Fortune\Facades\Manse;
use Pondol\Fortune\Traits\SelectDay as t_selectDay;

/**
 * 모든 계산법이 이사택일과 같으나 이사택일은 singu_jumsu  가 결혼택일은 dae_jumsu 가 마지막에 드러간다.
 */

class Move
{
    use t_selectDay;

    public function withManse($manse)
    {
        $this->hour = $manse->hour->ch;
        $this->day = $manse->day->ch;
        $this->month = $manse->month->ch;
        $this->year = $manse->year->ch;
        $this->hour_h = $manse->get_h('hour');
        $this->day_h = $manse->get_h('day');
        $this->month_h = $manse->get_h('month');
        $this->year_h = $manse->get_h('year');
        $this->hour_e = $manse->get_e('hour');
        $this->day_e = $manse->get_e('day');
        $this->month_e = $manse->get_e('month');
        $this->year_e = $manse->get_e('year');
        $this->gender = $manse->gender;

        return $this;
    }

    /**
     * 결혼택일은 본인과 상대방의 manse가 들어가야 한다.
     */
    public function marrage()
    {
        $this->calMarrageDay($targetdate, $sajuwha, $profile, $p_sajuwha, $p_profile); //
    }

    public function move()
    {
        $this->calMoveDay($targetdate, $sajuwha, $profile, $p_sajuwha, $p_profile); //
    }

    /**
     * @param $yymmdd : 이사일
     */
    public function calMoveDay($yyyymmdd, $sajuwha, $profile)
    {
        $birth_date = $sajuwha->no;
        $manse = ManseData::where('no', $yyyymmdd)->first();

        $jeol = $manse->jeol;

        $umdate = $manse->umdate;
        $to_umdate = substr($manse->umdate, 6, 2);


        $toyear_h = h_code_ch($manse->year_h);
        $tomonth_h = h_code_ch($manse->month_h);
        $today_h = h_code_ch($manse->day_h);

        $toyear_e = e_code_ch($manse->year_e);
        $tomonth_e = e_code_ch($manse->month_e);
        $today_e = e_code_ch($manse->day_e);

        $sum_year_h = $toyear_h;
        $sum_year_e = $toyear_e;

        $sum_month_h = $tomonth_h;
        $sum_month_e = $tomonth_e;

        $sum_day_h = $today_h;
        $sum_day_e = $today_e;

        $sum_day_he = $today_h . $today_e;

        $sum_to_umdate = $to_umdate;

        $sum_to_jeol = $jeol;

        // 1. 이사하는 시기의 나이 구하기
        $selected_year = substr($yyyymmdd, 0, 4);
        $my_age = $selected_year - substr($birth_date, 0, 4) + 1;


        $direction = _direction($my_age, $profile->gender);

        $titles = [];
        $scores = [];

        $aa = _he($toyear_e);
        $titles['color'] = 'black';
        if (in_array($sum_day_he, $aa)) {
            $titles['color'] = 'blue';
            $scores['color'] = 20;
        }

        $cal2 = _cal2($my_age, $profile->gender);
        $senggi_01 = $cal2['senggi_01'];
        $senggi_02 = $cal2['senggi_02'];
        $bokduk_01 = $cal2['bokduk_01'];
        $bokduk_02 = $cal2['bokduk_02'];
        $cheneu_01 = $cal2['cheneu_01'];
        $cheneu_02 = $cal2['cheneu_02'];

        if (($sum_day_e == $senggi_01) || ($sum_day_e == $senggi_02)) {
            $titles['senggi'] = '생기';
            $scores['sbc'] = 30;
        }
        if (($sum_day_e == $bokduk_01) || ($sum_day_e == $bokduk_02)) {
            $titles['bokduk'] = '복덕';
            $scores['sbc'] = 30;
        }
        if (($sum_day_e == $cheneu_01) || ($sum_day_e == $cheneu_02)) {
            $titles['cheneu'] = '천의';
            $scores['sbc'] = 30;
        }


        // 황도구하기
        $whangdo = _whangdo($sum_month_e, $sum_day_e);

        // 십악대패
        _sipak($sum_year_h, $sum_day_he, $sum_month_e, $titles, $scores);

        // 길신 >> 천덕
        _chenduk($sum_month_e, $sum_day_e, $sum_day_h, $titles, $scores);

        // 길신 >> 월덕
        _wolduk($sum_month_e, $sum_day_e, $sum_day_h, $titles, $scores);

        // 길신 >> 천덕합
        _chendukhap($sum_month_e, $sum_day_e, $sum_day_h, $titles, $scores);

        // 길신 >> 월덕합
        _woldukhap($sum_month_e, $sum_day_e, $sum_day_h, $titles, $scores);

        // 길신 >> 생기
        _seng($sum_month_e, $sum_day_e, $sum_day_h, $titles, $scores);

        // 길신 >> 천의
        _chen($sum_month_e, $sum_day_e, $titles, $scores);

        // 흉신 >> 천강
        _chengang($sum_month_e, $sum_day_e, $titles, $scores);

        // 흉신 >> 하괴
        _hague($sum_month_e, $sum_day_e, $titles, $scores);

        // 흉신 >> 지파
        _jipa($sum_month_e, $sum_day_e, $titles, $scores);

        // 흉신 >> 나망
        _namang($sum_month_e, $sum_day_e, $titles, $scores);

        // 흉신 >> 멸몰
        _myelmol($sum_month_e, $sum_day_e, $titles, $scores);

        // 흉신 >> 중상
        _jungsang($sum_month_e, $sum_day_h, $titles, $scores);

        // 흉신 >> 천구
        _chengu($sum_month_e, $sum_day_e, $titles, $scores);


        // 살구하기 >> 천살
        _chensal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 피마살
        _pamasal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 수사살
        _susasal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 망라살
        _mangrasal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 천적살
        _chenjeoksal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 고초살
        _gochosal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 귀기살
        _gueguesal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 왕망살
        _wangmangsal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 십악살
        _sipaksal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 월압살
        _wolapsal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 월살
        _wolsal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 황사살
        _hwangsasal($sum_month_e, $sum_day_e, $titles, $scores);

        // 살구하기 >> 홍사살
        _hongsasal($sum_month_e, $sum_day_e, $titles, $scores);


        // 축음양불장길일
        _chuk($sum_month_e, $sum_day_he, $titles, $scores);

        //  헌집/새집 길일
        _singu($sum_day_he, $titles, $scores);


        // 월기일 매월 초5일 14일 23 일
        _wolgi($sum_to_umdate, $titles, $scores);

        // 인동일
        _indong($sum_to_umdate, $titles, $scores);


        // 가취대흉일
        _gachui($sum_month_e, $sum_day_he, $titles, $scores);

        // 매달해일
        _haeil($sum_day_e, $titles, $scores);


        // 총점수
        $total = 0;
        foreach ($scores as $k => $v) {
            $total = $total + $v;
        }
    }
}
