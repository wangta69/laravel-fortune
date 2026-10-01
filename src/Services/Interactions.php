<?php

namespace Pondol\Fortune\Services;

/**
 * 사주 원국 간지 상호작용(형충회합 및 공망) 정통 분석 엔진
 *
 * [정통 명리학 원칙 준수]
 * 1. 거리론(距離論): 인접(隣接, 100% 발동)과 격지(隔地, 잠재/감쇄)의 엄격한 차등
 * 2. 천간과 지지의 층위 분리 (천간합충 vs 지지형충회합)
 * 3. 삼합(三合)과 왕지(旺地) 결핍에 따른 가합(假合/拱合) 명칭 보존
 * 4. 공망봉합(空亡逢合)에 따른 해공(解空) 판별
 */
class Interactions
{
    private Saju $saju;

    /** 천간합 (干合) */
    private const CHEONGAN_HAP = [
        '甲己' => ['name' => '갑기합', 'ch' => '甲己合', 'element' => '土'],
        '己甲' => ['name' => '갑기합', 'ch' => '甲己合', 'element' => '土'],
        '乙庚' => ['name' => '을경합', 'ch' => '乙庚合', 'element' => '金'],
        '庚乙' => ['name' => '을경합', 'ch' => '乙庚合', 'element' => '金'],
        '丙辛' => ['name' => '병신합', 'ch' => '丙辛合', 'element' => '水'],
        '辛丙' => ['name' => '병신합', 'ch' => '丙辛合', 'element' => '水'],
        '丁壬' => ['name' => '정임합', 'ch' => '丁壬合', 'element' => '木'],
        '壬丁' => ['name' => '정임합', 'ch' => '丁壬合', 'element' => '木'],
        '戊癸' => ['name' => '무계합', 'ch' => '戊癸合', 'element' => '火'],
        '癸戊' => ['name' => '무계합', 'ch' => '戊癸合', 'element' => '火'],
    ];

    /** 천간충 (干沖) */
    private const CHEONGAN_CHUNG = [
        '甲庚' => ['name' => '갑경충', 'ch' => '甲庚沖'],
        '庚甲' => ['name' => '갑경충', 'ch' => '甲庚沖'],
        '乙辛' => ['name' => '을신충', 'ch' => '乙辛沖'],
        '辛乙' => ['name' => '을신충', 'ch' => '乙辛沖'],
        '丙壬' => ['name' => '병임충', 'ch' => '丙壬沖'],
        '壬丙' => ['name' => '병임충', 'ch' => '丙壬沖'],
        '丁癸' => ['name' => '정계충', 'ch' => '丁癸沖'],
        '癸丁' => ['name' => '정계충', 'ch' => '丁癸沖'],
    ];

    /** 지지 육합 (六合) */
    private const JIJI_YOOKHAP = [
        '子丑' => ['name' => '자축육합', 'ch' => '子丑 六合', 'element' => '土'],
        '丑子' => ['name' => '자축육합', 'ch' => '子丑 六合', 'element' => '土'],
        '寅亥' => ['name' => '인해육합', 'ch' => '寅亥 六合', 'element' => '木'],
        '亥寅' => ['name' => '인해육합', 'ch' => '寅亥 六合', 'element' => '木'],
        '卯戌' => ['name' => '묘술육합', 'ch' => '卯戌 六合', 'element' => '火'],
        '戌卯' => ['name' => '묘술육합', 'ch' => '卯戌 六合', 'element' => '火'],
        '辰酉' => ['name' => '진유육합', 'ch' => '辰酉 六合', 'element' => '金'],
        '酉辰' => ['name' => '진유육합', 'ch' => '辰酉 六合', 'element' => '金'],
        '巳申' => ['name' => '사신육합', 'ch' => '巳申 六合', 'element' => '水'],
        '申巳' => ['name' => '사신육합', 'ch' => '巳申 六合', 'element' => '水'],
        '午未' => ['name' => '오미육합', 'ch' => '午未 六合', 'element' => '火'],
        '未午' => ['name' => '오미육합', 'ch' => '午未 六合', 'element' => '火'],
    ];

