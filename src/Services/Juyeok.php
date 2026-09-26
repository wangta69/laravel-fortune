<?php

namespace Pondol\Fortune\Services;

/**
 * Class Juyeok (주역 64괘 및 정통 육효 엔진)
 *
 * 본 클래스는 동양 역학의 정수인 주역(周易)과 정통 육효(六爻) 점학을 수행하는 통합 엔진입니다.
 *
 * [주요 기능]
 * 1. 선천괘 (Innate Trigram): 매화역수(梅花易數) 기법을 이용해 평생의 본질적인 운명괘를 산출합니다.
 * 2. 후천괘 (Temporal Trigram): 체용법(體用法)을 이용해 특정 시점(연운, 월운, 일진, 시진)의 기운을 산출합니다.
 * 3. 변괘 도출 (Transformed Trigram): 동효(動爻: 움직이는 효)의 비트를 반전시켜 미래의 전개 방향(지괘)을 계산합니다.
 * 4. 육효 분석 (Yukhyo Engine): 세효(世: 나), 응효(應: 상대방), 6대 신수(육수: 청룡~현무)를 자동 배치합니다.
 *
 * @author  Pondol
 *
 * @version 2.0.0
 *
 * @example
 * ```php
 * // 기본 인스턴스 생성
 * $juyeok = new \Pondol\Fortune\Services\Juyeok();
 *
 * // 1. 오늘의 주역 괘 산출 (J009 테이블 연동)
 * $temporal = $juyeok->getTemporalGwe($userSaju, $todaySaju, 'day');
 * $luckCode = $temporal->sangwe . $temporal->hagwe; // 예: "11", "81"
 *
 * // 2. 육효점 종합 분석 (본괘, 변괘, 세응, 육수)
 * $reading = $juyeok->getYukhyoFullReading($userSaju, $todaySaju, '재물');
 * ```
 */
class Juyeok
{
    /**
     * 8괘 배정을 위한 일간-지지 조합 테이블 (천간 체용)
     */
    private array $ganMap = [
        '乾' => ['甲寅', '甲午', '甲戌', '丙申', '丙子', '丙辰', '戊亥', '戊卯', '戊未', '庚巳', '庚酉', '庚丑', '壬寅', '壬午', '壬戌'],
        '兌' => ['乙寅', '乙午', '乙戌', '丁申', '丁子', '丁辰', '己亥', '己卯', '己未', '辛巳', '辛酉', '辛丑', '癸寅', '癸午', '癸戌'],
        '離' => ['甲巳', '甲酉', '甲丑', '丙寅', '丙午', '丙戌', '戊申', '戊子', '戊辰', '庚亥', '庚卯', '庚未', '壬巳', '壬酉', '壬丑'],
        '震' => ['乙巳', '乙酉', '乙丑', '丁寅', '丁午', '丁戌', '己申', '己子', '己辰', '辛亥', '辛卯', '辛未', '癸巳', '癸酉', '癸丑'],
        '巽' => ['甲亥', '甲卯', '甲未', '丙巳', '丙酉', '丙丑', '戊寅', '戊午', '戊戌', '庚申', '庚子', '庚辰', '壬亥', '壬卯', '壬未'],
        '坎' => ['乙亥', '乙卯', '乙未', '丁巳', '丁酉', '丁丑', '己寅', '己午', '己戌', '辛申', '辛子', '辛辰', '癸亥', '癸卯', '癸未'],
        '艮' => ['甲申', '甲子', '甲辰', '丙亥', '丙卯', '丙未', '戊巳', '戊酉', '戊丑', '庚寅', '庚午', '庚戌', '壬申', '壬子', '壬辰'],
        '坤' => ['乙申', '乙子', '乙辰', '丁亥', '丁卯', '丁未', '己巳', '己酉', '己丑', '辛寅', '辛午', '辛戌', '癸申', '癸子', '癸辰'],
    ];

