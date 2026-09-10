<?php

namespace Pondol\Fortune\Traits\Jami;

trait JamiStarTrait
{
    /**
     **************************************************주성계
     */
    /**
     * 1. 자미성(紫微) 위치 계산
     */
    protected function jami($myung_guk, $umday): array
    {
        $jami = array_fill(0, 12, null);
        $jami_keys = match ($myung_guk) {
            '木3局' => [2, 11, 0, 3, 0, 1, 4, 1, 2, 5, 2, 3, 6, 3, 4, 7, 4, 5, 8, 5, 6, 9, 6, 7, 10, 7, 8, 11, 8, 9],
            '火6局' => [7, 4, 9, 2, 11, 0, 8, 5, 10, 3, 0, 1, 9, 6, 11, 4, 1, 2, 10, 7, 0, 5, 2, 3, 11, 8, 1, 6, 3, 4],
            '土5局' => [4, 9, 2, 11, 0, 5, 10, 3, 0, 1, 6, 11, 4, 1, 2, 7, 0, 5, 2, 3, 8, 1, 6, 3, 4, 9, 2, 7, 4, 5],
            '金4局' => [9, 2, 11, 0, 10, 3, 0, 1, 11, 4, 1, 2, 0, 5, 2, 3, 1, 6, 3, 4, 2, 7, 4, 5, 3, 8, 5, 6, 4, 9],
            '水2局' => [11, 0, 0, 1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9, 9, 10, 10, 11, 11, 0, 0, 1, 1, 2],
            default => array_fill(0, 12, 10)
        };
        $key = (int) $umday - 1;
        $jami[$jami_keys[$key]] = '자미';

        return $jami;
    }

    /**
     * 자미성계 별들 (천기, 태양, 무곡, 천동, 염정) 배치
     */
    protected function jamis($jami): array
    {
        $res = ['cheanbu' => [], 'chengi' => [], 'taeyang' => [], 'mugok' => [], 'chendong' => [], 'yeamjung' => []];
        foreach ($res as $k => $v) {
            $res[$k] = array_fill(0, 12, null);
        }
        foreach ($jami as $k => $v) {
            if ($v) {
                $res['cheanbu'][(12 - $k) % 12] = '천부';
                $res['chengi'][($k + 11) % 12] = '천기';
                $res['taeyang'][($k + 9) % 12] = '태양';
                $res['mugok'][($k + 8) % 12] = '무곡';
                $res['chendong'][($k + 7) % 12] = '천동';
                $res['yeamjung'][($k + 4) % 12] = '염정';
            }
        }

        return $res;
    }

    /**
     * 천부성계 별들 (태음, 탐랑, 거문, 천상, 천량, 칠살, 파군) 배치
     */
    protected function cheanbus($cheanbu): array
    {
        $res = ['taeum' => [], 'tamrang' => [], 'geamun' => [], 'cheansang' => [], 'cheanryang' => [], 'chilsal' => [], 'pagun' => []];
        foreach ($res as $k => $v) {
            $res[$k] = array_fill(0, 12, null);
        }
        foreach ($cheanbu as $k => $v) {
            if ($v) {
                $res['taeum'][($k + 1) % 12] = '태음';
                $res['tamrang'][($k + 2) % 12] = '탐랑';
                $res['geamun'][($k + 3) % 12] = '거문';
                $res['cheansang'][($k + 4) % 12] = '천상';
                $res['cheanryang'][($k + 5) % 12] = '천량';
                $res['chilsal'][($k + 6) % 12] = '칠살';
                $res['pagun'][($k + 10) % 12] = '파군';
            }
        }

        return $res;
    }

    /**
     **************************************************살성/길성:
     */

    /**
     * 녹존, 경양, 타라 배치
     */
    protected function nokGungTara($year_h): array
    {
        $pos = match ($year_h) {
            '甲' => [0, 1, 11], '乙' => [1, 2, 0], '丙','戊' => [3, 4, 2],
            '丁','己' => [4, 5, 3], '庚' => [6, 7, 5], '辛' => [7, 8, 6],
            '壬' => [9, 10, 8], '癸' => [10, 11, 9], default => [0, 0, 0]
        };
        $res = ['nokjon' => array_fill(0, 12, null), 'gyungryang' => array_fill(0, 12, null), 'tara' => array_fill(0, 12, null)];
        $res['nokjon'][$pos[0]] = '녹존';
        $res['gyungryang'][$pos[1]] = '경양';
        $res['tara'][$pos[2]] = '타라';

        return $res;
    }

    /** 살성 중 [화성, 영성] 구하기 */
    private function whasunYeungsung($year_e, $hour_e)
    {
        $whasung = array_fill(0, 12, null);
        $yeungsung = array_fill(0, 12, null);

        // 그룹별 시작 위치를 명확히 정의
        $pos = match (true) {
            str_contains('寅午戌', $year_e) => ['w' => 11, 'y' => 1],
            str_contains('申子辰', $year_e) => ['w' => 0, 'y' => 8],
            str_contains('巳酉丑', $year_e) => ['w' => 1, 'y' => 8],
            str_contains('亥卯未', $year_e) => ['w' => 8, 'y' => 8],
            default => ['w' => 0, 'y' => 0]
        };

        $hourOffsets = ['子' => 0, '丑' => 1, '寅' => 2, '卯' => 3, '辰' => 4, '巳' => 5, '午' => 6, '未' => 7, '申' => 8, '酉' => 9, '戌' => 10, '亥' => 11];
        $off = $hourOffsets[$hour_e] ?? 0;

        $whasung[($pos['w'] + $off) % 12] = '화성';
        $yeungsung[($pos['y'] + $off) % 12] = '영성';

        return ['whasung' => $whasung, 'yeungsung' => $yeungsung];
    }

