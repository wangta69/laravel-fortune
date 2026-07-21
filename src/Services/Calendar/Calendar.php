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
        $lunar = new \Pondol\Fortune\Services\LunarSolar\Lunar;
        $year_e = mb_substr($lunar->gabja($year.'0101')->year->ch, 1, 1);

        $samjae_groups = [
            '申子辰' => ['寅', '卯', '辰'],
            '亥卯未' => ['巳', '午', '未'],
            '寅午戌' => ['申', '酉', '戌'],
            '巳酉丑' => ['亥', '子', '丑'],
        ];

        $target_samjae = [];
        foreach ($samjae_groups as $zodiacs_hanja => $years) {
            if (in_array($year_e, $years)) {
                $idx = array_search($year_e, $years);
                $status = ($idx === 0) ? '들삼재' : (($idx === 1) ? '눌삼재' : '날삼재');

                // [추가] 컨트롤러 112라인에서 기대하는 'type' 키 생성 ('들', '눌', '날')
                $type_labels = ['들', '눌', '날'];
                $type = $type_labels[$idx];

                $samjae_ko = [];
                for ($i = 0; $i < mb_strlen($zodiacs_hanja); $i++) {
                    $hj = mb_substr($zodiacs_hanja, $i, 1);
                    $ji_idx = array_search($hj, JI['ch']);
                    if ($ji_idx !== false) {
                        $samjae_ko[] = ZODIAC['ko'][$ji_idx];
                    }
                }

                $target_samjae = [
                    'status' => $status,
                    'type' => $type, // 컨트롤러 불일치 해결을 위한 키 추가
                    'zodiacs' => $zodiacs_hanja,
                    'current_year_ji' => $year_e,
                    'samjaes' => [
                        'ko' => $samjae_ko,
                    ],
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

    /**
     * 반려동물 입양 택일 서비스 (추가)
     */
    public function petAdoptionCalendar($saju, $yyyymm, $options = [])
    {
        return (new PetAdoptionCalendar)->cal($saju, $yyyymm, $options);
    }
}
