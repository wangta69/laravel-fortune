<?php

namespace Pondol\Fortune\Services\Calendar;

use Pondol\Fortune\Facades\Lunar;
use Pondol\Fortune\Traits\Calendar;
use Pondol\Fortune\Traits\SelectDay; // 길흉 로직 사용을 위해 추가

class LunarCalendar
{
    use Calendar, SelectDay; // SelectDay 트레이트 추가

    public function cal($yyyymm)
    {
        $this->info = Lunar::ymd($yyyymm.'01')->tolunar()->sajugabja()
            ->seasonal_division($yyyymm.'20')
            ->create();

        // 양력에 대한 갑자 년월 구하기
        [$year, $month] = Lunar::to_gabja($yyyymm);
        $this->info->solarInfo = (object) ['year' => $year, 'month' => $month];

        // 음력에 대한 갑자 년월 구하기
        $lunar_yyyymm = date('Ym', strtotime($this->info->lunar));
        [$year, $month] = Lunar::to_gabja($lunar_yyyymm);
        $this->info->lunarInfo = (object) ['year' => $year, 'month' => $month];

        $calendar = $this->_create($yyyymm);

        $seasonArr = $this->season_24_to_array($this->info->seasons);

        foreach ($calendar->days as $dayObject) {
            if ($dayObject && $dayObject->day) {
                // 3. 기존 절기 정보 매핑
                $solar_yyyymmdd = str_replace('-', '', $dayObject->solar);
                if (isset($seasonArr[$solar_yyyymmdd])) {
                    $dayObject->season24 = $seasonArr[$solar_yyyymmdd];
                }

                // 4. [추가] 공통 길흉 정보(손없는날, 황도, 복단, 월기) 계산 로직
                $this->setPublicFortune($dayObject);
            }
        }

        return $calendar->splitPerWeek();
    }

    /**
     * 사주 정보 없이 날짜 정보만으로 공통 길흉 추출
     */
    private function setPublicFortune($day)
    {
        $titles = [];
        $scores = []; // 점수는 참고용 (필요시 사용)

        // 판단을 위한 기초 데이터 (한자 기준)
        $year_h = mb_substr($day->lunarInfo->gabja->year->ch, 0, 1);  // 연간
        $month_e = mb_substr($day->lunarInfo->gabja->month->ch, 1, 1); // 월지
        $day_ganji = $day->lunarInfo->gabja->day->ch;                 // 일주(한자)
        $day_h = mb_substr($day_ganji, 0, 1);                         // 일간
        $day_e = mb_substr($day_ganji, 1, 1);                         // 일지
        $lunar_day = (int) substr($day->lunar, -2);                    // 음력 날짜

        // --- [긍정 시그널: Good Signs] ---

        // 1. 손 없는 날
        if ($lunar_day % 10 === 9 || $lunar_day % 10 === 0) {
            $titles['son'] = ['ko' => '손 없는 날', 'desc' => '악귀가 없는 날로 이사, 개업에 길함', 'type' => 'gilsin'];
        }

        // 2. 황도일 (금궤황도 등)
        $this->_whangdo($month_e, $day_e, $titles, $scores);

        // 3. 천사일 (하늘이 돕는 날)
        $this->_cheonsa($month_e, $day_ganji, $titles, $scores);

        // 4. 천덕/월덕 귀인 (월별 길신)
        $this->_chenduk($month_e, $day_e, $day_h, $titles, $scores);
        $this->_wolduk($month_e, $day_h, $titles, $scores);

        // --- [주의 시그널: Warning Signs] ---

        // 5. 복단일 (엎어지는 날)
        $bokdanil_list = ['甲寅', '乙卯', '庚寅', '辛卯', '戊戌', '己亥', '丙午', '丁未', '壬午', '癸未', '丙辰', '丁巳', '壬辰', '癸巳'];
        if (in_array($day_ganji, $bokdanil_list)) {
            $titles['bokdan'] = ['ko' => '복단일', 'desc' => '기운이 끊기는 날로 중요한 계약이나 시작 주의', 'type' => 'hyungsal'];
        }

        // 6. 월기일 (매월 5, 14, 23일)
        $this->_wolgi($lunar_day, $titles, $scores);

        // 7. 십악대패일
        $this->_sipak($year_h, $day_ganji, $month_e, $titles, $scores);

        // 8. 기타 흉살 (지파, 하괴 등 - 사주 무관)
        $this->_jipa($month_e, $day_e, $titles, $scores);
        $this->_hague($month_e, $day_e, $titles, $scores);

        // 결과 저장
        $day->titles = $titles;
        // 사주가 없으므로 공통 점수 합산 (선택 사항)
        $day->total = array_sum($scores);

    }

    private function season_24_to_array($season_24)
    {
        return [
            $season_24->center->year.str_pad($season_24->center->month, 2, '0', STR_PAD_LEFT).str_pad($season_24->center->day, 2, '0', STR_PAD_LEFT) => ['ko' => $season_24->center->name->ko, 'ch' => $season_24->center->name->ch],
            $season_24->ccenter->year.str_pad($season_24->ccenter->month, 2, '0', STR_PAD_LEFT).str_pad($season_24->ccenter->day, 2, '0', STR_PAD_LEFT) => ['ko' => $season_24->ccenter->name->ko, 'ch' => $season_24->ccenter->name->ch],
            $season_24->nenter->year.str_pad($season_24->nenter->month, 2, '0', STR_PAD_LEFT).str_pad($season_24->nenter->day, 2, '0', STR_PAD_LEFT) => ['ko' => $season_24->nenter->name->ko, 'ch' => $season_24->nenter->name->ch],
        ];
    }
}
