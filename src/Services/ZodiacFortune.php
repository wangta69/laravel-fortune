<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Facades\Lunar;
use Pondol\Fortune\Services\Saju;
use Pondol\Fortune\Services\Sinsal12;
use Pondol\Fortune\Services\Sipsin;
use Pondol\Fortune\Services\Woonsung12;

class ZodiacFortune
{
    /** 지지 칠충 (Interactions와 동일 규격) */
    private const OPPOSITE_JI = [
        '子' => '午', '丑' => '未', '寅' => '申', '卯' => '酉',
        '辰' => '戌', '巳' => '亥', '午' => '子', '未' => '丑',
        '申' => '寅', '酉' => '卯', '戌' => '辰', '亥' => '巳',
    ];

    /** 지지 육합 (Interactions와 동일 규격) */
    private const YOOKHAP_JI = [
        '子' => '丑', '丑' => '子', '寅' => '亥', '亥' => '寅',
        '卯' => '戌', '戌' => '卯', '辰' => '酉', '酉' => '辰',
        '巳' => '申', '申' => '巳', '午' => '未', '未' => '午',
    ];

    /** 12개 띠 동물별 대표 출생연도 (활성 5개 세대) */
    private const ZODIAC_GENERATIONS = [
        '子' => [1948, 1960, 1972, 1984, 1996, 2008], // 쥐
        '丑' => [1949, 1961, 1973, 1985, 1997, 2009], // 소
        '寅' => [1950, 1962, 1974, 1986, 1998, 2010], // 호랑이
        '卯' => [1951, 1963, 1975, 1987, 1999, 2011], // 토끼
        '辰' => [1952, 1964, 1976, 1988, 2000, 2012], // 용
        '巳' => [1953, 1965, 1977, 1989, 2001, 2013], // 뱀
        '午' => [1954, 1966, 1978, 1990, 2002, 2014], // 말
        '未' => [1955, 1967, 1979, 1991, 2003, 2015], // 양
        '申' => [1956, 1968, 1980, 1992, 2004, 2016], // 원숭이
        '酉' => [1957, 1969, 1981, 1993, 2005, 2017], // 닭
        '戌' => [1958, 1970, 1982, 1994, 2006, 2018], // 개
        '亥' => [1959, 1971, 1983, 1995, 2007, 2019], // 돼지
    ];

    /**
     * 특정 날짜의 12개 띠 전체 총평 카드 + 세부 생년별 운세 일괄 반환
     *
     * @param  string|null  $solarYmd  양력 날짜 (YYYY-MM-DD, 기본값 오늘)
     */
    public function daily(?string $solarYmd = null): object
    {
        $solarYmd = $solarYmd ?? now()->format('Y-m-d');

        // 1. 기존 Saju 엔진으로 오늘의 사주 기운 산출
        $todaySaju = (new Saju)->ymd($solarYmd)->create();
        $todayDayH = $todaySaju->day->h->ch; // 오늘 일간 한자 (예: 庚)
        $todayDayE = $todaySaju->day->e->ch; // 오늘 일지 한자 (예: 午)
        $todayMonthE = $todaySaju->month->e->ch; // 오늘 월지 한자 (예: 寅)

        $summaries = [];
        $individuals = [];

        // 12개 띠 순회 (자, 축, 인, 묘, 진, 사, 오, 미, 신, 유, 술, 해)
        $zodiacOrder = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];

        foreach ($zodiacOrder as $animalJi) {
            $animalYears = self::ZODIAC_GENERATIONS[$animalJi];
            $animalIndividualList = [];

            // 2. 해당 띠에 속한 각 생년별 개별 운세 계산
            foreach ($animalYears as $birthYear) {
                $indFortune = $this->calculateIndividual($birthYear, $todaySaju);
                $animalIndividualList[] = $indFortune;
                $individuals[] = $indFortune;
            }

            // 3. 12개 띠 총평 카드 계산 (개별 점수 평균 집계 및 거시적 관계 분석)
            $summaries[] = $this->calculateZodiacCard(
                $animalJi,
                $animalIndividualList,
                $todaySaju
            );
        }

