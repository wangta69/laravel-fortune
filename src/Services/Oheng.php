<?php

namespace Pondol\Fortune\Services;

class Oheng
{
    private $saju;

    public $year_h;

    public $year_e;

    public $month_h;

    public $month_e;

    public $day_h;

    public $day_e;

    public $hour_h;

    public $hour_e;

    /**
     * Saju 객체를 받아 사주 전체의 오행 정보를 계산하고,
     * 계산된 정보를 담은 자기 자신(Oheng 객체)을 반환합니다.
     * Blade 뷰에서 `$saju->oheng->year_h->ch` 와 같이 사용하기 위한 메소드입니다.
     *
     * @param  Saju  $saju  분석할 사주 객체
     */
    public function withSaju(Saju $saju): self
    {

        $this->saju = $saju;

        $this->year_h = $this->convert($saju->get_h('year'));
        $this->year_e = $this->convert($saju->get_e('year'));
        $this->month_h = $this->convert($saju->get_h('month'));
        $this->month_e = $this->convert($saju->get_e('month'));
        $this->day_h = $this->convert($saju->get_h('day'));
        $this->day_e = $this->convert($saju->get_e('day'));
        if ($saju->hourKnown) {
            $this->hour_h = $this->convert($saju->get_h('hour'));
            $this->hour_e = $this->convert($saju->get_e('hour'));
        } else {
            $this->hour_h = null;
            $this->hour_e = null;
        }

        return $this;
    }

    /**
     * 간지 문자를 음양(+/-)과 다국어 정보가 포함된 객체로 변환합니다.
     * (오직 withSaju 메소드 내부에서만 사용됩니다)
     */
    public function convert(string $char): object
    {
        $ohengData = [];
        switch ($char) {
            case '甲': case '寅':
                $ohengData = $this->getOhaengLanguage(0);
                $ohengData['flag'] = '+';
                break;
            case '乙': case '卯':
                $ohengData = $this->getOhaengLanguage(0);
                $ohengData['flag'] = '-';
                break;
            case '丙': case '巳':
                $ohengData = $this->getOhaengLanguage(1);
                $ohengData['flag'] = '+';
                break;
            case '丁': case '午':
                $ohengData = $this->getOhaengLanguage(1);
                $ohengData['flag'] = '-';
                break;
            case '戊': case '辰': case '戌':
                $ohengData = $this->getOhaengLanguage(2);
                $ohengData['flag'] = '+';
                break;
            case '己': case '未': case '丑':
                $ohengData = $this->getOhaengLanguage(2);
                $ohengData['flag'] = '-';
                break;
            case '庚': case '申':
                $ohengData = $this->getOhaengLanguage(3);
                $ohengData['flag'] = '+';
                break;
            case '辛': case '酉':
                $ohengData = $this->getOhaengLanguage(3);
                $ohengData['flag'] = '-';
                break;
            case '壬': case '亥':
                $ohengData = $this->getOhaengLanguage(4);
                $ohengData['flag'] = '+';
                break;
            case '癸': case '子':
                $ohengData = $this->getOhaengLanguage(4);
                $ohengData['flag'] = '-';
                break;
        }

        return (object) $ohengData;
    }

    /**
     * [Private] 오행의 다국어 이름과 CSS 클래스명을 배열로 반환합니다.
     */
    private function getOhaengLanguage(int $serial): array
    {
        $ch = ['木', '火', '土', '金', '水'];
        $ko = ['목', '화', '토', '금', '수'];
        $en = ['thu', 'tue', 'sat', 'fri', 'wed'];

        return ['ch' => $ch[$serial], 'ko' => $ko[$serial], 'en' => $en[$serial]];
    }

    /**
     * [Public] 한글 오행을 한자 오행으로 변환합니다. (NameController에서 사용)
     *
     * @param  string|null  $hangulOhaeng  '목', '화', '토', '금', '수'
     * @return string '木', '火', '土', '金', '水'
     */
    public function convertHangulToHanja(?string $hangulOhaeng): string
    {
        $map = ['목' => '木', '화' => '火', '토' => '土', '금' => '金', '수' => '水'];

        return $map[$hangulOhaeng] ?? '';
    }