    /** 살성 중 [지공, 지겁] 구하기 */
    private function jigongJigup($hour_e)
    {
        $map = [
            '子' => [9, 9], '丑' => [8, 10], '寅' => [7, 11], '卯' => [6, 0], '辰' => [5, 1], '巳' => [4, 2],
            '午' => [3, 3], '未' => [2, 4], '申' => [1, 5], '酉' => [0, 6], '戌' => [11, 7], '亥' => [10, 8],
        ];
        $res = ['jigong' => array_fill(0, 12, null), 'jigup' => array_fill(0, 12, null)];
        if (isset($map[$hour_e])) {
            $res['jigong'][$map[$hour_e][0]] = '지공';
            $res['jigup'][$map[$hour_e][1]] = '지겁';
        }

        return $res;
    }

    /**
     * 생시를 이용하여 문창을 구한다.
     */
    private function munchang_e($hour_e)
    {
        $map = [
            '子' => self::GUNG_SUL, '丑' => self::GUNG_YU, '寅' => self::GUNG_SIN, '卯' => self::GUNG_MI,
            '辰' => self::GUNG_O, '巳' => self::GUNG_SA, '午' => self::GUNG_JIN, '未' => self::GUNG_MYO,
            '申' => self::GUNG_IN, '酉' => self::GUNG_CHUK, '戌' => self::GUNG_JA, '亥' => self::GUNG_HAE,
        ];

        return $this->placeStarByMap('문창', $map, $hour_e);
    }

    /**
     * 생시를 이용하여 문곡을 구한다.
     */
    private function mungok_e($hour_e)
    {
        $map = [
            '子' => self::GUNG_JIN, '丑' => self::GUNG_SA, '寅' => self::GUNG_O, '卯' => self::GUNG_MI,
            '辰' => self::GUNG_SIN, '巳' => self::GUNG_YU, '午' => self::GUNG_SUL, '未' => self::GUNG_HAE,
            '申' => self::GUNG_JA, '酉' => self::GUNG_CHUK, '戌' => self::GUNG_IN, '亥' => self::GUNG_MYO,
        ];

        return $this->placeStarByMap('문곡', $map, $hour_e);
    }

    /**
     * 음력 생월을 이용하여 좌보를 구한다.
     */
    private function jabo($lunar_month)
    {
        $map = [
            '01' => self::GUNG_JIN, '02' => self::GUNG_SA, '03' => self::GUNG_O, '04' => self::GUNG_MI,
            '05' => self::GUNG_SIN, '06' => self::GUNG_YU, '07' => self::GUNG_SUL, '08' => self::GUNG_HAE,
            '09' => self::GUNG_JA, '10' => self::GUNG_CHUK, '11' => self::GUNG_IN, '12' => self::GUNG_MYO,
        ];

        return $this->placeStarByMap('좌보', $map, $lunar_month);
    }

    /**
     * 음력 생월을 이용하여 우필을 구한다.
     */
    private function upil($lunar_month)
    {
        $map = [
            '01' => self::GUNG_SUL, '02' => self::GUNG_YU, '03' => self::GUNG_SIN, '04' => self::GUNG_MI,
            '05' => self::GUNG_O, '06' => self::GUNG_SA, '07' => self::GUNG_JIN, '08' => self::GUNG_MYO,
            '09' => self::GUNG_IN, '10' => self::GUNG_CHUK, '11' => self::GUNG_JA, '12' => self::GUNG_HAE,
        ];

        return $this->placeStarByMap('우필', $map, $lunar_month);
    }

    /**
     **************************************************잡성(총 30여개)
     */

    /**
     * 천마
     */
    private function cheanma($year_e)
    {
        $m = ['寅' => 6, '午' => 6, '戌' => 6, '申' => 0, '子' => 0, '辰' => 0, '巳' => 9, '酉' => 9, '丑' => 9, '亥' => 3, '卯' => 3, '未' => 3];

        return $this->placeStarByMap('천마', $m, $year_e);
    }

    // # 천형/천요/해신/연해/천월/음살/천무
    /** 천형 */
    private function cheanhyung($lunar_month)
    {
        $m = ['01' => 7, '02' => 8, '03' => 9, '04' => 10, '05' => 11, '06' => 0, '07' => 1, '08' => 2, '09' => 3, '10' => 4, '11' => 5, '12' => 6];

        return $this->placeStarByMap('천형', $m, $lunar_month);
    }

    /** 천요 */
    private function cheanyo($lunar_month)
    {
        $m = ['01' => 11, '02' => 0, '03' => 1, '04' => 2, '05' => 3, '06' => 4, '07' => 5, '08' => 6, '09' => 7, '10' => 8, '11' => 9, '12' => 10];

        return $this->placeStarByMap('천요', $m, $lunar_month);
    }

