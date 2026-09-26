<?php

namespace Pondol\Fortune\Services;

class DangSajuResult
{
    protected $saju;

    protected $data = [];

    protected $y_idx;

    protected $m_val;

    protected $d_val;

    protected $d_idx;

    protected $h_idx;

    protected $year_star_idx;

    protected $month_star_idx;

    protected $day_star_idx;

    const JIJI = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];

    const STARS = ['천귀', '천액', '천권', '천파', '천간', '천문', '천복', '천역', '천고', '천인', '천예', '천수'];

    public function __construct($saju)
    {
        $this->saju = $saju;
        $this->initialize();
    }

    protected function initialize()
    {
        $this->y_idx = array_search($this->saju->get_e('year'), self::JIJI);
        $this->d_idx = array_search($this->saju->get_e('day'), self::JIJI);
        $this->h_idx = $this->saju->hourKnown ? array_search($this->saju->get_e('hour'), self::JIJI) : null;

        [$lunar_y, $lunar_m, $lunar_d] = explode('-', $this->saju->lunar);
        $this->m_val = (int) $lunar_m;
        $this->d_val = (int) $lunar_d;

        $this->year_star_idx = $this->y_idx;
        $this->month_star_idx = ($this->year_star_idx + $this->m_val - 1) % 12;
        $this->day_star_idx = ($this->month_star_idx + $this->d_val - 1) % 12;
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return $this->data[$name] = $this->calculate($name);
    }

    protected function calculate($name)
    {
        switch ($name) {
            case 'early':    return self::STARS[$this->year_star_idx];
            case 'middle':   return self::STARS[$this->month_star_idx];
            case 'prime':    return self::STARS[$this->day_star_idx];
            case 'later':    return ($this->h_idx !== null) ? self::STARS[($this->day_star_idx + $this->h_idx) % 12] : '시주불명';
            case 'wealth':   return self::STARS[($this->month_star_idx + $this->d_val - 1) % 12];
            case 'career':   return self::STARS[($this->year_star_idx + $this->d_val - 1) % 12];
            case 'housing':  return ($this->h_idx !== null) ? self::STARS[($this->month_star_idx + $this->h_idx) % 12] : '알수없음';
            case 'travel':   return self::STARS[($this->month_star_idx + $this->d_idx) % 12];
            case 'parents':  return self::STARS[($this->year_star_idx + $this->m_val - 1) % 12];
            case 'brother':  return self::STARS[($this->year_star_idx + $this->d_idx) % 12];
            case 'couple':   return self::STARS[($this->month_star_idx + $this->d_idx) % 12];
            case 'child':    return ($this->h_idx !== null) ? self::STARS[($this->day_star_idx + $this->h_idx) % 12] : '알수없음';
            case 'character': return self::STARS[$this->day_star_idx];
            case 'health':   return ($this->h_idx !== null) ? self::STARS[($this->day_star_idx + $this->h_idx) % 12] : '알수없음';
            case 'past_life': return self::STARS[($this->y_idx - 1 + 12) % 12];
            case 'lifespan':
                if ($this->h_idx === null) {
                    return null;
                }

                $code = $this->calcDang14($this->y_idx + 1, $this->h_idx + 1);

                // 2. 구한 코드를 인덱스로 변환하여 별 이름을 리턴합니다. (코드 1이면 STARS[0])
                return self::STARS[($code - 1) % 12];
            case 'misfortune':
                return $this->calculate('lifespan');

                /**
                 * 나이대 표시 (현대적 해석 기준)
                 */
            case 'early_age':  return '0세 ~ 20대 후반';
            case 'middle_age': return '30세 ~ 40대 후반';
            case 'prime_age':  return '50세 ~ 60대 중반';
            case 'later_age':  return '60대 후반 이후';

                // 조금 더 명확한 숫자를 원하신다면 아래 방식을 추천합니다.
                /*
                case 'early_age':  return '0세 ~ 29세';
                case 'middle_age': return '30세 ~ 49세';
                case 'prime_age':  return '50세 ~ 64세';
                case 'later_age':  return '65세 이후';
                */

                // 팔복궁 (사주신분복 01~08 코드 반환)
            case 'eight_luck':
            case 'eight_luck_code':
                return $this->calculateEightLuckCode();

            default: return null;
        }

    }

    /**
     * [팔복궁] 사주신분복 8가지 복록 코드('01' ~ '08') 산출
     * 태어난 계절(월지)과 시간(시지)의 조합
     */
    protected function calculateEightLuckCode(): string
    {
        // 1. 월지 (인=1 ~ 축=12 표준 번호)
        $me = $this->saju->month->e->num ?? 1;

        // 2. 시지 (시간을 모르면 낮 12시 오(午)시: 5번으로 보정하여 8번 쏠림 방지)
        $he = ($this->saju->hourKnown && isset($this->saju->hour->e->num))
            ? $this->saju->hour->e->num
            : 5;

        // 봄 (인, 묘, 진월)
        if (in_array($me, [1, 2, 3])) {
            return match (true) {
                $he == 11 => '01', // 자시
                in_array($he, [8, 2]) => '02', // 유, 축
                in_array($he, [9, 1]) => '03', // 술, 인
                $he == 7 => '04', // 신
                $he == 3 => '05', // 묘
                $he == 5 => '06', // 오
                in_array($he, [10, 12]) => '07', // 해, 미
                default => '08' // 사, 진
            };
        }
        // 여름 (사, 오, 미월)
        elseif (in_array($me, [4, 5, 6])) {
            return match (true) {
                $he == 5 => '01',
                in_array($he, [8, 2]) => '02',
                in_array($he, [7, 3]) => '03',
                $he == 1 => '04',
                $he == 9 => '05',
                $he == 11 => '06',
                in_array($he, [4, 6]) => '07',
                default => '08'
            };
        }
        // 가을 (신, 유, 술월)
        elseif (in_array($me, [7, 8, 9])) {
            return match (true) {
                $he == 2 => '01',
                in_array($he, [11, 5]) => '02',
                in_array($he, [12, 4]) => '03',
                $he == 10 => '04',
                $he == 6 => '05',
                $he == 8 => '06',
                in_array($he, [1, 3]) => '07',
                default => '08'
            };
        }
        // 겨울 (해, 자, 축월)
        else {
            return match (true) {
                $he == 8 => '01',
                in_array($he, [11, 5]) => '02',
                in_array($he, [10, 6]) => '03',
                $he == 4 => '04',
                $he == 12 => '05',
                $he == 2 => '06',
                in_array($he, [7, 9]) => '07',
                default => '08'
            };
        }
    }

    private function calcDang14($y, $h)
    {
        switch ($y) {
            case 1: case 5: case 9:  $v = $h + 7;
                break;
            case 2: case 6: case 10: $v = $h + 10;
                break;
            case 3: case 7: case 11: $v = $h + 1;
                break;
            default: $v = $h + 4;
                break;
        }
        $r = $v % 12;

        return ($r == 0) ? 12 : $r;
    }
}
