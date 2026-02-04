<?php

namespace Pondol\Fortune\Services;

class Juyeok
{
    /**
     * Juyeok Service Class
     *
     * 이 클래스는 두 가지 방식의 주역점 계산을 제공합니다.
     * 1. 선천괘 (Maehwa Method): 사용자의 사주팔자를 기반으로 변하지 않는 운명의 본질을 계산합니다.
     * 2. 후천괘 (Cheyong Method): 사용자의 사주(體)와 특정 날짜의 기운(用)을 결합하여 그날의 상호작용을 계산합니다.
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

    private $juyeokMap = [
        // 하괘가 건(111)인 그룹
        ['code' => '111000', 'ko' => '지천태', 'ch' => '地天泰', 'que8' => ['곤', '건'], 'image' => ['gon', 'gun']],
        ['code' => '111001', 'ko' => '산천대축', 'ch' => '山天大畜', 'que8' => ['간', '건'], 'image' => ['gan', 'gun']],
        ['code' => '111010', 'ko' => '수천수', 'ch' => '水天需', 'que8' => ['감', '건'], 'image' => ['gam', 'gun']],
        ['code' => '111011', 'ko' => '풍천소축', 'ch' => '風天小畜', 'que8' => ['손', '건'], 'image' => ['son', 'gun']],
        ['code' => '111100', 'ko' => '뇌천대장', 'ch' => '雷天大壯', 'que8' => ['진', '건'], 'image' => ['jin', 'gun']],
        ['code' => '111101', 'ko' => '화천대유', 'ch' => '火天大유', 'que8' => ['이', '건'], 'image' => ['lee', 'gun']],
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
        ['code' => '101001', 'ko' => '산화비', 'ch' => '山화비', 'que8' => ['간', '이'], 'image' => ['gan', 'lee']],
        ['code' => '101010', 'ko' => '수화기제', 'ch' => '水火旣濟', 'que8' => ['감', '이'], 'image' => ['gam', 'lee']],
        ['code' => '101011', 'ko' => '풍화가인', 'ch' => '風火家人', 'que8' => ['손', '이'], 'image' => ['son', 'lee']],
        ['code' => '101100', 'ko' => '뇌화풍', 'ch' => '雷火豊', 'que8' => ['진', '이'], 'image' => ['jin', 'lee']],
        ['code' => '101101', 'ko' => '이위화', 'ch' => '離爲화', 'que8' => ['이', '이'], 'image' => ['lee', 'lee']],
        ['code' => '101110', 'ko' => '택화혁', 'ch' => '澤화혁', 'que8' => ['태', '이'], 'image' => ['tae', 'lee']],
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
        ['code' => '010011', 'ko' => '풍수환', 'ch' => '風수환', 'que8' => ['손', '감'], 'image' => ['son', 'gam']],
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
        ['code' => '001101', 'ko' => '화산여', 'ch' => '火山旅', 'que8' => ['이', '간'], 'image' => ['lee', 'gan']],
        ['code' => '001110', 'ko' => '택산함', 'ch' => '澤山咸', 'que8' => ['태', '간'], 'image' => ['tae', 'gan']],
        ['code' => '001111', 'ko' => '천산돈', 'ch' => '天山遯', 'que8' => ['건', '간'], 'image' => ['gun', 'gan']],

        // 하괘가 곤(000)인 그룹
        ['code' => '000000', 'ko' => '곤위지', 'ch' => '坤爲地', 'que8' => ['곤', '곤'], 'image' => ['gon', 'gon']],
        ['code' => '000001', 'ko' => '산지박', 'ch' => '山地剝', 'que8' => ['간', '곤'], 'image' => ['gan', 'gon']],
        ['code' => '000010', 'ko' => '수지비', 'ch' => '水地比', 'que8' => ['감', '곤'], 'image' => ['gam', 'gon']],
        ['code' => '000011', 'ko' => '풍지관', 'ch' => '風地觀', 'que8' => ['손', '곤'], 'image' => ['son', 'gon']],
        ['code' => '000100', 'ko' => '뇌지예', 'ch' => '雷地豫', 'que8' => ['진', '곤'], 'image' => ['jin', 'gon']],
        ['code' => '000101', 'ko' => '화지진', 'ch' => '火地晉', 'que8' => ['이', '곤'], 'image' => ['lee', 'gon']],
        ['code' => '000110', 'ko' => '택지췌', 'ch' => '澤地萃', 'que8' => ['태', '곤'], 'image' => ['tae', 'gon']],
        ['code' => '000111', 'ko' => '천지비', 'ch' => '天地否', 'que8' => ['건', '곤'], 'image' => ['gun', 'gon']],
    ];

    private $binaryMap = [
        1 => '111', // 건(乾) ☰ (양-양-양)
        2 => '110', // 태(兌) ☱ (양-양-음)
        3 => '101', // 리(離) ☲ (양-음-양)
        4 => '100', // 진(震) ☳ (양-음-음)
        5 => '011', // 손(巽) ☴ (음-양-양)
        6 => '010', // 감(坎) ☵ (음-양-음)
        7 => '001', // 간(艮) ☶ (음-음-양)
        8 => '000', // 곤(坤) ☷ (음-음-음)
    ];

    /**
     * juyeokMap 을 가져오기
     *
     * @param  $field:  code, ko, ch...
     */
    public function map($field, $v)
    {
        foreach ($this->juyeokMap as $map) {
            if ($map[$field] === $v) {
                return $map;
            }
        }

        return [];
    }

