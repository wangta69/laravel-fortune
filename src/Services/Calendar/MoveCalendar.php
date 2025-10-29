<?php

namespace Pondol\Fortune\Services\Calendar;

use Pondol\Fortune\Traits\SelectDay as t_selectDay;
use Pondol\Fortune\Traits\Calendar;
use Pondol\Fortune\Facades\Saju;
use Pondol\Fortune\Facades\Lunar;

/**
* 모든 계산법이 이사택일과 같으나 이사택일은 singu_jumsu  가 결혼택일은 dae_jumsu 가 마지막에 드러간다.
*/

class MoveCalendar
{
    use Calendar;


    /**
     * 이사택일 구하기
     */
    public function cal($saju, $yyyymm, $options)
    {
        $this->info = Lunar::ymd($yyyymm.'01')->tolunar()->sajugabja()->create();

        // 1. Calendar 트레이트의 _create 메소드로 기본 달력 구조를 만듭니다.
        //    이때 $calendar->days 안의 Day 객체들은 solar, lunar, gabja 등의 기본 정보를 이미 가지고 있습니다.
        $calendar = $this->_create($yyyymm);

        // 2. 각 날짜를 순회하며 길흉 정보를 계산하고, 기존 Day 객체에 병합합니다.
        foreach ($this->days as $dayObject) { // $calendar->days가 아닌 $this->days를 사용

            if ($dayObject && !empty($dayObject->day)) {

                // 길흉신 계산을 위한 별도의 클래스(MoveDay) 사용
                $calculatedData = new MoveDay();
                $calculatedData->cal($saju, $yyyymm . str_pad($dayObject->day, 2, '0', STR_PAD_LEFT), $options);

                // 계산된 프로퍼티들(total, titles, taekilInfo)을 기존 $dayObject에 병합
                $dayObject->setObject($calculatedData);
            }
        }

        return $calendar->splitPerWeek();
    }

}

class MoveDay
{
    use t_selectDay;
    /**
     * $request
     * 대장군 상살방을 처리하기 위해서는 (moving_direction_enabled, moving_direction) 전달
     * 가족구성원 조화를 보기위해서는 (family_harmony_enabled, family_years) 전달
     */