    /** 해신 */
    private function haesin($lunar_month)
    {
        $map = [
            '01' => self::GUNG_SIN, '02' => self::GUNG_SIN, '03' => self::GUNG_SUL, '04' => self::GUNG_SUL,
            '05' => self::GUNG_JA, '06' => self::GUNG_JA, '07' => self::GUNG_IN, '08' => self::GUNG_IN,
            '09' => self::GUNG_JIN, '10' => self::GUNG_JIN, '11' => self::GUNG_O, '12' => self::GUNG_O,
        ];

        return $this->placeStarByMap('해신', $map, $lunar_month);
    }

    /** 음살 */
    private function eumsal($lunar_month)
    {
        $map = [
            '01' => self::GUNG_IN, '02' => self::GUNG_JA, '03' => self::GUNG_SUL, '04' => self::GUNG_SIN,
            '05' => self::GUNG_O, '06' => self::GUNG_JIN, '07' => self::GUNG_IN, '08' => self::GUNG_JA,
            '09' => self::GUNG_SUL, '10' => self::GUNG_SIN, '11' => self::GUNG_O, '12' => self::GUNG_JIN,
        ];

        return $this->placeStarByMap('음살', $map, $lunar_month);
    }

    /** 천무 */
    private function cheanmu($month_e)
    {
        $target = match (true) {
            str_contains('寅午戌', $month_e) => self::GUNG_SA,
            str_contains('申子辰', $month_e) => self::GUNG_IN,
            str_contains('巳酉丑', $month_e) => self::GUNG_HAE,
            str_contains('亥卯未', $month_e) => self::GUNG_SIN,
            default => 0
        };
        $res = array_fill(0, 12, null);
        $res[$target] = '천무';

        return $res;
    }

    /**
     * 은광구하기
     */
    private function eunkwang($munchang, $lunar_day)
    {
        return $this->calculateDayOffsetStar($munchang, $lunar_day, '은광', true);
    }

    /**
     * 천귀 구하기
     */
    private function cheungui($mungok_e, $lunar_day)
    {
        $lunar_day_temp = (int) $lunar_day % 12;
        $cheungui = array_fill(0, 12, '');
        $index = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        foreach ($mungok_e as $k => $v) {
            if ($v) {

                $start_index = $k - 1;
                if ($start_index < 0) {
                    $start_index = 11;
                }

                foreach ($index as $k1 => $v1) {
                    $index[$k1] = ($start_index + $k1) % 12;
                }

                switch ($lunar_day_temp) {
                    case 1: $cheungui[$index[0]] = '천귀';
                        break;
                    case 2: $cheungui[$index[1]] = '천귀';
                        break;
                    case 3: $cheungui[$index[2]] = '천귀';
                        break;
                    case 4: $cheungui[$index[3]] = '천귀';
                        break;
                    case 5: $cheungui[$index[4]] = '천귀';
                        break;
                    case 6: $cheungui[$index[5]] = '천귀';
                        break;
                    case 7: $cheungui[$index[6]] = '천귀';
                        break;
                    case 8: $cheungui[$index[7]] = '천귀';
                        break;
                    case 9: $cheungui[$index[8]] = '천귀';
                        break;
                    case 10: $cheungui[$index[9]] = '천귀';
                        break;
                    case 11: $cheungui[$index[10]] = '천귀';
                        break;
                    case 0: $cheungui[$index[11]] = '천귀';
                        break;
                }
            }
        }

        return $cheungui;

    }

    /**
     * 삼태구하기
     */
    private function samtae($jabo, $lunar_day)
    {
        return $this->calculateDayOffsetStar($jabo, $lunar_day, '삼태');
    }

    /**
     * 팔좌 구하기
     */
    private function paljoa($upil, $lunar_day)
    {
        $lunar_day_temp = (int) $lunar_day % 12;

        $paljoa = array_fill(0, 12, '');

        $default = [0, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];
        foreach ($upil as $k => $v) {
            if ($v) {

                $start_index = $k;
                $index = $default;
                for ($i = 0; $i < $k; $i++) {
                    // $index_k = array_shift($index);
                    $index_k = array_pop($index); // 마지막깞 빼기
                    array_unshift($index, $index_k); // 마지막값을 맨 앞으로 넣기

                }

                switch ($lunar_day_temp) {
                    case 1: $paljoa[$index[0]] = '팔좌';
                        break;
                    case 2: $paljoa[$index[1]] = '팔좌';
                        break;
                    case 3: $paljoa[$index[2]] = '팔좌';
                        break;
                    case 4: $paljoa[$index[3]] = '팔좌';
                        break;
                    case 5: $paljoa[$index[4]] = '팔좌';
                        break;
                    case 6: $paljoa[$index[5]] = '팔좌';
                        break;
                    case 7: $paljoa[$index[6]] = '팔좌';
                        break;
                    case 8: $paljoa[$index[7]] = '팔좌';
                        break;
                    case 9: $paljoa[$index[8]] = '팔좌';
                        break;
                    case 10: $paljoa[$index[9]] = '팔좌';
                        break;
                    case 11: $paljoa[$index[10]] = '팔좌';
                        break;
                    case 0: $paljoa[$index[11]] = '팔좌';
                        break;
                }
            }
        }

        return $paljoa;
    }

