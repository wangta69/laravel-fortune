<?php

namespace Pondol\Fortune\Services;

/**
 * 사주 정보를 바탕으로 특정 시점(년, 월 등)의 운세 흐름을 분석하는 클래스입니다.
 */
class Unse
{
    private const DEFINITIONS = [
        '들삼재' => ['ch' => '入三災', 'type' => 'hyungsal', 'desc' => '삼재가 시작되는 첫 해'],
        '눌삼재' => ['ch' => '묵三災', 'type' => 'hyungsal', 'desc' => '삼재가 머무는 두 번째 해'],
        '날삼재' => ['ch' => '出三災', 'type' => 'hyungsal', 'desc' => '삼재가 끝나는 마지막 해'],
        '상문살' => ['ch' => '喪門殺', 'type' => 'hyungsal', 'desc' => '초상집 방문 등에 주의가 필요한 해'],
        '조객살' => ['ch' => '弔客殺', 'type' => 'hyungsal', 'desc' => '상문살과 유사하며, 문상 등에 신중해야 하는 해'],
        '관부살' => ['ch' => '官符殺', 'type' => 'hyungsal', 'desc' => '관재구설이나 법적인 문제에 휘말릴 수 있는 해'],
    ];

    private array $saju = [];
    public array $currentYear = [];

    public function withSaju($saju): self
    {
        $this->saju['e'] = ['y' => $saju->get_e('year')];

        // [핵심] withSaju가 호출될 때, 현재 년도를 기준으로 운세를 미리 계산하여 속성에 저장합니다.
        $this->currentYear = $this->calculateForYear((int)date('Y'));

        return $this;
    }

    /**
     * (외부 호출용) 특정 년도에 해당하는 운세 정보를 계산하여 반환하는 유틸리티 메서드.
     * @param int $targetYear
     * @return array
     */
    public function checkYear(int $targetYear): array
    {
        return $this->calculateForYear($targetYear);
    }

    /**
     * 특정 년도에 대한 모든 운세 항목을 계산하는 핵심 엔진입니다.
     */
    private function calculateForYear(int $year): array
    {
        $results = [];
        $targetYeonji = $this->getJijiForYear($year);
        $yeonji = $this->saju['e']['y'];

        // 삼재 확인
        $samjae = $this->isSamjae($yeonji, $targetYeonji);
        if ($samjae) {
            $results['samjae'] = array_merge(['ko' => $samjae], self::DEFINITIONS[$samjae]);
        }

        // 상문살 확인
        if ($this->isSangmun($yeonji, $targetYeonji)) {
            $results['sangmun'] = array_merge(['ko' => '상문살'], self::DEFINITIONS['상문살']);
        }

        // 조객살 확인
        if ($this->isJogaek($yeonji, $targetYeonji)) {
            $results['jogaek'] = array_merge(['ko' => '조객살'], self::DEFINITIONS['조객살']);
        }

        // 관부살 확인
        if ($this->isGuanbu($yeonji, $targetYeonji)) {
            $results['guanbu'] = array_merge(['ko' => '관부살'], self::DEFINITIONS['관부살']);
        }

        return $results;
    }

    private function getJijiForYear(int $year): string
    {
        $jiji = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];
        return $jiji[($year - 4) % 12];
    }

    private function isSamjae(string $yeonji, string $targetYeonji): ?string
    {
        $samjaeMap = [
            '들삼재' => ['申' => '寅', '子' => '寅', '辰' => '寅', '亥' => '巳', '卯' => '巳', '未' => '巳', '寅' => '申', '午' => '申', '戌' => '申', '巳' => '亥', '酉' => '亥', '丑' => '亥'],
            '눌삼재' => ['申' => '卯', '子' => '卯', '辰' => '卯', '亥' => '午', '卯' => '午', '未' => '午', '寅' => '酉', '午' => '酉', '戌' => '酉', '巳' => '子', '酉' => '子', '丑' => '子'],
            '날삼재' => ['申' => '辰', '子' => '辰', '辰' => '辰', '亥' => '未', '卯' => '未', '未' => '未', '寅' => '戌', '午' => '戌', '戌' => '戌', '巳' => '丑', '酉' => '丑', '丑' => '丑'],
        ];

        foreach ($samjaeMap as $name => $map) {
            if (isset($map[$yeonji]) && $map[$yeonji] === $targetYeonji) {
                return $name;
            }
        }
        return null;
    }

    private function isSangmun(string $yeonji, string $targetYeonji): bool
    {
        return in_array($yeonji.$targetYeonji, ['子寅','丑卯','寅辰','卯巳','辰午','巳未','午申','未酉','申戌','酉亥','戌子','亥丑']);
    }

    private function isJogaek(string $yeonji, string $targetYeonji): bool
    {
        return in_array($yeonji.$targetYeonji, ['子戌','丑亥','寅子','卯丑','辰寅','巳卯','午辰','未巳','申午','酉未','戌申','亥酉']);
    }

    private function isGuanbu(string $yeonji, string $targetYeonji): bool
    {
        return in_array($yeonji.$targetYeonji, ['子辰','丑巳','寅午','卯未','辰申','巳酉','午戌','未亥','申子','酉丑','戌寅','亥卯']);
    }
}