    public function cal($saju, $yyyymmdd, $options)
    {
        // 그날의 간지 정보를 위해 Saju 객체 생성
        $now = Saju::ymd($yyyymmdd)->create();
        $solarDateString = substr($yyyymmdd, 0, 4) . '-' . substr($yyyymmdd, 4, 2) . '-' . substr($yyyymmdd, 6, 2);

        $titles = [];
        $scores = [];
        $this->taekilInfo = []; // Taekil 상세 결과를 담을 프로퍼티

        // --- 1. 개인 띠(年支) 기준 길흉 (Taekil 서비스) ---
        $taekilResult = $saju->taekil()->checkDate($solarDateString);
        $this->taekilInfo = $taekilResult; // 결과를 뷰에서 사용하기 위해 저장

        // Taekil 결과에 따른 점수 반영
        foreach ($taekilResult as $sinsal) {
            if ($sinsal['type'] === 'gilsin') {
                $scores[$sinsal['ko']] = 20; // 길신 +20점
            } elseif ($sinsal['type'] === 'hyungsal') {
                $scores[$sinsal['ko']] = -30; // 흉살 -30점
            }
        }

        // --- 2. 개인 나이 기준 길흉 (생기/복덕/천의) ---
        $my_age = (int)substr($yyyymmdd, 0, 4) - (int)substr($saju->solar, 0, 4) + 1;
        $senggiBokdukCheneu = $this->_senggiBokdukCheneu($my_age, $saju->gender);
        if (in_array($now->get_e('day'), $senggiBokdukCheneu['senggi'])) {
            $titles['senggi'] = ['ko' => '생기일', 'desc' => '개인에게 활력이 넘치는 좋은 날입니다.', 'type' => 'gilsin'];
            $scores['senggi'] = 30;
        }
        if (in_array($now->get_e('day'), $senggiBokdukCheneu['bokduk'])) {
            $titles['bokduk'] = ['ko' => '복덕일', 'desc' => '개인에게 복과 덕이 따르는 좋은 날입니다.', 'type' => 'gilsin'];
            $scores['bokduk'] = 30;
        }
        if (in_array($now->get_e('day'), $senggiBokdukCheneu['cheneu'])) {
            $titles['cheneu'] = ['ko' => '천의일', 'desc' => '하늘의 의사가 돕는 날로, 병을 치료하거나 건강을 돌보기에 좋은 날입니다.', 'type' => 'gilsin'];
            $scores['cheneu'] = 30;
        }

        // --- 3. 개인 일간(日干) 기준 길흉 (천간합/충) ---
        $my_ilgan = $saju->get_h('day'); // 사용자의 일간
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


        // --- 4. 보편적 대흉일 (반드시 피해야 할 날) ---

        // 복단일(伏斷日) 확인
        $today_ganji = $now->get_he('day'); // 이사일의 간지 (예: 甲子)
        $bokdanil_list = [
            '甲寅', '乙卯', '庚寅', '辛卯', // 角, 亢
            '戊戌', '己亥',             // 婁, 胃
            '丙午', '丁未', '壬午', '癸未', // 井, 鬼
            '丙辰', '丁巳', '壬辰', '癸巳'  // 翼, 軫
        ];

        if (in_array($today_ganji, $bokdanil_list)) {
            $titles['bokdanil'] = ['ko' => '복단일', 'desc' => '엎어지고 끊어지는 대흉일로, 이사와 같은 새로운 시작을 하기에 매우 부적합합니다.', 'type' => 'hyungsal'];
            $scores['bokdanil'] = -100;
        }

        // 기타 대흉일
        $this->_sipak($now->get_h('year'), $now->get_he('day'), $now->get_e('month'), $titles, $scores); // 십악대패일

        $lunar_day_str = substr($now->lunar, -2);
        $this->_wolgi($lunar_day_str, $titles, $scores); // 월기일
        $this->_indong($lunar_day_str, $titles, $scores); // 제사불의 등 피해야 할 날
        $this->_gachui($now->get_e('month'), $today_ganji, $titles, $scores); // 가취대흉일 (결혼에 주로 쓰나 이사에도 참고)

        // --- 5. 보편적 길일 / 기타 흉살 (참고하면 좋은 날) --
        $this->_whangdo($now->get_e('month'), $now->get_e('day'), $titles, $scores); // 황도/흑도
        $this->_chuk($now->get_e('month'), $today_ganji, $titles, $scores); // 축음양불장길일(이사에 특화된 길일)
        // 주요 길신
        $this->_chenduk($now->get_e('month'), $now->get_e('day'), $today_ilgan, $titles, $scores);
        $this->_wolduk($now->get_e('month'), $today_ilgan, $titles, $scores);
        $this->_chendukhap($now->get_e('month'), $now->get_e('day'), $today_ilgan, $titles, $scores); // [파라미터 수정됨]
        $this->_woldukhap($now->get_e('month'), $now->get_e('day'), $today_ilgan, $titles, $scores);

        // 주요 흉신 (흑도 포함)
        $this->_chengang($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_hague($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_jipa($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_namang($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_myelmol($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_jungsang($now->get_e('month'), $today_ilgan, $titles, $scores);
        $this->_chengu($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        // 기타 살(煞)
        $this->_chensal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_pamasal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_susasal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_mangrasal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_chenjeoksal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_gochosal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_gueguesal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_wangmangsal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_sipaksal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_wolapsal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_wolsal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_hwangsasal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_hongsasal($now->get_e('month'), $now->get_e('day'), $titles, $scores);
        $this->_haeil($now->get_e('day'), $titles, $scores);

        // 헌집/새집 길일 (이사에 매우 적합)
        $this->_singu($today_ganji, $titles, $scores);

        // 6. "손 없는 날(민속 길일)" (이사에 가장 대중적인 길일)
        if ((int)$lunar_day_str % 10 === 9 || (int)$lunar_day_str % 10 === 0) {
            $titles['son_eopneun_nal'] = ['ko' => '손 없는 날', 'desc' => '악귀가 없는 길한 날로, 이사에 가장 좋은 날 중 하나입니다.', 'type' => 'gilsin'];
            $scores['son_eopneun_nal'] = 40;
        }

        // --- 7. 옵션 (방향, 가족 조화) ---
        // 대장군/삼살방을 계산하는 함수 필요
        $moving_direction_enabled = $options['moving_direction_enabled'];
        $moving_direction = $options['moving_direction'];

        // echo 'moving_direction_enabled:'.$moving_direction_enabled.', moving_direction:'.$moving_direction.PHP_EOL;
        if ($moving_direction_enabled && $moving_direction) {
            $year_e = $now->get_e('year');
            $bad_directions = $this->getBadDirections($year_e);
            $user_move_direction = $moving_direction; // 사용자가 입력한 방향

            if ($user_move_direction === $bad_directions['daejanggun']) {
                $titles['daejanggun'] = ['ko' => '대장군방', 'desc' => '선택한 이사 방향이 올해 가장 흉한 대장군 방향에 해당합니다.', 'type' => 'hyungsal'];
                $scores['daejanggun'] = -100;
            }
            if ($user_move_direction === $bad_directions['samsalbang']) {
                $titles['samsalbang'] = ['ko' => '삼살방', 'desc' => '선택한 이사 방향이 올해 세 가지 재앙이 깃든 삼살방에 해당합니다.', 'type' => 'hyungsal'];
                $scores['samsalbang'] = -100;
            }

            // print_r($scores);
        }

        // 가족 구성원과의 조화
        $family_harmony_enabled = $options['family_harmony_enabled'];
        $family_years = $options['family_years'];
        if ($family_harmony_enabled && $family_years) {
            $today_ilji = $now->get_e('day');
            // $family_birth_years = $request->input('family_years'); // ['寅', '午', ...]

            $chung_map = ['子' => '午', '午' => '子', '丑' => '未', '未' => '丑', '寅' => '申', '申' => '寅', '卯' => '酉', '酉' => '卯', '辰' => '戌', '戌' => '辰', '巳' => '亥', '亥' => '巳'];
            // $familySaju
            foreach ($family_years as $birth_year) {
                // Saju에서는 입춘을 기준으로 년을 계산하므로 입춘이후의 월일을 임의로 대입, 여기서는 02월 04일을 사용
                $yeonji = Saju::ymd($birth_year . '0204')->create()->get_e('year');
                if (isset($chung_map[$yeonji]) && $chung_map[$yeonji] === $today_ilji) {
                    $titles['family_chung'] = ['ko' => '가족과 충(沖)', 'desc' => '이삿날이 가족 구성원의 띠와 충돌하여 불화나 좋지 않은 일이 생길 수 있습니다.', 'type' => 'hyungsal'];
                    $scores['family_chung'] = -30;


                    break; // 한 명이라도 충하면 더 계산할 필요 없음
                }
            }
        }


        // 5. 총점 계산
        $this->total = 0;
        $this->titles = $titles;
        foreach ($scores as $score) {
            $this->total += $score;
        }
    }


    // 해당 년도의 대장군/삼살방을 계산하는 함수 필요
    public function getBadDirections($year_e) // 해당 년도의 지지 (ex: 巳)
    {
        $daejanggun = '';
        $samsalbang = '';

        if (in_array($year_e, ['亥', '子', '丑'])) {
            $daejanggun = '서';
            $samsalbang = '남';
        } // 해자축년
        if (in_array($year_e, ['寅', '卯', '辰'])) {
            $daejanggun = '북';
            $samsalbang = '서';
        } // 인묘진년
        if (in_array($year_e, ['巳', '午', '未'])) {
            $daejanggun = '동';
            $samsalbang = '북';
        } // 사오미년
        if (in_array($year_e, ['申', '酉', '戌'])) {
            $daejanggun = '남';
            $samsalbang = '동';
        } // 신유술년

        return ['daejanggun' => $daejanggun, 'samsalbang' => $samsalbang];
    }
}