    /** 지지 칠충 (七沖) */
    private const JIJI_CHUNG = [
        '子午' => ['name' => '자오충', 'ch' => '子午 沖'],
        '午子' => ['name' => '자오충', 'ch' => '子午 沖'],
        '丑未' => ['name' => '축미충', 'ch' => '丑未 沖'],
        '未丑' => ['name' => '축미충', 'ch' => '丑未 沖'],
        '寅申' => ['name' => '인신충', 'ch' => '寅申 沖'],
        '申寅' => ['name' => '인신충', 'ch' => '寅申 沖'],
        '卯酉' => ['name' => '묘유충', 'ch' => '卯酉 沖'],
        '酉卯' => ['name' => '묘유충', 'ch' => '卯酉 沖'],
        '辰戌' => ['name' => '진술충', 'ch' => '辰戌 沖'],
        '戌辰' => ['name' => '진술충', 'ch' => '辰戌 沖'],
        '巳亥' => ['name' => '사해충', 'ch' => '巳亥 沖'],
        '亥巳' => ['name' => '사해충', 'ch' => '巳亥 沖'],
    ];

    /** 지지 원진 (怨嗔) */
    private const JIJI_WONJIN = [
        '子未' => ['name' => '자미원진', 'ch' => '子未 怨嗔'],
        '未子' => ['name' => '자미원진', 'ch' => '子未 怨嗔'],
        '丑午' => ['name' => '축오원진', 'ch' => '丑午 怨嗔'],
        '午丑' => ['name' => '축오원진', 'ch' => '丑午 怨嗔'],
        '寅酉' => ['name' => '인유원진', 'ch' => '寅酉 怨嗔'],
        '酉寅' => ['name' => '인유원진', 'ch' => '寅酉 怨嗔'],
        '卯申' => ['name' => '묘신원진', 'ch' => '卯申 怨嗔'],
        '申卯' => ['name' => '묘신원진', 'ch' => '卯申 怨嗔'],
        '辰亥' => ['name' => '진해원진', 'ch' => '辰亥 怨嗔'],
        '亥辰' => ['name' => '진해원진', 'ch' => '辰亥 怨嗔'],
        '巳戌' => ['name' => '사술원진', 'ch' => '巳戌 怨嗔'],
        '戌巳' => ['name' => '사술원진', 'ch' => '巳戌 怨嗔'],
    ];

    /** 지지 파 (破) */
    private const JIJI_PA = [
        '子酉' => ['name' => '자유파', 'ch' => '子酉 破'],
        '酉子' => ['name' => '자유파', 'ch' => '子酉 破'],
        '丑辰' => ['name' => '축진파', 'ch' => '丑辰 破'],
        '辰丑' => ['name' => '축진파', 'ch' => '丑辰 破'],
        '寅亥' => ['name' => '인해파', 'ch' => '寅亥 破'],
        '亥寅' => ['name' => '인해파', 'ch' => '寅亥 破'],
        '卯午' => ['name' => '묘오파', 'ch' => '卯午 破'],
        '午卯' => ['name' => '묘오파', 'ch' => '卯午 破'],
        '巳申' => ['name' => '사신파', 'ch' => '巳申 破'],
        '申巳' => ['name' => '사신파', 'ch' => '巳申 破'],
        '戌未' => ['name' => '술미파', 'ch' => '戌未 破'],
        '未戌' => ['name' => '술미파', 'ch' => '戌未 破'],
    ];

    /** 지지 육해 (六害) */
    private const JIJI_HAE = [
        '子未' => ['name' => '자미해', 'ch' => '子未 害'],
        '未子' => ['name' => '자미해', 'ch' => '子未 害'],
        '丑午' => ['name' => '축오해', 'ch' => '丑午 害'],
        '午丑' => ['name' => '축오해', 'ch' => '丑午 害'],
        '寅巳' => ['name' => '인사해', 'ch' => '寅巳 害'],
        '巳寅' => ['name' => '인사해', 'ch' => '寅巳 害'],
        '卯辰' => ['name' => '묘진해', 'ch' => '卯辰 害'],
        '辰卯' => ['name' => '묘진해', 'ch' => '卯辰 害'],
        '申亥' => ['name' => '신해해', 'ch' => '申亥 害'],
        '亥申' => ['name' => '신해해', 'ch' => '申亥 害'],
        '酉戌' => ['name' => '유술해', 'ch' => '酉戌 害'],
        '戌酉' => ['name' => '유술해', 'ch' => '酉戌 害'],
    ];

