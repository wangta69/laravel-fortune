<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Facades\Lunar;

class Saju
{
    public $sl = 'solar'; // solar | lunar

    public $solar; // 양력 yyyy-mm-dd

    public $lunar; // 음력 yyyy-mm-dd

    public $leap = false; // 윤달 여부

    public $ymd; // 생년월일 yyyy-mm-dd

    public $hi = '9999'; // 생시 hhmm (예: 1330, 9999는 시간 모름)

    public $hourKnown = true; // 생시 정보 유무 플래그

    /**
     * 사주 4주(년·월·일·시) 기둥 객체
     * 각 기둥은 ko(한글 간지), ch(한자 간지), h(천간 객체), e(지지 객체)를 포함합니다.
     *
     * @var object{ko: string, ch: string, h: object, e: object}
     */
    public $year;

    public $month;

    public $day;

    public $hour;

    public $gender = 'M'; // M(남성) | F(여성) 기본값 M

    public $name = '';

    public $korean_age; // 한국 나이

    public $oheng; // 오행 분석기

    public $sipsin; // 10신 분석기

    public $zizangan; // 지장간 분석기

    public $daewoon; // 대운 분석기

    public $saewoon; // 세운 분석기

    public $woonsung12; // 12운성 분석기

    public $sinsal; // 신살 분석기

    public $sinsal12; // 12신살 분석기

    public $unse; // 운세(특수 신살 포함) 분석기

    public $taekil; // 택일 분석기

    public $gita; // 기타 특수 신살 분석기

    public $sinyaksingang; // 신약·신강 분석기

    public $tojeong; // 토정비결 작괘 분석기

    public $gabja; // 4주 통합 접근 객체

    public $dangsaju; // 당사주 분석기

    // [표준 메타데이터] 10천간 (1:甲 ~ 10:癸)
    private static array $ganMeta = [
        '甲' => ['ko' => '갑', 'ch' => '甲', 'num' => 1,  'code' => '01'],
        '乙' => ['ko' => '을', 'ch' => '乙', 'num' => 2,  'code' => '02'],
        '丙' => ['ko' => '병', 'ch' => '丙', 'num' => 3,  'code' => '03'],
        '丁' => ['ko' => '정', 'ch' => '丁', 'num' => 4,  'code' => '04'],
        '戊' => ['ko' => '무', 'ch' => '戊', 'num' => 5,  'code' => '05'],
        '己' => ['ko' => '기', 'ch' => '己', 'num' => 6,  'code' => '06'],
        '庚' => ['ko' => '경', 'ch' => '庚', 'num' => 7,  'code' => '07'],
        '辛' => ['ko' => '신', 'ch' => '辛', 'num' => 8,  'code' => '08'],
        '壬' => ['ko' => '임', 'ch' => '壬', 'num' => 9,  'code' => '09'],
        '癸' => ['ko' => '계', 'ch' => '癸', 'num' => 10, 'code' => '10'],
    ];

    // [프로젝트 공식 표준] 12지지 (01:인(寅) ~ 12:축(丑))
    // - num/code: 프로젝트 표준 (01:인 ~ 12:축)
    // - order: 천문 십이지 순번 (1:자 ~ 12:해) 호환용
    private static array $jiMeta = [
        '寅' => ['ko' => '인', 'ch' => '寅', 'num' => 1,  'code' => '01', 'animal' => '호랑이', 'order' => 3, 'yookhap' => '亥'],
        '卯' => ['ko' => '묘', 'ch' => '卯', 'num' => 2,  'code' => '02', 'animal' => '토끼',   'order' => 4, 'yookhap' => '戌'],
        '辰' => ['ko' => '진', 'ch' => '辰', 'num' => 3,  'code' => '03', 'animal' => '용',     'order' => 5, 'yookhap' => '酉'],
        '巳' => ['ko' => '사', 'ch' => '巳', 'num' => 4,  'code' => '04', 'animal' => '뱀',     'order' => 6, 'yookhap' => '申'],
        '午' => ['ko' => '오', 'ch' => '午', 'num' => 5,  'code' => '05', 'animal' => '말',     'order' => 7, 'yookhap' => '未'],
        '未' => ['ko' => '미', 'ch' => '未', 'num' => 6,  'code' => '06', 'animal' => '양',     'order' => 8, 'yookhap' => '午'],
        '申' => ['ko' => '신', 'ch' => '申', 'num' => 7,  'code' => '07', 'animal' => '원숭이', 'order' => 9, 'yookhap' => '巳'],
        '酉' => ['ko' => '유', 'ch' => '酉', 'num' => 8,  'code' => '08', 'animal' => '닭',     'order' => 10, 'yookhap' => '辰'],
        '戌' => ['ko' => '술', 'ch' => '戌', 'num' => 9,  'code' => '09', 'animal' => '개',     'order' => 11, 'yookhap' => '卯'],
        '亥' => ['ko' => '해', 'ch' => '亥', 'num' => 10, 'code' => '10', 'animal' => '돼지',   'order' => 12, 'yookhap' => '寅'],
        '子' => ['ko' => '자', 'ch' => '子', 'num' => 11, 'code' => '11', 'animal' => '쥐',     'order' => 1,  'yookhap' => '丑'],
        '丑' => ['ko' => '축', 'ch' => '丑', 'num' => 12, 'code' => '12', 'animal' => '소',     'order' => 2,  'yookhap' => '子'],
    ];

