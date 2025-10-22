<?php

namespace Pondol\Fortune\Services\Calendar;

use Pondol\Fortune\Traits\SelectDay as t_selectDay;
use Pondol\Fortune\Traits\Calendar;
use Pondol\Fortune\Facades\Saju;
use Pondol\Fortune\Facades\Lunar;

class MarriageCalendar
{
    use Calendar;


    public function cal($saju_male, $saju_female, $yyyymm, $options = [])
    {
        $this->info = Lunar::ymd($yyyymm.'01')->tolunar()->sajugabja()->create();
        $calendar = $this->_create($yyyymm);

        foreach ($this->days as $dayObject) {
            if ($dayObject && !empty($dayObject->day)) {
                $calculatedData = new MarriageDay();
                $calculatedData->cal($saju_male, $saju_female, $yyyymm . str_pad($dayObject->day, 2, '0', STR_PAD_LEFT), $options);
                $dayObject->setObject($calculatedData);
            }
        }

        return $this->splitPerWeek();
    }
}

class MarriageDay
{
    use t_selectDay;

    public function cal($saju_male, $saju_female, $yyyymmdd, $options = [])
    {
        $now = Saju::ymd($yyyymmdd)->create();

        // --- 신랑 기준 점수 계산 ---
        $male_titles = [];
        $male_scores = [];
        $this->calculateForPerson($saju_male, $now, $male_titles, $male_scores);

        // --- 신부 기준 점수 계산 ---
        $female_titles = [];
        $female_scores = [];
        $this->calculateForPerson($saju_female, $now, $female_titles, $female_scores);

        // --- 공통/결혼 특화 점수 계산 ---
        $common_titles = [];
        $common_scores = [];
        $this->calculateCommon($saju_male, $saju_female, $now, $common_titles, $common_scores); // 공통 계산은 신부 사주 기준(대리월 등)

        // --- 최종 점수 및 정보 종합 ---
        $this->male_total = array_sum($male_scores) + array_sum($common_scores);
        $this->female_total = array_sum($female_scores) + array_sum($common_scores);
        $this->total = round(($this->male_total + $this->female_total) / 2); // 남녀 점수 평균

        // 뷰에서 보여줄 모든 길흉신 이름 (중복 제거)
        $this->titles = array_merge($male_titles, $female_titles, $common_titles);
        $this->taekilInfo_male = $saju_male->taekil()->checkDate(substr($now->solar, 0, 10));
        $this->taekilInfo_female = $saju_female->taekil()->checkDate(substr($now->solar, 0, 10));
    }