    /** 천월 */
    private function chenwol($lunar_month)
    {
        $m = ['01' => 8, '02' => 3, '03' => 2, '04' => 0, '05' => 5, '06' => 1, '07' => 9, '08' => 5, '09' => 0, '10' => 4, '11' => 8, '12' => 0];

        return $this->placeStarByMap('천월', $m, $lunar_month);
    }

    /** 연해 */
    private function yeanhae($year_e)
    {
        $m = ['子' => 9, '丑' => 8, '寅' => 7, '卯' => 6, '辰' => 5, '巳' => 4, '午' => 3, '未' => 2, '申' => 1, '酉' => 0, '戌' => 11, '亥' => 10];

        return $this->placeStarByMap('연해', $m, $year_e);
    }

    /** 순공 */
    private function sungong($year_h, $year_e)
    {
        $yh = $year_h.$year_e;
        $m = [
            '甲子' => 8, '丙寅' => 8, '戊辰' => 8, '庚午' => 8, '壬申' => 8, '乙丑' => 9, '丁卯' => 9, '己巳' => 9, '辛未' => 9, '癸酉' => 9,
            '甲戌' => 6, '丙子' => 6, '戊寅' => 6, '庚辰' => 6, '壬午' => 6, '乙亥' => 7, '丁丑' => 7, '己卯' => 7, '辛巳' => 7, '癸未' => 7,
            '甲申' => 4, '丙戌' => 4, '戊子' => 4, '庚寅' => 4, '壬辰' => 4, '乙酉' => 5, '丁亥' => 5, '己丑' => 5, '辛卯' => 5, '癸巳' => 5,
            '甲午' => 2, '丙申' => 2, '戊戌' => 2, '庚子' => 2, '壬寅' => 2, '乙未' => 3, '丁酉' => 3, '己亥' => 3, '辛丑' => 3, '癸卯' => 3,
            '甲辰' => 0, '丙午' => 0, '戊申' => 0, '庚戌' => 0, '壬子' => 0, '乙巳' => 1, '丁未' => 1, '己酉' => 1, '辛亥' => 1, '癸丑' => 1,
            '甲寅' => 10, '丙辰' => 10, '戊午' => 10, '庚申' => 10, '壬戌' => 10, '乙卯' => 11, '丁巳' => 11, '己未' => 11, '辛酉' => 11, '癸亥' => 11,
        ];

        return $this->placeStarByMap('순공', $m, $yh);
    }

    /** 절공 */
    private function jealgong($year_h)
    {
        $m = ['甲' => 6, '乙' => 4, '丙' => 2, '丁' => 1, '戊' => 10, '己' => 7, '庚' => 4, '辛' => 3, '壬' => 0, '癸' => 11];

        return $this->placeStarByMap('절공', $m, $year_h);
    }

    /** 천관 */
    private function cheanguan($year_h)
    {
        $m = ['甲' => 5, '乙' => 2, '丙' => 3, '丁' => 9, '戊' => 1, '己' => 7, '庚' => 9, '辛' => 7, '壬' => 8, '癸' => 4];

        return $this->placeStarByMap('천관', $m, $year_h);
    }

    /** 천복 */
    private function cheanbok($year_h)
    {
        $m = ['甲' => 7, '乙' => 6, '丙' => 10, '丁' => 9, '戊' => 1, '己' => 0, '庚' => 4, '辛' => 3, '壬' => 4, '癸' => 3];

        return $this->placeStarByMap('천복', $m, $year_h);
    }

    /** 화개 */
    private function hwagae($year_e)
    {
        $m = ['寅' => 8, '午' => 8, '戌' => 8, '申' => 2, '子' => 2, '辰' => 2, '巳' => 11, '酉' => 11, '丑' => 11, '亥' => 5, '卯' => 5, '未' => 5];

        return $this->placeStarByMap('화개', $m, $year_e);
    }

    /** 겁살 (장성12신 중 하나) */
    private function guepsal($year_e)
    {
        $map = [
            '寅' => self::GUNG_HAE, '午' => self::GUNG_HAE, '戌' => self::GUNG_HAE,
            '申' => self::GUNG_SA, '子' => self::GUNG_SA, '辰' => self::GUNG_SA,
            '巳' => self::GUNG_IN, '酉' => self::GUNG_IN, '丑' => self::GUNG_IN,
            '亥' => self::GUNG_SIN, '卯' => self::GUNG_SIN, '未' => self::GUNG_SIN,
        ];

        return $this->placeStarByMap('겁살', $map, $year_e);
    }

    /**
     * 함지 구하기
     */
    private function hamji($year_e)
    {
        $m = ['寅' => 1, '午' => 1, '戌' => 1, '申' => 7, '子' => 7, '辰' => 7, '巳' => 4, '酉' => 4, '丑' => 4, '亥' => 0, '卯' => 0, '未' => 0];

        return $this->placeStarByMap('함지', $m, $year_e);
    }

