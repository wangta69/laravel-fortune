<?php

namespace Pondol\Fortune\Services;

class ZodiacFortune
{
    /** 지지 칠충 (대척점 관계) */
    private const OPPOSITE_JI = [
        '子' => '午', '丑' => '未', '寅' => '申', '卯' => '酉',
        '辰' => '戌', '巳' => '亥', '午' => '子', '未' => '丑',
        '申' => '寅', '酉' => '卯', '戌' => '辰', '亥' => '巳',
    ];

    /** 12지지별 12진법 서기 연도 나머지 매핑 ($year % 12) */
    private const JI_YEAR_REMAINDER = [
        '申' => 0, '酉' => 1, '戌' => 2, '亥' => 3,
        '子' => 4, '丑' => 5, '寅' => 6, '卯' => 7,
        '辰' => 8, '巳' => 9, '午' => 10, '未' => 11,
    ];

    /** 12간지 천문 순서 (자:1 ~ 해:12) */
    private const ZODIAC_CYCLE = [
        '子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥',
    ];

    /**
     * 12간지 동물별 대표 출생연도 목록 동적 생성 (영구 무유지보수형)
     *
     * @param  string  $animalJi  12지 한자 ('子', '丑' 등)
     * @param  int  $count  추출할 세대 수 (기본 5개)
     * @param  int|null  $maxYear  기준 상한 연도 (미지정 시 현재 연도 기준 유효 연령 자동 산출)
     * @return array<int> 예: [1960, 1972, 1984, 1996, 2008]
     */
    public function getYearsForAnimal(string $animalJi, int $count = 5, ?int $maxYear = null): array
    {
        // 미지정 시 운세를 소비하기 시작하는 청소년/성인(약 12세 이상) 연령대를 최신 세대로 자동 계산
        $baseYear = $maxYear ?? (now()->year - 8);
        $remainder = self::JI_YEAR_REMAINDER[$animalJi] ?? 4;

        // baseYear 이하에서 해당 띠의 가장 최근 연도 역산
        $diff = ($baseYear % 12) - $remainder;
        if ($diff < 0) {
            $diff += 12;
        }
        $latestYear = $baseYear - $diff;

        $years = [];
        for ($i = 0; $i < $count; $i++) {
            $years[] = $latestYear - ($i * 12);
        }

        // 오름차순(과거 -> 최근) 정렬
        sort($years);

        return $years;
    }

    /**
     * 12개 띠 전체의 출생연도 맵을 동적으로 한 번에 반환
     */
    public function getZodiacGenerations(int $count = 5, ?int $maxYear = null): array
    {
        $generations = [];
        foreach (self::ZODIAC_CYCLE as $ji) {
            $generations[$ji] = $this->getYearsForAnimal($ji, $count, $maxYear);
        }

        return $generations;
    }

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
        $todayDayH = $todaySaju->day->h->ch;
        $todayDayE = $todaySaju->day->e->ch;
        $todayMonthE = $todaySaju->month->e->ch;

        $summaries = [];
        $individuals = [];

