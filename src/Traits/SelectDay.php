<?php

namespace Pondol\Fortune\Traits;

use Pondol\Fortune\Services\Taekil;

trait SelectDay
{
    use SinsalRules;

    /**
     * [Core Engine] 단일 사주 기반의 택일 점수 계산 통합 메서드
     */
    /**
     * [Core Engine] 3단계 레이어 기반의 택일 점수 산출 통합 메서드
     * 1단계(base)는 이미 LunarCalendar에서 계산되어 $dayData에 담겨 있습니다.
     */
    public function calculateFortune($saju, $dayData, $categoryKey, $options = [])
    {
        // [준비] 기존 LunarCalendar에서 계산된 base 항목들을 시작점으로 잡음
        $foundTitles = [];
        $totalScore = 0;

        // 판정용 컨텍스트 준비
        $context = [
            'solar' => str_replace('-', '', $dayData->solar),
            'lunar_d' => $dayData->lDay,
            'day_he' => $dayData->gabja->day->ch,
            'day_h' => mb_substr($dayData->gabja->day->ch, 0, 1),
            'day_e' => mb_substr($dayData->gabja->day->ch, 1, 1),
            'month_e' => mb_substr($dayData->gabja->month->ch, 1, 1),
            'year_e' => mb_substr($dayData->gabja->year->ch, 1, 1),
        ];

        // --- [Layer 2] 유료 카테고리 공통 데이터 (category_base) 처리 ---
        $catBaseConf = config('pondol-fortune.select_day.category_base', []);
        foreach ($catBaseConf as $key => $conf) {
            $method = "_check_{$key}";
            if (method_exists($this, $method)) {
                // 사주와 무관한 공통 항목이므로 첫 인자는 null 전달
                if ($this->$method(null, $context)) {
                    $foundTitles[$key] = $conf;
                    $totalScore += $conf['score'];
                }
            }
        }

        // --- [Layer 3] 카테고리별 특화 및 개인화 (items) 처리 ---
        $catConf = config("pondol-fortune.select_day.category.{$categoryKey}");
        if (isset($catConf['items'])) {
            foreach ($catConf['items'] as $key => $conf) {
                // 방어 코드: 필수 키 누락 시 기본값 주입
                $conf['ko'] = $conf['ko'] ?? $key;
                $conf['type'] = $conf['type'] ?? 'junglip';

                // A. 신살(Taekil)인 경우
                if (isset($conf['is_taekil']) && $conf['is_taekil']) {
                    $taekilResult = $saju->taekil()->checkDate(mb_substr($dayData->solar, 0, 10));
                    if (isset($taekilResult[$key])) {
                        $foundTitles[$key] = $conf;
                        $totalScore += $conf['score'];
                    }
                }
                // B. 그룹 항목인 경우 (is_group)
                elseif (isset($conf['is_group']) && $conf['is_group']) {
                    $method = "_group_{$key}";
                    if (method_exists($this, $method)) {
                        $subKey = $this->$method($saju, $context);
                        if ($subKey && isset($conf['items'][$subKey])) {
                            $item = $conf['items'][$subKey];
                            $foundTitles[$subKey] = $item;
                            $totalScore += $item['score'];
                        }
                    }
                }
                // C. 일반 단일 개인화 항목 (_check_)
                else {
                    $method = "_check_{$key}";
                    if (method_exists($this, $method) && $this->$method($saju, $context, $options)) {
                        $foundTitles[$key] = $conf;
                        $totalScore += $conf['score'];
                    }
                }
            }
        }

        // --- [보정] Base Override 처리 (손없는날 점수 강화 등) ---
        if (isset($catConf['base_override'])) {
            foreach ($catConf['base_override'] as $bKey => $ovr) {
                if (isset($foundTitles[$bKey])) {
                    $oldScore = $foundTitles[$bKey]['score'];
                    $newScore = $ovr['score'] ?? $oldScore;

                    $totalScore += ($newScore - $oldScore); // 차액만큼 가감
                    $foundTitles[$bKey]['score'] = $newScore;
                    if (isset($ovr['desc'])) {
                        $foundTitles[$bKey]['desc'] = $ovr['desc'];
                    }
                }
            }
        }

        // 최종 우선순위 정렬
        uasort($foundTitles, fn ($a, $b) => ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0));