    /** 과숙 */
    private function guasuck($year_e)
    {
        $m = ['寅' => 11, '卯' => 11, '辰' => 11, '巳' => 2, '午' => 2, '未' => 2, '申' => 5, '酉' => 5, '戌' => 5, '亥' => 8, '子' => 8, '丑' => 8];

        return $this->placeStarByMap('과숙', $m, $year_e);
    }

    /** 고진 */
    private function gojin($year_e)
    {
        $m = ['寅' => 3, '卯' => 3, '辰' => 3, '巳' => 6, '午' => 6, '未' => 6, '申' => 9, '酉' => 9, '戌' => 9, '亥' => 0, '子' => 0, '丑' => 0];

        return $this->placeStarByMap('고진', $m, $year_e);
    }

    /** 천희 */
    private function cheanhee($year_e)
    {
        $m = ['子' => 7, '丑' => 6, '寅' => 5, '卯' => 4, '辰' => 3, '巳' => 2, '午' => 1, '未' => 0, '申' => 11, '酉' => 10, '戌' => 9, '亥' => 8];

        return $this->placeStarByMap('천희', $m, $year_e);
    }

    /** 홍란 */
    private function hongran($year_e)
    {
        $m = ['子' => 1, '丑' => 0, '寅' => 11, '卯' => 10, '辰' => 9, '巳' => 8, '午' => 7, '未' => 6, '申' => 5, '酉' => 4, '戌' => 3, '亥' => 2];

        return $this->placeStarByMap('홍란', $m, $year_e);
    }

    /** 천곡 */
    private function cheangok($year_e)
    {
        $m = ['子' => 4, '丑' => 3, '寅' => 2, '卯' => 1, '辰' => 0, '巳' => 11, '午' => 10, '未' => 9, '申' => 8, '酉' => 7, '戌' => 6, '亥' => 5];

        return $this->placeStarByMap('천곡', $m, $year_e);
    }

    // # cheanhue/cheangok/hongran/cheanhee
    /** 천허 */
    private function cheanhue($year_e)
    {
        $m = ['子' => 4, '丑' => 5, '寅' => 6, '卯' => 7, '辰' => 8, '巳' => 9, '午' => 10, '未' => 11, '申' => 0, '酉' => 1, '戌' => 2, '亥' => 3];

        return $this->placeStarByMap('천허', $m, $year_e);
    }

    /**
     * 천수
     *
     * @param  $year_e  : 생년지
     */
    private function cheansu($sin, $year_e)
    {
        $k = array_search('신', $sin);
        $m = ['子' => 0, '丑' => 1, '寅' => 2, '卯' => 3, '辰' => 4, '巳' => 5, '午' => 6, '未' => 7, '申' => 8, '酉' => 9, '戌' => 10, '亥' => 11];
        $res = array_fill(0, 12, null);
        if ($k !== false) {
            $res[($k + $m[$year_e]) % 12] = '천수';
        }

        return $res;
    }

    /**
     *천재
     *
     * @param  $year_e  생년지
     */
    private function cheanjae($myung, $year_e)
    {
        $k = array_search('명', $myung);
        $m = ['子' => 0, '丑' => 1, '寅' => 2, '卯' => 3, '辰' => 4, '巳' => 5, '午' => 6, '未' => 7, '申' => 8, '酉' => 9, '戌' => 10, '亥' => 11];
        $res = array_fill(0, 12, null);
        if ($k !== false) {
            $res[($k + $m[$year_e]) % 12] = '천재';
        }

        return $res;
    }

    /** 봉각 */
    private function bonggak($year_e)
    {
        $map = [
            '子' => self::GUNG_SUL, '丑' => self::GUNG_YU, '寅' => self::GUNG_SIN, '卯' => self::GUNG_MI,
            '辰' => self::GUNG_O, '巳' => self::GUNG_SA, '午' => self::GUNG_JIN, '未' => self::GUNG_MYO,
            '申' => self::GUNG_IN, '酉' => self::GUNG_CHUK, '戌' => self::GUNG_JA, '亥' => self::GUNG_HAE,
        ];

        return $this->placeStarByMap('봉각', $map, $year_e);
    }

    /** 용지 */
    private function yongji($year_e)
    {
        $map = [
            '子' => self::GUNG_JIN, '丑' => self::GUNG_SA, '寅' => self::GUNG_O, '卯' => self::GUNG_MI,
            '辰' => self::GUNG_SIN, '巳' => self::GUNG_YU, '午' => self::GUNG_SUL, '未' => self::GUNG_HAE,
            '申' => self::GUNG_JA, '酉' => self::GUNG_CHUK, '戌' => self::GUNG_IN, '亥' => self::GUNG_MYO,
        ];

        return $this->placeStarByMap('용지', $map, $year_e);
    }

    /**
     * 천사
     */
    private function chensa($gung)
    {
        $k = array_search('疾厄', $gung);
        $res = array_fill(0, 12, null);
        if ($k !== false) {
            $res[$k] = '천사';
        }

        return $res;
    }

    /**
     * 천상
     */
    private function chensang($gung)
    {
        $k = array_search('奴僕', $gung);
        $res = array_fill(0, 12, null);
        if ($k !== false) {
            $res[$k] = '천상';
        }

        return $res;
    }