    /**
     * [Public] 한자 오행을 한글 오행으로 변환합니다. (NameController에서 사용)
     *
     * @param  string|null  $hanjaOhaeng  '木', '火', '土', '金', '水'
     * @return string '목', '화', '토', '금', '수'
     */
    public function convertHanjaToHangul(?string $hanjaOhaeng): string
    {
        $map = ['木' => '목', '火' => '화', '土' => '토', '金' => '금', '水' => '수'];

        return $map[$hanjaOhaeng] ?? '';
    }

    /**
     * [Public] 신강/신약 분석 결과에 따라 용신(Yongsin)을 찾아 반환합니다. (NameController에서 사용)
     *
     * @param  object  $strengthResult  SinyakSingang 클래스의 create() 메소드가 반환한 결과 객체
     * @return array ['priority1' => 1순위 용신, 'priority2' => 2순위 용신]
     */
    public function findYongsin(object $strengthResult): array
    {
        $dayMaster = $this->saju->get_h('day');
        $monthE = $this->saju->get_e('month');

        $eokbu = $this->calculateEokbu($strengthResult, $dayMaster);
        $johu = $this->calculateJohu($monthE);
        $tonggwan = $this->calculateTonggwan();

        // 1. 기본 순위는 억부(Eokbu)를 따릅니다.
        $p1 = $eokbu['priority1'];
        $p2 = $eokbu['priority2'];

        // 2. 조후(Johu)가 있다면 긴급 상황으로 보고 우선순위를 높입니다.
        if ($johu) {
            $p1 = $johu;
            $p2 = $eokbu['priority1'];
        }

        // 3. [핵심 수정] 통관(Tonggwan) 용신이 감지되면 이는 사주의 심각한 갈등을
        // 해결하는 기운이므로 조후(P1)를 밀어내고 1순위로 격상시킵니다.
        // 이미지의 사주처럼 '수'가 많아 '불'이 꺼지는 상황에서 '목'이 통관으로 잡히면
        // '목'이 1순위가 되어야 합니다.
        if ($tonggwan) {
            $p1 = $tonggwan;
            $p2 = $johu ?: $eokbu['priority1'];
        }

        return [
            'day_master' => $dayMaster,
            'priority1' => $p1,
            'priority2' => $p2,
            'is_johu' => $johu ? true : false,
            'is_tonggwan' => $tonggwan ? true : false,
        ];
    }

    /**
     * 신강/신약 분석 결과에 따라 용신, 희신, 기신을 찾아 반환합니다.
     *
     * @param  object  $strengthResult  SinyakSingang 클래스의 create() 결과
     * @return array ['yongsin' => '오행', 'huisin' => '오행', 'gisin' => '오행']
     */
    public function findYongsinAndGisin(object $strengthResult): array
    {
        // 기존 findYongsin 로직을 활용
        $yongsinData = $this->findYongsin($strengthResult);

        // 기신(忌神) 찾기 (용신을 극하는 오행)
        $overcomingCycle = ['木' => '金', '火' => '水', '土' => '木', '金' => '火', '水' => '土'];
        $yongsin = $yongsinData['priority1'] ?? null;
        $gisin = $yongsin ? $overcomingCycle[$yongsin] : null;

        return [
            'yongsin' => $yongsin,
            'huisin' => $yongsinData['priority2'] ?? null,
            'gisin' => $gisin,
            'is_johu' => $yongsinData['is_johu'], // 플래그 전달 추가
            'is_tonggwan' => $yongsinData['is_tonggwan'], // 플래그 전달 추가
        ];
    }

    /**
     * 사주팔자 8글자의 오행 개수를 집계하여 배열로 반환합니다.
     *
     * @return array ['목' => count, '화' => count, ...]
     */
    public function getOhaengCount(): array
    {
        $count = ['목' => 0, '화' => 0, '토' => 0, '금' => 0, '수' => 0];
        $pillars = ['year_h', 'year_e', 'month_h', 'month_e', 'day_h', 'day_e'];

        if ($this->hour_h && $this->hour_e) {
            $pillars[] = 'hour_h';
            $pillars[] = 'hour_e';
        }

        foreach ($pillars as $pillar) {
            if (isset($this->{$pillar}->ko)) {
                $ohaengName = $this->{$pillar}->ko;
                if ($ohaengName) {
                    $count[$ohaengName]++;
                }
            }
        }

        return $count;
    }