    /**
     * 8괘 배정을 위한 일지-지지 조합 테이블 (지지 체용)
     */
    private array $jiMap = [
        '乾' => ['子寅', '子午', '子戌', '卯申', '卯子', '卯辰', '辰巳', '辰酉', '辰丑', '未亥', '未卯', '未未', '申寅', '申午', '申戌', '亥申', '亥子', '亥辰'],
        '兌' => ['丑寅', '丑午', '丑戌', '寅申', '寅子', '寅辰', '巳巳', '巳酉', '巳丑', '午亥', '午卯', '午未', '酉寅', '酉午', '酉戌', '戌申', '戌子', '戌辰'],
        '離' => ['丑申', '丑子', '丑辰', '寅寅', '寅午', '寅戌', '巳亥', '巳卯', '巳未', '午巳', '午酉', '午丑', '酉申', '酉子', '酉辰', '戌寅', '戌午', '戌戌'],
        '震' => ['子申', '子子', '子辰', '卯寅', '卯午', '卯戌', '辰亥', '辰卯', '辰未', '未巳', '未酉', '未丑', '申申', '申子', '申辰', '亥寅', '亥午', '亥戌'],
        '巽' => ['子巳', '子酉', '子丑', '卯亥', '卯卯', '卯未', '辰寅', '辰午', '辰戌', '未申', '未子', '未辰', '申巳', '申酉', '申丑', '亥亥', '亥卯', '亥未'],
        '坎' => ['丑巳', '丑酉', '丑丑', '寅亥', '寅卯', '寅未', '巳寅', '巳午', '巳戌', '午申', '午子', '午辰', '酉巳', '酉酉', '酉丑', '戌亥', '戌卯', '戌未'],
        '艮' => ['丑亥', '丑卯', '丑未', '寅巳', '寅酉', '寅丑', '巳申', '巳子', '巳辰', '午寅', '午午', '午戌', '酉亥', '酉卯', '酉未', '戌巳', '戌酉', '戌丑'],
        '坤' => ['子亥', '子卯', '子未', '卯巳', '卯酉', '卯丑', '辰申', '辰子', '辰辰', '未寅', '未午', '未戌', '申亥', '申卯', '申未', '亥巳', '亥酉', '亥丑'],
    ];

