<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Enums\Gender;

class NameService
{
    /**
     * 한글 초성을 오행으로 변환하는 맵.
     */
    private static $choseongToOhaengMap = [
        'ㄱ' => '목', 'ㅋ' => '목', 'ㄲ' => '목', // 木
        'ㄴ' => '화', 'ㄷ' => '화', 'ㄹ' => '화', 'ㅌ' => '화', 'ㄸ' => '화', // 火
        'ㅇ' => '토', 'ㅎ' => '토', // 土
        'ㅅ' => '금', 'ㅈ' => '금', 'ㅊ' => '금', 'ㅆ' => '금', 'ㅉ' => '금', // 金
        'ㅁ' => '수', 'ㅂ' => '수', 'ㅍ' => '수', 'ㅃ' => '수', // 水
    ];

    /**
     * 주어진 한글 문자에서 초성을 추출합니다.
     */
    public function getChoseong(string $char): ?string
    {
        $choseongArr = ['ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅃ', 'ㅅ', 'ㅆ', 'ㅇ', 'ㅈ', 'ㅉ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ'];
        $unicode = mb_ord($char, 'UTF-8');

        if ($unicode >= 0xAC00 && $unicode <= 0xD7A3) {
            $syllableIndex = $unicode - 0xAC00;
            $choseongIndex = floor($syllableIndex / 588);

            return $choseongArr[$choseongIndex];
        }

        return null;
    }

    /**
     * 주어진 한글 문자에서 음령오행을 구합니다.
     */
    public function getEumnyeongOhaeng(string $char): ?string
    {
        $choseong = $this->getChoseong($char);

        if ($choseong && isset(self::$choseongToOhaengMap[$choseong])) {
            return self::$choseongToOhaengMap[$choseong];
        }

        return null;
    }

    /**
     * 이름 구조 타입(예: '12', '13')을 결정합니다.
     */
    public function determineNameType(array $input): string
    {
        $type = (isset($input['f2'])) ? '2' : '1';
        $type .= (isset($input['g3'])) ? '3' : ((isset($input['g2'])) ? '2' : '1');

        return $type;
    }

    /**
     * 4격(원형이정) 전체를 계산하여 배열로 반환합니다.
     */
    public function calculateAllGueks(int $f_sum, int $g1, int $g2_etc): array
    {
        $isOneCharName = ($g2_etc === 0);

        return [
            'early' => $isOneCharName ? ($g1 + 1) : ($g1 + $g2_etc),           // 원격(초년)
            'first' => $f_sum + $g1,                                           // 형격(장년)
            'middle' => $isOneCharName ? ($f_sum + 1) : ($f_sum + $g2_etc),    // 이격(중년)
            'last' => $f_sum + $g1 + $g2_etc,                                 // 정격(말년)
        ];
    }

    /**
     * 각 시기별 획수 계산 공식을 텍스트로 생성합니다.
     */
    public function generateLuckFormulas(array $info, array $gueks): array
    {
        $f1 = $info['f1'] ?? null;
        $f2 = $info['f2'] ?? null;
        $g1 = $info['g1'] ?? null;
        $g2 = $info['g2'] ?? null;
        $g3 = $info['g3'] ?? null;

        $lastSungChar = $f2 ?: $f1;
        $lastNameChar = $g3 ?: ($g2 ?: $g1);
        $isOneCharName = ! isset($g2) && ! isset($g3);

        return [
            'earlyYear' => $isOneCharName
                ? "{$g1->hanja_char}({$g1->stroke_count}) + 가수(1) = {$gueks['early']}"
                : "{$g1->hanja_char}({$g1->stroke_count}) + {$g2->hanja_char}({$g2->stroke_count}) = {$gueks['early']}",
            'firstYear' => "{$lastSungChar->hanja_char}({$lastSungChar->stroke_count}) + {$g1->hanja_char}({$g1->stroke_count}) = {$gueks['first']}",
            'middleYear' => $isOneCharName
                ? "{$f1->hanja_char}({$f1->stroke_count}) + 가수(1) = {$gueks['middle']}"
                : "{$f1->hanja_char}({$f1->stroke_count}) + {$lastNameChar->hanja_char}({$lastNameChar->stroke_count}) = {$gueks['middle']}",
            'lastYear' => "성명 전체 획수 합계 = {$gueks['last']}",
        ];
    }

