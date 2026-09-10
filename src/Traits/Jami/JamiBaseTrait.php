<?php

namespace Pondol\Fortune\Traits\Jami;

trait JamiBaseTrait
{
    /**
     * 기초 명반 및 12궁의 천간을 생성합니다. (오소법 기반)
     */
    protected function basic($year_h): array
    {
        $map = [
            '甲' => ['丙寅', '丁卯', '戊辰', '己巳', '庚午', '辛未', '壬申', '癸酉', '甲戌', '乙亥', '丙子', '丁丑'],
            '己' => ['丙寅', '丁卯', '戊辰', '己巳', '庚午', '辛未', '壬申', '癸酉', '甲戌', '乙亥', '丙子', '丁丑'],
            '乙' => ['戊寅', '己卯', '庚辰', '辛巳', '壬午', '癸未', '甲申', '乙酉', '丙戌', '丁亥', '戊子', '己丑'],
            '庚' => ['戊寅', '己卯', '庚辰', '辛巳', '壬午', '癸未', '甲申', '乙酉', '丙戌', '丁亥', '戊子', '己丑'],
            '丙' => ['庚寅', '辛卯', '壬辰', '癸巳', '甲午', '乙未', '丙申', '丁酉', '戊戌', '己亥', '庚子', '辛丑'],
            '辛' => ['庚寅', '辛卯', '壬辰', '癸巳', '甲午', '乙未', '丙申', '丁酉', '戊戌', '己亥', '庚子', '辛丑'],
            '丁' => ['壬寅', '癸卯', '甲辰', '乙巳', '丙午', '丁未', '戊申', '己酉', '庚戌', '辛亥', '壬子', '癸丑'],
            '壬' => ['壬寅', '癸卯', '甲辰', '乙巳', '丙午', '丁未', '戊申', '己酉', '庚戌', '辛亥', '壬子', '癸丑'],
            '戊' => ['甲寅', '乙卯', '丙辰', '丁巳', '戊午', '己未', '庚申', '辛酉', '壬戌', '癸亥', '甲子', '乙丑'],
            '癸' => ['甲寅', '乙卯', '丙辰', '丁巳', '戊午', '己未', '庚申', '辛酉', '壬戌', '癸亥', '甲子', '乙丑'],
        ];

        return $map[$year_h] ?? array_fill(0, 12, null);
    }

    /**
     * 명궁(命宮)과 신궁(身宮)의 위치를 구합니다.
     */
    protected function myungsin($hour_e, $month): array
    {
        $myung = array_fill(0, 12, null);
        $sin = array_fill(0, 12, null);

        $m_k = match ($hour_e) {
            '子' => $month + 11, '丑' => $month + 10, '寅' => $month + 9, '卯' => $month + 8,
            '辰' => $month + 7,  '巳' => $month + 6,  '午' => $month + 5, '未' => $month + 4,
            '申' => $month + 3,  '酉' => $month + 2,  '戌' => $month + 1, '亥' => $month,
            default => 0
        };

        $s_k = match ($hour_e) {
            '子' => $month + 11, '丑' => $month,      '寅' => $month + 1, '卯' => $month + 2,
            '辰' => $month + 3,  '巳' => $month + 4,  '午' => $month + 5, '未' => $month + 6,
            '申' => $month + 7,  '酉' => $month + 8,  '戌' => $month + 9, '亥' => $month + 10,
            default => 0
        };

        $m_k = $m_k % 12;
        $s_k = $s_k % 12;
        $myung[$m_k] = '명';
        $sin[$s_k] = '신';

        return ['myung' => $myung, 'sin' => $sin];
    }

    /**
     * 12궁(사항궁)을 명궁 기준으로 배치합니다.
     */
    protected function gung($myung): array
    {
        $gung = array_fill(0, 12, null);
        $gung12 = ['父母', '福德', '田宅', '官祿', '奴僕', '遷移', '疾厄', '財帛', '子女', '夫妻', '兄弟'];

        $myung_index = array_search('명', $myung);
        if ($myung_index === false) {
            return $gung;
        }

        for ($i = 0; $i < 11; $i++) {
            $palace_index = ($myung_index - 1 - $i + 12) % 12;
            $gung[$palace_index] = $gung12[$i];
        }

        return $gung;
    }

    /**
     * 양남, 음남, 양녀, 음녀를 구분합니다.
     */
    protected function yangum($gender, $year_h): string
    {
        $isYangGan = in_array($year_h, ['甲', '丙', '戊', '庚', '壬']);
        if ($gender === 'M') {
            return $isYangGan ? '양남' : '음남';
        }

        return $isYangGan ? '양녀' : '음녀';
    }

    /**
     * 특정 궁의 주성 및 매핑 정보를 가져오는 핵심 메소드
     * (사용자님 요청에 따라 stars_with_sihua 변수명 적용)
     */
    protected function getPalaceInfo($jamidusu, $palace_offset, $targetYearH = null): object
    {
        $myung_index = array_search('명', $jamidusu->myung);
        if ($myung_index === false) {
            return (object) ['gung' => null, 'jusung14' => null, 'stars_with_sihua' => null];
        }

        $palace_index = ($myung_index + $palace_offset + 12) % 12;
        $palaceOrder = ['寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥', '子', '丑'];
        $gung = $palaceOrder[$palace_index];

        $jusung14 = $this->jusung14($palace_index, $jamidusu->jusung14);

        // 차성안궁 로직
        if (! $jusung14) {
            $opposite_palace_index = ($palace_index + 6) % 12;
            $jusung14 = $this->jusung14($opposite_palace_index, $jamidusu->jusung14);
        }

        // 사화 결합 로직 (JamiStarTrait에서 제공 예정)
        $starsWithSihua = $jusung14;
        if ($targetYearH && method_exists($this, 'attachSihua')) {
            $starsWithSihua = $this->attachSihua($jusung14, $targetYearH);
        }

        return (object) [
            'gung' => $gung,
            'jusung14' => $jusung14,
            'stars_with_sihua' => $starsWithSihua,
        ];
    }

    /**
     * 특정 위치에 있는 모든 주성을 합칩니다.
     */
    protected function jusung14($k, $goong): string
    {
        $res = '';
        $stars = ['taeum', 'tamrang', 'geamun', 'cheansang', 'cheanryang', 'chilsal', 'pagun', 'yeamjung', 'chendong', 'mugok', 'taeyang', 'chengi', 'cheanbu', 'jami'];
        foreach ($stars as $s) {
            if (! empty($goong[$s][$k])) {
                $res .= $goong[$s][$k];
            }
        }

        return $res;
    }
}