    /**
     * 주역 64괘 마스터 데이터
     * - code: 하괘 3비트 + 상괘 3비트 (2진수 문자열)
     * - que8: [상괘명, 하괘명]
     */
    private array $juyeokMap = [
        // 하괘가 건(111)인 그룹
        ['code' => '111000', 'ko' => '지천태', 'ch' => '地天泰', 'que8' => ['곤', '건'], 'image' => ['gon', 'gun']],
        ['code' => '111001', 'ko' => '산천대축', 'ch' => '山天大畜', 'que8' => ['간', '건'], 'image' => ['gan', 'gun']],
        ['code' => '111010', 'ko' => '수천수', 'ch' => '水天需', 'que8' => ['감', '건'], 'image' => ['gam', 'gun']],
        ['code' => '111011', 'ko' => '풍천소축', 'ch' => '風天小畜', 'que8' => ['손', '건'], 'image' => ['son', 'gun']],
        ['code' => '111100', 'ko' => '뇌천대장', 'ch' => '雷天大壯', 'que8' => ['진', '건'], 'image' => ['jin', 'gun']],
        ['code' => '111101', 'ko' => '화천대유', 'ch' => '火天大有', 'que8' => ['이', '건'], 'image' => ['lee', 'gun']],
        ['code' => '111110', 'ko' => '택천쾌', 'ch' => '澤天夬', 'que8' => ['태', '건'], 'image' => ['tae', 'gun']],
        ['code' => '111111', 'ko' => '건위천', 'ch' => '乾爲天', 'que8' => ['건', '건'], 'image' => ['gun', 'gun']],

        // 하괘가 태(110)인 그룹
        ['code' => '110000', 'ko' => '지택림', 'ch' => '地澤臨', 'que8' => ['곤', '태'], 'image' => ['gon', 'tae']],
        ['code' => '110001', 'ko' => '산택손', 'ch' => '山澤損', 'que8' => ['간', '태'], 'image' => ['gan', 'tae']],
        ['code' => '110010', 'ko' => '수택절', 'ch' => '水澤節', 'que8' => ['감', '태'], 'image' => ['gam', 'tae']],
        ['code' => '110011', 'ko' => '풍택중부', 'ch' => '風澤中孚', 'que8' => ['손', '태'], 'image' => ['son', 'tae']],
        ['code' => '110100', 'ko' => '뇌택귀매', 'ch' => '雷澤歸妹', 'que8' => ['진', '태'], 'image' => ['jin', 'tae']],
        ['code' => '110101', 'ko' => '화택규', 'ch' => '火澤睽', 'que8' => ['이', '태'], 'image' => ['lee', 'tae']],
        ['code' => '110110', 'ko' => '태위택', 'ch' => '兌爲澤', 'que8' => ['태', '태'], 'image' => ['tae', 'tae']],
        ['code' => '110111', 'ko' => '천택리', 'ch' => '天澤履', 'que8' => ['건', '태'], 'image' => ['gun', 'tae']],

        // 하괘가 이(101)인 그룹
        ['code' => '101000', 'ko' => '지화명이', 'ch' => '地火明夷', 'que8' => ['곤', '이'], 'image' => ['gon', 'lee']],
        ['code' => '101001', 'ko' => '산화비', 'ch' => '山火賁', 'que8' => ['간', '이'], 'image' => ['gan', 'lee']],
        ['code' => '101010', 'ko' => '수화기제', 'ch' => '水火旣濟', 'que8' => ['감', '이'], 'image' => ['gam', 'lee']],
        ['code' => '101011', 'ko' => '풍화가인', 'ch' => '風火家人', 'que8' => ['손', '이'], 'image' => ['son', 'lee']],
        ['code' => '101100', 'ko' => '뇌화풍', 'ch' => '雷火豊', 'que8' => ['진', '이'], 'image' => ['jin', 'lee']],
        ['code' => '101101', 'ko' => '이위화', 'ch' => '離爲火', 'que8' => ['이', '이'], 'image' => ['lee', 'lee']],
        ['code' => '101110', 'ko' => '택화혁', 'ch' => '澤火革', 'que8' => ['태', '이'], 'image' => ['tae', 'lee']],
        ['code' => '101111', 'ko' => '천화동인', 'ch' => '天火同人', 'que8' => ['건', '이'], 'image' => ['gun', 'lee']],

        // 하괘가 진(100)인 그룹
        ['code' => '100000', 'ko' => '지뢰복', 'ch' => '地雷復', 'que8' => ['곤', '진'], 'image' => ['gon', 'jin']],
        ['code' => '100001', 'ko' => '산뢰이', 'ch' => '山雷頤', 'que8' => ['간', '진'], 'image' => ['gan', 'jin']],
        ['code' => '100010', 'ko' => '수뢰둔', 'ch' => '水雷屯', 'que8' => ['감', '진'], 'image' => ['gam', 'jin']],
        ['code' => '100011', 'ko' => '풍뢰익', 'ch' => '風雷益', 'que8' => ['손', '진'], 'image' => ['son', 'jin']],
        ['code' => '100100', 'ko' => '진위뢰', 'ch' => '震爲雷', 'que8' => ['진', '진'], 'image' => ['jin', 'jin']],
        ['code' => '100101', 'ko' => '화뢰서합', 'ch' => '火雷噬嗑', 'que8' => ['이', '진'], 'image' => ['lee', 'jin']],
        ['code' => '100110', 'ko' => '택뢰수', 'ch' => '澤雷隨', 'que8' => ['태', '진'], 'image' => ['tae', 'jin']],
        ['code' => '100111', 'ko' => '천뢰무망', 'ch' => '天雷無妄', 'que8' => ['건', '진'], 'image' => ['gun', 'jin']],

        // 하괘가 손(011)인 그룹
        ['code' => '011000', 'ko' => '지풍승', 'ch' => '地風升', 'que8' => ['곤', '손'], 'image' => ['gon', 'son']],
        ['code' => '011001', 'ko' => '산풍고', 'ch' => '山風蠱', 'que8' => ['간', '손'], 'image' => ['gan', 'son']],
        ['code' => '011010', 'ko' => '수풍정', 'ch' => '水風井', 'que8' => ['감', '손'], 'image' => ['gam', 'son']],
        ['code' => '011011', 'ko' => '손위풍', 'ch' => '巽爲風', 'que8' => ['손', '손'], 'image' => ['son', 'son']],
        ['code' => '011100', 'ko' => '뇌풍항', 'ch' => '雷風恒', 'que8' => ['진', '손'], 'image' => ['jin', 'son']],
        ['code' => '011101', 'ko' => '화풍정', 'ch' => '火風鼎', 'que8' => ['이', '손'], 'image' => ['lee', 'son']],
        ['code' => '011110', 'ko' => '택풍대과', 'ch' => '澤風大過', 'que8' => ['태', '손'], 'image' => ['tae', 'son']],
        ['code' => '011111', 'ko' => '천풍구', 'ch' => '天風姤', 'que8' => ['건', '손'], 'image' => ['gun', 'son']],

        // 하괘가 감(010)인 그룹
        ['code' => '010000', 'ko' => '지수사', 'ch' => '地水師', 'que8' => ['곤', '감'], 'image' => ['gon', 'gam']],
        ['code' => '010001', 'ko' => '산수몽', 'ch' => '山水蒙', 'que8' => ['간', '감'], 'image' => ['gan', 'gam']],
        ['code' => '010010', 'ko' => '감위수', 'ch' => '坎爲水', 'que8' => ['감', '감'], 'image' => ['gam', 'gam']],
        ['code' => '010011', 'ko' => '풍수환', 'ch' => '風水渙', 'que8' => ['손', '감'], 'image' => ['son', 'gam']],
        ['code' => '010100', 'ko' => '뇌수해', 'ch' => '雷水解', 'que8' => ['진', '감'], 'image' => ['jin', 'gam']],
        ['code' => '010101', 'ko' => '화수미제', 'ch' => '火水未濟', 'que8' => ['이', '감'], 'image' => ['lee', 'gam']],
        ['code' => '010110', 'ko' => '택수곤', 'ch' => '澤水困', 'que8' => ['태', '감'], 'image' => ['tae', 'gam']],
        ['code' => '010111', 'ko' => '천수송', 'ch' => '天水訟', 'que8' => ['건', '감'], 'image' => ['gun', 'gam']],

        // 하괘가 간(001)인 그룹
        ['code' => '001000', 'ko' => '지산겸', 'ch' => '地山謙', 'que8' => ['곤', '간'], 'image' => ['gon', 'gan']],
        ['code' => '001001', 'ko' => '간위산', 'ch' => '艮爲山', 'que8' => ['간', '간'], 'image' => ['gan', 'gan']],
        ['code' => '001010', 'ko' => '수산건', 'ch' => '水山蹇', 'que8' => ['감', '간'], 'image' => ['gam', 'gan']],
        ['code' => '001011', 'ko' => '풍산점', 'ch' => '風山漸', 'que8' => ['손', '간'], 'image' => ['son', 'gan']],
        ['code' => '001100', 'ko' => '뇌산소과', 'ch' => '雷山小過', 'que8' => ['진', '간'], 'image' => ['jin', 'gan']],
        ['code' => '001101', 'ko' => '화산려', 'ch' => '火山旅', 'que8' => ['이', '간'], 'image' => ['lee', 'gan']],
        ['code' => '001110', 'ko' => '택산함', 'ch' => '澤山咸', 'que8' => ['태', '간'], 'image' => ['tae', 'gan']],
        ['code' => '001111', 'ko' => '천산돈', 'ch' => '天山遯', 'que8' => ['건', '간'], 'image' => ['gun', 'gan']],

        // 하괘가 곤(000)인 그룹
        ['code' => '000000', 'ko' => '곤위지', 'ch' => '坤爲地', 'que8' => ['곤', '곤'], 'image' => ['gon', 'gon']],
        ['code' => '000001', 'ko' => '산지박', 'ch' => '山地剝', 'que8' => ['간', '곤'], 'image' => ['gan', '곤']],
        ['code' => '000010', 'ko' => '수지비', 'ch' => '水地比', 'que8' => ['감', '곤'], 'image' => ['gam', '곤']],
        ['code' => '000011', 'ko' => '풍지관', 'ch' => '風地觀', 'que8' => ['손', '곤'], 'image' => ['son', '곤']],
        ['code' => '000100', 'ko' => '뇌지예', 'ch' => '雷地豫', 'que8' => ['진', '곤'], 'image' => ['jin', '곤']],
        ['code' => '000101', 'ko' => '화지진', 'ch' => '火地晉', 'que8' => ['이', '곤'], 'image' => ['lee', '곤']],
        ['code' => '000110', 'ko' => '택지췌', 'ch' => '澤地萃', 'que8' => ['태', '곤'], 'image' => ['tae', '곤']],
        ['code' => '000111', 'ko' => '천지비', 'ch' => '天地否', 'que8' => ['건', '곤'], 'image' => ['gun', '곤']],
    ];