    /**
     * 개인별 길흉 점수를 계산하는 헬퍼 메소드
     */
    private function calculateForPerson($saju_person, $now, &$titles, &$scores)
    {
        // 생기복덕, 천의
        $my_age = (int)substr($now->solar, 0, 4) - (int)substr($saju_person->solar, 0, 4) + 1;
        $senggi = $this->_senggiBokdukCheneu($my_age, $saju_person->gender);
        if (in_array($now->get_e('day'), $senggi['senggi'])) {
            $titles['senggi'] = ['ko' => '생기일', 'desc' => '개인에게 활력이 넘치는 좋은 날입니다.', 'type' => 'gilsin'];
            $scores['senggi'] = 30;
        }
        if (in_array($now->get_e('day'), $senggi['bokduk'])) {
            $titles['bokduk'] = ['ko' => '복덕일', 'desc' => '개인에게 복과 덕이 따르는 좋은 날입니다.', 'type' => 'gilsin'];
            $scores['bokduk'] = 30;
        }
        if (in_array($now->get_e('day'), $senggi['cheneu'])) {
            $titles['cheneu'] = ['ko' => '천의일', 'desc' => '하늘의 의사가 돕는 날로, 건강 관련 행사에 좋습니다.', 'type' => 'gilsin'];
            $scores['cheneu'] = 30;
        }

        // 일간 합/충
        $my_ilgan = $saju_person->get_h('day'); // 사용자의 일간
        $today_ilgan = $now->get_h('day'); // 이사일의 일간

        // 천간합(天干合) 확인 - 매우 길함
        $isHap = false;
        if (($my_ilgan === '甲' && $today_ilgan === '己') || ($my_ilgan === '己' && $today_ilgan === '甲')) {
            $isHap = true;
        }
        if (($my_ilgan === '乙' && $today_ilgan === '庚') || ($my_ilgan === '庚' && $today_ilgan === '乙')) {
            $isHap = true;
        }
        if (($my_ilgan === '丙' && $today_ilgan === '辛') || ($my_ilgan === '辛' && $today_ilgan === '丙')) {
            $isHap = true;
        }
        if (($my_ilgan === '丁' && $today_ilgan === '壬') || ($my_ilgan === '壬' && $today_ilgan === '丁')) {
            $isHap = true;
        }
        if (($my_ilgan === '戊' && $today_ilgan === '癸') || ($my_ilgan === '癸' && $today_ilgan === '戊')) {
            $isHap = true;
        }

        // 천간충(天干沖) 확인 - 매우 흉함
        $isChung = false;
        if (($my_ilgan === '甲' && $today_ilgan === '庚') || ($my_ilgan === '庚' && $today_ilgan === '甲')) {
            $isChung = true;
        }
        if (($my_ilgan === '乙' && $today_ilgan === '辛') || ($my_ilgan === '辛' && $today_ilgan === '乙')) {
            $isChung = true;
        }
        if (($my_ilgan === '丙' && $today_ilgan === '壬') || ($my_ilgan === '壬' && $today_ilgan === '丙')) {
            $isChung = true;
        }
        if (($my_ilgan === '丁' && $today_ilgan === '癸') || ($my_ilgan === '癸' && $today_ilgan === '丁')) {
            $isChung = true;
        }

        if ($isHap) {
            $titles['ilgan_hap'] = ['ko' => '천간합일', 'desc' => '나의 기운과 날의 기운이 합을 이루어 만사가 순조로운 매우 좋은 날입니다.', 'type' => 'gilsin'];
            $scores['ilgan_hap'] = 50;
        }

        if ($isChung) {
            $titles['ilgan_chung'] = ['ko' => '천간충일', 'desc' => '나의 기운과 날의 기운이 정면으로 충돌하여, 다툼이나 계획 변경이 생기기 쉬운 흉한 날입니다.', 'type' => 'hyungsal'];
            $scores['ilgan_chung'] = -50;
        }

    }