    /**
     * 길흉(吉凶) 등급에 따라 점수와 한글 등급명을 반환합니다. (통합 표준)
     */
    public function getScoreByRating(string $rating): array
    {
        switch ($rating) {
            case '吉':
                return ['jumsu' => 100, 'title' => '대길(大吉)'];
            case '中吉':
                return ['jumsu' => 80,  'title' => '길(吉)'];
            case '半吉半凶':
                return ['jumsu' => 80,  'title' => '반길반흉(半吉半凶)'];
            case '中凶':
                return ['jumsu' => 60,  'title' => '보통(平)'];
            case '凶':
                return ['jumsu' => 40,  'title' => '흉(凶)'];
            default:
                return ['jumsu' => 60,  'title' => '보통(平)'];
        }
    }

    /**
     * 총점에 따라 최종 등급(rank 0~5)을 계산합니다.
     */
    public function calculateRank(int $totalScore): int
    {
        if ($totalScore >= 530) {
            return 5;
        }
        if ($totalScore >= 430) {
            return 4;
        }
        if ($totalScore >= 300) {
            return 3;
        }
        if ($totalScore >= 200) {
            return 2;
        }
        if ($totalScore >= 100) {
            return 1;
        }

        return 0;
    }

    /**
     * 사주(태어난 월/계절)와의 조화(궁합)를 분석하여 점수와 해설을 계산합니다.
     */
    public function calculateSajuCompatibility(string $month_e, array $gueks): array
    {
        $seasonalElements = $this->getSeasonalElements($month_e);
        if (! $seasonalElements) {
            return ['jumsu' => 0, 'title' => '정보없음', 'analysis' => '태어난 월에 대한 사주 정보가 없습니다.'];
        }

        $nameElements = [
            'early' => $this->getOhaengByNumber($this->correctMod(10, $gueks['early'])),
            'first' => $this->getOhaengByNumber($this->correctMod(10, $gueks['first'])),
            'last' => $this->getOhaengByNumber($this->correctMod(10, $gueks['last'])),
        ];

        $elementCounts = $this->countKeyElements(array_values($nameElements), $seasonalElements);
        $scoreResult = $this->determineFinalScore($elementCounts);

        return [
            'jumsu' => $scoreResult['jumsu'],
            'title' => $scoreResult['title'],
            'analysis' => [
                'month_e' => $month_e,
                'season' => $seasonalElements['season'],
                'primary_needed' => $seasonalElements['primaryNeeded'],
                'secondary_needed' => $seasonalElements['secondaryNeeded'],
                'name_elements' => $nameElements,
                'element_counts' => $elementCounts,
            ],
        ];
    }

    private function getSeasonalElements(string $month_e): ?array
    {
        if (in_array($month_e, ['寅', '卯', '辰'])) {
            return [
                'season' => '봄',
                'primaryNeeded' => '金',
                'secondaryNeeded' => '土',
                'avoided' => ['木'],
            ];
        }
        if (in_array($month_e, ['巳', '午', '未'])) {
            return [
                'season' => '여름',
                'primaryNeeded' => '水',
                'secondaryNeeded' => '金',
                'avoided' => ['火'],
            ];
        }
        if (in_array($month_e, ['申', '酉', '戌'])) {
            return [
                'season' => '가을',
                'primaryNeeded' => '木',
                'secondaryNeeded' => '水',
                'avoided' => ['金', '火'],
            ];
        }
        if (in_array($month_e, ['亥', '子', '丑'])) {
            return [
                'season' => '겨울',
                'primaryNeeded' => '火',
                'secondaryNeeded' => '木',
                'avoided' => ['水'],
            ];
        }

        return null;
    }

    private function countKeyElements(array $nameElements, array $seasonalElements): array
    {
        $counts = array_count_values($nameElements);

        $primaryNeededCount = $counts[$seasonalElements['primaryNeeded']] ?? 0;
        $secondaryNeededCount = $counts[$seasonalElements['secondaryNeeded']] ?? 0;

        $totalAvoidedCount = 0;
        foreach ($seasonalElements['avoided'] as $avoidedElement) {
            $totalAvoidedCount += $counts[$avoidedElement] ?? 0;
        }

        return compact('primaryNeededCount', 'secondaryNeededCount', 'totalAvoidedCount');
    }

