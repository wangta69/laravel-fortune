<?php

namespace Pondol\Fortune\Services;

class DangSaju
{
    const JIJI = ['寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥', '子', '축'];

    const DANGSAJU_STARS = ['천권', '천파', '천간', '천문', '천복', '천역', '천고', '천인', '천예', '천수', '천귀', '천액'];

    public function getDangSajuStars(string $year_jiji, string $hour_jiji, string $lunar_date): array
    {
        [$lunar_year, $lunar_month, $lunar_day] = explode('-', $lunar_date);

        // 1. 각 지지 문자에 해당하는 배열 인덱스(0~11)를 찾습니다.
        $year_index = array_search($year_jiji, self::JIJI);

        // [수정] array_search는 결과가 없으면 false를 반환합니다.
        $hour_index_val = array_search($hour_jiji, self::JIJI);

        // 2. 년(초년운)
        $year_star_index = $year_index;

        // 3. 월(중년운)
        $month_star_index = ($year_star_index + (int) $lunar_month - 1) % 12;

        // 4. 일(장년운)
        $day_star_index = ($month_star_index + (int) $lunar_day - 1) % 12;

        // 5. 시(말년운) 계산 - 시간이 있을 때만 수행
        $hour_star_name = ''; // 기본값 설정

        // $hour_index_val이 false가 아닐 때(즉, 시간이 존재할 때)만 계산
        if ($hour_index_val !== false) {
            $hour_star_index = ($day_star_index + $hour_index_val) % 12;
            $hour_star_name = self::DANGSAJU_STARS[$hour_star_index];
        }

        // 6. 결과 반환
        return [
            'year' => self::DANGSAJU_STARS[$year_star_index],
            'month' => self::DANGSAJU_STARS[$month_star_index],
            'day' => self::DANGSAJU_STARS[$day_star_index],
            'hour' => $hour_star_name, // 계산된 이름 또는 '알수없음'
        ];
    }
}