    /**
     * 결혼에 공통적으로 적용되는 길흉을 계산하는 헬퍼 메소드
     */
    private function calculateCommon($saju_male, $saju_female, $now, &$titles, &$scores)
    {
        // --- 1. 대흉일 (반드시 피해야 할 날) ---
        // 복단일(伏斷日) 확인
        $today_ganji = $now->get_he('day'); // 이사일의 간지 (예: 甲子)
        $today_ilgan = $now->get_h('day');
        $today_ilji = $now->get_e('day');
        $month_e = $now->get_e('month');
        $year_h = $now->get_h('year');
        $lunar_day_str = substr($now->lunar, -2);

        // --- 1. 대흉일 (반드시 피해야 할 날) ---
        $bokdanil_list = [
            '甲寅', '乙卯', '庚寅', '辛卯', // 角, 亢
            '戊戌', '己亥',             // 婁, 胃
            '丙午', '丁未', '壬午', '癸未', // 井, 鬼
            '丙辰', '丁巳', '壬辰', '癸巳'  // 翼, 軫
        ];

        if (in_array($today_ganji, $bokdanil_list)) {
            $titles['bokdanil'] = ['ko' => '복단일', 'desc' => '엎어지고 끊어지는 대흉일로, 결혼에는 매우 부적합합니다.', 'type' => 'hyungsal'];
            $scores['bokdanil'] = -100;
        }

        // 기타 대흉일
        $this->_sipak($year_h, $today_ganji, $month_e, $titles, $scores); // 십악대패
        $this->_wolgi($lunar_day_str, $titles, $scores); // 월기일 매월 초5일 14일 23 일
        $this->_gachui($month_e, $today_ganji, $titles, $scores); // 가취대흉일
        $this->_indong($lunar_day_str, $titles, $scores); // 인동일

        // --- 2. 길일 (결혼에 좋은 날) ---
        $this->_whangdo($month_e, $today_ilji, $titles, $scores); // 황도
        $this->_chuk($month_e, $today_ganji, $titles, $scores);
        $this->_cheonsa($month_e, $today_ganji, $titles, $scores); // 천사일

        // --- 3. 기타 길흉신 (MoveDay와 공통) ---
        $this->_chenduk($month_e, $today_ilji, $today_ilgan, $titles, $scores); // 길신 >> 천덕
        $this->_wolduk($month_e, $today_ilgan, $titles, $scores); // 길신 >> 월덕
        $this->_chendukhap($month_e, $today_ilji, $today_ilgan, $titles, $scores); // 길신 >> 천덕합
        $this->_woldukhap($month_e, $today_ilji, $today_ilgan, $titles, $scores);  // 길신 >> 월덕합

        $this->_chengang($month_e, $today_ilji, $titles, $scores); // 흉신 >> 천강
        $this->_hague($month_e, $today_ilji, $titles, $scores); // 흉신 >> 하괴
        $this->_jipa($month_e, $today_ilji, $titles, $scores); // 흉신 >> 지파
        $this->_namang($month_e, $today_ilji, $titles, $scores);  // 흉신 >> 나망
        $this->_myelmol($month_e, $today_ilji, $titles, $scores); // 흉신 >> 멸몰
        $this->_jungsang($month_e, $today_ilgan, $titles, $scores); // 흉신 >> 중상
        $this->_chengu($month_e, $today_ilji, $titles, $scores);  // 흉신 >> 천구
        $this->_wolsal($month_e, $today_ilji, $titles, $scores); // 살구하기 >> 월살
        $this->_haeil($today_ilji, $titles, $scores); // 매달해일

        // --- 4. 결혼 특화 길흉 ---
        // 대리월/소리월 등 (신부 띠 기준)
        $this->_dae($saju_female->get_e('year'), $now->get_e('month'), $titles, $scores);

        // 여명부주 (신부 띠 기준, 신랑에게 해로운 날)
        $this->_yeomyeongbuju($saju_female->get_e('year'), $today_ganji, $titles, $scores);

        // 남명부처 (신랑 띠 기준, 신부에게 해로운 날)
        $this->_nammyeongbucheo($saju_male->get_e('year'), $today_ganji, $titles, $scores);

        // --- 5. [추가] 기타 참고 흉살 (낮은 가중치) ---
        $month_e = $now->get_e('month');
        $day_e = $now->get_e('day');

        $this->_chensal($month_e, $day_e, $titles, $scores); // 살구하기 >> 천살
        $this->_pamasal($month_e, $day_e, $titles, $scores); // 살구하기 >> 피마살
        $this->_susasal($month_e, $day_e, $titles, $scores); // 살구하기 >> 수사살
        $this->_mangrasal($month_e, $day_e, $titles, $scores); // 살구하기 >> 망라살
        $this->_chenjeoksal($month_e, $day_e, $titles, $scores); // 살구하기 >> 천적살
        $this->_gochosal($month_e, $day_e, $titles, $scores); // 살구하기 >> 고초살
        $this->_gueguesal($month_e, $day_e, $titles, $scores); // 살구하기 >> 귀기살
        $this->_wangmangsal($month_e, $day_e, $titles, $scores); // 살구하기 >> 왕망살
        $this->_sipaksal($month_e, $day_e, $titles, $scores);  // 살구하기 >> 십악살
        $this->_wolapsal($month_e, $day_e, $titles, $scores); // 살구하기 >> 월압살
        $this->_hwangsasal($month_e, $day_e, $titles, $scores); // 살구하기 >> 황사살
        $this->_hongsasal($month_e, $day_e, $titles, $scores);

    }
}