    /** 월덕 */
    private function wolduk($year_e)
    {
        $m = ['子' => 3, '丑' => 4, '寅' => 5, '卯' => 6, '辰' => 7, '巳' => 8, '午' => 9, '未' => 10, '申' => 11, '酉' => 0, '戌' => 1, '亥' => 2];

        return $this->placeStarByMap('월덕', $m, $year_e);
    }

    /** 천덕 */
    private function cheanduk($year_e)
    {
        $m = ['子' => 7, '丑' => 8, '寅' => 9, '卯' => 10, '辰' => 11, '巳' => 0, '午' => 1, '未' => 2, '申' => 3, '酉' => 4, '戌' => 5, '亥' => 6];

        return $this->placeStarByMap('천덕', $m, $year_e);
    }

    /** 파쇄 */
    private function pase($year_e)
    {
        $m = ['子' => 3, '丑' => 11, '寅' => 7, '卯' => 3, '辰' => 11, '巳' => 7, '午' => 3, '未' => 11, '申' => 7, '酉' => 3, '戌' => 11, '亥' => 7];

        return $this->placeStarByMap('파쇄', $m, $year_e);
    }

    /** 대모 */
    private function daemo($year_e)
    {
        $m = ['子' => 5, '丑' => 4, '寅' => 7, '卯' => 6, '辰' => 9, '巳' => 8, '午' => 11, '未' => 10, '申' => 1, '酉' => 0, '戌' => 3, '亥' => 2];

        return $this->placeStarByMap('대모', $m, $year_e);
    }

    /** 천공 */
    private function cheangong($year_e)
    {
        $m = ['子' => 11, '丑' => 0, '寅' => 1, '卯' => 2, '辰' => 3, '巳' => 4, '午' => 5, '未' => 6, '申' => 7, '酉' => 8, '戌' => 9, '亥' => 10];

        return $this->placeStarByMap('천공', $m, $year_e);
    }

    /**
     * 봉고
     */
    private function bonggo($mungok)
    {
        $k = array_search('문곡', $mungok);
        $res = array_fill(0, 12, null);
        if ($k !== false) {
            $res[($k + 10) % 12] = '봉고';
        }

        return $res;
    }

    /**
     * 태보
     */
    private function taebo($mungok)
    {
        $k = array_search('문곡', $mungok);
        $res = array_fill(0, 12, null);
        if ($k !== false) {
            $res[($k + 2) % 12] = '태보';
        }

        return $res;
    }

    /** 천주 */
    private function cheanju($year_h)
    {
        $m = ['甲' => 3, '乙' => 4, '丙' => 10, '丁' => 3, '戊' => 4, '己' => 6, '庚' => 0, '辛' => 4, '壬' => 7, '癸' => 9];

        return $this->placeStarByMap('천주', $m, $year_h);
    }

    /** 홍염 */
    private function hongyeam($year_h)
    {
        $m = ['甲' => 4, '乙' => 6, '丙' => 0, '丁' => 5, '戊' => 2, '己' => 2, '庚' => 8, '辛' => 7, '壬' => 10, '癸' => 6];

        return $this->placeStarByMap('홍염', $m, $year_h);
    }

    /** 비렴 */
    private function biryeum($year_e)
    {
        $m = ['子' => 6, '丑' => 7, '寅' => 8, '卯' => 3, '辰' => 4, '巳' => 5, '午' => 0, '未' => 1, '申' => 2, '酉' => 9, '戌' => 10, '亥' => 11];

        return $this->placeStarByMap('비렴', $m, $year_e);
    }

    /**
     **************************************************신살계
     */

    /**
     * 박사12신
     */
    private function baksa($nokjon, $yangum)
    {
        $baksaArr = ['박사', '역사', '청룡', '소모', '장군', '주서', '비렴', '희신', '병부', '대모', '복병', '관부'];
        $baksa = array_fill(0, 12, null);
        $start_pos = array_search('녹존', $nokjon);

        if ($start_pos !== false) {
            $isForward = in_array($yangum, ['양남', '음녀']);
            foreach ($baksaArr as $i => $name) {
                $idx = $isForward ? ($start_pos + $i) % 12 : ($start_pos - $i + 12) % 12;
                $baksa[$idx] = $name;
            }
        }

        return $baksa;
    }

    /**
     * 장성십이신/jangsung
     */
    private function jangsung($year_e)
    {
        $jangsungArr = ['장성', '반안', '세역', '식신', '화개', '겁살', '재살', '천살', '지배', '함지', '월살', '망신'];
        $groups = ['寅午戌' => 0, '申子辰' => 6, '巳酉丑' => 3, '亥卯未' => 9];
        $start_pos = 0;
        foreach ($groups as $jiGroup => $pos) {
            if (str_contains($jiGroup, $year_e)) {
                $start_pos = $pos;
                break;
            }
        }

        $jangsung = array_fill(0, 12, null);
        foreach ($jangsungArr as $i => $name) {
            $jangsung[($start_pos + $i) % 12] = $name;
        }

        return $jangsung;
    }

