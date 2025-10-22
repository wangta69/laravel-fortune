<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Traits\SinsalRules;

/**
 * 사주 정보를 바탕으로 길신과 흉신(신살)을 계산하는 클래스입니다.
 * 선언적 규칙 기반 아키텍처를 사용하여 가독성, 확장성, 유지보수성을 극대화했습니다.
 */
class Sinsal
{
    use SinsalRules;
    /**
     * 모든 신살의 정적인 정보(한자, 유형 등)를 중앙에서 통합 관리하는 '마스터 데이터'입니다.
     * 'type' => gilsin(길신), hyungsal(흉살), junglip(중립)
     */
    private const DEFINITIONS = [
        // --- 귀인(貴人) 시리즈 (대표적인 길신) ---
        '천을귀인' => ['ch' => '天乙貴人', 'type' => 'gilsin'],
        '태극귀인' => ['ch' => '太極貴人', 'type' => 'gilsin'],
        '천덕귀인' => ['ch' => '天德貴人', 'type' => 'gilsin'],
        '월덕귀인' => ['ch' => '月德貴人', 'type' => 'gilsin'],
        '천관귀인' => ['ch' => '天官貴人', 'type' => 'gilsin'],
        '천복귀인' => ['ch' => '天福貴人', 'type' => 'gilsin'],
        '천주귀인' => ['ch' => '天廚貴人', 'type' => 'gilsin'],
        '복성귀인' => ['ch' => '福星貴人', 'type' => 'gilsin'],

        // --- 학문/지혜 관련 길신 ---
        '문창귀인' => ['ch' => '文昌貴人', 'type' => 'gilsin'],
        '문곡귀인' => ['ch' => '文曲貴人', 'type' => 'gilsin', 'aliases' => ['문곡성']],
        '학당귀인' => ['ch' => '學堂貴人', 'type' => 'gilsin', 'aliases' => ['학당']],
        '관귀학관' => ['ch' => '官貴學館', 'type' => 'gilsin'],

        // --- 재물/명예 관련 길신 ---
        '암록'     => ['ch' => '暗祿',     'type' => 'gilsin'],
        '금여록'   => ['ch' => '金輿祿',   'type' => 'gilsin'],

        // --- 건강/기타 길신 ---
        '천의성'   => ['ch' => '天醫星',   'type' => 'gilsin'],
        '황은대사' => ['ch' => '皇恩大赦', 'type' => 'gilsin'],
        '월덕합'   => ['ch' => '月德合',   'type' => 'gilsin'],
        '천덕합'   => ['ch' => '天德合',   'type' => 'gilsin'],

        // --- 강력한 작용을 하는 주요 신살 (중립/흉살) ---
        '괴강살'   => ['ch' => '魁罡殺',   'type' => 'hyungsal'],
        '백호살'   => ['ch' => '白虎殺',   'type' => 'hyungsal'],
        '양인살'   => ['ch' => '羊刃殺',   'type' => 'hyungsal'],

        // --- 인간관계/감정 관련 신살 (주로 흉살) ---
        '원진살'   => ['ch' => '元辰殺',   'type' => 'hyungsal'],
        '귀문관살' => ['ch' => '鬼門關殺', 'type' => 'hyungsal'],
        '홍염살'   => ['ch' => '紅艶殺',   'type' => 'junglip'],
        '도화살'   => ['ch' => '桃花殺',   'type' => 'junglip'],
        '고신살'   => ['ch' => '孤神殺',   'type' => 'hyungsal'],
        '과숙살'   => ['ch' => '寡宿殺',   'type' => 'hyungsal'],
        '상처살'   => ['ch' => '喪妻殺',   'type' => 'hyungsal'],
        '상부살'   => ['ch' => '喪夫殺',   'type' => 'hyungsal'],

        // --- 건강/사건사고 관련 흉살 ---
        '탕화살'   => ['ch' => '湯火殺',   'type' => 'hyungsal'],
        '급각살'   => ['ch' => '急脚殺',   'type' => 'hyungsal'],
        '단교관살' => ['ch' => '斷橋關殺', 'type' => 'hyungsal'],
        '상문살'   => ['ch' => '喪門殺',   'type' => 'hyungsal'],
        '재살'     => ['ch' => '災殺',     'type' => 'hyungsal', 'aliases' => ['수옥살']],
        '겁살'     => ['ch' => '劫殺',     'type' => 'hyungsal'],

        // --- [추가] 개인 특성 관련 기타 신살 ---
        '황은대사' => ['ch' => '皇恩大赦', 'type' => 'gilsin'],
        '월덕합'   => ['ch' => '月德合',   'type' => 'gilsin'],
        '천덕합'   => ['ch' => '天德合',   'type' => 'gilsin'],
        '탕화살'   => ['ch' => '湯火殺',   'type' => 'hyungsal'],
        '단교관살' => ['ch' => '斷橋關殺', 'type' => 'hyungsal'],
        '유하살'   => ['ch' => '流霞殺',   'type' => 'hyungsal'],
        '비인살'   => ['ch' => '飛刃殺',   'type' => 'hyungsal'],
        '음양착살' => ['ch' => '陰陽錯殺', 'type' => 'hyungsal'],

        // --- 기타 신살 ---
        '공망'     => ['ch' => '空亡',     'type' => 'hyungsal'],
    ];