        foreach (self::ZODIAC_CYCLE as $animalJi) {
            // 2. 해당 띠에 속한 각 생년별 세대 목록 동적 획득 (5개)
            $animalYears = $this->getYearsForAnimal($animalJi, 5);
            $animalIndividualList = [];

            // 3. 개별 생년 운세 연산
            foreach ($animalYears as $birthYear) {
                $indFortune = $this->calculateIndividual($birthYear, $todaySaju);
                $animalIndividualList[] = $indFortune;
                $individuals[] = $indFortune;
            }

            // 4. 12개 띠 총평 카드 연산
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

        // 간지 문자열 입력 시 유효한 대표 출생연도로 자동 변환
        $representativeYear = $this->convertGanjiToYear((string) $birthYearOrGanji);

        return $this->calculateIndividual($representativeYear, $todaySaju);
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
        $stemRel = Sipsin::cal($myH, $todayH, 'h') ?? '비견';
        $sinsalName = Sinsal12::cal($myE, $todayE) ?? '평살';

        // 3. 지지 상호작용 판별
        $branchRel = $this->evaluateBranchRelation($myE, $todayE);

        // 4. 결정론적 시드 인덱스 산출 (Saju::$jiMeta 직접 참조)
        $dayIndex = Saju::getGanji60Index($todaySaju->day->ch);
        $birthIndex = Saju::getGanji60Index($birthGanjiCh);
        $monthIndex = Saju::$jiMeta[$todayMonthE]['order'] ?? 1;

        // 계절 이름 사전 선언
        $seasonName = $this->getSeasonName($todayMonthE);

        // [무결점 공식] 인덱스 및 풀 크기 계산
        $themePoolCount = count(config("pondol-fortune-zodiac.theme.{$stemRel}") ?? []);
        $themeIdx = ($dayIndex + $birthIndex + intdiv($birthIndex, 10)) % ($themePoolCount ?: 15);

        $actionPoolCount = count(config("pondol-fortune-zodiac.action.{$branchRel}") ?? []);
        $actionIdx = ($dayIndex + $monthIndex + intdiv($birthIndex, 12) * 3) % ($actionPoolCount ?: 15);

        $seasonPoolCount = count(config("pondol-fortune-zodiac.season.{$seasonName}") ?? []);
        $seasonIdx = ($monthIndex + $birthIndex + intdiv($birthIndex, 12)) % ($seasonPoolCount ?: 15);

        // 5. config/pondol-fortune-zodiac.php 파일에서 멘트 인출
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
            'animal_ko' => Saju::$jiMeta[$myE]['animal'] ?? $birthSaju->year->e->animal,
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
            'lucky_keyword' => $this->getLuckyKeyword($myE, $birthIndex, $dayIndex),
        ];
    }

    /**
     * 12개 띠 총평 카드 연산 (Saju::$jiMeta 공개 프로퍼티 직접 활용)
     */
    private function calculateZodiacCard(string $animalJi, array $individuals, Saju $todaySaju): object
    {
        $todayE = $todaySaju->day->e->ch;
        $relation = $this->evaluateBranchRelation($animalJi, $todayE);
        $sinsal = Sinsal12::cal($animalJi, $todayE) ?? '평살';

        $avgScore = (int) round(collect($individuals)->avg('score_total'));

        // Saju::$jiMeta 표준 데이터 직접 활용
        $animalOrder = Saju::$jiMeta[$animalJi]['order'] ?? 1;
        $dayIndex = Saju::getGanji60Index($todaySaju->day->ch);

        $headlines = config("pondol-fortune-zodiac.headlines.{$relation}") ?? [
            '평온한 리듬 속에서 알짜배기 실속을 챙기는 하루입니다.',
        ];
        $count = count($headlines);

        // 당일 12개 띠 100% 비중복 + 한 달간 매일 회전하는 무결점 모듈러 공식
        $headlineIdx = ($dayIndex * 3 + $animalOrder * 7) % $count;
        $headline = $headlines[$headlineIdx];

        // 동물 이름 및 궁합 띠 산출 (Saju::$jiMeta 기반)
        $animalName = (Saju::$jiMeta[$animalJi]['animal'] ?? '').'띠';

        $yookhapJi = Saju::$jiMeta[$animalJi]['yookhap'] ?? '';
        $bestPartner = ! empty($yookhapJi) ? (Saju::$jiMeta[$yookhapJi]['animal'].'띠') : '';

        $oppositeJi = self::OPPOSITE_JI[$animalJi] ?? '';
        $avoidPartner = ! empty($oppositeJi) ? (Saju::$jiMeta[$oppositeJi]['animal'].'띠') : '';

        return (object) [
            'ji' => $animalJi,
            'name' => $animalName,
            'headline' => $headline,
            'avg_score' => $avgScore,
            'star_rating' => round($avgScore / 20, 1),
            'relation' => $relation,
            'sinsal' => $sinsal,
            'best_partner' => $bestPartner,
            'avoid_partner' => $avoidPartner,
            'tags' => $this->getZodiacTags($relation, $sinsal),
            'individuals' => $individuals,
        ];
    }

    /**
     * 지지 상호작용 규칙 판별
     */
    private function evaluateBranchRelation(string $e1, string $e2): string
    {
        $pair = $e1.$e2;

        // 1. 칠충 (七沖)
        if (isset(self::OPPOSITE_JI[$e1]) && self::OPPOSITE_JI[$e1] === $e2) {
            return '충';
        }

        // 2. 육합 (六合, Saju::$jiMeta 참조)
        if (isset(Saju::$jiMeta[$e1]['yookhap']) && Saju::$jiMeta[$e1]['yookhap'] === $e2) {
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

        match ($stemRel) {
            '정재', '편재' => [$wealth += 18, $love += 5],
            '정관', '정인' => [$love += 15, $wealth += 10],
            '식신' => [$health += 10, $wealth += 10],
            '상관' => [$love -= 10, $wealth += 5],
            '편관' => [$health -= 15, $love -= 10],
            '겁재' => [$wealth -= 15, $health -= 5],
            default => null,
        };

        match ($branchRel) {
            '육합', '삼합' => [$love += 12, $wealth += 8, $health += 5],
            '충' => [$health -= 15, $love -= 15, $wealth -= 10],
            '형', '원진' => [$health -= 10, $love -= 12],
            '파', '해' => [$love -= 8, $wealth -= 5],
            default => null,
        };

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

    private function getLuckyKeyword(string $ji, int $birthIndex, int $dayIndex): string
    {
        $keywords = [
            '子' => ['블루 머그컵', '메모 습관', '심야 산책', '네이비 셔츠', '따뜻한 차', '가죽 카드지갑', '은색 볼펜', '비타민 음료', '정리정돈', '클래식 음악', '스마트 워치', '독서'],
            '丑' => ['브라운 지갑', '따뜻한 둥굴레차', '원칙 준수', '베이지 니트', '나무 책갈피', '편안한 운동화', '황동 키링', '견과류 간식', '단정한 옷차림', '우직한 침묵', '체크무늬 손수건', '가죽 다이어리'],
            '寅' => ['그린 셔츠', '등산/조깅', '당당한 태도', '스포티한 백팩', '허브티', '우드 만년필', '에스프레소', '피트니스', '과감한 결단', '호안석 팔찌', '활동적인 스니커즈', '향수'],
            '卯' => ['실크 스카프', '화분 가꾸기', '부드러운 미소', '파스텔톤 노트', '캐모마일 차', '핸드크림', '클립형 북마크', '민트 캔디', '밝은색 양말', '클래식 안경', '스트레칭', '미니 손거울'],
            '辰' => ['골드 액세서리', '큰 그림 그리기', '자신감 넘치는 말투', '블랙 재킷', '루이보스티', '메탈 시계', '고급 만년필', '선글라스', '당당한 걸음걸이', '비즈니스 명함지갑', '홍차', '향초'],
            '巳' => ['선글라스', '신중한 서류 검토', '레드 넥타이/스카프', '가죽 벨트', '진한 아메리카노', '정밀 볼펜', '태블릿 PC', '차분한 관찰', '다크초콜릿', '스마트폰 링', '차량 방향제', '단호한 거절'],
            '午' => ['러닝화', '빠른 결단력', '활기찬 인사', '오렌지색 소품', '이온 음료', '무선 이어폰', '스포츠 타월', '시원한 탄산수', '빠른 걸음', '가벼운 윈드브레이커', '자전거 타기', '비타민C'],
            '未' => ['노란색 메모지', '동료 배려하기', '달콤한 디저트', '포근한 가디건', '밀크티', '도자기 머그컵', '부드러운 담요', '아로마 오일', '따뜻한 격려', '꽃 한 송이', '쿠키 한 조각', '칭찬 건네기'],
            '申' => ['스마트 워치', '재치 있는 유머', '금속 볼펜', '그레이 슬랙스', '에너지 드링크', '노트북 파우치', '다기능 멀티툴', '새로운 앱 사용', '빠른 타이핑', '민트향 껌', '무선 마우스', '휴대용 보조배터리'],
            '酉' => ['화이트 셔츠', '주변 정리정돈', '정확한 시간 엄수', '은장 볼펜', '얼그레이 차', '깔끔한 손톱 손질', '각 잡힌 서류철', '손목시계 확인', '구두 닦기', '정갈한 식사', '메탈 프레임 안경', '영수증 정리'],
            '戌' => ['가죽 백팩', '신뢰 지키기', '반려동물 산책', '카키색 점퍼', '보리차', '튼튼한 텀블러', '워커 부츠', '진심 어린 경청', '우직한 약속 이행', '캐주얼 캡모자', '등산용 양말', '단단한 키체인'],
            '亥' => ['보온 텀블러', '충분한 수면', '너그러운 양보', '블랙 롱패딩/코트', '유자차', '푹신한 방석', '목베개', '따뜻한 족욕', '해산물 요리', '느긋한 마음가짐', '수면 안대', '힐링 영상 시청'],
        ];

        $list = $keywords[$ji] ?? ['따뜻한 차', '신중한 태도', '정리정돈'];

        // 같은 띠 5명(0, 12, 24, 36, 48)에게 고유 인덱스를 주어 100% 비중복 보장
        $generationOffset = intdiv($birthIndex, 12) * 5;
        $itemIdx = ($dayIndex * 2 + $generationOffset) % count($list);

        return $list[$itemIdx];
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

    /**
     * 동물 이름/한자 문자열을 표준 지지 한자('子'~'亥')로 정규화 (Saju::$jiMeta 기반)
     */
    private function resolveToJi(string $str): string
    {
        $cleanStr = str_replace('띠', '', trim($str));

        foreach (Saju::$jiMeta as $ji => $meta) {
            if ($cleanStr === $meta['animal'] || $cleanStr === $meta['ko'] || $cleanStr === $ji) {
                return $ji;
            }
        }

        return '子';
    }

    /**
     * 간지 문자열('갑자', '甲子' 등)을 현재 유효 연령대의 대표 연도로 역산 변환
     */
    private function convertGanjiToYear(string $ganji): int
    {
        $ganjiIdx = Saju::getGanji60Index($ganji); // 1 ~ 60
        $baseYear = 1984 + ($ganjiIdx - 1); // 1984년이 갑자(1번)

        $currentYear = now()->year;
        while ($baseYear < ($currentYear - 60)) {
            $baseYear += 60;
        }
        while ($baseYear > $currentYear) {
            $baseYear -= 60;
        }

        return $baseYear;
    }
}