    public function __construct()
    {
        $this->ymdhi(now()->format('YmdHi'));
        $this->sl = 'solar';
        $this->leap = false;
        $this->gender = 'M'; // M | F 표준 규격
        $this->name = '';

        // [안전장치] create() 호출 전에도 $saju->day->e->num 접근 시 에러가 발생하지 않도록 초기화
        $emptyPillar = (object) [
            'ko' => '',
            'ch' => '',
            'h' => (object) ['ko' => '', 'ch' => '', 'num' => 0, 'code' => ''],
            'e' => (object) ['ko' => '', 'ch' => '', 'num' => 0, 'code' => '', 'animal' => '', 'order' => 0],
        ];

        $this->year = clone $emptyPillar;
        $this->month = clone $emptyPillar;
        $this->day = clone $emptyPillar;
        $this->hour = clone $emptyPillar;
    }

    /**
     * 생년월일시 입력 파싱 (8자리 또는 12자리)
     */
    public function ymdhi($ymdhi)
    {
        $ymdhi = str_replace(['-', ':', ' '], '', trim($ymdhi));
        $len = strlen($ymdhi);

        switch ($len) {
            case 8:
                $ymd = $ymdhi;
                $this->hi = '9999';
                $this->hourKnown = false;
                break;
            case 12:
                preg_match('/^([0-9]{8})([0-9]{4})$/', trim($ymdhi), $match);
                [, $ymd, $hi] = $match;
                if ($hi === '9999' || substr($hi, 0, 2) === '99') {
                    $this->hi = '9999';
                    $this->hourKnown = false;
                } else {
                    $this->hi = $hi;
                    $this->hourKnown = true;
                }
                break;
            default:
                throw new \Exception('Invalid date length. Expected 8 or 12 characters, but got '.$len);
        }

        preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})$/', trim($ymd), $match);
        if (count($match) < 4) {
            throw new \Exception('Failed to parse ymd: '.$ymd);
        }
        [, $y, $m, $d] = $match;
        $this->ymd = $y.'-'.$m.'-'.$d;

        return $this;
    }

    public function ymd($ymd)
    {
        return $this->ymdhi($ymd);
    }

    public function sl($sl)
    {
        $this->sl = $sl;

        return $this;
    }

    public function leap($leap)
    {
        $this->leap = $leap;

        return $this;
    }

    public function gender($gender)
    {
        // '남'/'여' 입력 시에도 'M'/'F'로 자동 정규화
        $this->gender = in_array(strtoupper($gender), ['M', '남']) ? 'M' : 'F';

        return $this;
    }

    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * 사주 원국 계산 및 메타데이터 주입
     */
    public function create()
    {
        switch ($this->sl) {
            case 'solar':
                $this->solar = $this->ymd;
                if ($this->hourKnown) {
                    $saju = Lunar::ymd($this->ymd)->hi($this->hi)->tolunar()->sajugabja()->create();
                } else {
                    $saju = Lunar::ymd($this->ymd)->tolunar()->sajugabja(false)->create();
                }
                $this->lunar = $saju->lunar;
                break;
            case 'lunar':
                $this->lunar = $this->ymd;
                if ($this->hourKnown) {
                    $saju = Lunar::ymd($this->ymd)->hi($this->hi)->tosolar($this->leap)->sajugabja()->create();
                } else {
                    $saju = Lunar::ymd($this->ymd)->tosolar($this->leap)->sajugabja(false)->create();
                }
                $this->solar = $saju->solar;
                break;
        }

        // --- 4주(년·월·일·시) 구조 보정 및 표준 메타데이터 확장 ---
        $pillars = ['year', 'month', 'day', 'hour'];
        foreach ($pillars as $p) {
            $source = $saju->gabja->{$p} ?? null;

            // 시주 정보를 모를 때의 방어 코드
            if ($p === 'hour' && (! $this->hourKnown || ! $source)) {
                $this->hour = (object) [
                    'ko' => '알수없음',
                    'ch' => '時柱不明',
                    'h' => (object) ['ko' => '', 'ch' => '', 'num' => 0, 'code' => ''],
                    'e' => (object) ['ko' => '', 'ch' => '', 'num' => 0, 'code' => '', 'animal' => '', 'order' => 0],
                ];

                continue;
            }

            $koVal = $source->ko; // 예: "갑자"
            $chVal = $source->ch; // 예: "甲子"

            $hCh = mb_substr($chVal, 0, 1);
            $eCh = mb_substr($chVal, 1, 1);

            // 메타데이터 매핑
            $hMeta = self::$ganMeta[$hCh] ?? ['ko' => mb_substr($koVal, 0, 1), 'ch' => $hCh, 'num' => 1, 'code' => '01'];
            $eMeta = self::$jiMeta[$eCh] ?? ['ko' => mb_substr($koVal, 1, 1), 'ch' => $eCh, 'num' => 1, 'code' => '01', 'animal' => '', 'order' => 1];

            // 4주 각 기둥에 확장 객체 할당
            $this->{$p} = (object) [
                'ko' => $koVal,
                'ch' => $chVal,
                'h' => (object) $hMeta, // ko, ch, num(1~10), code('01'~'10')
                'e' => (object) $eMeta, // ko, ch, num(1~12:인~축), code('01'~'12'), animal('호랑이' 등), order(자=1기준)
            ];
        }

        // 호환용 gabja 프로퍼티 구성
        $this->gabja = (object) [
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'hour' => $this->hour,
        ];

        $this->korean_age = (int) date('Y') - (int) substr($this->solar, 0, 4) + 1;

        return $this;
    }

    public function seasonal_division($ymd)
    {
        return Lunar::seasonal_division($ymd)->create();
    }

    public function get_h($str, $lan = 'ch')
    {
        if ($str === 'hour' && ! $this->hourKnown) {
            return '';
        }

        return mb_substr($this->{$str}->{$lan}, 0, 1);
    }

    public function get_h_serial($str)
    {
        return h_to_serial($this->get_h($str));
    }

    public function get_e($str, $lan = 'ch')
    {
        if ($str === 'hour' && ! $this->hourKnown) {
            return '';
        }

        return mb_substr($this->{$str}->{$lan}, 1, 1);
    }

    public function get_e_serial($str)
    {
        return e_to_serial($this->get_e($str));
    }

    public function get_e_wolgun($str)
    {
        return e_to_wolgun($this->get_e($str));
    }

    public function get_he($str, $lan = 'ch')
    {
        if ($str === 'hour' && ! $this->hourKnown) {
            return '';
        }

        return $this->{$str}->{$lan};
    }

    public function oheng()
    {
        if (! isset($this->oheng)) {
            $ohengCalculator = new Oheng;
            $this->oheng = $ohengCalculator->withSaju($this);
        }

        return $this->oheng;
    }

    public function get_oheng(string $pillar, string $type = 'h'): string
    {
        if (! isset($this->oheng)) {
            $this->oheng();
        }
        $property = $pillar.'_'.$type;

        return $this->oheng->{$property}->ch ?? '';
    }

    public function sinsal()
    {
        if (! isset($this->sinsal)) {
            $sinsal = new Sinsal;
            $this->sinsal = $sinsal->withSaju($this)->sinsal()->create();
        }

        return $this->sinsal;
    }

    public function sinsal12()
    {
        if (! isset($this->sinsal12)) {
            $this->sinsal12 = (new Sinsal12)->withSaju($this);
        }

        return $this->sinsal12;
    }

    public function unse()
    {
        if (! isset($this->unse)) {
            $this->unse = (new Unse)->withSaju($this);
        }

        return $this->unse;
    }

    public function taekil()
    {
        if (! isset($this->taekil)) {
            $this->taekil = (new Taekil)->withSaju($this);
        }

        return $this->taekil;
    }

    public function gita()
    {
        if (! isset($this->gita)) {
            $this->gita = (new Gita)->withSaju($this);
        }

        return $this->gita;
    }

    public function woonsung12()
    {
        if (! isset($this->woonsung12)) {
            $woonsung12 = new Woonsung12;
            $this->woonsung12 = $woonsung12->withSaju($this);
        }

        return $this->woonsung12;
    }

    public function sipsin()
    {
        if (! isset($this->sipsin)) {
            $sipsin = new Sipsin;
            $this->sipsin = $sipsin->withSaju($this);
        }

        return $this->sipsin;
    }

    public function zizangan()
    {
        if (! isset($this->zizangan)) {
            $zizangan = new Zizangan;
            $this->zizangan = $zizangan->withSaju($this);
        }

        return $this->zizangan;
    }

    public function daewoon()
    {
        if (! isset($this->daewoon)) {
            $daewoon = new DaeWoon;
            $this->daewoon = $daewoon->withSaju($this);
        }

        return $this->daewoon;
    }

    public function saewoon()
    {
        if (! isset($this->saewoon)) {
            $saewoon = new SaeWoon;
            $this->saewoon = $saewoon->withSaju($this);
        }

        return $this->saewoon;
    }

    public function sinyaksingang()
    {
        if (! isset($this->sinyaksingang)) {
            $sinyaksingang = new SinyakSingang;
            $this->sinyaksingang = $sinyaksingang->withSaju($this);
        }

        return $this->sinyaksingang;
    }

    public function tojeong()
    {
        if (! isset($this->tojeong)) {
            $this->tojeong = (new TojeongJakgwae)->withSaju($this);
        }

        return $this->tojeong;
    }

    // 기타 util 함수
    /**
     * 기준 지지(기본: 일지)로부터 대상 지지까지의 12진법 순환 상대 거리 산출 (1 ~ 12)
     * - 동일 지지: 1 (제자리/복음)
     * - 대척 지지: 7 (정충/대충)
     * - 삼합 지지: 5, 9
     *
     * @param  int|string  $targetJi  대상 지지 (한자 '子', 한글 '자', 또는 숫자 1~12)
     * @param  string  $basePillar  기준 기둥 ('day': 일지 기준, 'year': 년지 기준)
     * @return int 1 ~ 12 사이의 상대적 거리 인덱스
     */
    public function getJiDistance($targetJi, string $basePillar = 'day'): int
    {
        // 1. 기준 지지의 표준 순번(1:인 ~ 12:축) 추출
        $baseNum = $this->{$basePillar}->e->num;

        // 2. 대상 지지가 문자(한자/한글)인 경우 표준 번호로 변환
        if (is_string($targetJi)) {
            $targetNum = self::$jiMeta[$targetJi]['num'] ?? 1;
        } else {
            $targetNum = (int) $targetJi;
        }

        // 3. 12진법 상대 거리 공식: ((대상 - 기준 + 12) % 12) + 1
        return (($targetNum - $baseNum + 12) % 12) + 1;
    }

    // 당사주
    public function dangsaju()
    {
        if (! isset($this->dangsaju)) {
            $this->dangsaju = (new DangSaju)->make($this);
        }

        return $this->dangsaju;
    }

    /**
     * [유년신수] 대상 연도(세운)와 본인의 음력 생월을 결합한 연간 신수 객체 반환
     *
     * @param  Saju  $targetYearSaju  대상 연도 Saju 객체 (입춘 보정 세운)
     * @return object {
     *                num: int (1~12, 인=1 기준 표준 번호),
     *                code: string ('01'~'12'),
     *                ko: string ('자', '축', '인'...),
     *                ch: string ('子', '丑', '寅'...),
     *                order: int (1~12, 자=1 기준 천문 순번),
     *                woonsung: string (12운성 명칭),
     *                sinsal12: string (12신살 명칭)
     *                }
     */
    public function sinsu(Saju $targetYearSaju): object
    {
        // 1. 세운 천간 번호 (1:甲 ~ 10:癸)
        $yearGanNum = $targetYearSaju->year->h->num ?? 1;

        // 2. 본인 음력 생월 추출 (1 ~ 12)
        [$lYear, $lMonth] = explode('-', $this->lunar);
        $birthMonth = (int) $lMonth;

        // 3. 유년신수 14 오프셋 순환 공식
        $sv = 14 - $yearGanNum;
        if ($sv > 12) {
            $sv -= 12;
        }

        // 1~12 결과 산출 (기존 getWolgyeSinsuCode 수식)
        $codeNum = ($sv + $birthMonth - 1) % 12 ?: 12;

        // 4. 지지 매핑 (Saju::$jiMeta의 표준 인=1 ~ 축=12 규격과 연동)
        // 12지 한글 배열 (순번 1~12 매칭)
        $jiList = [
            1 => '인', 2 => '묘', 3 => '진', 4 => '사', 5 => '오', 6 => '미',
            7 => '신', 8 => '유', 9 => '술', 10 => '해', 11 => '자', 12 => '축',
        ];
        $ko = $jiList[$codeNum] ?? '자';

        // 지지 한자 및 메타데이터 추출
        $meta = self::$jiMeta[array_search($ko, array_column(self::$jiMeta, 'ko', 'ch'))] ?? [];

        // 5. 12운성 및 12신살 도출 (내 일간/년지 기준 결합)
        $woonsung = Woonsung12::cal($this->day->h->ch, $meta['ch'] ?? '子');
        $sinsal12 = Sinsal12::cal($this->year->e->ch, $meta['ch'] ?? '子'); // 년지 기준 12신살

        return (object) [
            'num' => $codeNum,                                     // 1 ~ 12 (숫자)
            'code' => str_pad((string) $codeNum, 2, '0', STR_PAD_LEFT), // '01' ~ '12' (패딩 문자열)
            'ko' => $ko,                                          // '자', '축' ... (한글 지지)
            'ch' => $meta['ch'] ?? '子',                          // '子', '丑' ... (한자 지지)
            'woonsung' => $woonsung,                                    // '장생', '제왕' 등 12운성
            'sinsal12' => $sinsal12,                                    // '역마살', '화개살' 등 12신살
        ];
    }

    /**
     * 60갑자 문자열(한자 또는 한글)의 순번 인덱스 반환 (1 ~ 60)
     * constants.php의 GANJI 상수 직접 활용
     */
    public static function getGanji60Index(string $ganji): int
    {
        $idx = array_search($ganji, GANJI['ch']);
        if ($idx === false) {
            $idx = array_search($ganji, GANJI['ko']);
        }

        return ($idx !== false) ? $idx + 1 : 1;
    }

    /**
     * 사주 원국의 모든 신살(일반 신살 + 12신살) 통합 및 길신 우선 정렬 반환
     *
     * @return array 정제된 신살 객체 배열
     */
    public function allSinsals(): array
    {
        $collection = collect();

        // 1. 일반 신살 통합 (y, m, d, h 배열)
        $sinsal = $this->sinsal();
        foreach (['y', 'm', 'd', 'h'] as $pos) {
            if (isset($sinsal->{$pos}) && is_iterable($sinsal->{$pos})) {
                foreach ($sinsal->{$pos} as $item) {
                    $collection->push((object) [
                        'ko' => $item->ko,
                        'ch' => $item->ch,
                        'type' => $item->type ?? 'junglip',
                    ]);
                }
            }
        }

        // 2. 12신살 통합 (year, month, day, hour 단일 객체)
        $sinsal12 = $this->sinsal12();
        foreach (['year', 'month', 'day', 'hour'] as $pos) {
            if (isset($sinsal12->{$pos}) && $sinsal12->{$pos}) {
                $item12 = $sinsal12->{$pos};
                $collection->push((object) [
                    'ko' => $item12->ko,
                    'ch' => $item12->ch,
                    'type' => $item12->type ?? 'junglip',
                ]);
            }
        }

        // 3. 중복 신살 제거 및 길신(gilsin) 우선 정렬
        return $collection->unique('ko')
            ->sortByDesc(fn ($item) => $item->type === 'gilsin')
            ->values()
            ->all();
    }
}