    private array $saju = [];
    private array $rules = [];
    private array $calculatedSinsals = [];
    private string $gender = 'M';

    public function __construct()
    {
        $this->initializeRules();
    }

    public function withSaju($saju): self
    {
        $this->saju = [
            'ganji' => ['y' => $saju->year->ch, 'm' => $saju->month->ch, 'd' => $saju->day->ch, 'h' => $saju->hour->ch],
            'h'     => ['y' => $saju->get_h('year'), 'm' => $saju->get_h('month'), 'd' => $saju->get_h('day'), 'h' => $saju->get_h('hour')],
            'e'     => ['y' => $saju->get_e('year'), 'm' => $saju->get_e('month'), 'd' => $saju->get_e('day'), 'h' => $saju->get_e('hour')],
        ];
        $this->gender = $saju->gender;
        return $this;
    }

    public function sinsal(): self
    {
        $this->calculatedSinsals = ['y' => [], 'm' => [], 'd' => [], 'h' => []];

        foreach ($this->rules as $name => $rule) {
            if (isset($rule['gender']) && $rule['gender'] !== $this->gender) {
                continue;
            }

            $logic = $rule['logic'];
            $positions = $rule['positions'] ?? ['y', 'm', 'd', 'h'];

            foreach ($positions as $pos) {
                $params = [];
                foreach ($rule['params'] as $paramType) {

                    $value = null;
                    switch ($paramType) {
                        case 'ilgan': $value = $this->saju['h']['d'];
                            break;
                        case 'wolji': $value = $this->saju['e']['m'];
                            break;
                        case 'yeonji': $value = $this->saju['e']['y'];
                            break;
                        case 'ilji': $value = $this->saju['e']['d'];
                            break;
                        case 'jiji': $value = $this->saju['e'][$pos];
                            break;
                        case 'ganji': $value = $this->saju['ganji'][$pos];
                            break;
                        case 'cheongan': $value = $this->saju['h'][$pos];
                            break;
                    }
                    $params[] = $value;
                }

                if ($logic(...$params)) {
                    $this->calculatedSinsals[$pos][] = $name;
                }
            }
        }
        return $this;
    }

    public function create(): object
    {
        $return = (object)['y' => [], 'm' => [], 'd' => [], 'h' => []];
        foreach ($this->calculatedSinsals as $posKey => $sinsalNames) {
            foreach (array_unique($sinsalNames) as $name) {
                if (isset(self::DEFINITIONS[$name])) {
                    $def = self::DEFINITIONS[$name];
                    $return->{$posKey}[] = (object)[
                        'ko'   => $name,
                        'ch'   => $def['ch'],
                        'type' => $def['type'] ?? 'junglip',
                    ];
                }
            }
        }
        return $return;
    }