    private function determineFinalScore(array $counts): array
    {
        $primaryNeededCount = $counts['primaryNeededCount'];
        $secondaryNeededCount = $counts['secondaryNeededCount'];
        $totalAvoidedCount = $counts['totalAvoidedCount'];
        $otherElementCount = 3 - ($primaryNeededCount + $secondaryNeededCount + $totalAvoidedCount);

        if ($primaryNeededCount == 3) {
            return ['jumsu' => 95, 'title' => '最上格'];
        }
        if ($primaryNeededCount == 2 && $secondaryNeededCount == 1) {
            return ['jumsu' => 95, 'title' => '最上格'];
        }
        if ($primaryNeededCount == 1 && $secondaryNeededCount == 2) {
            return ['jumsu' => 95, 'title' => '最上格'];
        }
        if ($secondaryNeededCount == 3) {
            return ['jumsu' => 90, 'title' => '上格'];
        }
        if ($primaryNeededCount == 2 && $otherElementCount == 1) {
            return ['jumsu' => 90, 'title' => '上格'];
        }
        if ($secondaryNeededCount == 2 && $primaryNeededCount == 0 && $otherElementCount == 1) {
            return ['jumsu' => 90, 'title' => '上格'];
        }
        if ($primaryNeededCount == 1 && $secondaryNeededCount == 1 && $otherElementCount == 1) {
            return ['jumsu' => 85, 'title' => '上格'];
        }
        if ($primaryNeededCount == 1 && $secondaryNeededCount == 1 && $totalAvoidedCount == 1) {
            return ['jumsu' => 80, 'title' => '上格'];
        }
        if ($primaryNeededCount == 1 && $otherElementCount == 2) {
            return ['jumsu' => 75, 'title' => '中格'];
        }
        if ($primaryNeededCount == 2 && $totalAvoidedCount == 1) {
            return ['jumsu' => 70, 'title' => '中格'];
        }
        if ($secondaryNeededCount == 2 && $totalAvoidedCount == 1) {
            return ['jumsu' => 65, 'title' => '中格'];
        }
        if ($secondaryNeededCount == 1 && $otherElementCount == 2) {
            return ['jumsu' => 60, 'title' => '中格'];
        }
        if ($otherElementCount == 3) {
            return ['jumsu' => 55, 'title' => '下格'];
        }
        if ($primaryNeededCount == 1 && $totalAvoidedCount == 1 && $otherElementCount == 1) {
            return ['jumsu' => 50, 'title' => '下格'];
        }
        if ($secondaryNeededCount == 1 && $totalAvoidedCount == 1 && $otherElementCount == 1) {
            return ['jumsu' => 45, 'title' => '下格'];
        }
        if ($totalAvoidedCount == 1 && $otherElementCount == 2) {
            return ['jumsu' => 30, 'title' => '不調化'];
        }
        if ($primaryNeededCount == 1 && $totalAvoidedCount == 2) {
            return ['jumsu' => 25, 'title' => '不調化'];
        }
        if ($secondaryNeededCount == 1 && $totalAvoidedCount == 2) {
            return ['jumsu' => 20, 'title' => '不調化'];
        }
        if ($totalAvoidedCount == 2 && $otherElementCount == 1) {
            return ['jumsu' => 10, 'title' => '不調化'];
        }

        return ['jumsu' => 0, 'title' => '最下格'];
    }

    /**
     * 숫자에 따른 오행 한자 반환 (사주 조화 계산용)
     */
    public function getOhaengByNumber(int $num): string
    {
        if ($num == 0) {
            $num = 10;
        }
        if ($num <= 2) {
            return '木';
        }
        if ($num <= 4) {
            return '火';
        }
        if ($num <= 6) {
            return '土';
        }
        if ($num <= 8) {
            return '金';
        }

        return '水';
    }