    /** 삼합 군(局) */
    private const SAMHAP_GROUPS = [
        '水' => ['chars' => ['申', '子', '辰'], 'center' => '子', 'name' => '신자진 수국', 'ch' => '申子辰 水局'],
        '火' => ['chars' => ['寅', '午', '戌'], 'center' => '午', 'name' => '인오술 화국', 'ch' => '寅午戌 火局'],
        '金' => ['chars' => ['巳', '酉', '丑'], 'center' => '酉', 'name' => '사유축 금국', 'ch' => '巳酉丑 金局'],
        '木' => ['chars' => ['亥', '卯', '未'], 'center' => '卯', 'name' => '해묘미 목국', 'ch' => '亥卯未 木局'],
    ];

    /** 방합 군(方) */
    private const BANGHAP_GROUPS = [
        '木' => ['chars' => ['寅', '卯', '辰'], 'name' => '인묘진 방합', 'ch' => '寅卯辰 方合', 'season' => '봄'],
        '火' => ['chars' => ['巳', '午', '未'], 'name' => '사오미 방합', 'ch' => '巳午未 方合', 'season' => '여름'],
        '金' => ['chars' => ['申', '酉', '戌'], 'name' => '신유술 방합', 'ch' => '申酉戌 方合', 'season' => '가을'],
        '水' => ['chars' => ['亥', '子', '丑'], 'name' => '해자축 방합', 'ch' => '亥子丑 方合', 'season' => '겨울'],
    ];

    public function withSaju(Saju $saju): self
    {
        $this->saju = $saju;

        return $this;
    }

    /**
     * 일주 기준 순중공망(旬中空亡) 및 해공(解空) 여부 정밀 분석
     */
    public function gongmang(): object
    {
        $iljuCh = $this->saju->day->ch;
        $iljuKo = $this->saju->day->ko;

        $pairData = Sinsal::getGongmangPair($iljuCh);
        $pairCh = [$pairData['g1'], $pairData['g2']];

        $jiKoMap = [
            '子' => '자', '丑' => '축', '寅' => '인', '卯' => '묘',
            '辰' => '진', '巳' => '사', '午' => '오', '未' => '미',
            '申' => '신', '酉' => '유', '戌' => '술', '亥' => '해',
        ];

        $pairKo = [$jiKoMap[$pairCh[0]] ?? '', $jiKoMap[$pairCh[1]] ?? ''];

        $pillars = ['year', 'month', 'day', 'hour'];
        $pillarsResult = new \stdClass;
        $affectedPillars = [];

        foreach ($pillars as $p) {
            if ($p === 'hour' && ! $this->saju->hourKnown) {
                $pillarsResult->hour = (object) [
                    'is_gongmang' => false,
                    'ch' => '',
                    'ko' => '',
                    'known' => false,
                ];

                continue;
            }

            $jijiCh = $this->saju->get_e($p, 'ch');
            $jijiKo = $this->saju->get_e($p, 'ko');
            $isGongmang = in_array($jijiCh, $pairCh);

            if ($isGongmang) {
                $affectedPillars[] = $p;
            }

            $pillarsResult->{$p} = (object) [
                'is_gongmang' => $isGongmang,
                'ch' => $jijiCh,
                'ko' => $jijiKo,
                'known' => true,
            ];
        }

        return (object) [
            'base' => (object) ['ch' => $iljuCh, 'ko' => $iljuKo],
            'sunsu' => $pairData['chul'] ?? '',
            'pair_ch' => $pairCh,
            'pair_ko' => $pairKo,
            'pillars' => $pillarsResult,
            'affected_pillars' => $affectedPillars,
            'has_gongmang' => ! empty($affectedPillars),
        ];
    }

