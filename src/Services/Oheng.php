<?php

namespace Pondol\Fortune\Services;

class Oheng
{
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
      * @param Saju $saju 분석할 사주 객체
      * @return self
      */
    public function withSaju(Saju $saju): self
    {
        $this->year_h  = $this->convert($saju->get_h('year'));
        $this->year_e  = $this->convert($saju->get_e('year'));
        $this->month_h = $this->convert($saju->get_h('month'));
        $this->month_e = $this->convert($saju->get_e('month'));
        $this->day_h   = $this->convert($saju->get_h('day'));
        $this->day_e   = $this->convert($saju->get_e('day'));
        $this->hour_h  = $this->convert($saju->get_h('hour'));
        $this->hour_e  = $this->convert($saju->get_e('hour'));

        return $this;
    }

    /**
     * [Private] 간지 문자를 음양(+/-)과 다국어 정보가 포함된 객체로 변환합니다.
     * (오직 withSaju 메소드 내부에서만 사용됩니다)
     * @param string $char
     * @return object
     */
    private function convert(string $char): object
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
        return (object)$ohengData;
    }

    /**
     * [Private] 오행의 다국어 이름과 CSS 클래스명을 배열로 반환합니다.
     * @param int $serial
     * @return array
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
     * @param string|null $hangulOhaeng '목', '화', '토', '금', '수'
     * @return string '木', '火', '土', '金', '水'
     */
    public function convertHangulToHanja(?string $hangulOhaeng): string
    {
        $map = ['목' => '木', '화' => '火', '토' => '土', '금' => '金', '수' => '水'];
        return $map[$hangulOhaeng] ?? '';
    }



    /**
     * [Public] 한자 오행을 한글 오행으로 변환합니다. (NameController에서 사용)
     * @param string|null $hanjaOhaeng '木', '火', '土', '金', '水'
     * @return string '목', '화', '토', '금', '수'
     */
    public function convertHanjaToHangul(?string $hanjaOhaeng): string
    {
        $map = ['木' => '목', '火' => '화', '土' => '토', '金' => '금', '水' => '수'];
        return $map[$hanjaOhaeng] ?? '';
    }

    /**
     * [Public] 신강/신약 분석 결과에 따라 용신(Yongsin)을 찾아 반환합니다. (NameController에서 사용)
     * @param object $strengthResult SinyakSingang 클래스의 create() 메소드가 반환한 결과 객체
     * @return array ['priority1' => 1순위 용신, 'priority2' => 2순위 용신]
     */
    public function findYongsin(object $strengthResult): array
    {
        $generationCycle = ['木' => '水', '火' => '木', '土' => '火', '金' => '土', '水' => '金']; // 인성(印星)
        $overcomingCycle = ['木' => '金', '火' => '水', '土' => '木', '金' => '火', '水' => '土']; // 관성(官星)
        $expressionCycle = array_flip($generationCycle); // 식상(食傷)

        $dayMaster = $strengthResult->day_master;

        if ($strengthResult->result === '신강') {
            // 신강 사주: 힘이 넘치므로 억제하거나(관성), 기운을 빼주는(식상) 오행이 필요.
            return [
                'priority1' => $overcomingCycle[$dayMaster],
                'priority2' => $expressionCycle[$dayMaster]
            ];
        } else {
            // 신약 사주: 힘이 부족하므로 돕거나(인성), 같은 편(비겁)이 필요.
            return [
                'priority1' => $generationCycle[$dayMaster],
                'priority2' => $dayMaster
            ];
        }
    }

    /**
     * 사주팔자 8글자의 오행 개수를 집계하여 배열로 반환합니다.
     *
     * @return array ['목' => count, '화' => count, ...]
     */
    public function getOhaengCount(): array
    {
        $count = ['목' => 0, '화' => 0, '토' => 0, '금' => 0, '수' => 0];
        $pillars = ['year_h', 'year_e', 'month_h', 'month_e', 'day_h', 'day_e', 'hour_h', 'hour_e'];

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
     * 60갑자를 납음오행(納音五行)으로 변환합니다.
     *
     * @param string $ganji (예: "甲子")
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
     *
     * @param array $ohaengCount
     * @return string
     */
    public function getNeededElement(array $ohaengCount): string
    {
        asort($ohaengCount);
        return key($ohaengCount);
    }

}