    /**
     * 8괘 번호와 3비트 2진수 매핑 (1=양, 0=음)
     * [비트 순서: 초효 -> 2효 -> 3효]
     */
    private array $binaryMap = [
        1 => '111', // 건(乾) ☰
        2 => '110', // 태(兌) ☱
        3 => '101', // 리(離) ☲
        4 => '100', // 진(震) ☳
        5 => '011', // 손(巽) ☴
        6 => '010', // 감(坎) ☵
        7 => '001', // 간(艮) ☶
        8 => '000', // 곤(坤) ☷
    ];

    /**
     * 64괘 맵에서 특정 필드 값으로 괘 데이터 조회
     *
     * @param  string  $field  검색할 필드명 ('code', 'ko', 'ch' 등)
     * @param  string  $v  검색할 값 (예: '111000', '지천태')
     *
     * @example
     * ```php
     * $gwe = $juyeok->map('ko', '지천태');
     * echo $gwe['ch']; // "地天泰"
     * ```
     */
    public function map(string $field, string $v): array
    {
        foreach ($this->juyeokMap as $map) {
            if ($map[$field] === $v) {
                return $map;
            }
        }

        return [];
    }

    /**
     * '선천괘(先天卦)' 계산 - 매화역수(梅花易數) 기반
     * 사용자의 사주 원국(생년월일시)을 조합하여 평생 변하지 않는 운명괘와 동효를 산출합니다.
     *
     * [계산 원리]
     * - 상괘: (년지 + 월지 + 일지) % 8
     * - 하괘: (년지 + 월지 + 일지 + 시지) % 8
     * - 동효: (년지 + 월지 + 일지 + 시지) % 6
     *
     * @param  object  $saju  Saju 만세력 객체
     * @return object { sangwe: int, hagwe: int, donghyo: int, map: array }
     *
     * @example
     * ```php
     * $innate = $juyeok->getInnateGwe($userSaju);
     * echo "선천 상괘: " . $innate->sangwe; // 1~8
     * echo "선천 하괘: " . $innate->hagwe;  // 1~8
     * echo "동효: " . $innate->donghyo . "효"; // 1~6
     * echo "본명괘: " . $innate->map['ko']; // 예: 지천태
     * ```
     */
    public function getInnateGwe(object $saju): object
    {
        $ymd_sum = $saju->get_e_serial('year') + $saju->get_e_serial('month') + $saju->get_e_serial('day');

        $sangweNum = $ymd_sum % 8 ?: 8;

        $total_sum = $ymd_sum + $saju->get_e_serial('hour');
        $hagweNum = $total_sum % 8 ?: 8;

        $donghyo = $total_sum % 6 ?: 6;

        $code = $this->binaryMap[$hagweNum].$this->binaryMap[$sangweNum];
        $map = $this->map('code', $code);

        return (object) [
            'sangwe' => $sangweNum,
            'hagwe' => $hagweNum,
            'donghyo' => $donghyo,
            'map' => $map,
        ];
    }