    /**
     * [조후 용신] 계절에 따른 긴급 처방
     */
    private function calculateJohu(string $monthE): ?string
    {
        // 겨울 (해, 자, 축월) -> 화(火) 필요
        if (in_array($monthE, ['亥', '子', '丑'])) {
            return '火';
        }
        // 여름 (사, 오, 미월) -> 수(水) 필요
        if (in_array($monthE, ['巳', '午', '未'])) {
            return '水';
        }

        return null; // 봄, 가을은 조후가 급하지 않음
    }

    /**
     * [통관 용신] 강한 두 기운 사이의 다리 역할
     */
    private function calculateTonggwan(): ?string
    {
        $counts = $this->getOhaengCount();
        $hanjaCounts = [];
        foreach ($counts as $ko => $count) {
            $hanjaCounts[$this->convertHangulToHanja($ko)] = $count;
        }

        // --- 1. 강한 기운이 약한 기운을 일방적으로 극할 때 (고립 구제) ---

        // 수(水)가 강하고 화(火)가 약할 때: 수극화(水剋火)를 수생목->목생화로 연결
        if (($hanjaCounts['水'] ?? 0) >= 3 && ($hanjaCounts['火'] ?? 0) <= 1) {
            return '木';
        }

        // 금(金)이 강하고 목(木)이 약할 때: 금극목(金剋木)을 금생수->수생목으로 연결
        if (($hanjaCounts['金'] ?? 0) >= 3 && ($hanjaCounts['木'] ?? 0) <= 1) {
            return '水';
        }

        // 목(木)이 강하고 토(土)가 약할 때: 목극토(木剋土)를 목생화->화생토로 연결
        if (($hanjaCounts['木'] ?? 0) >= 3 && ($hanjaCounts['土'] ?? 0) <= 1) {
            return '火';
        }

        // 화(火)가 강하고 금(金)이 약할 때: 화극금(火剋金)을 화생토->토생금으로 연결
        if (($hanjaCounts['火'] ?? 0) >= 3 && ($hanjaCounts['金'] ?? 0) <= 1) {
            return '土';
        }

        // 토(土)가 강하고 수(水)가 약할 때: 토극수(土剋水)를 토생금->금생수로 연결
        if (($hanjaCounts['土'] ?? 0) >= 3 && ($hanjaCounts['水'] ?? 0) <= 1) {
            return '金';
        }

        // --- 2. 서로 강한 세력끼리 정면 충돌할 때 (세력 균형) ---

        // 금(金) 3 vs 목(木) 3 이 싸울 때 -> 水로 통관
        if (($hanjaCounts['金'] ?? 0) >= 3 && ($hanjaCounts['木'] ?? 0) >= 3) {
            return '水';
        }

        // 목(木) 3 vs 토(土) 3 이 싸울 때 -> 火로 통관
        if (($hanjaCounts['木'] ?? 0) >= 3 && ($hanjaCounts['土'] ?? 0) >= 3) {
            return '火';
        }

        // 화(火) 3 vs 금(金) 3 이 싸울 때 -> 土로 통관
        if (($hanjaCounts['火'] ?? 0) >= 3 && ($hanjaCounts['金'] ?? 0) >= 3) {
            return '土';
        }

        // 토(土) 3 vs 수(水) 3 이 싸울 때 -> 金으로 통관
        if (($hanjaCounts['土'] ?? 0) >= 3 && ($hanjaCounts['水'] ?? 0) >= 3) {
            return '金';
        }

        // 수(水) 3 vs 화(火) 3 이 싸울 때 -> 木으로 통관
        if (($hanjaCounts['水'] ?? 0) >= 3 && ($hanjaCounts['火'] ?? 0) >= 3) {
            return '木';
        }

        return null;
    }

