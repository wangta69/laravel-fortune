<?php
namespace Pondol\Fortune\Services;

class DangSaju
{
  // 기준이 되는 지지와 12성(운성)을 순서에 맞게 상수로 정의합니다.
  // 계산의 편의를 위해 '인'부터 시작하는 배열을 사용합니다. (인=1, 묘=2...)
  // const JIJI = ['인', '묘', '진', '사', '오', '미', '신', '유', '술', '해', '자', '축'];
  const JIJI = ['寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥', '子', '丑'];
  const DANGSAJU_STARS = ['천권', '천파', '천간', '천문', '천복', '천역', '천고', '천인', '천예', '천수', '천귀', '천액'];
  
  public function getDangSajuStars(string $year_jiji, string $hour_jiji, string $lunar_date): array
    {
      list($lunar_year, $lunar_month, $lunar_day) = explode('-', $lunar_date);

      // 1. 각 지지 문자에 해당하는 배열 인덱스(0~11)를 찾습니다.
      $year_index = array_search($year_jiji, self::JIJI);
      $hour_index_val = array_search($hour_jiji, self::JIJI);

      // 2. 년(초년운)의 12성은 태어난 해의 지지가 그대로 결정합니다.
      $year_star_index = $year_index;

      // 3. 월(중년운)의 12성을 계산합니다.
      // (년 인덱스 + 음력 월 - 1)의 결과를 12로 나눈 나머지 값으로 인덱스를 구합니다.
      $month_star_index = ($year_star_index + (int)$lunar_month - 1) % 12;

      // 4. 일(장년운)의 12성을 계산합니다.
      $day_star_index = ($month_star_index + (int)$lunar_day - 1) % 12;
      
      // 5. 시(말년운)의 12성을 계산합니다.
      $hour_star_index = ($day_star_index + $hour_index_val) % 12;

      // 6. 계산된 각 인덱스를 사용하여 12성 배열에서 해당하는 이름을 반환합니다.
      return [
        'year'  => self::DANGSAJU_STARS[$year_star_index],
        'month' => self::DANGSAJU_STARS[$month_star_index],
        'day'   => self::DANGSAJU_STARS[$day_star_index],
        'hour'  => self::DANGSAJU_STARS[$hour_star_index],
      ];
    }
}