    /**
     * '후천괘(後天卦)' 계산 - 체용법(體用法) 기반
     * 나의 본질(일주: 體)과 특정 시점의 기운(用)이 부딪혀 발생하는 현재/미래의 운을 산출합니다.
     *
     * [계산 원리]
     * - 상괘: 내 일간 + 목표 시점의 지지 조합 ($ganMap)
     * - 하괘: 내 일지 + 목표 시점의 지지 조합 ($jiMap)
     * - 동효: (내 일간수 + 내 일지수 + 목표 시점 지지수) % 6
     *
     * @param  object  $saju  나의 사주(體) 만세력 객체
     * @param  object  $today  특정 시점(用) 만세력 객체
     * @param  string  $type  'day'(오늘의 운세/방위), 'year'(신년운세/연운), 'month', 'hour'
     * @return object { sangwe: int, hagwe: int, donghyo: int, map: array }
     *
     * @example
     * ```php
     * // 1. 오늘의 행운 방위(J009) 조회 시
     * $temporal = $juyeok->getTemporalGwe($userSaju, $todaySaju, 'day');
     * $luckCode = $temporal->sangwe . $temporal->hagwe; // 예: "31" (화천대유)
     *
     * // 2. 신년 운세 주역 괘 산출 시 (한 해 동안 고정)
     * $yearGwe = $juyeok->getTemporalGwe($userSaju, $targetYearManse, 'year');
     * ```
     */
    public function getTemporalGwe(object $saju, object $today, string $type = 'day'): object
    {
        $myIlgan = $saju->get_h('day');
        $targetJi = $today->get_e($type);
        $sangweHanja = $this->findTrigramByCombination('Gan', $myIlgan, $targetJi);
        $sangweNum = $this->convertHanjaToNum($sangweHanja);

        $myIlji = $saju->get_e('day');
        $hagweHanja = $this->findTrigramByCombination('Ji', $myIlji, $targetJi);
        $hagweNum = $this->convertHanjaToNum($hagweHanja);

        $total_sum = $saju->get_h_serial('day')
                   + $saju->get_e_serial('day')
                   + $today->get_e_serial($type);

        $donghyo = $total_sum % 6 ?: 6;

        $code = $this->binaryMap[$hagweNum].$this->binaryMap[$sangweNum];
        $map = $this->map('code', $code);

        return (object) [
            'sangwe' => $sangweNum,
            'hagwe' => $hagweNum,
            'donghyo' => $donghyo,
            'map' => $map,
        ];
    }

    /**
     * 선천괘(타고난 운명)와 후천괘(오늘의 기운)를 한 번에 조회
     *
     * @param  object  $saju  나의 사주 객체
     * @param  object  $today  오늘의 사주 객체
     *
     * @example
     * ```php
     * $reading = $juyeok->getFullReading($userSaju, $todaySaju);
     * echo $reading->temporal->description; // "나의 본질과 오늘의 기운의 상호작용"
     * echo $reading->innate->donghyo;        // 선천괘 동효
     * ```
     */
    public function getFullReading(object $saju, object $today): object
    {
        $temporalGwe = $this->getTemporalGwe($saju, $today);
        $innateGwe = $this->getInnateGwe($saju);

        return (object) [
            'temporal' => (object) [
                'description' => '나의 본질(일주)과 오늘의 기운의 상호작용 (후천괘)',
                'sangwe' => $this->convertNumToHanja($temporalGwe->sangwe),
                'hagwe' => $this->convertNumToHanja($temporalGwe->hagwe),
            ],
            'innate' => (object) [
                'description' => '사주로 본 나의 타고난 운명괘 (선천괘)',
                'sangwe' => $this->convertNumToHanja($innateGwe->sangwe),
                'hagwe' => $this->convertNumToHanja($innateGwe->hagwe),
                'donghyo' => $innateGwe->donghyo,
            ],
        ];
    }

