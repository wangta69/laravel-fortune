<?php

namespace Pondol\Fortune\Services;

use Carbon\Carbon;
use Pondol\Fortune\Enums\Gender;

/**
 * 10년 주기 대운(大運) 간지 및 대운수 계산 서비스
 */
class DaeWoon
{
    // 외부에서 접근하는 속성들
    public array $age = [];

    public array $year = [];

    public array $daeun_h = [];

    public array $daeun_e = [];

    public array $sipsin_e = [];

    public array $woonsung_e = [];

    // 클래스 내부 속성
    private $gender;

    private $month_h;

    private $year_h;

    private $month_e;

    private $direction;

    public bool $ageIsApproximate = false; // 대운수 나이가 근사치인지 여부 플래그

    public function withSaju($saju)
    {
        $this->month_h = $saju->get_h('month');
        $this->year_h = $saju->get_h('year');
        $this->month_e = $saju->get_e('month');
        $this->gender = $saju->gender;
        $this->direction = $this->get_direction();

        // 1. 재사용 시 배열 데이터 누적 방지 초기화
        $this->daeun_h = [];
        $this->daeun_e = [];

        $h_list = ['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];
        $e_list = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];

        // 2. 외부 헬퍼 함수 의존 대신 array_search로 0-based 인덱스 절대 보장 (丁午 오류 원천 차단)
        $h_idx = array_search($this->month_h, $h_list);
        $e_idx = array_search($this->month_e, $e_list);

        switch ($this->direction) {
            case 'forward':
                for ($i = 1; $i <= 10; $i++) {
                    $this->daeun_h[] = $h_list[($h_idx + $i) % 10]; // 다음 글자부터 (+1)
                    $this->daeun_e[] = $e_list[($e_idx + $i) % 12];
                }
                break;
            case 'reverse':
                for ($i = 1; $i <= 10; $i++) {
                    $this->daeun_h[] = $h_list[($h_idx - $i + 10) % 10]; // 이전 글자부터 (-1)
                    $this->daeun_e[] = $e_list[($e_idx - $i + 12) % 12];
                }
                break;
        }

        // 대운 나이 및 십성/12운성 계산
        $this->daeunAge($saju);
        $this->sipsin_e = $this->sipsin_e($saju);
        $this->woonsung_e = $this->woonsung_e($saju);

        return $this;
    }

    // 대운의 지지 10성 구하기
    private function sipsin_e($saju)
    {
        $sipsin_e = [];
        foreach ($this->daeun_e as $k => $v) {
            $sipsin_e[$k] = Sipsin::cal($saju->get_h('day'), $v, 'e');
        }

        return $sipsin_e;
    }

    // 대운의 지지 12운성 구하기
    private function woonsung_e($saju)
    {
        $woonsung_e = [];
        foreach ($this->daeun_e as $k => $v) {
            $woonsung_e[$k] = Woonsung12::cal($saju->get_h('day'), $v);
        }

        return $woonsung_e;
    }

    /**
     * 대운 방향 판별 (Gender Enum 활용)
     * 양남음녀(陽男陰女) -> 순행 (forward)
     * 음남양녀(陰男陽女) -> 역행 (reverse)
     */
    private function get_direction()
    {
        $isYangGan = in_array($this->year_h, ['甲', '丙', '戊', '庚', '壬']);
        $gender = Gender::fromMixed($this->gender);

        if (($gender->isMale() && $isYangGan) || ($gender->isFemale() && ! $isYangGan)) {
            return 'forward';
        }

        return 'reverse';
    }

    /**
     * 대운수(大運數) 계산: 절입 시각 기준
     */
    private function daeunAge($saju)
    {
        try {
            $seasonal_division = $saju->seasonal_division(trim($saju->solar));

            if (! isset($seasonal_division->seasons, $seasonal_division->seasons->center, $seasonal_division->seasons->nenter)) {
                throw new \Exception('Seasonal division data is invalid.');
            }

            // 시주 정보 유무에 따른 기준 시간 설정
            $birthTime = '1200'; // 기본 정오
            if ($saju->hourKnown) {
                $birthTime = $saju->hi;
            } else {
                $this->ageIsApproximate = true;
            }

            $birthDateTime = Carbon::createFromFormat('Y-m-d Hi', trim($saju->solar).' '.$birthTime);

            if ($this->direction === 'forward') {
                // 순행: 미래 절입일(nenter)까지의 분 차이
                $season = $seasonal_division->seasons->nenter;
                $seasonDateTime = Carbon::create(
                    $season->year,
                    $season->month,
                    $season->day,
                    $season->hour,
                    $season->min
                );
                $diffInMinutes = abs($seasonDateTime->diffInMinutes($birthDateTime, false));
            } else {
                // 역행: 과거 절입일(center)까지의 분 차이
                $season = $seasonal_division->seasons->center;
                $seasonDateTime = Carbon::create(
                    $season->year,
                    $season->month,
                    $season->day,
                    $season->hour,
                    $season->min
                );
                $diffInMinutes = abs($birthDateTime->diffInMinutes($seasonDateTime, false));
            }

            // 3일 = 1년 (1일 = 1440분)
            $diffInDays = $diffInMinutes / 1440;
            $mok = (int) round($diffInDays / 3);

        } catch (\Exception $e) {
            \Log::error('DaeWoon Age calculation failed.', ['error' => $e->getMessage(), 'saju' => $saju]);
            $mok = 3; // 에러 시 안전 기본값
        }

        // 대운수는 1~10 범위로 안전 보정
        $daeunBase = max(1, min(10, $mok));
        $age[0] = $daeunBase;
        $year[0] = (int) substr($saju->solar, 0, 4) + $daeunBase - 1;

        for ($i = 1; $i < 10; $i++) {
            $j = $i * 10;
            $age[$i] = $age[0] + $j;
            $year[$i] = $year[0] + $j;
        }

        $this->age = $age;
        $this->year = $year;
    }
}