    /**
     * 생년태세12신
     */
    private function taese($year_e)
    {
        $taeseArr = ['태세', '태양', '상문', '태음', '관부', '사부', '세파', '용덕', '백호', '복덕', '조객', '병부'];
        $jiOrder = ['寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥', '子', '丑'];
        $start_idx = array_search($year_e, $jiOrder);

        $taese = array_fill(0, 12, null);
        foreach ($taeseArr as $i => $name) {
            $taese[($start_idx + $i) % 12] = $name;
        }

        return $taese;
    }

    /**
     * 십이운성베치
     */
    private function unsung($yangum, $myung_guk)
    {

        $unsungArr = ['生', '浴', '帶', '冠', '旺', '衰', '病', '死', '墓', '絶', '胎', '養'];
        $unsung = array_fill(0, 12, null);

        switch ($myung_guk) {
            case '火6局':
                switch ($yangum) {
                    case '양남': case '음녀':
                        $unsung_k = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                        break;
                    case '음남': case '양녀':
                        $unsung_k = [0, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];
                        break;
                }
                break;
            case '土5局':
                switch ($yangum) {
                    case '양남': case '음녀':
                        $unsung_k = [6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5];
                        break;
                    case '음남': case '양녀':
                        $unsung_k = [6, 5, 4, 3, 2, 1, 0, 11, 10, 9, 8, 7];
                        break;
                }
                break;
            case '金4局':
                switch ($yangum) {
                    case '양남': case '음녀':
                        $unsung_k = [3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2];
                        break;
                    case '음남': case '양녀':
                        $unsung_k = [3, 2, 1, 0, 11, 10, 9, 8, 7, 6, 5, 4];
                        break;
                }
                break;
            case '水2局':
                switch ($yangum) {
                    case '양남': case '음녀':
                        $unsung_k = [6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5];
                        break;
                    case '음남': case '양녀':
                        $unsung_k = [6, 5, 4, 3, 2, 1, 0, 11, 10, 9, 8, 7];
                        break;
                }
                break;
            case '木3局':
                switch ($yangum) {
                    case '양남': case '음녀':
                        $unsung_k = [9, 10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8];
                        break;
                    case '음남': case '양녀':
                        $unsung_k = [9, 8, 7, 6, 5, 4, 3, 2, 1, 0, 11, 10];
                        break;
                }
                break;
        }

        foreach ($unsung_k as $k => $v) {
            $unsung[$v] = $unsungArr[$k];
        }

        return $unsung;
    }

    /**
     **************************************************공통 헬퍼
     */

    /**
     * 14 정성의 묘왕리함 상태를 가져오는 범용 함수
     *
     * @param  array  $starPositions  별의 위치 배열 (예: $data->arr['jami'])
     * @param  array  $statusMap  묘왕리함 상태 매핑 배열
     */
    protected function getJungsungStatus(array $starPositions, array $statusMap): array
    {
        $result = array_fill(0, 12, null);
        foreach ($starPositions as $index => $star) {
            if ($star && isset($statusMap[$index])) {
                $result[$index] = $statusMap[$index];
            }
        }

        return $result;
    }

    /**
     * [REFACTORED HELPER]
     * 지정된 규칙(map)에 따라 별 하나를 12궁 중 한 곳에 배치합니다.
     *
     * @param  string  $starName  배치할 별의 이름 (예: '문창')
     * @param  array  $positionMap  ['입력값' => '궁 위치 상수'] 형태의 규칙 배열
     * @param  string  $key  규칙을 찾기 위한 현재 입력값 (예: '甲', '子', '01')
     * @return array 12궁에 별이 배치된 배열
     */

    /**
     * 특정 연도의 천간을 입력받아 별 이름에 사화(록, 권, 과, 기)를 결합합니다.
     */
    public function attachSihua(string $jusung, string $targetYearH): string
    {
        // 이제 JamiMappingTrait에서 상수로 정의했으므로 self:: 가 정상 작동합니다.
        if (! isset(self::SIHUA_MAP[$targetYearH])) {
            return $jusung;
        }

        $currentSihua = self::SIHUA_MAP[$targetYearH];

        foreach ($currentSihua as $suffix => $starName) {
            if (mb_strpos($jusung, $starName) !== false) {
                return $starName.$suffix;
            }
        }

        return $jusung;
    }

    /**
     * [개선] 일자 기반 별(은광, 천귀, 삼태, 팔좌)의 회전 로직 단순화
     * 복잡한 array_shift/push(배열 회전) 대신 모듈러 연산(%)으로 처리
     */
    private function calculateDayOffsetStar($baseStarArray, $lunarDay, $starName, $reverse = false)
    {
        $targetPalace = array_fill(0, 12, '');
        // array_filter 후 첫 번째 유효한 인덱스를 가져옵니다.
        $filtered = array_filter($baseStarArray);
        $baseIdx = ! empty($filtered) ? array_key_first($filtered) : false;

        if ($baseIdx === false) {
            return $targetPalace;
        }

        $dayOffset = ($lunarDay - 1) % 12;
        $finalIdx = $reverse ? ($baseIdx - $dayOffset + 12) % 12 : ($baseIdx + $dayOffset) % 12;
        $targetPalace[$finalIdx] = $starName;

        return $targetPalace;
    }