    /**
     * 상괘 번호(1~8)와 하괘 번호(1~8)로 64괘 마스터 데이터 직접 조회
     *
     * @param  int  $sangwe  상괘 번호 (1:건 ~ 8:곤)
     * @param  int  $hagwe  하괘 번호 (1:건 ~ 8:곤)
     * @return array 64괘 맵 배열 (code, ko, ch, que8, image)
     *
     * @example
     * ```php
     * // 상괘 8(곤), 하괘 1(건) -> 지천태
     * $gwe = $juyeok->getGweByNums(8, 1);
     * echo $gwe['ko']; // "지천태"
     * echo $gwe['ch']; // "地天泰"
     * ```
     */
    public function getGweByNums(int $sangwe, int $hagwe): array
    {
        $code = ($this->binaryMap[$hagwe] ?? '000').($this->binaryMap[$sangwe] ?? '000');

        return $this->map('code', $code);
    }

    /**
     * 동효(動爻)의 비트 반전을 통해 변괘(之卦: 미래의 괘)를 도출
     *
     * [원리]
     * - 64괘 6개 비트 중 지정된 동효(1~6효) 위치의 비트를 0↔1로 반전
     * - 반전된 새로운 6비트 코드로 미래의 변화 결과괘를 즉시 역산
     *
     * @param  int  $sangwe  본괘 상괘 (1~8)
     * @param  int  $hagwe  본괘 하괘 (1~8)
     * @param  int  $donghyo  변한 효 위치 (1~6효)
     * @return object { sangwe: int, hagwe: int, code: string, map: array }
     *
     * @example
     * ```php
     * // 지천태(상괘8, 하괘1)의 3번째 효가 동했을 때 변괘 산출
     * $transformed = $juyeok->getTransformedGwe(8, 1, 3);
     * echo "변괘 이름: " . $transformed->map['ko']; // "지택림"
     * echo "미래 상괘: " . $transformed->sangwe;     // 8
     * echo "미래 하괘: " . $transformed->hagwe;      // 2
     * ```
     */
    public function getTransformedGwe(int $sangwe, int $hagwe, int $donghyo): object
    {
        $code = ($this->binaryMap[$hagwe] ?? '000').($this->binaryMap[$sangwe] ?? '000');

        $flipIdx = $donghyo - 1;
        $flippedBit = ($code[$flipIdx] === '1') ? '0' : '1';
        $newCode = substr_replace($code, $flippedBit, $flipIdx, 1);

        $newHaBit = substr($newCode, 0, 3);
        $newSangBit = substr($newCode, 3, 3);

        $bitToNum = array_flip($this->binaryMap);
        $newHagwe = $bitToNum[$newHaBit] ?? $hagwe;
        $newSangwe = $bitToNum[$newSangBit] ?? $sangwe;

        return (object) [
            'sangwe' => $newSangwe,
            'hagwe' => $newHagwe,
            'code' => $newCode,
            'map' => $this->map('code', $newCode),
        ];
    }

    /**
     * 64괘의 8궁(八宮) 배치 법칙에 따른 세효(世: 나)와 응효(應: 상대방) 위치 계산
     *
     * [원리]
     * - 상괘 3비트와 하괘 3비트의 XOR 비트 차이를 이용한 8궁괘 수학적 판별
     * - 세효: 내가 주도하는 효의 자리 (1~6)
     * - 응효: 상대방이나 대상 사건의 자리 (세효와 3칸 떨어진 자리)
     *
     * @param  int  $sangwe  상괘 번호 (1~8)
     * @param  int  $hagwe  하괘 번호 (1~8)
     * @return array ['se' => int(1~6), 'eung' => int(1~6)]
     *
     * @example
     * ```php
     * // 지천태(상괘8 곤, 하괘1 건)의 세·응 위치
     * $seeung = $juyeok->getSeEung(8, 1);
     * echo "세효(나): " . $seeung['se'] . "효";    // 3효
     * echo "응효(상대): " . $seeung['eung'] . "효"; // 6효
     * ```
     */
    public function getSeEung(int $sangwe, int $hagwe): array
    {
        $haBit = $this->binaryMap[$hagwe] ?? '000';
        $sangBit = $this->binaryMap[$sangwe] ?? '000';

        $diff1 = $haBit[0] !== $sangBit[0]; // 1효 vs 4효
        $diff2 = $haBit[1] !== $sangBit[1]; // 2효 vs 5효
        $diff3 = $haBit[2] !== $sangBit[2]; // 3효 vs 6효

        $se = match (true) {
            ! $diff1 && ! $diff2 && ! $diff3 => 6, // 본궁괘: 6세
            $diff1 && ! $diff2 && ! $diff3 => 1, // 1세괘
            $diff1 && $diff2 && ! $diff3 => 2, // 2세괘
            $diff1 && $diff2 && $diff3 => 3, // 3세괘
            ! $diff1 && $diff2 && $diff3 => 4, // 4세괘
            ! $diff1 && ! $diff2 && $diff3 => 5, // 5세괘
            $diff1 && ! $diff2 && $diff3 => 4, // 유혼괘: 4세
            ! $diff1 && $diff2 && ! $diff3 => 3, // 귀혼괘: 3세
            default => 6,
        };

        $eung = ($se > 3) ? ($se - 3) : ($se + 3);

        return ['se' => $se, 'eung' => $eung];
    }