        return (object) [
            'date' => $solarYmd,
            'day_ganji' => (object) ['ko' => $todaySaju->day->ko, 'ch' => $todaySaju->day->ch],
            'month_jiji' => (object) ['ko' => $todaySaju->month->e->ko, 'ch' => $todayMonthE],
            'summaries' => $summaries,   // 12개 띠 총평 카드
            'individuals' => $individuals, // 60개 생년별 세부 운세
        ];
    }

    /**
     * 특정 띠(예: '쥐' or '子')의 오늘 총평 카드 및 해당 연도 운세 조회
     */
    public function forZodiac(string $animalOrJi, ?string $solarYmd = null): object
    {
        $daily = $this->daily($solarYmd);
        $targetJi = $this->resolveToJi($animalOrJi);

        $summary = collect($daily->summaries)->firstWhere('ji', $targetJi);
        $individuals = collect($daily->individuals)->where('animal_ji', $targetJi)->values()->all();

        return (object) [
            'date' => $daily->date,
            'day_ganji' => $daily->day_ganji,
            'summary' => $summary,
            'individuals' => $individuals,
        ];
    }

    /**
     * 특정 생년(예: 1984 또는 '갑자'/'甲子')의 특정일 운세 단일 조회
     */
    public function forBirthYear(string|int $birthYearOrGanji, ?string $solarYmd = null): object
    {
        $solarYmd = $solarYmd ?? now()->format('Y-m-d');
        $todaySaju = (new Saju)->ymd($solarYmd)->create();

        if (is_numeric($birthYearOrGanji)) {
            return $this->calculateIndividual((int) $birthYearOrGanji, $todaySaju);
        }

        // 간지 문자열(예: '갑자', '甲子')로 조회할 경우
        $birthSaju = (new Saju)->ymd("{$birthYearOrGanji}-06-15")->create(); // 간지 역추출용

        return $this->calculateIndividualFromGanji($birthYearOrGanji, $todaySaju);
    }

    // =========================================================================
    // 내부 핵심 연산 엔진 (결정론적 슬롯 조합)
    // =========================================================================

    /**
     * 개별 생년 운세 연산
     */
    private function calculateIndividual(int $birthYear, Saju $todaySaju): object
    {
        // 1. 생년 사주 기운 도출 (입춘 기준 간지 정규화)
        $birthSaju = (new Saju)->ymd("{$birthYear}-06-15")->create();
        $birthGanjiKo = $birthSaju->year->ko;
        $birthGanjiCh = $birthSaju->year->ch;
        $myH = $birthSaju->year->h->ch;
        $myE = $birthSaju->year->e->ch;

        $todayH = $todaySaju->day->h->ch;
        $todayE = $todaySaju->day->e->ch;
        $todayMonthE = $todaySaju->month->e->ch;

        // 2. 패키지 내 Sipsin & Sinsal12 코어 클래스 직접 활용
        $stemRel = Sipsin::cal($myH, $todayH, 'h') ?? '비견'; // 십신
        $sinsalName = Sinsal12::cal($myE, $todayE) ?? '평살';   // 12신살

        // 3. 지지 상호작용 판별 (Interactions 규격 완벽 준수)
        $branchRel = $this->evaluateBranchRelation($myE, $todayE);

        // 4. 결정론적 시드 인덱스 산출 (언제 조회해도 동일 날짜/생년은 100% 동일 결과)
        $dayIndex = Saju::getGanji60Index($todaySaju->day->ch);
        $birthIndex = Saju::getGanji60Index($birthGanjiCh);
        $monthIndex = $todaySaju->month->e->order ?? 1;

        // 신의 숫자: 10개(테마) x 6개(액션) x 3개(계절) 모듈러
        $themeIdx = ($dayIndex + $birthIndex) % 10;
        $actionIdx = ($dayIndex + $monthIndex) % 6;
        $seasonIdx = ($monthIndex + $birthIndex) % 3;

        // 5. config/pondol-fortune-zodiac.php 파일에서 멘트 인출
        $seasonName = $this->getSeasonName($todayMonthE);
        $themeTxt = config("pondol-fortune-zodiac.theme.{$stemRel}.{$themeIdx}")
            ?? '자신의 주관을 뚜렷이 세우고 실속을 챙기기 좋은 날입니다.';
        $actionTxt = config("pondol-fortune-zodiac.action.{$branchRel}.{$actionIdx}")
            ?? '무리한 변화보다는 현재의 안정된 리듬을 지켜나가십시오.';
        $seasonTxt = config("pondol-fortune-zodiac.season.{$seasonName}.{$seasonIdx}")
            ?? '차분하게 내실을 다지며 기회를 도모하세요.';

        $summaryText = "{$themeTxt} {$actionTxt} {$seasonTxt}";

        // 6. 점수 산출 알고리즘
        $scores = $this->calculateScores($stemRel, $branchRel, $birthIndex);

        return (object) [
            'year' => $birthYear,
            'animal_ji' => $myE,
            'animal_ko' => $birthSaju->year->e->animal,
            'ganji' => (object) ['ko' => $birthGanjiKo, 'ch' => $birthGanjiCh],
            'relation_desc' => "천간: {$myH}-{$todayH} {$stemRel} • 지지: {$myE}-{$todayE} {$branchRel} ({$sinsalName})",
            'stem_rel' => $stemRel,
            'branch_rel' => $branchRel,
            'sinsal' => $sinsalName,
            'summary' => $summaryText,
            'score_health' => $scores['health'],
            'score_love' => $scores['love'],
            'score_wealth' => $scores['wealth'],
            'score_total' => $scores['total'],
            'lucky_keyword' => $this->getLuckyKeyword($myE, $birthIndex),
        ];
    }

    /**
     * 12개 띠 총평 카드 연산
     */
    private function calculateZodiacCard(string $animalJi, array $individuals, Saju $todaySaju): object
    {
        $todayE = $todaySaju->day->e->ch;
        $relation = $this->evaluateBranchRelation($animalJi, $todayE);
        $sinsal = Sinsal12::cal($animalJi, $todayE) ?? '평살';

        $avgScore = (int) round(collect($individuals)->avg('score_total'));

        // 결정론적 헤드라인 인출
        $seed = crc32($animalJi.$todaySaju->day->ch);
        $headlines = config("pondol-fortune-zodiac.headlines.{$relation}") ?? [
            '평온한 리듬 속에서 알짜배기 실속을 챙기는 하루입니다.',
        ];
        $headline = $headlines[$seed % count($headlines)];

        $names = [
            '子' => '쥐띠', '丑' => '소띠', '寅' => '호랑이띠', '卯' => '토끼띠',
            '辰' => '용띠', '巳' => '뱀띠', '午' => '말띠',     '未' => '양띠',
            '申' => '원숭이띠', '酉' => '닭띠', '戌' => '개띠',   '亥' => '돼지띠',
        ];

        return (object) [
            'ji' => $animalJi,
            'name' => $names[$animalJi] ?? '띠',
            'headline' => $headline,
            'avg_score' => $avgScore,
            'star_rating' => round($avgScore / 20, 1),
            'relation' => $relation,
            'sinsal' => $sinsal,
            'best_partner' => $names[self::YOOKHAP_JI[$animalJi]] ?? '',  // 오늘 가장 합이 좋은 띠
            'avoid_partner' => $names[self::OPPOSITE_JI[$animalJi]] ?? '', // 오늘 주의할 띠
            'tags' => $this->getZodiacTags($relation, $sinsal),
            'individuals' => $individuals,
        ];
    }

    /**
     * Interactions의 지지 상호작용 규칙 판별
     */
    private function evaluateBranchRelation(string $e1, string $e2): string
    {
        $pair = $e1.$e2;
        $revPair = $e2.$e1;

        // 1. 칠충 (七沖)
        if (isset(self::OPPOSITE_JI[$e1]) && self::OPPOSITE_JI[$e1] === $e2) {
            return '충';
        }

        // 2. 육합 (六合)
        if (isset(self::YOOKHAP_JI[$e1]) && self::YOOKHAP_JI[$e1] === $e2) {
            return '육합';
        }

        // 3. 삼합(반합) 군 확인 (申子辰, 寅午戌, 巳酉丑, 亥卯未)
        $samhap = [
            ['申', '子', '辰'], ['寅', '午', '戌'],
            ['巳', '酉', '丑'], ['亥', '卯', '未'],
        ];
        foreach ($samhap as $group) {
            if (in_array($e1, $group) && in_array($e2, $group) && $e1 !== $e2) {
                return '삼합';
            }
        }

        // 4. 형(刑) 살
        $hyeongPairs = ['寅巳', '巳寅', '巳申', '申巳', '寅申', '申寅', '丑戌', '戌丑', '戌未', '未戌', '丑未', '未丑', '子卯', '卯子'];
        if (in_array($pair, $hyeongPairs) || ($e1 === $e2 && in_array($e1, ['辰', '午', '酉', '亥']))) {
            return '형';
        }

        // 5. 원진 (怨嗔)
        $wonjinPairs = ['子未', '未子', '丑午', '午丑', '寅酉', '酉寅', '卯申', '申卯', '辰亥', '亥辰', '巳戌', '戌巳'];
        if (in_array($pair, $wonjinPairs)) {
            return '원진';
        }

        // 6. 파(破) & 해(害)
        $paPairs = ['子酉', '酉子', '丑辰', '辰丑', '寅亥', '亥寅', '卯午', '午卯', '巳申', '申巳', '戌未', '未戌'];
        if (in_array($pair, $paPairs)) {
            return '파';
        }

        $haePairs = ['子未', '未子', '丑午', '午丑', '寅巳', '巳寅', '卯辰', '辰卯', '申亥', '亥申', '酉戌', '戌酉'];
        if (in_array($pair, $haePairs)) {
            return '해';
        }

        return '평';
    }

    /**
     * 점수 산출 공식
     */
    private function calculateScores(string $stemRel, string $branchRel, int $seed): array
    {
        $health = 75;
        $love = 75;
        $wealth = 75;

        // 십신 보정
        match ($stemRel) {
            '정재', '편재' => [$wealth += 18, $love += 5],
            '정관', '정인' => [$love += 15, $wealth += 10],
            '식신' => [$health += 10, $wealth += 10],
            '상관' => [$love -= 10, $wealth += 5],
            '편관' => [$health -= 15, $love -= 10],
            '겁재' => [$wealth -= 15, $health -= 5],
            default => null,
        };

        // 지지 관계 보정
        match ($branchRel) {
            '육합', '삼합' => [$love += 12, $wealth += 8, $health += 5],
            '충' => [$health -= 15, $love -= 15, $wealth -= 10],
            '형', '원진' => [$health -= 10, $love -= 12],
            '파', '해' => [$love -= 8, $wealth -= 5],
            default => null,
        };

        // 시드 기반 1~3점 미세 분산
        $offset = ($seed % 5) - 2;
        $health = max(40, min(99, $health + $offset));
        $love = max(40, min(99, $love + $offset));
        $wealth = max(40, min(99, $wealth + $offset));

        $total = (int) round(($health * 0.3) + ($love * 0.3) + ($wealth * 0.4));

        return [
            'health' => $health,
            'love' => $love,
            'wealth' => $wealth,
            'total' => $total,
        ];
    }

    private function getSeasonName(string $monthE): string
    {
        return match ($monthE) {
            '寅', '卯', '辰' => '봄',
            '巳', '午', '未' => '여름',
            '申', '酉', '戌' => '가을',
            default => '겨울',
        };
    }

    private function getLuckyKeyword(string $ji, int $seed): string
    {
        $keywords = [
            '子' => ['블루 머그컵', '메모 습관', '심야 산책'],
            '丑' => ['브라운 지갑', '따뜻한 차', '원칙 준수'],
            '寅' => ['그린 셔츠', '등산/운동', '당당한 태도'],
            '卯' => ['실크 스카프', '화분 가꾸기', '부드러운 대화'],
            '辰' => ['골드 액세서리', '큰 그림 그리기', '자신감'],
            '巳' => ['선글라스', '신중한 서류 검토', '레드 포인트'],
            '午' => ['러닝화', '빠른 결단', '활기찬 인사'],
            '未' => ['노란색 노트', '동료 배려', '달콤한 디저트'],
            '申' => ['스마트 워치', '임기응변', '금속 볼펜'],
            '酉' => ['화이트 셔츠', '정리정돈', '정확한 계산'],
            '戌' => ['가죽 백팩', '신뢰 지키기', '반려동물 산책'],
            '亥' => ['텀블러', '충분한 수면', '너그러운 양보'],
        ];

        $list = $keywords[$ji] ?? ['따뜻한 차', '신중한 태도'];

        return $list[$seed % count($list)];
    }

    private function getZodiacTags(string $relation, string $sinsal): array
    {
        $tags = ["#{$sinsal}"];
        match ($relation) {
            '충' => array_push($tags, '#변화기회', '#신중모드'),
            '육합' => array_push($tags, '#귀인의도움', '#화합과안정'),
            '삼합' => array_push($tags, '#팀워크', '#시너지폭발'),
            '형' => array_push($tags, '#디테일점검', '#조율필요'),
            '원진' => array_push($tags, '#감정조절', '#마인드컨트롤'),
            default => array_push($tags, '#평온한흐름', '#내실다지기'),
        };

        return $tags;
    }

    private function resolveToJi(string $str): string
    {
        $map = [
            '쥐' => '子', '소' => '丑', '호랑이' => '寅', '토끼' => '卯',
            '용' => '辰', '뱀' => '巳', '말' => '午',     '양' => '未',
            '원숭이' => '申', '닭' => '酉', '개' => '戌',   '돼지' => '亥',
        ];

        return $map[$str] ?? (in_array($str, array_values($map)) ? $str : '子');
    }
}