    /**
     * 정통 명리학 원근법(인접 vs 격지)에 입각한 종합 케미스트리 데이터 반환
     *
     * @return object {
     *                adjacent_pairs: array<object>, // 인접 기둥 쌍 (100% 직접 발현: 시-일, 일-월, 월-년)
     *                remote_pairs: array<object>,   // 격지 기둥 쌍 (감쇄/잠재: 일-년, 시-월, 시-년)
     *                global_samhap: array<object>,  // 3자 온전한 삼합 국
     *                global_banghap: array<object>, // 3자 온전한 방합 국
     *                global_hyeong: array<object>,  // 삼형살 및 자형살
     *                gongmang: object               // 공망 분석 객체
     *                }
     */
    public function getChemistry(): object
    {
        $allPairs = $this->analyzeAllPairs();

        $adjacent = [];
        $remote = [];

        foreach ($allPairs as $pair) {
            if ($pair->distance === 'adjacent') {
                $adjacent[] = $pair;
            } else {
                $remote[] = $pair;
            }
        }

        return (object) [
            'adjacent_pairs' => $adjacent,
            'remote_pairs' => $remote,
            'global_samhap' => $this->getGlobalSamhap(),
            'global_banghap' => $this->getGlobalBanghap(),
            'global_hyeong' => $this->getGlobalHyeongsal(),
            'gongmang' => $this->gongmang(),
        ];
    }

    /**
     * 모든 기둥 쌍 간의 상호작용 정밀 분석 (거리론 적용)
     */
    private function analyzeAllPairs(): array
    {
        $posKo = ['hour' => '시', 'day' => '일', 'month' => '월', 'year' => '년'];

        $stems = [
            'hour' => $this->saju->hourKnown ? $this->saju->get_h('hour') : null,
            'day' => $this->saju->get_h('day'),
            'month' => $this->saju->get_h('month'),
            'year' => $this->saju->get_h('year'),
        ];

        $branches = [
            'hour' => $this->saju->hourKnown ? $this->saju->get_e('hour') : null,
            'day' => $this->saju->get_e('day'),
            'month' => $this->saju->get_e('month'),
            'year' => $this->saju->get_e('year'),
        ];

        // 정통 거리 정의: adjacent(인접), remote(1칸 격지), distant(2칸 요격)
        $definitions = [
            ['p1' => 'day',   'p2' => 'month', 'dist' => 'adjacent', 'desc' => '나와 사회적 현실'],
            ['p1' => 'hour',  'p2' => 'day',   'dist' => 'adjacent', 'desc' => '말년/자녀와 나'],
            ['p1' => 'month', 'p2' => 'year',  'dist' => 'adjacent', 'desc' => '사회 환경과 가문/초년'],
            ['p1' => 'day',   'p2' => 'year',  'dist' => 'remote',   'desc' => '나와 가문/초년 (격지)'],
            ['p1' => 'hour',  'p2' => 'month', 'dist' => 'remote',   'desc' => '말년과 사회 환경 (격지)'],
            ['p1' => 'hour',  'p2' => 'year',  'dist' => 'distant',  'desc' => '말년과 초년 (요격)'],
        ];

        $results = [];

        foreach ($definitions as $def) {
            $p1 = $def['p1'];
            $p2 = $def['p2'];

            if (($p1 === 'hour' || $p2 === 'hour') && ! $this->saju->hourKnown) {
                continue;
            }

            $h1 = $stems[$p1];
            $h2 = $stems[$p2];
            $e1 = $branches[$p1];
            $e2 = $branches[$p2];

            $stemInteractions = [];
            $branchInteractions = [];

            // 1. 천간합
            if ($h1 && $h2 && isset(self::CHEONGAN_HAP[$h1.$h2])) {
                $stemInteractions[] = (object) array_merge(
                    ['category' => 'stem_hap', 'type' => '합'],
                    self::CHEONGAN_HAP[$h1.$h2]
                );
            }

            // 2. 천간충
            if ($h1 && $h2 && isset(self::CHEONGAN_CHUNG[$h1.$h2])) {
                $stemInteractions[] = (object) array_merge(
                    ['category' => 'stem_chung', 'type' => '충'],
                    self::CHEONGAN_CHUNG[$h1.$h2]
                );
            }

            // 3. 지지 육합
            if ($e1 && $e2 && isset(self::JIJI_YOOKHAP[$e1.$e2])) {
                $branchInteractions[] = (object) array_merge(
                    ['category' => 'branch_yookhap', 'type' => '육합'],
                    self::JIJI_YOOKHAP[$e1.$e2]
                );
            }

            // 4. 지지 삼합 2자: 반합(半合) vs 가합(假合) 엄격 분기
            $banhap = $this->checkBanhap($e1, $e2);
            if ($banhap) {
                $branchInteractions[] = $banhap;
            }

            // 5. 지지 칠충
            if ($e1 && $e2 && isset(self::JIJI_CHUNG[$e1.$e2])) {
                $branchInteractions[] = (object) array_merge(
                    ['category' => 'branch_chung', 'type' => '충'],
                    self::JIJI_CHUNG[$e1.$e2]
                );
            }

            // 6. 지지 형(刑)
            $hyeong = $this->checkPairHyeong($e1, $e2);
            if ($hyeong) {
                $branchInteractions[] = $hyeong;
            }

            // 7. 지지 원진
            if ($e1 && $e2 && isset(self::JIJI_WONJIN[$e1.$e2])) {
                $branchInteractions[] = (object) array_merge(
                    ['category' => 'branch_wonjin', 'type' => '원진'],
                    self::JIJI_WONJIN[$e1.$e2]
                );
            }

            // 8. 지지 해(害)
            if ($e1 && $e2 && isset(self::JIJI_HAE[$e1.$e2])) {
                $branchInteractions[] = (object) array_merge(
                    ['category' => 'branch_hae', 'type' => '해'],
                    self::JIJI_HAE[$e1.$e2]
                );
            }

            // 9. 지지 파(破)
            if ($e1 && $e2 && isset(self::JIJI_PA[$e1.$e2])) {
                $branchInteractions[] = (object) array_merge(
                    ['category' => 'branch_pa', 'type' => '파'],
                    self::JIJI_PA[$e1.$e2]
                );
            }

            if (! empty($stemInteractions) || ! empty($branchInteractions)) {
                $results[] = (object) [
                    'p1' => $p1,
                    'p2' => $p2,
                    'label' => "{$posKo[$p1]}-{$posKo[$p2]}",
                    'distance' => $def['dist'],
                    'relation_desc' => $def['desc'],
                    'stem_chars' => $h1.$h2,
                    'branch_chars' => $e1.$e2,
                    'stem_interactions' => $stemInteractions,
                    'branch_interactions' => $branchInteractions,
                ];
            }
        }

        return $results;
    }

