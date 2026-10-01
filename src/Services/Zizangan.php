<?php

namespace Pondol\Fortune\Services;

/**
 * 지장간을 찾는다
 * 지장간은 지지에 포함된 천간을 찾는 것이다.
 */
class Zizangan
{
    public $year = [];

    public $month = [];

    public $day = [];

    public $hour = [];

    private $zizangan = [
        '子' => '壬癸',   // 임계 (초기, 본기)
        '丑' => '癸辛己', // 계신기 (초기, 중기, 본기)
        '寅' => '戊丙甲', // 무병갑
        '卯' => '甲乙',   // 갑을
        '辰' => '乙癸戊', // 을계무
        '巳' => '戊庚丙', // 무경병
        '午' => '丙己丁', // 병기정
        '未' => '丁乙己', // 정을기
        '申' => '戊壬庚', // 무임경 (주석 오타 수정)
        '酉' => '庚辛',   // 경신
        '戌' => '辛丁戊', // 신정무
        '亥' => '戊甲壬', // 무갑임
    ];

    public function withSaju($saju)
    {
        $dayH = $saju->get_h('day');

        // [안전장치] 시간(Hour) 정보 유무 체크 및 null 방어
        if ($saju->hourKnown && ! empty($saju->get_e('hour'))) {
            $hourJi = $saju->get_e('hour');
            $this->hour = $this->cal($this->zizangan[$hourJi] ?? '', $dayH);
        } else {
            $this->hour = [];
        }

        $this->day = $this->cal($this->zizangan[$saju->get_e('day')] ?? '', $dayH);
        $this->month = $this->cal($this->zizangan[$saju->get_e('month')] ?? '', $dayH);
        $this->year = $this->cal($this->zizangan[$saju->get_e('year')] ?? '', $dayH);

        return $this;
    }

    /**
     * 지장간 문자열을 파싱하여 십신과 역할(초기/중기/본기)을 담은 객체 배열로 반환
     */
    private function cal($str, $day_h)
    {
        // [방어 로직] 문자열이나 일간이 비어있으면 빈 배열 반환 (PHP 8.1+ null 경고 원천 차단)
        if (empty($str) || empty($day_h)) {
            return [];
        }

        $ret = [];
        $len = mb_strlen($str);

        // 지장간 위치별 명칭 정의 (2글자인 지지: 초기/본기, 3글자인 지지: 초기/중기/본기)
        $roleMap = ($len === 2)
            ? ['초기', '본기']
            : ['초기', '중기', '본기'];

        for ($i = 0; $i < $len; $i++) {
            $he = mb_substr($str, $i, 1);
            $sipsin = Sipsin::cal($day_h, $he, 'h');

            $ret[] = (object) [
                'h' => $he,
                'sipsin' => $sipsin,
                'role' => $roleMap[$i] ?? '본기', // [추가] 초기, 중기, 본기 구분값
                'is_main' => ($i === $len - 1),     // [추가] 마지막 글자가 가장 강력한 본기(정기)임을 표시
            ];
        }

        return $ret;
    }
}
