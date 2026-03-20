<?php

namespace Pondol\Fortune\Services;

use Carbon\Carbon;
use Pondol\Fortune\Facades\Saju;

/**
 * 12실살을 제외한 기타 길신 및 흉신 구하기
 * 일지기준으로 하면 집안일
 * 년지기준으로 하면 바깥일
 */
class DaeWoon
{
    // 외부에서 접근하는 속성들을 public으로 명확하게 선언
    public array $age = [];

    public array $year = [];

    public array $daeun_h = [];

    public array $daeun_e = [];

    public array $sipsin_e = [];

    public array $woonsung_e = [];

    // 클래스 내부에서만 사용하는 속성

    private $gender;

    private $birth_time;

    private $lunar_ymd;

    private $month_h;

    private $year_h;

    private $month_e;

    private $direction;

    public bool $ageIsApproximate = false; // [추가] 대운수 나이가 근사치인지 여부를 나타내는 플래그

    /**
     * @param  string  $gender  : W, M (from profile)
     * @param  string  $birth_ym  : hhmm (from profile)
     * @param  string  $lunar_ymd  음력 생년월일 (from saju)
     */
    // function daeun($gender, $birth_time, $lunar_ymd, $month_h, $year_h, $month_e){ //
    public function withSaju($saju) //
    {
        $this->month_h = $saju->get_h('month');
        $this->year_h = $saju->get_h('year');
        $this->month_e = $saju->get_e('month');
        $this->gender = $saju->gender;
        $this->direction = $this->get_direction();

        // $gabja_array = array('甲子','乙丑','丙寅','丁卯','戊辰','己巳','庚午','辛未','壬申','癸酉','甲戌','乙亥','丙子','丁丑','戊寅','己卯','庚辰','辛巳','壬午','癸未','甲申','乙酉','丙戌','丁亥','戊子','己丑','庚寅','辛卯','壬辰','癸巳','甲午','乙未','丙申','丁酉','戊戌','己亥','庚子','辛丑','壬寅','癸卯','甲辰','乙巳','丙午','丁未','戊申','己酉','庚戌','辛亥','壬子','癸丑','甲寅','乙卯','丙辰','丁巳','戊午','己未','庚申','辛酉','壬戌','癸亥');

        // daeun_u : 천간을 기준으로 하는 대운
        // daeun_l : 지지를 기준으로 하는 대운
        // $he = $this->month_h.$this->month_e;

        $h_list = ['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];
        $e_list = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];

        $h_idx = h_to_serial($this->month_h); // 乙 이면 1
        $e_idx = e_to_serial($this->month_e); // 酉 면 9

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

        // 대운 나이 구하기
        $this->daeunAge($saju); // $this->daeunAge($lunar_ymd, $birth_time, $direction)

        $this->sipsin_e = $this->sipsin_e($saju);
        $this->woonsung_e = $this->woonsung_e($saju);

        return $this;
    }

    // 대운의 지지 10성 구하기
    private function sipsin_e($saju)
    {
        $sipsin_e = [];
        foreach ($this->daeun_e as $k => $v) {
            // 지지의 10성
            $sipsin_e[$k] = Sipsin::cal($saju->get_h('day'), $v, 'e');
        }

        return $sipsin_e;
    }

    // 대운의 지지 12운성 구하기
    private function woonsung_e($saju)
    {
        $woonsung_e = [];
        foreach ($this->daeun_e as $k => $v) {
            // 지지의 12운성
            $woonsung_e[$k] = Woonsung12::cal($saju->get_h('day'), $v);
        }

        return $woonsung_e;
    }

    /**
     * 대운은 남/녀 및 천간의 년에 따라 방향이 달라진다.
     */
    private function get_direction()
    {
        // 천간이 갑병무경임(양간)인지 확인
        $isYangGan = in_array($this->year_h, ['甲', '丙', '戊', '庚', '壬']);

        // 성별 값을 대문자로 변환하여 정규화 ('w', 'f', 'W', 'F' 모두 대응)
        $gender = strtoupper($this->gender);

        // 여성 판별: 'W' (Woman) 또는 'F' (Female)
        $isFemale = ($gender === 'W' || $gender === 'F');
        // 남성 판별: 'M' (Male)
        $isMale = ($gender === 'M');

        /**
         * 명리학 대운 방향 원칙:
         * 1. 양남음녀(陽男陰女)는 순행 (forward)
         *    - 남성(M)이면서 양간(Yang)일 때
         *    - 여성(W/F)이면서 음간(!Yang)일 때
         * 2. 그 외(음남양녀)는 역행 (reverse)
         */
        if (($isMale && $isYangGan) || ($isFemale && ! $isYangGan)) {
            return 'forward';
        }

        return 'reverse';
    }

    /**
     * 대운은 10년마다 한번씩 온다.
     * 절입(center/nenter)을 기준으로, 시간(minute)까지 계산하여 정확도 향상
     */
    private function daeunAge($saju)
    {
        try {
            $seasonal_division = $saju->seasonal_division(trim($saju->solar));

            if (! isset($seasonal_division->seasons, $seasonal_division->seasons->center, $seasonal_division->seasons->nenter)) {
                throw new \Exception('Seasonal division data is invalid.');
            }

            // 태어난 시각 Carbon 객체 생성
            // [수정] 시를 아는지 모르는지에 따라 birthTime을 다르게 설정
            $birthTime = '1200'; // 기본값은 정오(12:00)
            if ($saju->hourKnown) {
                $birthTime = $saju->hi;
            } else {
                // 시를 모를 경우, 결과가 근사치임을 표시
                $this->ageIsApproximate = true;
            }

            $birthDateTime = Carbon::createFromFormat('Y-m-d Hi', trim($saju->solar).' '.$birthTime);

            if ($this->direction == 'forward') {
                // 순행: 다음 절입일까지의 시간 차이
                $season = $seasonal_division->seasons->nenter;
                $seasonDateTime = Carbon::create(
                    $season->year,
                    $season->month,
                    $season->day,
                    $season->hour,
                    $season->min
                );
                $diffInMinutes = $birthDateTime->diffInMinutes($seasonDateTime, false);
            } else { // reverse
                // 역행: 이전 절입일까지의 시간 차이
                $season = $seasonal_division->seasons->center;
                $seasonDateTime = Carbon::create(
                    $season->year,
                    $season->month,
                    $season->day,
                    $season->hour,
                    $season->min
                );
                $diffInMinutes = $seasonDateTime->diffInMinutes($birthDateTime, false);
            }

            // 3일을 1로 하여 대운수를 계산 (1일=1440분)
            $diffInDays = $diffInMinutes / 1440;
            $mok = (int) round($diffInDays / 3);

        } catch (\Exception $e) {
            \Log::error('DaeWoon Age calculation failed.', ['error' => $e->getMessage(), 'saju' => $saju]);
            $mok = 3; // 오류 발생 시 안전한 기본값
        }

        $daeunBase = $mok < 1 ? 1 : $mok;
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
