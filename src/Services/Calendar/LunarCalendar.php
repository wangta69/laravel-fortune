<?php

namespace Pondol\Fortune\Services\Calendar;

use Pondol\Fortune\Facades\Lunar;
use Pondol\Fortune\Traits\Calendar;
use Pondol\Fortune\Traits\SelectDay;

class LunarCalendar
{
    use Calendar, SelectDay;

    // PHP 8.2+ 동적 프로퍼티 에러 방지용 선언
    public $info;

    /**
     * 특정 연월의 기초 데이터를 생성하고 공통 길흉 점수 및 일주 유형을 산출합니다.
     */
    public function cal($yyyymm)
    {
        // 1. 해당 월의 절기 및 음양력 기초 정보 생성 (패키지 엔진 호출)
        $this->info = Lunar::ymd($yyyymm.'01')->tolunar()->sajugabja()
            ->seasonal_division($yyyymm.'20')
            ->create();

        // 2. 만세력 헤더용 데이터 보정 (solarInfo, lunarInfo)
        [$year, $month] = Lunar::to_gabja($yyyymm);
        $this->info->solarInfo = (object) ['year' => $year, 'month' => $month];

        $lunar_yyyymm = date('Ym', strtotime($this->info->lunar));
        [$year, $month] = Lunar::to_gabja($lunar_yyyymm);
        $this->info->lunarInfo = (object) ['year' => $year, 'month' => $month];

        // 3. 기초 달력 그리드 생성 (Traits/Calendar.php 의 _create 호출)
        // 이 과정에서 각 dayObject는 이미 gabja, lDay 등의 기초 데이터를 가집니다.
        $calendar = $this->_create($yyyymm);
        $seasonArr = $this->season_24_to_array($this->info->seasons);

        // 4. 모든 날짜를 순회하며 "공통 길흉" 및 "일주 유형" 데이터 주입
        foreach ($calendar->days as $dayObject) {
            if ($dayObject && isset($dayObject->day) && $dayObject->day) {
                // [4-1] 절기 정보 매핑
                $solar_key = str_replace('-', '', $dayObject->solar);
                if (isset($seasonArr[$solar_key])) {
                    $dayObject->season24 = $seasonArr[$solar_key];
                }

                // [4-2] 일주 별칭 및 유형 매핑 (Config 활용)
                $this->setIljuArchetype($dayObject);

                // [4-3] 공통 기초 길흉(황도, 복단 등) 계산 및 주입
                $this->setPublicFortune($dayObject);
            }
        }

        // 5. 모든 연산 완료 후 주(Week) 단위로 분할하여 반환
        return $calendar->splitPerWeek();
    }

    /**
     * 일주(日柱) 간지를 바탕으로 닉네임과 아키타입을 설정합니다.
     */
    private function setIljuArchetype($day)
    {
        if (! isset($day->gabja->day->ch)) {
            return;
        }

        $ilju = $day->gabja->day->ch; // 예: "甲子"
        $archetypes = config('pondol-fortune.ilju_archetypes');

        if (isset($archetypes[$ilju])) {
            $day->nickname = $archetypes[$ilju]['nickname'];
            $day->archetype = $archetypes[$ilju]['archetype'];
        }
    }

    /**
     * [Dispatcher] Config 설정을 순회하며 그룹/단일 로직을 동적으로 매핑합니다.
     */
    private function setPublicFortune($day)
    {
        if (! is_object($day) || ! isset($day->gabja)) {
            return;
        }

        $baseConfigs = config('pondol-fortune.select_day.base');
        $foundTitles = [];
        $totalScore = 0;

        // [보정] 판정에 필요한 데이터 컨텍스트 확장
        $context = [
            'solar' => str_replace('-', '', $day->solar),
            'lunar_d' => $day->lDay,
            'day_he' => $day->gabja->day->ch,
            'day_h' => mb_substr($day->gabja->day->ch, 0, 1),
            'day_e' => mb_substr($day->gabja->day->ch, 1, 1),
            'month_e' => mb_substr($day->gabja->month->ch, 1, 1),
            'year_e' => mb_substr($day->gabja->year->ch, 1, 1),
            'year_h' => mb_substr($day->gabja->year->ch, 0, 1),
        ];

        foreach ($baseConfigs as $key => $conf) {
            if (isset($conf['is_group']) && $conf['is_group']) {
                $method = "_group_{$key}";
                if (method_exists($this, $method)) {
                    // 사주가 없으므로 첫 인자에 null 전달
                    $subKey = $this->$method(null, $context);
                    if ($subKey && isset($conf['items'][$subKey])) {
                        $foundTitles[$subKey] = $conf['items'][$subKey];
                        $totalScore += $conf['items'][$subKey]['score'];
                    }
                }
            } else {
                $method = "_check_{$key}";
                if (method_exists($this, $method)) {
                    // 사주가 없으므로 첫 인자에 null 전달
                    if ($this->$method(null, $context)) {
                        $foundTitles[$key] = $conf;
                        $totalScore += $conf['score'];
                    }
                }
            }
        }

        uasort($foundTitles, fn ($a, $b) => ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0));
        $day->titles = $foundTitles;
        $day->total = $totalScore;
    }

    private function season_24_to_array($season_24)
    {
        if (! $season_24) {
            return [];
        }

        return [
            $season_24->center->year.str_pad($season_24->center->month, 2, '0', STR_PAD_LEFT).str_pad($season_24->center->day, 2, '0', STR_PAD_LEFT) => ['ko' => $season_24->center->name->ko, 'ch' => $season_24->center->name->ch],
            $season_24->ccenter->year.str_pad($season_24->ccenter->month, 2, '0', STR_PAD_LEFT).str_pad($season_24->ccenter->day, 2, '0', STR_PAD_LEFT) => ['ko' => $season_24->ccenter->name->ko, 'ch' => $season_24->ccenter->name->ch],
            $season_24->nenter->year.str_pad($season_24->nenter->month, 2, '0', STR_PAD_LEFT).str_pad($season_24->nenter->day, 2, '0', STR_PAD_LEFT) => ['ko' => $season_24->nenter->name->ko, 'ch' => $season_24->nenter->name->ch],
        ];
    }
}
