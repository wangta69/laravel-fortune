<?php

namespace Pondol\Fortune\Services;

/**
 * 다른 분류에 속하지 않는 특수한 신살이나 관계성을 분석하는 클래스입니다.
 */
class Gita
{
    private const DEFINITIONS = [
        '파군' => ['ch' => '破軍', 'type' => 'hyungsal', 'desc' => '삼합의 기운을 깨뜨리는 작용.'],
        '구신' => ['ch' => '勾神', 'type' => 'hyungsal', 'desc' => '일에 얽매이거나 지체되는 작용.'],
        '교신' => ['ch' => '絞神', 'type' => 'hyungsal', 'desc' => '일이 꼬이거나 방해받는 작용.'],
        '비염살' => ['ch' => '飛廉殺', 'type' => 'hyungsal', 'desc' => '구설수나 예상치 못한 방해를 암시.'],
    ];

    private array $saju = [];
    public array $results = [];

    public function withSaju($saju): self
    {
        $this->saju['e'] = [
            'y' => $saju->get_e('year'), 'm' => $saju->get_e('month'),
            'd' => $saju->get_e('day'), 'h' => $saju->get_e('hour')
        ];

        // withSaju 호출 시 모든 기타 신살을 계산하여 results에 저장
        $this->calculateAll();

        return $this;
    }

    private function calculateAll(): void
    {
        $yeonji = $this->saju['e']['y'];

        foreach ($this->saju['e'] as $pos => $jiji) {
            $found = [];
            if ($this->isPagun($yeonji, $jiji)) {
                $found[] = '파군';
            }
            if ($this->isGusin($yeonji, $jiji)) {
                $found[] = '구신';
            }
            if ($this->isGyosin($yeonji, $jiji)) {
                $found[] = '교신';
            }
            if ($this->isBiyeom($yeonji, $jiji)) {
                $found[] = '비염살';
            }

            if (!empty($found)) {
                foreach ($found as $name) {
                    $this->results[$pos][] = array_merge(['ko' => $name], self::DEFINITIONS[$name]);
                }
            }
        }
    }

    // --- private 헬퍼 메서드 ---
    private function isPagun(string $yeonji, string $jiji): bool
    {
        if (in_array($yeonji, ['子', '辰', '申']) && $jiji === '酉') {
            return true;
        }
        if (in_array($yeonji, ['丑', '巳', '酉']) && $jiji === '巳') {
            return true;
        }
        if (in_array($yeonji, ['寅', '午', '戌']) && $jiji === '寅') {
            return true;
        }
        if (in_array($yeonji, ['卯', '未', '亥']) && $jiji === '亥') {
            return true;
        }
        return false;
    }

    private function isGusin(string $yeonji, string $jiji): bool
    {
        return in_array($yeonji.$jiji, ['子卯','丑辰','寅巳','卯午','辰未','巳申','午酉','未戌','申亥','酉子','戌丑','亥寅']);
    }
    private function isGyosin(string $yeonji, string $jiji): bool
    {
        return in_array($yeonji.$jiji, ['子酉','丑戌','寅亥','卯子','辰丑','巳寅','午卯','未辰','申巳','酉午','戌未','亥申']);
    }
    private function isBiyeom(string $yeonji, string $jiji): bool
    {
        return in_array($yeonji.$jiji, ['子申','丑酉','寅戌','卯亥','辰子','巳丑','午寅','未卯','申辰','酉巳','戌午','亥未']);
    }
}