    /**
     * [억부 용신] 기존의 강약 로직 분리
     */
    private function calculateEokbu(object $strengthResult, string $dayMaster): array
    {
        $generationCycle = ['木' => '水', '火' => '木', '土' => '火', '金' => '土', '水' => '金'];
        $overcomingCycle = ['木' => '金', '火' => '水', '土' => '木', '金' => '火', '水' => '土'];
        $expressionCycle = array_flip($generationCycle);

        // 로그상 구조 대비 방어 코드
        $res = $strengthResult->result ?? ($strengthResult->stdClass->result ?? '신약');

        if ($res === '신강') {
            return [
                'priority1' => $overcomingCycle[$dayMaster] ?? '',
                'priority2' => $expressionCycle[$dayMaster] ?? '',
            ];
        } else {
            return [
                'priority1' => $generationCycle[$dayMaster] ?? '',
                'priority2' => $dayMaster,
            ];
        }
    }

    /**
     * 60갑자를 납음오행(納音五行)으로 변환합니다.
     *
     * @param  string  $ganji  (예: "甲子")
     * @return string (예: "金")
     */
    public function getNadiElement(string $ganji): string
    {
        // 이 데이터는 config 파일로 관리하면 더 좋습니다.
        $nadiMap = [
            '甲子' => '金', '乙丑' => '金', '丙寅' => '火', '丁卯' => '火', '戊辰' => '木', '己巳' => '木',
            '庚午' => '土', '辛未' => '土', '壬申' => '金', '癸酉' => '金', '甲戌' => '火', '乙亥' => '火',
            '丙子' => '水', '丁丑' => '水', '戊寅' => '土', '己卯' => '土', '庚辰' => '金', '辛巳' => '金',
            '壬午' => '木', '癸未' => '木', '甲申' => '水', '乙酉' => '水', '丙戌' => '土', '丁亥' => '土',
            '戊子' => '火', '己丑' => '火', '庚寅' => '木', '辛卯' => '木', '壬辰' => '水', '癸巳' => '水',
            '甲午' => '金', '乙未' => '金', '丙申' => '火', '丁酉' => '火', '戊戌' => '木', '己亥' => '木',
            '庚子' => '土', '辛丑' => '土', '壬寅' => '金', '癸卯' => '金', '甲辰' => '火', '乙巳' => '火',
            '丙午' => '水', '丁未' => '水', '戊申' => '土', '己酉' => '土', '庚戌' => '金', '辛亥' => '金',
            '壬子' => '木', '癸丑' => '木', '甲寅' => '水', '乙卯' => '水', '丙辰' => '土', '丁巳' => '土',
            '戊午' => '火', '己未' => '火', '庚申' => '木', '辛酉' => '木', '壬戌' => '水', '癸亥' => '水',
        ];

        return $nadiMap[$ganji] ?? '';
    }

    /**
     * 오행 개수 배열을 받아 가장 약한 오행(용신 후보)의 이름을 반환합니다.
     */
    public function getNeededElement(array $ohaengCount): string
    {
        asort($ohaengCount);

        return key($ohaengCount);
    }

    /**
     * [추가] 오행 중 개수가 가장 적은(부족한) 성분을 반환합니다. (예: '목')
     * StarFortuneController의 궁합 로직에서 사용됩니다.
     */
    public function getWeakest(): string
    {
        // 1. 현재 사주의 오행 개수를 가져옵니다. (['목'=>0, '화'=>2 ...])
        $counts = $this->getOhaengCount();

        // 2. 오름차순 정렬 (적은 순서대로)
        asort($counts);

        // 3. 가장 첫 번째 키(가장 적은 오행)를 반환
        return array_key_first($counts);
    }

    /**
     * [추가] 오행 중 개수가 가장 많은(강한) 성분을 반환합니다. (예: '토')
     * StarFortuneController의 궁합 로직에서 사용됩니다.
     */
    public function getStrongest(): string
    {
        $counts = $this->getOhaengCount();
        arsort($counts);

        return array_key_first($counts);
    }
}