    /**
     * [정통 명리 엄수] 삼합 2자 결합 시 왕지 유무에 따른 반합/가합 판별
     * - 왕지(子午卯酉) 포함: 반합(半合)
     * - 왕지 결핍(생지+묘지): 가합(假合) 또는 공합(拱合)
     */
    private function checkBanhap(string $e1, string $e2): ?object
    {
        if ($e1 === $e2) {
            return null;
        }

        foreach (self::SAMHAP_GROUPS as $element => $group) {
            $chars = $group['chars'];
            $center = $group['center'];

            if (in_array($e1, $chars) && in_array($e2, $chars)) {
                $hasCenter = ($e1 === $center || $e2 === $center);

                // 고전 원칙: 왕지가 없으면 '가합(假合)'
                $term = $hasCenter ? '반합' : '가합';
                $termCh = $hasCenter ? '半合' : '假合';

                return (object) [
                    'category' => 'branch_banhap',
                    'type' => $term,
                    'name' => "{$e1}{$e2} {$term}({$element})",
                    'ch' => "{$e1}{$e2} {$termCh}",
                    'element' => $element,
                    'has_center' => $hasCenter,
                ];
            }
        }

        return null;
    }

    /**
     * 두 지지 간의 형(상형, 자형, 삼형 2자) 판별
     */
    private function checkPairHyeong(string $e1, string $e2): ?object
    {
        // 자형 (辰辰, 午午, 酉酉, 亥亥)
        if ($e1 === $e2 && in_array($e1, ['辰', '午', '酉', '亥'])) {
            return (object) [
                'category' => 'branch_hyeong',
                'type' => '자형',
                'name' => "{$e1}{$e2} 자형",
                'ch' => "{$e1}{$e2} 自刑",
            ];
        }

        // 상형 (子卯)
        if (in_array($e1.$e2, ['子卯', '卯子'])) {
            return (object) [
                'category' => 'branch_hyeong',
                'type' => '상형',
                'name' => '자묘 상형',
                'ch' => '子卯 相刑',
            ];
        }

        // 삼형 2자 (인사, 사신, 신인 / 축술, 술미, 미축)
        $insasinPairs = ['寅巳', '巳寅', '巳申', '申巳', '寅申', '申寅'];
        if (in_array($e1.$e2, $insasinPairs)) {
            return (object) [
                'category' => 'branch_hyeong',
                'type' => '형',
                'name' => "{$e1}{$e2} 형",
                'ch' => "{$e1}{$e2} 刑",
                'group' => '인사신',
            ];
        }

        $chuksulmiPairs = ['丑戌', '戌丑', '戌未', '未戌', '丑未', '未丑'];
        if (in_array($e1.$e2, $chuksulmiPairs)) {
            return (object) [
                'category' => 'branch_hyeong',
                'type' => '형',
                'name' => "{$e1}{$e2} 형",
                'ch' => "{$e1}{$e2} 刑",
                'group' => '축술미',
            ];
        }

        return null;
    }

