<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Facades\Saju;
use Carbon\Carbon;

class LottoFortuneService
{
    private const OHAENG_NUMBERS = [
        '水' => [1, 6, 11, 16, 21, 26, 31, 36, 41], '火' => [2, 7, 12, 17, 22, 27, 32, 37, 42],
        '木' => [3, 8, 13, 18, 23, 28, 33, 38, 43], '金' => [4, 9, 14, 19, 24, 29, 34, 39, 44],
        '土' => [5, 10, 15, 20, 25, 30, 35, 40, 45],
    ];
    private const GENERATION_CYCLE = ['木' => '水', '火' => '木', '土' => '火', '金' => '土', '水' => '金'];

    // Saju 객체를 직접 받도록 하여 의존성을 명확히 함
    private $userSaju;

    public function withSaju(Saju $saju): self
    {
        $this->userSaju = $saju;
        return $this;
    }

    /**
     * 로또 번호 및 분석 데이터를 생성하는 메인 메소드
     *
     * @param Carbon $drawDate 추첨일
     * @return array
     */
    public function generate(Carbon $drawDate): array
    {
        if (!$this->userSaju) {
            throw new \Exception("Saju object must be set using withSaju() method.");
        }

        // 1. [人] 나의 사주 분석
        $sinyakSingangResult = $this->userSaju->sinyaksingang()->create();
        $yongsinData = $this->userSaju->oheng()->findYongsin($sinyakSingangResult);

        // 2. [天 & 地] 추첨일 기운 분석
        $drawDateSaju = Saju::ymd($drawDate->format('Ymd'))->create();
        $iljinGanOhaeng = $this->userSaju->sinyaksingang()->convertCharToOhaeng($drawDateSaju->get_h('day'));
        $iljinJiOhaeng = $this->userSaju->sinyaksingang()->convertCharToOhaeng($drawDateSaju->get_e('day'));
        $woljiOhaeng = $this->userSaju->sinyaksingang()->convertCharToOhaeng($drawDateSaju->get_e('month'));

        // 3. 오행 스코어 계산
        $scores = $this->calculateOhaengScores(
            $yongsinData['priority1'],
            $yongsinData['priority2'],
            $iljinGanOhaeng,
            $iljinJiOhaeng,
            $woljiOhaeng
        );
        arsort($scores);
        $sortedOhaeng = array_keys($scores);

        // 4. 3단계 구조 생성
        $coreNumbers = $this->extractCoreNumbers($sortedOhaeng, $this->userSaju, $drawDate);
        $recommendedPool = $this->createRecommendedPool($sortedOhaeng, $coreNumbers);
        $finalCombination = $this->createFinalCombination($coreNumbers, $recommendedPool);

        // 5. 분석에 사용된 모든 데이터를 구조화하여 반환
        return [
            // 최종 결과물
            'core_numbers'      => $coreNumbers,
            'recommended_pool'  => $recommendedPool,
            'final_combination' => $finalCombination,

            // 스토리텔링을 위한 재료 데이터
            'analysis_data' => [
                'yongsin'           => $yongsinData['priority1'],
                'iljin_ohaeng'      => $iljinGanOhaeng, // 천간 오행
                'best_ohaeng'       => $sortedOhaeng[0],
                'scores'            => $scores
            ]
        ];
    }

    private function calculateOhaengScores($yongsin, $huisin, $iljinGan, $iljinJi, $wolji): array
    {
        $scores = ['木' => 0, '火' => 0, '土' => 0, '金' => 0, '水' => 0];
        if (isset($scores[$yongsin])) {
            $scores[$yongsin] += 20;
        }
        if (isset($scores[$huisin])) {
            $scores[$huisin] += 15;
        }
        if (isset($scores[$iljinGan])) {
            $scores[$iljinGan] += 10;
        }
        if (isset($scores[$iljinJi])) {
            $scores[$iljinJi] += 10;
        }
        if (isset($scores[$wolji])) {
            $scores[$wolji] += 5;
        }
        if (isset(self::GENERATION_CYCLE[$yongsin]) && self::GENERATION_CYCLE[$yongsin] === $iljinGan) {
            $scores[$yongsin] += 7;
        }
        if (isset(self::GENERATION_CYCLE[$yongsin]) && self::GENERATION_CYCLE[$yongsin] === $iljinJi) {
            $scores[$yongsin] += 7;
        }
        return $scores;
    }

    private function extractCoreNumbers(array $sortedOhaeng, Saju $saju, Carbon $drawDate): array
    {
        $coreNumbers = [];
        $poolA = self::OHAENG_NUMBERS[$sortedOhaeng[0]];
        $seed = (int)str_replace('-', '', $saju->solar) + $drawDate->dayOfYear;
        $index1 = $seed % count($poolA);
        $coreNumbers[] = $poolA[$index1];
        if (count($poolA) > 1) {
            $index2 = ($seed + date('Y')) % count($poolA);
            if ($index1 !== $index2 && !in_array($poolA[$index2], $coreNumbers)) {
                $coreNumbers[] = $poolA[$index2];
            }
        }
        sort($coreNumbers);
        return $coreNumbers;
    }

    private function createRecommendedPool(array $sortedOhaeng, array $coreNumbers): array
    {
        $pool = array_merge(
            self::OHAENG_NUMBERS[$sortedOhaeng[0]],
            self::OHAENG_NUMBERS[$sortedOhaeng[1]],
            self::OHAENG_NUMBERS[$sortedOhaeng[2]]
        );
        $recommendedPool = array_diff(array_unique($pool), $coreNumbers);
        sort($recommendedPool);
        return array_values($recommendedPool);
    }

    private function createFinalCombination(array $coreNumbers, array $recommendedPool): array
    {
        $finalCombination = $coreNumbers;
        $needed = 6 - count($coreNumbers);
        if ($needed > 0 && count($recommendedPool) >= $needed) {
            $randomKeys = array_rand($recommendedPool, $needed);
            foreach ((array)$randomKeys as $key) {
                $finalCombination[] = $recommendedPool[$key];
            }
        }
        sort($finalCombination);
        return $finalCombination;
    }
}