    private function initializeRules(): void
    {
        $this->rules = [
            // --- 귀인(貴人) 시리즈 ---
            '천을귀인' => [
                'params' => ['ilgan', 'jiji'],
                'logic' => function ($ilgan, $jiji) {
                    switch ($ilgan) {
                        case '甲': case '戊': case '庚': // 갑 무 경
                            return in_array($jiji, ['丑', '未']);
                        case '乙': case '己': // 을기
                            return in_array($jiji, ['子', '申']);
                        case '丙': case '丁': // 병정
                            return in_array($jiji, ['酉', '亥']);
                        case '辛':
                            return in_array($jiji, ['寅', '午']);
                        case '壬': case '癸': // 임 계
                            return in_array($jiji, ['卯', '巳']);
                        default:
                            return false;
                    }
                }
            ],
            '태극귀인' => [
                'params' => ['ilgan', 'jiji'],
                'logic' => function ($ilgan, $jiji) {
                    switch ($ilgan) {
                        case '甲': case '乙':
                            return in_array($jiji, ['子', '午']);
                        case '丙': case '丁':
                            return in_array($jiji, ['卯', '酉']);
                        case '戊': case '己':
                            return in_array($jiji, ['辰', '戌', '丑', '未']);
                        case '庚': case '辛':
                            return in_array($jiji, ['寅', '亥']);
                        case '壬': case '癸':
                            return in_array($jiji, ['巳', '申']);
                        default:
                            return false;
                    }
                }
            ],
            '천덕귀인' => [
                'params' => ['wolji', 'cheongan', 'jiji'],
                'logic' => [self::class, 'isChunduk']
            ],
            '월덕귀인' => [
                'params' => ['wolji', 'cheongan'],
                'logic' => [self::class, 'isWolduk']
            ],

            // --- 학문/지혜 관련 ---
            '문창귀인' => [
                'params' => ['ilgan', 'jiji'],
                'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲巳', '乙午', '丙申', '丁酉', '戊申', '己酉', '庚亥', '辛子', '壬寅', '癸卯'])
            ],
            '학당귀인' => [
                'params' => ['ilgan', 'jiji'],
                'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲亥', '乙午', '丙寅', '丁酉', '戊寅', '己酉', '庚巳', '辛子', '壬申', '癸卯'])
            ],
            '문곡귀인' => ['params' => ['ilgan', 'jiji'], 'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲亥', '乙子', '丙寅', '丁卯', '戊寅', '己卯', '庚巳', '辛午', '壬申', '癸酉'])],
            '관귀학관' => ['params' => ['ilgan', 'jiji'], 'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲巳', '乙巳', '丙申', '丁申', '戊亥', '己亥', '庚寅', '辛寅', '壬申', '癸申'])],

            // --- 재물/명예/기타 길신 ---
            '천주귀인' => ['params' => ['ilgan', 'jiji'], 'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲巳', '乙午', '丙巳', '丁午', '戊申', '己酉', '庚亥', '辛子', '壬寅', '癸卯'])],
            '천관귀인' => ['params' => ['ilgan', 'jiji'], 'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲未', '乙辰', '丙巳', '丁寅', '戊卯', '己酉', '庚亥', '辛申', '壬酉', '癸午'])],
            '천복귀인' => ['params' => ['ilgan', 'jiji'], 'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲酉', '乙申', '丙子', '丁亥', '戊卯', '己寅', '庚午', '辛巳', '壬午', '癸巳'])],
            '복성귀인' => ['params' => ['ilgan', 'jiji'], 'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲寅', '乙丑', '丙子', '丁酉', '戊申', '己未', '庚午', '辛巳', '壬辰', '癸卯'])],
            '암록'     => ['params' => ['ilgan', 'jiji'], 'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲亥', '乙戌', '丙申', '丁未', '戊申', '己未', '庚巳', '辛辰', '壬寅', '癸丑'])],
            '금여록'   => ['params' => ['ilgan', 'jiji'], 'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲辰', '乙巳', '丙未', '丁申', '戊未', '己申', '庚戌', '辛亥', '壬丑', '癸寅'])],
            '천의성'   => [
                'params' => ['wolji', 'jiji'],
                'positions' => ['y', 'd', 'h'],
                'logic' => [self::class, 'isChene']
            ],

            // --- 주요 흉살(凶神) 및 중립살 ---
            '백호살' => [
                'params' => ['ganji'],
                'logic' => fn ($ganji) => in_array($ganji, ['甲辰', '乙未', '丙戌', '丁丑', '戊辰', '壬戌', '癸丑'])
            ],
            '괴강살' => [
                'params' => ['ganji'],
                'logic' => fn ($ganji) => in_array($ganji, ['壬辰', '壬戌', '戊戌', '庚辰', '庚戌'])
            ],
            '양인살' => [
                'params' => ['ilgan', 'jiji'],
                'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲卯', '丙午', '戊午', '庚酉', '壬子'])
                // ['甲卯','乙辰','丙午','丁未','戊午','己未','庚酉','辛戌','壬子','癸丑'] 양인살은 좌측 처럼 구하는 경우도 있음(학파에 따라 다름)
            ],
            '홍염살' => [
                'params' => ['ilgan', 'jiji'],
                'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲午', '乙午', '丙寅', '丁未', '戊辰', '己辰', '庚戌', '辛酉', '壬子', '癸申'])
            ],
            '원진살' => [
                'params' => ['ilji', 'jiji'],
                'positions' => ['y', 'm', 'h'],
                'logic' => function ($ilji, $jiji) {
                    $pairs = ['子未', '丑午', '寅酉', '卯申', '辰亥', '巳戌'];
                    return in_array($ilji . $jiji, $pairs) || in_array($jiji . $ilji, $pairs);
                }
            ],
            '귀문관살' => [
                'params' => ['ilji', 'jiji'],
                'positions' => ['y', 'm', 'h'],
                'logic' => function ($ilji, $jiji) {
                    $pairs = ['子酉', '丑午', '寅未', '卯申', '辰亥', '巳戌']; // 진해, 사술은 원진과 겹침
                    return in_array($ilji . $jiji, $pairs) || in_array($jiji . $ilji, $pairs);
                }
            ],
            '상문살' => [
                'params' => ['ilji', 'jiji'],
                'positions' => ['y', 'm', 'h'],
                'logic' => fn ($ilji, $jiji) => in_array($ilji . $jiji, ['子寅','丑卯','寅辰','卯巳','辰午','巳未','午申','未酉','申戌','酉亥','戌子','亥丑'])
            ],
            '급각살' => [
                'params' => ['wolji', 'jiji'],
                'positions' => ['y', 'd', 'h'],
                'logic' => function ($wolji, $jiji) {
                    if (in_array($wolji, ['寅', '卯', '辰'])) {
                        return in_array($jiji, ['亥', '子']);
                    }
                    if (in_array($wolji, ['巳', '午', '未'])) {
                        return in_array($jiji, ['卯', '未']);
                    }
                    if (in_array($wolji, ['申', '酉', '戌'])) {
                        return in_array($jiji, ['寅', '戌']);
                    }
                    if (in_array($wolji, ['亥', '子', '丑'])) {
                        return in_array($jiji, ['丑', '辰']);
                    }
                    return false;
                }
            ],
            '고신살' => [
                'gender' => 'M', // 남성에게만 적용
                'params' => ['yeonji', 'jiji'],
                'logic' => function ($yeonji, $jiji) {
                    if (in_array($yeonji, ['寅', '卯', '辰'])) {
                        return $jiji === '巳';
                    }
                    if (in_array($yeonji, ['巳', '午', '未'])) {
                        return $jiji === '申';
                    }
                    if (in_array($yeonji, ['申', '酉', '戌'])) {
                        return $jiji === '亥';
                    }
                    if (in_array($yeonji, ['亥', '子', '丑'])) {
                        return $jiji === '寅';
                    }
                    return false;
                }
            ],
            '과숙살' => [
                'gender' => 'W', // 여성에게만 적용
                'params' => ['yeonji', 'jiji'],
                'logic' => function ($yeonji, $jiji) {
                    if (in_array($yeonji, ['寅', '卯', '辰'])) {
                        return $jiji === '丑';
                    }
                    if (in_array($yeonji, ['巳', '午', '未'])) {
                        return $jiji === '辰';
                    }
                    if (in_array($yeonji, ['申', '酉', '戌'])) {
                        return $jiji === '未';
                    }
                    if (in_array($yeonji, ['亥', '子', '丑'])) {
                        return $jiji === '戌';
                    }
                    return false;
                }
            ],
            // --- 기타 신살 규칙 ---
            '황은대사' => [
                'params' => ['wolji', 'jiji'],
                'logic' => fn ($wolji, $jiji) => in_array($wolji . $jiji, ['子申','丑未','寅戌','卯丑','辰寅','巳巳','午酉','未卯','申子','酉午','戌亥','亥辰'])
            ],
            '월덕합' => [
                'params' => ['wolji', 'cheongan'],
                'logic' => [self::class, 'isWoldukHap']
            ],
            '천덕합' => [
                'params' => ['wolji', 'jiji'],
                'logic' => [self::class, 'isChendukHap']
            ],
            '단교관살' => [
                'params' => ['ilji', 'jiji'],
                'positions' => ['d'], // 기존 코드상 일지에만 해당
                'logic' => fn ($ilji, $jiji) => in_array($ilji . $jiji, ['子亥','丑子','寅寅','卯卯','辰申','巳丑','午戌','未酉','申辰','酉巳','戌午','亥未'])
            ],
            '탕화살' => [
                'params' => ['yeonji', 'jiji'], // 원본 코드는 my_e(생년지) 기준이었음
                'logic' => fn ($yeonji, $jiji) => in_array($yeonji . $jiji, ['子午','丑未','寅寅','卯午','辰未','巳寅','午午','未未','申寅','酉午','戌未','亥寅'])
            ],
            '유하살' => [
                'params' => ['ilgan', 'jiji'],
                'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲酉','乙戌','丙未','丁申','戊巳','己午','庚辰','辛卯','壬亥','癸寅'])
            ],
            '비인살' => [
                'params' => ['ilgan', 'jiji'],
                'logic' => fn ($ilgan, $jiji) => in_array($ilgan . $jiji, ['甲酉','乙戌','丙子','丁丑','戊子','己丑','庚卯','辛辰','壬午','癸未'])
            ],
            '음양착살' => [
                'params' => ['ilgan', 'jiji'],
                'logic' => function ($ilgan, $jiji) {
                    switch ($ilgan) {
                        case '丙': return in_array($jiji, ['子', '午']);
                        case '丁': return in_array($jiji, ['丑', '未']);
                        case '戊': return in_array($jiji, ['寅', '申']);
                        case '辛': return in_array($jiji, ['卯', '酉']);
                        case '壬': return in_array($jiji, ['辰', '戌']);
                        case '癸': return in_array($jiji, ['巳', '亥']);
                    }
                    return false;
                }
            ],
            '공망' => [
                'params' => ['ilgan', 'ilji', 'jiji'], // 일간, 일지, 그리고 비교할 다른 지지가 필요
                'logic' => function ($ilgan, $ilji, $jiji) {
                    $ilju = $ilgan . $ilji;
                    $gongmangPair = [];

                    // 60갑자 순환(순)에 따른 공망 찾기
                    if (in_array($ilju, ['甲子','乙丑','丙寅','丁卯','戊辰','己巳','庚午','辛未','壬申','癸酉'])) {
                        $gongmangPair = ['戌', '亥'];
                    } elseif (in_array($ilju, ['甲戌','乙亥','丙子','丁丑','戊寅','己卯','庚辰','辛巳','壬午','癸未'])) {
                        $gongmangPair = ['申', '酉'];
                    } elseif (in_array($ilju, ['甲申','乙酉','丙戌','丁亥','戊子','己丑','庚寅','辛卯','壬辰','癸巳'])) {
                        $gongmangPair = ['午', '未'];
                    } elseif (in_array($ilju, ['甲午','乙未','丙申','丁酉','戊戌','己亥','庚子','辛丑','壬寅','癸卯'])) {
                        $gongmangPair = ['辰', '巳'];
                    } elseif (in_array($ilju, ['甲辰','乙巳','丙午','丁未','戊申','己酉','庚戌','辛亥','壬子','癸丑'])) {
                        $gongmangPair = ['寅', '卯'];
                    } elseif (in_array($ilju, ['甲寅','乙卯','丙辰','丁巳','戊午','己未','庚申','辛酉','壬戌','癸亥'])) {
                        $gongmangPair = ['子', '丑'];
                    }

                    // 비교 대상 지지가 공망에 해당하는지 확인
                    return in_array($jiji, $gongmangPair);
                }
            ],

        ];
    }

}