    private function getGlobalSamhap(): array
    {
        $raw = $this->getRawBranches();
        $unique = array_unique($raw);
        $found = [];

        foreach (self::SAMHAP_GROUPS as $el => $g) {
            if (count(array_intersect($g['chars'], $unique)) === 3) {
                $found[] = (object) [
                    'category' => 'global_samhap',
                    'name' => $g['name'],
                    'ch' => $g['ch'],
                    'element' => $el,
                    'chars' => $g['chars'],
                ];
            }
        }

        return $found;
    }

    private function getGlobalBanghap(): array
    {
        $raw = $this->getRawBranches();
        $unique = array_unique($raw);
        $found = [];

        foreach (self::BANGHAP_GROUPS as $el => $g) {
            if (count(array_intersect($g['chars'], $unique)) === 3) {
                $found[] = (object) [
                    'category' => 'global_banghap',
                    'name' => $g['name'],
                    'ch' => $g['ch'],
                    'element' => $el,
                    'season' => $g['season'],
                    'chars' => $g['chars'],
                ];
            }
        }

        return $found;
    }

    private function getGlobalHyeongsal(): array
    {
        $raw = $this->getRawBranches();
        $unique = array_unique($raw);
        $counts = array_count_values($raw);
        $found = [];

        $insasinIntersect = array_values(array_intersect(['寅', '巳', '申'], $unique));
        if (count($insasinIntersect) >= 2) {
            $found[] = (object) [
                'name' => count($insasinIntersect) === 3 ? '인사신 삼형' : '인사신 삼형(준형)',
                'ch' => '寅巳申 刑',
                'chars' => $insasinIntersect,
                'is_full' => count($insasinIntersect) === 3,
            ];
        }

        $chuksulmiIntersect = array_values(array_intersect(['丑', '戌', '未'], $unique));
        if (count($chuksulmiIntersect) >= 2) {
            $found[] = (object) [
                'name' => count($chuksulmiIntersect) === 3 ? '축술미 삼형' : '축술미 삼형(준형)',
                'ch' => '丑戌未 刑',
                'chars' => $chuksulmiIntersect,
                'is_full' => count($chuksulmiIntersect) === 3,
            ];
        }

        if (in_array('子', $unique) && in_array('卯', $unique)) {
            $found[] = (object) [
                'name' => '자묘 상형',
                'ch' => '子卯 相刑',
                'chars' => ['子', '卯'],
                'is_full' => true,
            ];
        }

        foreach (['辰', '午', '酉', '亥'] as $ji) {
            if (($counts[$ji] ?? 0) >= 2) {
                $found[] = (object) [
                    'name' => "{$ji}{$ji} 자형",
                    'ch' => "{$ji}{$ji} 自刑",
                    'char' => $ji,
                    'count' => $counts[$ji],
                ];
            }
        }

        return $found;
    }

    private function getRawBranches(): array
    {
        $branches = [
            $this->saju->get_e('year'),
            $this->saju->get_e('month'),
            $this->saju->get_e('day'),
        ];

        if ($this->saju->hourKnown && ! empty($this->saju->get_e('hour'))) {
            $branches[] = $this->saju->get_e('hour');
        }

        return array_filter($branches);
    }
}