    /**
     * 오늘 일간(Day Stem)에 따른 육수(六獸: 6대 신수)의 1~6효 자동 배치
     *
     * [배치 순서]
     * - 甲/乙: 청룡(青龍) 시작
     * - 丙/丁: 주작(朱雀) 시작
     * - 戊:    구진(句陳) 시작
     * - 己:    등사(騰蛇) 시작
     * - 庚/辛: 백호(白虎) 시작
     * - 壬/癸: 현무(玄武) 시작
     *
     * @param  string  $dayHanja  오늘 일간 한자 (甲 ~ 癸)
     * @return array [1 => '신수명', 2 => '신수명', ... 6 => '신수명']
     *
     * @example
     * ```php
     * $yuksu = $juyeok->getYuksu('甲');
     * // [1 => '청룡', 2 => '주작', 3 => '구진', 4 => '등사', 5 => '백호', 6 => '현무']
     * echo "3번째 효의 수호신수: " . $yuksu[3]; // "구진"
     * ```
     */
    public function getYuksu(string $dayHanja): array
    {
        $order = ['청룡', '주작', '구진', '등사', '백호', '현무'];

        $startIndex = match ($dayHanja) {
            '甲', '乙' => 0,
            '丙', '丁' => 1,
            '戊' => 2,
            '己' => 3,
            '庚', '辛' => 4,
            '壬', '癸' => 5,
            default => 0,
        };

        $result = [];
        for ($i = 0; $i < 6; $i++) {
            $result[$i + 1] = $order[($startIndex + $i) % 6];
        }

        return $result;
    }

    /**
     * 정통 육효점(六爻占) 종합 분석 엔진
     * 본괘, 동효, 변괘(지괘), 세응(世應), 육수(六獸)를 한 번에 조립하여 완벽한 점학 객체로 반환합니다.
     *
     * @param  object  $saju  질문자 사주 객체
     * @param  object  $today  오늘의 일진 사주 객체
     * @param  string  $jumsa  점치는 주제 ('재물', '구직', '애정', '소송' 등)
     * @return object { original, donghyo, transformed, seeung, yuksu }
     *
     * @example
     * ```php
     * // 육효 서비스(YukhyoService)에서 단 한 줄로 호출
     * $juyeok = new \Pondol\Fortune\Services\Juyeok();
     * $reading = $juyeok->getYukhyoFullReading($userSaju, $todaySaju, '사업');
     *
     * echo "본괘: " . $reading->original->map['ko'];      // 예: "지천태"
     * echo "움직인 효: " . $reading->donghyo . "효 동함"; // 예: "3효"
     * echo "변괘(미래): " . $reading->transformed->map['ko']; // 예: "지택림"
     * echo "내 자리(世): " . $reading->seeung['se'] . "효";  // 예: "3효"
     * echo "3효 신수: " . $reading->yuksu[3];              // 예: "구진"
     * ```
     */
    public function getYukhyoFullReading(object $saju, object $today, string $jumsa = '기타'): object
    {
        $temporal = $this->getTemporalGwe($saju, $today, 'day');
        $transformed = $this->getTransformedGwe($temporal->sangwe, $temporal->hagwe, $temporal->donghyo);
        $seeung = $this->getSeEung($temporal->sangwe, $temporal->hagwe);
        $yuksu = $this->getYuksu($today->day->h->ch);

        return (object) [
            'original' => $temporal,
            'donghyo' => $temporal->donghyo,
            'transformed' => $transformed,
            'seeung' => $seeung,
            'yuksu' => $yuksu,
        ];
    }

    /**
     * [내부 헬퍼] 조합 문자열로 8괘 명칭 검색
     */
    private function findTrigramByCombination(string $mode, string $var1, string $var2): string
    {
        $targetMap = ($mode === 'Gan') ? $this->ganMap : $this->jiMap;
        $value = $var1.$var2;

        foreach ($targetMap as $gweName => $combinations) {
            if (in_array($value, $combinations)) {
                return $gweName;
            }
        }

        return '坤';
    }