    /**
     * 획수(수리오행)에 따른 오행 문자열을 반환합니다.
     */
    public function getOhaengByStrokes(int $strokes): string
    {
        $lastDigit = $strokes % 10;
        if ($lastDigit == 0) {
            $lastDigit = 10;
        }

        if ($lastDigit <= 2) {
            return '木';
        }
        if ($lastDigit <= 4) {
            return '火';
        }
        if ($lastDigit <= 6) {
            return '土';
        }
        if ($lastDigit <= 8) {
            return '金';
        }

        return '水';
    }

    /**
     * 신강/신약 결과에 따라 보완할 오행(용신) 후보군을 우선순위 배열로 반환합니다.
     */
    public function findYongsin(object $strengthResult, $saju): array
    {
        $generationCycle = ['木' => '水', '火' => '木', '土' => '火', '金' => '土', '水' => '金'];
        $overcomingCycle = ['木' => '金', '火' => '水', '土' => '木', '金' => '火', '水' => '土'];
        $expressionCycle = array_flip($generationCycle);

        // 천간 -> 오행 매핑 (타입 불일치 방지)
        $stemToElement = [
            '甲' => '木', '乙' => '木', '丙' => '火', '丁' => '火', '戊' => '土',
            '己' => '土', '庚' => '金', '辛' => '金', '壬' => '水', '癸' => '水',
        ];

        $rawStem = $strengthResult->day_master ?? ($saju ? $saju->get_h('day') : '甲');
        $dayMaster = $stemToElement[$rawStem] ?? $rawStem;
        $candidates = [];

        if ($strengthResult->result === '신강') {
            $candidates[$overcomingCycle[$dayMaster]] = 100;
            $candidates[$expressionCycle[$dayMaster]] = 90;
            $candidates[array_search($overcomingCycle[$dayMaster], $generationCycle, true)] = 80;
        } else {
            $candidates[$generationCycle[$dayMaster]] = 100;
            $candidates[$dayMaster] = 90;
        }

        $allOhaengScores = (new SinyakSingang)->withSaju($saju)->getAllOhaengScores();
        foreach ($allOhaengScores as $element => $power) {
            if ($power > 40 && isset($candidates[$element])) {
                $candidates[$element] -= 30;
            }
        }
        arsort($candidates);

        return array_slice(array_keys($candidates), 0, 2);
    }

    /**
     * 주어진 조건에 맞는 길한 이름 획수 조합을 필터링합니다.
     */
    public function filterGoodStrokes($ohengs, $gender, $hanjano1): array
    {
        $oheng2 = mb_substr($ohengs, 1, 1);
        $oheng3 = mb_substr($ohengs, 2, 1);

        $isMale = Gender::fromMixed($gender)->isMale();

        $suri_no = $isMale
            ? [1, 3, 5, 6, 7, 8, 11, 13, 15, 16, 18, 21, 23, 24, 25, 29, 31, 32, 33, 35, 37, 39, 41, 45, 47, 48, 52, 57, 61, 63, 65, 67, 68, 81]
            : [1, 3, 5, 6, 7, 8, 11, 13, 15, 16, 17, 18, 24, 25, 29, 31, 35, 37, 38, 41, 45, 47, 48, 52, 57, 58, 61, 63, 65, 67, 68, 81];

        $result = [];
        for ($j = 1; $j < 30; $j++) {
            if ($this->getOhaengByStrokes($j) !== $oheng2) {
                continue;
            }
            for ($k = 1; $k < 30; $k++) {
                if ($this->getOhaengByStrokes($k) !== $oheng3) {
                    continue;
                }

                $gueks = $this->calculateAllGueks($hanjano1, $j, $k);

                $isAllSuriGood = true;
                foreach ($gueks as $guekVal) {
                    if (! in_array($this->correctMod(81, $guekVal), $suri_no)) {
                        $isAllSuriGood = false;
                        break;
                    }
                }
                if ($isAllSuriGood) {
                    $result[] = ['first' => $j, 'second' => $k];
                }
            }
        }

        return $result;
    }

    /**
     * 81수리 계산을 위해 나머지를 보정하는 함수.
     */
    public function correctMod(int $mod, int $value): int
    {
        if ($value <= 0) {
            return 1;
        }
        $result = $value % $mod;

        return ($result === 0) ? $mod : $result;
    }
}
