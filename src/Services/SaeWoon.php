<?php

namespace Pondol\Fortune\Services;

/**
 * 1년 주기 세운(歲運 / 연운) 간지 및 십성/12운성 계산 서비스
 * (세운은 남녀 구분 없이 모든 사람에게 매년 동일하게 순행합니다)
 */
class SaeWoon
{
    // 1. 외부(컨트롤러 등)에서 접근하는 프로퍼티 명시적 선언 (PHP 8.2+ 동적 프로퍼티 경고 방지)
    public array $year = [];

    public array $age = [];

    public array $saewoon = [];

    public array $saewoon_h = [];

    public array $saewoon_e = [];

    public array $sipsin_e = [];

    public array $woonsung_e = [];

    /**
     * Saju 객체를 받아 평생(110년간)의 세운 간지 및 운성 계산
     */
    public function withSaju($saju)
    {
        // 재사용 시 데이터 누적 방지 초기화
        $this->year = [];
        $this->age = [];
        $this->saewoon = [];
        $this->saewoon_h = [];
        $this->saewoon_e = [];

        // 양력 연도 기준으로 일치시키고, 고령자(80세 이상)를 위해 110년치 생성
        $start = (int) substr($saju->solar, 0, 4);
        $end = $start + 110;

        $i = 0;
        for ($year = $start; $year < $end; $year++) {
            $result = calgabja($year);

            $this->saewoon_h[$i] = $result->h;
            $this->saewoon_e[$i] = $result->e;
            $this->age[$i] = $i + 1;
            $this->year[$i] = $year;
            $i++;
        }

        $this->sipsin_e = $this->sipsin_e($saju);
        $this->woonsung_e = $this->woonsung_e($saju);

        return $this;
    }

    // 세운의 지지 10성 구하기
    private function sipsin_e($saju)
    {
        $sipsin_e = [];
        foreach ($this->saewoon_e as $k => $v) {
            // [수정] SipSin -> Sipsin 대소문자 일치 (리눅스 Class Not Found 방지)
            $sipsin_e[$k] = Sipsin::cal($saju->get_h('day'), $v, 'e');
        }

        return $sipsin_e;
    }

    // 세운의 지지 12운성 구하기
    private function woonsung_e($saju)
    {
        $woonsung_e = [];
        foreach ($this->saewoon_e as $k => $v) {
            $woonsung_e[$k] = Woonsung12::cal($saju->get_h('day'), $v);
        }

        return $woonsung_e;
    }
}