    /**
     * '선천괘'를 계산합니다. (매화역수)
     *
     * @param  object  $saju  Saju 객체
     */
    public function getInnateGwe(object $saju): object
    {
        // 1. 년, 월, 일 지지 숫자의 합을 먼저 구합니다.
        $ymd_sum = $saju->get_e_serial('year') + $saju->get_e_serial('month') + $saju->get_e_serial('day');

        // 2. 상괘(上卦)를 계산합니다.
        $sangweNum = $ymd_sum % 8;
        $sangweNum = ($sangweNum == 0) ? 8 : $sangweNum;

        // 3. 하괘(下卦)를 계산합니다.
        $total_sum = $ymd_sum + $saju->get_e_serial('hour');
        $hagweNum = $total_sum % 8;
        $hagweNum = ($hagweNum == 0) ? 8 : $hagweNum;

        // 4. 동효(動爻)를 계산합니다.
        $donghyo = $total_sum % 6;
        $donghyo = ($donghyo == 0) ? 6 : $donghyo;

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
     * 사주(體)와 특정일(用)로 '후천괘'를 계산합니다. (고유 점법)
     *
     * @param  object  $saju  나의 사주(體) 객체
     * @param  object  $today  특정 날짜(用)의 Saju 객체
     * @param  object  $type  hour, day, month, year 등으로 시, 일, 월, 년에 대한 운세를 구한다.
     */
    public function getTemporalGwe(object $saju, object $today, string $type = 'day'): object
    {
        // 1. 상괘(上卦) 계산: 나의 일간(체)과 특정 시간의 지지(용) 조합
        $myIlgan = $saju->get_h('day');
        $targetJi = $today->get_e($type);
        $sangweHanja = $this->findTrigramByCombination('Gan', $myIlgan, $targetJi);
        $sangweNum = $this->convertHanjaToNum($sangweHanja);

        // 2. 하괘(下卦) 계산: 나의 일지(체)와 특정 시간의 지지(용) 조합
        $myIlji = $saju->get_e('day');
        // $targetJi는 위에서 이미 정의되었으므로 재사용
        $hagweHanja = $this->findTrigramByCombination('Ji', $myIlji, $targetJi);
        $hagweNum = $this->convertHanjaToNum($hagweHanja);

        // 3. 동효(動爻) 계산: 괘를 구성하는 모든 요소의 숫자(serial) 합
        $total_sum = $saju->get_h_serial('day')   // 나의 일간 숫자
                   + $saju->get_e_serial('day')   // 나의 일지 숫자
                   + $today->get_e_serial($type); // 특정 시간의 지지 숫자

        $donghyo = $total_sum % 6;
        $donghyo = ($donghyo == 0) ? 6 : $donghyo;

        // 4. 괘 코드 생성 및 맵 정보 조회
        $code = $this->binaryMap[$hagweNum].$this->binaryMap[$sangweNum];
        $map = $this->map('code', $code);
        // print_r($map);

        // 5. 최종 결과 객체를 getInnateGwe와 동일한 형식으로 반환
        return (object) [
            'sangwe' => $sangweNum,
            'hagwe' => $hagweNum,
            'donghyo' => $donghyo,
            'map' => $map,
        ];
    }

    /**
     * 두 가지 방식의 주역점 결과를 모두 반환하는 메인 메소드
     */
    public function getFullReading(object $saju, object $today): object
    {
        // 1. 후천괘 (오늘의 운세) 계산
        $temporalGwe = $this->getTemporalGwe($saju, $today);

        // 2. 선천괘 (타고난 운명) 계산
        $innateGwe = $this->getInnateGwe($saju);

        // 3. 최종 결과 객체 생성
        return (object) [
            'temporal' => (object) [
                'description' => '나의 본질(일주)과 오늘의 기운(일지)의 상호작용 (후천괘)',
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
     * 조합에 해당하는 8괘를 찾습니다.
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

        return '오류';
    }

    // --- Helper Methods ---

    /**
     * [헬퍼] 8괘 숫자를 한자로 변환합니다.
     */
    private function convertNumToHanja(int $num): string
    {
        $map = [1 => '乾', 2 => '兌', 3 => '離', 4 => '震', 5 => '巽', 6 => '坎', 7 => '艮', 8 => '坤'];

        return $map[$num] ?? '오류';
    }

    /**
     * [헬퍼] 8괘 한자를 숫자로 변환합니다.
     */
    private function convertHanjaToNum(string $hanja): int
    {
        $map = ['乾' => 1, '兌' => 2, '離' => 3, '震' => 4, '巽' => 5, '坎' => 6, '艮' => 7, '坤' => 8];

        return $map[$hanja] ?? 0;
    }
}
