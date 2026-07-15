<?php

namespace Pondol\Fortune\Services\Calendar;

use Illuminate\Support\Facades\Cache;
use Pondol\Fortune\Services\LunarSolar\Lunar;

class Calendar
{
    /**
     * [핵심] 공통 기초 만세력 데이터를 가져옵니다. (캐시 레이어)
     * 모든 택일 서비스(이사, 개업 등)는 이 메서드가 생성한 캐시를 '재료'로 사용합니다.
     */
    public function lunarCalendar($yyyymm)
    {
        $cacheKey = "lunar_calendar_{$yyyymm}";

        // 30일(86400 * 30) 동안 캐싱하여 서버 CPU 부하를 원천 차단합니다.
        return Cache::remember($cacheKey, 86400 * 30, function () use ($yyyymm) {
            // LunarCalendar 클래스의 cal 메서드를 호출하여 기초 데이터와 공통 길흉(황도, 복단 등)을 생성합니다.
            return (new LunarCalendar)->cal($yyyymm);
        });
    }

    /**
     * 특정 연도의 24절기 정확한 시각 데이터를 가져옵니다. (도감용)
     */
    public function season24Calendar($year)
    {
        $cacheKey = "season24_calendar_{$year}";

        return Cache::remember($cacheKey, 86400 * 365, function () use ($year) {
            $lunar = new Lunar;

            // 해당 연도의 1월 1일 기준으로 절기 분기점을 계산합니다.
            return $lunar->seasonal_division($year.'0101')->seasons;
        });
    }

    /**
     * 특정 연도의 삼재(三災) 정보를 판정합니다.
     */
    public function samjae($year)
    {
        $lunar = new Lunar;
        // 해당 연도의 지지(년지)를 구함
        $year_e = mb_substr($lunar->gabja($year.'0101')->year->ch, 1, 1);

        /**
         * 삼재 판정 정석 로직:
         * 신자진(원숭이, 쥐, 용띠) -> 인묘진년 삼재
         * 해묘미(돼지, 토끼, 양띠) -> 사오미년 삼재
         * 인오술(범, 말, 개띠)     -> 신유술년 삼재
         * 사유축(뱀, 닭, 소띠)     -> 해자축년 삼재
         */
        $samjae_groups = [
            '申子辰' => ['寅', '卯', '辰'],
            '亥卯未' => ['巳', '午', '未'],
            '寅午戌' => ['申', '酉', '戌'],
            '巳酉丑' => ['亥', '子', '丑'],
        ];

        $target_samjae = [];
        foreach ($samjae_groups as $zodiacs => $years) {
            if (in_array($year_e, $years)) {
                $idx = array_search($year_e, $years);
                $status = ($idx === 0) ? '들삼재' : (($idx === 1) ? '눌삼재' : '날삼재');

                $target_samjae = [
                    'status' => $status,
                    'zodiacs' => $zodiacs,
                    'current_year_ji' => $year_e,
                ];
                break;
            }
        }

        return $target_samjae;
    }

    /*
    |--------------------------------------------------------------------------
    | 개별 택일 서비스 라우팅
    |--------------------------------------------------------------------------
    */

    /**
     * 이사 택일 서비스
     */
    public function moveCalendar($saju, $yyyymm, $options = [])
    {
        return (new MoveCalendar)->cal($saju, $yyyymm, $options);
    }

    /**
     * 개업 택일 서비스
     */
    public function businessDayCalendar($saju, $yyyymm, $options = [])
    {
        return (new BusinessDayCalendar)->cal($saju, $yyyymm, $options);
    }

    /**
     * 결혼 택일 서비스
     */
    public function marriageCalendar($saju_male, $saju_female, $yyyymm, $options = [])
    {
        return (new MarriageCalendar)->cal($saju_male, $saju_female, $yyyymm, $options);
    }

    /**
     * 신차 출고(인수) 택일 서비스
     */
    public function carDeliveryCalendar($saju, $yyyymm, $options = [])
    {
        return (new CarDeliveryCalendar)->cal($saju, $yyyymm, $options);
    }
}