        return ['total' => $totalScore, 'titles' => $foundTitles];
    }

    /**
     * [추가] 해당 년도의 대장군/삼살방 방향을 반환합니다.
     */
    public function getBadDirections($year_e)
    {
        if (in_array($year_e, ['亥', '子', '丑'])) {
            return ['daejanggun' => '서', 'samsalbang' => '남'];
        }
        if (in_array($year_e, ['寅', '卯', '辰'])) {
            return ['daejanggun' => '북', 'samsalbang' => '서'];
        }
        if (in_array($year_e, ['巳', '午', '未'])) {
            return ['daejanggun' => '동', 'samsalbang' => '북'];
        }
        if (in_array($year_e, ['申', '酉', '戌'])) {
            return ['daejanggun' => '남', 'samsalbang' => '동'];
        }

        return ['daejanggun' => '', 'samsalbang' => ''];
    }

    //
    /**
     * [추가] 생기복덕천의 산출 로직
     * 사용자의 나이와 성별에 따른 길한 지지 배열을 반환합니다.
     */
    protected function _senggiBokdukCheneu($my_age, $gender)
    {
        $res = ['senggi' => [], 'bokduk' => [], 'cheneu' => []];
        // 나이를 8로 나눈 나머지를 기준으로 순환 (전통 방식)
        $mod = $my_age % 8;

        if ($gender === 'M') {
            switch ($mod) {
                case 2: $res = ['senggi' => ['戌', '亥'], 'bokduk' => ['未', '申'], 'cheneu' => ['午']];
                    break;
                case 1: $res = ['senggi' => ['丑', '寅'], 'bokduk' => ['酉'], 'cheneu' => ['辰', '巳']];
                    break;
                case 0: $res = ['senggi' => ['卯'], 'bokduk' => ['辰', '巳'], 'cheneu' => ['酉']];
                    break;
                case 7: $res = ['senggi' => ['子'], 'bokduk' => ['午'], 'cheneu' => ['未', '申']];
                    break;
                case 6: $res = ['senggi' => ['午'], 'bokduk' => ['戌', '亥'], 'cheneu' => ['子']];
                    break;
                case 5: $res = ['senggi' => ['未', '申'], 'bokduk' => ['戌', '亥'], 'cheneu' => ['子']];
                    break;
                case 4: $res = ['senggi' => ['辰', '巳'], 'bokduk' => ['卯'], 'cheneu' => ['丑', '寅']];
                    break;
                case 3: $res = ['senggi' => ['酉'], 'bokduk' => ['丑', '寅'], 'cheneu' => ['卯']];
                    break;
            }
        } else { // 여성(W/F)
            switch ($mod) {
                case 3: $res = ['senggi' => ['戌', '亥'], 'bokduk' => ['未', '申'], 'cheneu' => ['午']];
                    break;
                case 4: $res = ['senggi' => ['丑', '寅'], 'bokduk' => ['酉'], 'cheneu' => ['辰', '巳']];
                    break;
                case 5: $res = ['senggi' => ['卯'], 'bokduk' => ['辰', '巳'], 'cheneu' => ['酉']];
                    break;
                case 6: $res = ['senggi' => ['子'], 'bokduk' => ['午'], 'cheneu' => ['未', '申']];
                    break;
                case 7: $res = ['senggi' => ['午'], 'bokduk' => ['戌', '亥'], 'cheneu' => ['子']];
                    break;
                case 0: $res = ['senggi' => ['未', '申'], 'bokduk' => ['戌', '亥'], 'cheneu' => ['子']];
                    break;
                case 1: $res = ['senggi' => ['辰', '巳'], 'bokduk' => ['卯'], 'cheneu' => ['丑', '寅']];
                    break;
                case 2: $res = ['senggi' => ['酉'], 'bokduk' => ['丑', '寅'], 'cheneu' => ['卯']];
                    break;
            }
        }

        return $res;
    }

    // --- [1. 단일 판정 메서드: _check_키이름] ---
    /**
     * [추가] 축음양불장길 판정 로직
     */
    protected function _check_chuk($saju, $ctx)
    {
        $m = $ctx['month_e'];  // 월지
        $h = $ctx['day_he'];   // 일진(60갑자)

        $chuk_map = [
            '寅' => ['丙寅', '丁卯', '丙子', '戊寅', '己卯', '戊子', '己丑', '庚寅', '辛卯', '庚子', '辛丑'],
            '卯' => ['乙丑', '丙寅', '丙子', '戊寅', '戊子', '己丑', '庚寅', '戊戌', '庚子', '庚戌'],
            '辰' => ['甲子', '乙丑', '甲戌', '丙子', '乙酉', '戊子', '己丑', '丁酉', '戊戌', '己酉'],
            '巳' => ['甲子', '甲戌', '丙子', '甲申', '乙酉', '戊子', '丙申', '丁酉', '戊戌', '戊申', '己酉'],
            '午' => ['癸酉', '甲戌', '癸未', '甲申', '乙酉', '丙申', '戊戌', '戊申'],
            '未' => ['壬申', '癸酉', '甲戌', '壬午', '癸未', '甲申', '乙酉', '甲午'],
            '申' => ['壬申', '癸酉', '壬午', '癸未', '甲申', '乙酉', '癸巳', '甲午', '乙巳'],
            '酉' => ['辛未', '壬申', '辛巳', '壬午', '癸未', '甲申', '壬辰', '癸巳', '甲午'],
            '戌' => ['庚午', '辛未', '庚辰', '辛巳', '壬午', '癸未', '辛卯', '壬辰', '癸巳', '癸卯'],
            '亥' => ['庚午', '庚辰', '辛巳', '壬午', '庚寅', '辛卯', '壬辰', '癸巳', '壬寅', '癸卯'],
            '子' => ['丁卯', '己巳', '己卯', '庚辰', '辛巳', '己丑', '庚寅', '辛卯', '壬辰', '辛丑', '壬寅', '丁巳'],
            '丑' => ['丙寅', '丁卯', '戊辰', '丙子', '戊寅', '己卯', '庚辰', '戊子', '己丑', '庚寅', '辛卯', '庚子', '辛丑', '丙辰', '丁巳', '己巳', '辛巳'],
        ];

        return isset($chuk_map[$m]) && in_array($h, $chuk_map[$m]);
    }

    /**
     * [추가] 천구(天狗) 판정 로직
     * 월지 기준으로 일지의 위치를 계산합니다.
     */
    protected function _check_chengu($saju, $ctx)
    {
        $m = $ctx['month_e']; // 월지
        $d = $ctx['day_e'];   // 일지

        // 월지 인덱스에서 2를 뺀 값이 일지 인덱스와 같으면 천구
        // (寅월 -> 子일, 卯월 -> 丑일 ...)
        $m_idx = e_to_serial($m);
        $d_idx = e_to_serial($d);

        return $d_idx === ($m_idx - 2 + 12) % 12;
    }

    protected function _check_son($saju, $ctx)
    {
        return in_array($ctx['lunar_d'] % 10, [0, 9]);
    }

    protected function _check_cheonsa($saju, $ctx)
    {
        $map = ['寅' => '戊寅', '卯' => '戊寅', '辰' => '戊寅', '巳' => '甲午', '午' => '甲午', '未' => '甲午', '申' => '戊申', '酉' => '戊申', '戌' => '戊申', '亥' => '甲子', '子' => '甲子', '丑' => '甲子'];

        return isset($map[$ctx['month_e']]) && $map[$ctx['month_e']] === $ctx['day_he'];
    }

    protected function _check_bokdan($saju, $ctx)
    {
        $list = ['甲寅', '乙卯', '庚寅', '辛卯', '戊戌', '己亥', '丙午', '丁未', '壬午', '癸未', '丙辰', '丁巳', '壬辰', '癸巳'];

        return in_array($ctx['day_he'], $list);
    }

    protected function _check_wolgi($saju, $ctx)
    {
        return in_array($ctx['lunar_d'], [5, 14, 23]);
    }

    protected function _check_indong($saju, $ctx)
    {
        return in_array($ctx['lunar_d'], [1, 8, 13, 18, 23, 24, 28]);
    }

    /**
     * [추가] 지파(地破) 판정 로직
     * 월지와 일지의 파(破) 관계를 체크합니다.
     */
    protected function _check_jipa($saju, $ctx)
    {
        $m = $ctx['month_e'];
        $d = $ctx['day_e'];

        // 지지 파(破) 관계 리스트
        $pairs = [
            '子' => '酉', '酉' => '子',
            '丑' => '辰', '辰' => '丑',
            '寅' => '亥', '亥' => '寅',
            '卯' => '午', '午' => '卯',
            '巳' => '申', '申' => '巳',
            '未' => '戌', '戌' => '未',
        ];

        return isset($pairs[$m]) && $pairs[$m] === $d;
    }

    /**
     * [추가] 고초살 판정 로직
     */
    protected function _check_gocho($saju, $ctx)
    {
        $m = $ctx['month_e'];
        $d = $ctx['day_e'];

        $map = [
            '寅' => '辰', '卯' => '丑', '辰' => '戌',
            '巳' => '未', '午' => '卯', '未' => '子',
            '申' => '酉', '酉' => '午', '戌' => '寅',
            '亥' => '亥', '子' => '申', '丑' => '巳',
        ];

        return isset($map[$m]) && $map[$m] === $d;
    }

    /**
     * [추가] 피마살 판정 로직
     */
    protected function _check_pima($saju, $ctx)
    {
        $m = $ctx['month_e'];
        $d = $ctx['day_e'];

        $map = [
            '寅' => '子', '卯' => '酉', '辰' => '午',
            '巳' => '卯', '午' => '子', '未' => '酉',
            '申' => '午', '酉' => '卯', '戌' => '子',
            '亥' => '酉', '子' => '午', '丑' => '卯',
        ];

        return isset($map[$m]) && $map[$m] === $d;
    }

    protected function _check_hague($saju, $ctx)
    {
        // Taekil 서비스의 정적 메서드를 활용 (월지와 일지만 전달)
        return Taekil::hasHague($ctx['month_e'], $ctx['day_e']);
    }

    protected function _check_haeil($saju, $ctx)
    {
        return $ctx['day_e'] === '亥';
    }

    protected function _check_cheonmun($saju, $ctx)
    {
        $map = ['子' => '巳', '丑' => '午', '寅' => '未', '卯' => '申', '辰' => '酉', '巳' => '戌', '午' => '亥', '未' => '子', '申' => '丑', '酉' => '寅', '戌' => '卯', '亥' => '辰'];

        return isset($map[$ctx['month_e']]) && $map[$ctx['month_e']] === $ctx['day_e'];
    }

    protected function _check_ilgan_hap($saju, $ctx)
    {
        $map = ['甲' => '己', '己' => '甲', '乙' => '庚', '庚' => '乙', '丙' => '辛', '辛' => '丙', '丁' => '壬', '壬' => '丁', '戊' => '癸', '癸' => '戊'];

        return isset($map[$saju->get_h('day')]) && $map[$saju->get_h('day')] === $ctx['day_h'];
    }

    protected function _check_ilgan_chung($saju, $ctx)
    {
        $map = ['甲' => '庚', '庚' => '甲', '乙' => '辛', '辛' => '乙', '丙' => '壬', '壬' => '丙', '丁' => '癸', '癸' => '丁'];

        return isset($map[$saju->get_h('day')]) && $map[$saju->get_h('day')] === $ctx['day_h'];
    }

    protected function _check_gongmang($saju, $ctx)
    {
        // 기존 7/14 제공해주신 60갑자 공망 맵 로직 사용
        $gongMap = [
            '甲子' => ['戌', '亥'], '乙丑' => ['戌', '亥'], '丙寅' => ['戌', '亥'], '丁卯' => ['戌', '亥'], '戊辰' => ['戌', '亥'], '己巳' => ['戌', '亥'], '庚午' => ['戌', '亥'], '辛未' => ['戌', '亥'], '壬申' => ['戌', '亥'], '癸酉' => ['戌', '亥'],
            '甲戌' => ['申', '酉'], '乙亥' => ['申', '酉'], '丙子' => ['申', '酉'], '丁丑' => ['申', '酉'], '戊寅' => ['申', '酉'], '己卯' => ['申', '酉'], '庚辰' => ['申', '酉'], '辛巳' => ['申', '酉'], '壬午' => ['申', '酉'], '癸未' => ['申', '酉'],
            '甲申' => ['午', '未'], '乙酉' => ['午', '未'], '丙戌' => ['午', '未'], '丁亥' => ['午', '未'], '戊子' => ['午', '未'], '己丑' => ['午', '未'], '庚寅' => ['午', '未'], '辛卯' => ['午', '未'], '壬辰' => ['午', '未'], '癸巳' => ['午', '未'],
            '甲午' => ['辰', '巳'], '乙未' => ['辰', '巳'], '丙申' => ['辰', '巳'], '丁酉' => ['辰', '巳'], '戊戌' => ['辰', '巳'], '己亥' => ['辰', '巳'], '庚子' => ['辰', '巳'], '辛丑' => ['辰', '巳'], '壬寅' => ['辰', '巳'], '癸卯' => ['辰', '巳'],
            '甲辰' => ['寅', '卯'], '乙巳' => ['寅', '卯'], '丙午' => ['寅', '卯'], '丁未' => ['寅', '卯'], '戊申' => ['寅', '卯'], '己酉' => ['寅', '卯'], '庚戌' => ['寅', '卯'], '辛亥' => ['寅', '卯'], '壬子' => ['寅', '卯'], '癸丑' => ['寅', '卯'],
            '甲寅' => ['子', '丑'], '乙卯' => ['子', '丑'], '丙辰' => ['子', '丑'], '丁巳' => ['子', '丑'], '戊午' => ['子', '丑'], '己未' => ['子', '丑'], '庚申' => ['子', '丑'], '辛酉' => ['子', '丑'], '壬戌' => ['子', '丑'], '癸亥' => ['子', '丑'],
        ];

        return in_array($ctx['day_e'], $gongMap[$saju->get_he('day')] ?? []);
    }

    protected function _check_risk($saju, $ctx)
    {
        $rel = $this->checkJijiRelation($saju->get_e('day'), $ctx['day_e']);

        return $rel === '충' || $rel === '형살';
    }

    protected function _check_singu($saju, $ctx)
    {
        $both = ['甲子', '乙丑', '丙寅', '庚午', '乙酉', '庚寅', '壬辰', '癸巳', '壬寅', '癸卯', '丙午', '庚戌', '乙卯', '丙辰', '丁巳', '己未', '庚申'];

        return in_array($ctx['day_he'], $both);
    }

    protected function _check_chenduk($saju, $ctx)
    {
        return Taekil::hasChunduk($ctx['month_e'], $ctx['day_h'], $ctx['day_e']);
    }

    protected function _check_wolduk($saju, $ctx)
    {
        return Taekil::hasWolduk($ctx['month_e'], $ctx['day_h']);
    }

    protected function _check_chendukhap($saju, $ctx)
    {
        return Taekil::hasChendukHap($ctx['month_e'], $ctx['day_e']);
    }

    protected function _check_woldukhap($saju, $ctx)
    {
        return Taekil::hasWoldukHap($ctx['month_e'], $ctx['day_h']);
    }

    protected function _check_gachui($saju, $ctx)
    {
        // 결혼 가취대흉 판정 로직
        $m = $ctx['month_e'];
        $h = $ctx['day_he'];
        if (in_array($m, ['寅', '卯', '辰']) && in_array($h, ['甲子', '乙丑'])) {
            return true;
        }
        if (in_array($m, ['巳', '午', '未']) && in_array($h, ['丙子', '丁丑'])) {
            return true;
        }
        if (in_array($m, ['申', '酉', '戌']) && in_array($h, ['庚子', '辛丑'])) {
            return true;
        }
        if (in_array($m, ['亥', '子', '丑']) && in_array($h, ['壬子', '癸丑'])) {
            return true;
        }

        return false;
    }

    protected function _check_car_match($saju, $ctx)
    {
        $strength = $saju->sinyaksingang()->create();
        $yongsinData = $saju->oheng()->findYongsinAndGisin($strength);
        // Saju 대신 SinyakSingang 클래스의 메서드 활용
        $helper = new \Pondol\Fortune\Services\SinyakSingang;

        return in_array($yongsinData['yongsin'], ['金', '火']) && $helper->convertCharToOhaeng($ctx['day_h']) === $yongsinData['yongsin'];
    }

    protected function _check_car_safety($saju, $ctx)
    {
        return $this->checkJijiRelation($saju->get_e('day'), $ctx['day_e']) === null;
    }

    protected function _check_car_yeokma($saju, $ctx)
    {
        $movement_map = ['申' => '寅', '子' => '寅', '辰' => '寅', '寅' => '申', '午' => '申', '戌' => '申', '巳' => '亥', '酉' => '亥', '丑' => '亥', '亥' => '巳', '卯' => '巳', '未' => '巳'];
        $yukhap = ['寅' => '亥', '亥' => '寅', '申' => '巳', '巳' => '申', '卯' => '戌', '戌' => '卯', '辰' => '酉', '酉' => '辰', '午' => '未', '未' => '午', '子' => '丑', '丑' => '子'];
        $my_yeokma = $movement_map[$saju->get_e('year')] ?? '';

        return ($yukhap[$my_yeokma] ?? '') === $ctx['day_e'];
    }

    // 3. [추가: 이사 방향 판정]
    protected function _check_daejanggun($saju, $ctx, $options)
    {
        if (! ($options['moving_direction_enabled'] ?? false)) {
            return false;
        }
        $bad = $this->getBadDirections($ctx['year_e']);

        return $options['moving_direction'] === $bad['daejanggun'];
    }

    protected function _check_samsalbang($saju, $ctx, $options)
    {
        if (! ($options['moving_direction_enabled'] ?? false)) {
            return false;
        }
        $bad = $this->getBadDirections($ctx['year_e']);

        return $options['moving_direction'] === $bad['samsalbang'];
    }

    protected function _check_wonjin($saju, $ctx)
    {
        $my_jiji = $saju->get_e('day'); // 주인 일지
        $day_jiji = $ctx['day_e'];     // 날짜 지지

        $map = [
            '子' => '未', '丑' => '午', '寅' => '酉', '卯' => '申', '辰' => '亥', '巳' => '戌',
            '午' => '丑', '未' => '子', '申' => '卯', '酉' => '寅', '戌' => '巳', '亥' => '辰',
        ];

        return ($map[$my_jiji] ?? '') === $day_jiji;
    }

    // --- [2. 그룹 판정 메서드: _group_키이름] ---

    protected function _group_sbc_group($saju, $ctx)
    {
        $my_age = (int) date('Y') - (int) substr($saju->solar, 0, 4) + 1;
        $sbc = $this->_senggiBokdukCheneu($my_age, $saju->gender);
        if (in_array($ctx['day_e'], $sbc['senggi'])) {
            return 'senggi';
        }
        if (in_array($ctx['day_e'], $sbc['bokduk'])) {
            return 'bokduk';
        }
        if (in_array($ctx['day_e'], $sbc['cheneu'])) {
            return 'cheneu';
        }

        return null;
    }

    protected function _group_woonseong_group($saju, $ctx)
    {
        $my_ilgan = $saju->get_h('day');
        $jaeseong_h = tr_code(['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'], ['戊', '戊', '庚', '庚', '壬', '壬', '甲', '甲', '丙', '丙'], $my_ilgan);
        $woon_map = [
            '甲' => ['亥' => 'jangseng', '丑' => 'gwandae', '寅' => 'geonrok', '卯' => 'jewang'],
            '丙' => ['寅' => 'jangseng', '辰' => 'gwandae', '巳' => 'geonrok', '午' => 'jewang'],
            '戊' => ['寅' => 'jangseng', '辰' => 'gwandae', '巳' => 'geonrok', '午' => 'jewang'],
            '庚' => ['巳' => 'jangseng', '未' => 'gwandae', '申' => 'geonrok', '酉' => 'jewang'],
            '壬' => ['申' => 'jangseng', '戌' => 'gwandae', '亥' => 'geonrok', '子' => 'jewang'],
        ];
        $woon_map['乙'] = $woon_map['甲'];
        $woon_map['丁'] = $woon_map['丙'];
        $woon_map['己'] = $woon_map['戊'];
        $woon_map['辛'] = $woon_map['庚'];
        $woon_map['癸'] = $woon_map['壬'];

        return $woon_map[$jaeseong_h][$ctx['day_e']] ?? null;
    }

    protected function _group_dae_group($saju, $ctx)
    {
        $bride_yeonji = $saju->get_e('year');
        $month_e = $ctx['month_e'];

        // 반환값을 영문 키로 변경
        $map = [
            '子' => ['子' => 'bangnyeosin', '丑' => 'daeri',    '寅' => 'bangmaessi', '卯' => 'bangonggo',  '辰' => 'bangnyebu',  '巳' => 'bangbuju'],
            '丑' => ['午' => 'daeri',       '未' => 'bangnyeosin', '申' => 'bangbuju',   '酉' => 'bangnyebu',  '戌' => 'bangonggo',  '亥' => 'bangmaessi'],
            '寅' => ['子' => 'bangnyebu',  '丑' => 'bangbuju',   '寅' => 'bangnyeosin', '卯' => 'daeri',     '辰' => 'bangmaessi', '巳' => 'bangonggo'],
            '卯' => ['午' => 'bangonggo',  '未' => 'bangmaessi', '申' => 'daeri',       '酉' => 'bangnyeosin', '戌' => 'bangbuju',   '亥' => 'bangnyebu'],
            '辰' => ['子' => 'bangmaessi', '丑' => 'bangonggo',  '寅' => 'bangnyebu',  '卯' => 'bangbuju',   '辰' => 'bangnyeosin', '巳' => 'daeri'],
            '巳' => ['午' => 'bangbuju',   '未' => 'bangnyebu',  '申' => 'bangonggo',  '酉' => 'bangmaessi', '戌' => 'daeri',       '亥' => 'bangnyeosin'],
        ];
        $map['午'] = $map['子'];
        $map['未'] = $map['丑'];
        $map['申'] = $map['寅'];
        $map['酉'] = $map['卯'];
        $map['戌'] = $map['辰'];
        $map['亥'] = $map['巳'];

        return $map[$bride_yeonji][$month_e] ?? null;
    }

    protected function _group_whangdo($saju, $ctx)
    {
        $start_map = [
            '寅' => '子', '申' => '子', '卯' => '寅', '酉' => '寅', '辰' => '辰', '戌' => '辰',
            '巳' => '午', '亥' => '午', '午' => '申', '子' => '申', '未' => '戌', '丑' => '戌',
        ];
        if (! isset($start_map[$ctx['month_e']])) {
            return null;
        }
        $diff = (e_to_serial($ctx['day_e']) - e_to_serial($start_map[$ctx['month_e']]) + 12) % 12;
        $shins_map = [0 => 'cheong', 1 => 'myeong', 4 => 'geum', 5 => 'cheon', 7 => 'ok', 10 => 'sa'];

        return $shins_map[$diff] ?? null;
    }
}