    /**
     * [내부 헬퍼] 8괘 숫자를 한자로 변환 (1:乾 ~ 8:坤)
     */
    private function convertNumToHanja(int $num): string
    {
        $map = [1 => '乾', 2 => '兌', 3 => '離', 4 => '震', 5 => '巽', 6 => '坎', 7 => '艮', 8 => '坤'];

        return $map[$num] ?? '坤';
    }

    /**
     * [내부 헬퍼] 8괘 한자를 숫자로 변환 (乾:1 ~ 坤:8)
     */
    private function convertHanjaToNum(string $hanja): int
    {
        $map = ['乾' => 1, '兌' => 2, '離' => 3, '震' => 4, '巽' => 5, '坎' => 6, '艮' => 7, '坤' => 8];

        return $map[$hanja] ?? 8;
    }

    // 주역신수 60갑자 수리 점수표
    private static array $sinsuScores = [
        'yean' => [88, 96, 248, 24, 240, 248, 16, 176, 24, 240, 248, 320, 168, 104, 160, 104, 320, 240, 328, 96, 24, 240, 328, 328, 80, 184, 240, 96, 248, 160, 328, 96, 104, 248, 240, 24, 160, 176, 168, 16, 248, 160, 328, 104, 16, 328, 320, 16, 88, 96, 168, 16, 328, 168, 320, 184, 96, 320, 248, 320],
        'wol' => [316, 48, 332, 348, 216, 332, 232, 176, 348, 216, 332, 32, 132, 164, 16, 164, 32, 216, 148, 48, 348, 216, 148, 148, 200, 364, 216, 48, 332, 16, 148, 48, 164, 332, 216, 348, 16, 248, 132, 232, 332, 16, 348, 164, 232, 148, 32, 232, 316, 48, 132, 232, 148, 132, 32, 364, 48, 32, 332, 32],
        'il' => [70, 120, 110, 150, 60, 110, 100, 140, 150, 60, 110, 80, 90, 170, 40, 170, 80, 60, 130, 120, 150, 60, 130, 130, 20, 190, 60, 120, 110, 40, 130, 120, 170, 110, 60, 150, 40, 140, 90, 100, 110, 40, 150, 170, 100, 130, 80, 100, 70, 120, 90, 100, 130, 90, 80, 190, 120, 80, 110, 80],
        'si' => [7, 12, 11, 15, 6, 11, 10, 14, 15, 6, 11, 8, 9, 17, 4, 17, 8, 6, 13, 12, 15, 6, 13, 13, 2, 19, 6, 12, 11, 4, 13, 12, 17, 11, 6, 15, 4, 14, 9, 10, 11, 4, 15, 17, 10, 13, 8, 10, 7, 12, 9, 10, 13, 9, 8, 19, 12, 8, 11, 8],
        'tae' => [20, 21, 17, 16, 18, 18, 17, 20, 18, 17, 20, 19, 18, 19, 15, 19, 21, 16, 15, 18, 21, 20, 20, 17, 16, 22, 18, 17, 19, 14, 18, 21, 19, 18, 18, 18, 19, 20, 16, 15, 22, 17, 16, 19, 17, 21, 21, 18, 17, 18, 19, 18, 20, 15, 14, 21, 20, 19, 19, 16],
    ];

    /**
     * 주역신수용 60갑자 점수 반환
     */
    public function getScore(string $type, string $ganji): int
    {
        $idx = Saju::getGanji60Index($ganji) - 1;

        return self::$sinsuScores[$type][$idx] ?? 0;
    }

    /**
     * [정통 주역신수 384효] 인덱스 산출 (1 ~ 384)
     * 사주 4주 수리 + 태세(세운) 수리 + 나이 합산 후 384 모듈러 연산
     */
    public function calculateSinsu384(object $saju, int $targetYear): int
    {
        $score = $this->getScore('yean', $saju->year->ko);
        $score += $this->getScore('wol', $saju->month->ko);
        $score += $this->getScore('il', $saju->day->ko);

        if ($saju->hourKnown && ! empty($saju->hour->ko) && $saju->hour->ko !== '알수없음') {
            $score += $this->getScore('si', $saju->hour->ko);
        }

        // 대상 연도 입춘 기준 세운 기둥
        $targetPillar = \Pondol\Fortune\Facades\Saju::ymdhi($targetYear.'05010000')->create();
        $score += $this->getScore('tae', $targetPillar->year->ko);

        $age = $targetYear - (int) substr($saju->solar, 0, 4) + 1;
        $total = ($score + $age) % 384;

        return ($total === 0) ? 384 : (int) $total;
    }
}