    /**
     * 원본 로직의 모든 잡성/살성 배치를 일괄 처리합니다.
     */
    protected function processExtraStars(&$data, $year_h, $year_e, $month_e, $hour_e, $lunar_month, $lunar_day, $yangum)
    {

        // 문창, 문곡, 좌보, 우필 (계산 후 즉시 할당)
        $data->arr['munchang'] = $this->munchang_e($hour_e);
        $data->arr['mungok'] = $this->mungok_e($hour_e);
        $data->arr['jabo'] = $this->jabo($lunar_month);
        $data->arr['upil'] = $this->upil($lunar_month);

        // 녹존, 경양, 타라
        $nokResult = $this->nokGungTara($year_h);
        $data->arr['nokjon'] = $nokResult['nokjon'];
        $data->arr['gyungryang'] = $nokResult['gyungryang'];
        $data->arr['tara'] = $nokResult['tara'];

        // 녹존의 묘왕리함 상태 할당 (원본 로직 유지)
        $data->arr['nokjon_14'] = $this->getJungsungStatus($data->arr['nokjon'], ['묘', '왕', '', '묘', '왕', '', '묘', '왕', '', '묘', '왕', '']);

        // 화성, 영성
        $whaYeung = $this->whasunYeungsung($year_e, $hour_e);
        $data->arr['whasung'] = $whaYeung['whasung'];
        $data->arr['yeungsung'] = $whaYeung['yeungsung'];

        // 지공, 지겁
        $jigongJigup = $this->jigongJigup($hour_e);
        $data->arr['jigong'] = $jigongJigup['jigong'];
        $data->arr['jigup'] = $jigongJigup['jigup'];

        // --- 2. 기본 잡성 배치 (기존 코드 유지) ---
        $data->arr['cheanyo'] = $this->cheanyo($lunar_month);
        $data->arr['cheanhyung'] = $this->cheanhyung($lunar_month);
        $data->arr['cheanmu'] = $this->cheanmu($month_e);
        $data->arr['eumsal'] = $this->eumsal($lunar_month);
        $data->arr['chenwol'] = $this->chenwol($lunar_month);
        $data->arr['haesin'] = $this->haesin($lunar_month);
        $data->arr['yeanhae'] = $this->yeanhae($year_e);
        $data->arr['sungong'] = $this->sungong($year_h, $year_e);
        $data->arr['jealgong'] = $this->jealgong($year_h);
        $data->arr['cheanguan'] = $this->cheanguan($year_h);
        $data->arr['cheanbok'] = $this->cheanbok($year_h);
        $data->arr['hwagae'] = $this->hwagae($year_e);
        $data->arr['guepsal'] = $this->guepsal($year_e);
        $data->arr['hamji'] = $this->hamji($year_e);
        $data->arr['guasuck'] = $this->guasuck($year_e);
        $data->arr['gojin'] = $this->gojin($year_e);
        $data->arr['cheanhee'] = $this->cheanhee($year_e);
        $data->arr['hongran'] = $this->hongran($year_e);
        $data->arr['cheangok'] = $this->cheangok($year_e);
        $data->arr['cheanhue'] = $this->cheanhue($year_e);
        $data->arr['bonggak'] = $this->bonggak($year_e);
        $data->arr['yongji'] = $this->yongji($year_e);
        $data->arr['cheanju'] = $this->cheanju($year_h);
        $data->arr['hongyeam'] = $this->hongyeam($year_h);
        $data->arr['biryeum'] = $this->biryeum($year_e);
        $data->arr['wolduk'] = $this->wolduk($year_e);
        $data->arr['cheanduk'] = $this->cheanduk($year_e);
        $data->arr['pase'] = $this->pase($year_e);
        $data->arr['daemo'] = $this->daemo($year_e);
        $data->arr['cheangong'] = $this->cheangong($year_e);

        // --- 3. 연동성 배치 (이미 앞서 기본성들이 할당되었으므로 이제 정상 작동함) ---
        $data->arr['eunkwang'] = $this->eunkwang($data->arr['munchang'], $lunar_day);
        $data->arr['cheungui'] = $this->cheungui($data->arr['mungok'], $lunar_day);
        $data->arr['samtae'] = $this->samtae($data->arr['jabo'], $lunar_day);
        $data->arr['paljoa'] = $this->paljoa($data->arr['upil'], $lunar_day);
        $data->arr['taebo'] = $this->taebo($data->arr['mungok']);
        $data->arr['bonggo'] = $this->bonggo($data->arr['mungok']);

        // --- 4. 궁 위치 기준 성 ---
        $data->arr['chensa'] = $this->chensa($data->arr['gung']);
        $data->arr['chensang'] = $this->chensang($data->arr['gung']);
        $data->arr['cheanjae'] = $this->cheanjae($data->arr['myung'], $year_e);
        $data->arr['cheansu'] = $this->cheansu($data->arr['sin'], $year_e);
    }

    /**
     * 보조성 배치용 헬퍼
     */
    protected function placeStarByMap(string $starName, array $positionMap, string $key): array
    {
        $palace = array_fill(0, 12, null);
        if (isset($positionMap[$key])) {
            $palace[$positionMap[$key]] = $starName;
        }

        return $palace;
    }
}
