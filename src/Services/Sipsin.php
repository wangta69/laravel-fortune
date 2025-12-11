<?php

namespace Pondol\Fortune\Services;

/**
 * 십신(十神) 정보를 담는 간단한 데이터 객체(DTO)
 * 이 클래스는 타입 힌팅을 통해 코드의 안정성과 가독성을 높여줍니다.
 */
class SipsinPillar
{
    public ?string $h; // 천간 십신
    public ?string $e; // 지지 십신

    public function __construct(?string $h, ?string $e)
    {
        $this->h = $h;
        $this->e = $e;
    }
}
/**
 * 십신(十神)을 계산하고 관리하는 클래스
 * 일간(日干)을 기준으로 다른 간지와의 관계를 분석합니다.
 */
class Sipsin
{
    /** @var string 나의 사주 일간 (기준점) */
    private $dayMaster;

    /** @var Saju Saju 원국 객체 */
    private $saju;

    // --- 원국 십신 속성 ---
    public SipsinPillar $year;
    public SipsinPillar $month;
    public SipsinPillar $day;
    public SipsinPillar $hour;

    /** @var array 십신 관계를 정의하는 규칙
     * 일간을 기준으로 천간과 지지 각각에 대한 십신 관계를 맵핑합니다.
    */
    private static $sipsinRules = [
        'h' => [ // 천간(h) 규칙
            '甲' => ['甲' => '비견', '乙' => '겁재', '丙' => '식신', '丁' => '상관', '戊' => '편재', '己' => '정재', '庚' => '편관', '辛' => '정관', '壬' => '편인', '癸' => '정인'],
            '乙' => ['乙' => '비견', '甲' => '겁재', '丁' => '식신', '丙' => '상관', '己' => '편재', '戊' => '정재', '辛' => '편관', '庚' => '정관', '癸' => '편인', '壬' => '정인'],
            '丙' => ['丙' => '비견', '丁' => '겁재', '戊' => '식신', '己' => '상관', '庚' => '편재', '辛' => '정재', '壬' => '편관', '癸' => '정관', '甲' => '편인', '乙' => '정인'],
            '丁' => ['丁' => '비견', '丙' => '겁재', '己' => '식신', '戊' => '상관', '辛' => '편재', '庚' => '정재', '癸' => '편관', '壬' => '정관', '乙' => '편인', '甲' => '정인'],
            '戊' => ['戊' => '비견', '己' => '겁재', '庚' => '식신', '辛' => '상관', '壬' => '편재', '癸' => '정재', '甲' => '편관', '乙' => '정관', '丙' => '편인', '丁' => '정인'],
            '己' => ['己' => '비견', '戊' => '겁재', '辛' => '식신', '庚' => '상관', '癸' => '편재', '壬' => '정재', '乙' => '편관', '甲' => '정관', '丁' => '편인', '丙' => '정인'],
            '庚' => ['庚' => '비견', '辛' => '겁재', '壬' => '식신', '癸' => '상관', '甲' => '편재', '乙' => '정재', '丙' => '편관', '丁' => '정관', '戊' => '편인', '己' => '정인'],
            '辛' => ['辛' => '비견', '庚' => '겁재', '癸' => '식신', '壬' => '상관', '乙' => '편재', '甲' => '정재', '丁' => '편관', '丙' => '정관', '己' => '편인', '戊' => '정인'],
            '壬' => ['壬' => '비견', '癸' => '겁재', '甲' => '식신', '乙' => '상관', '丙' => '편재', '丁' => '정재', '戊' => '편관', '己' => '정관', '庚' => '편인', '辛' => '정인'],
            '癸' => ['癸' => '비견', '壬' => '겁재', '乙' => '식신', '甲' => '상관', '丁' => '편재', '丙' => '정재', '己' => '편관', '戊' => '정관', '辛' => '편인', '庚' => '정인']
        ],
        'e' => [ // 지지(e) 규칙
            '甲' => ['寅' => '비견', '卯' => '겁재', '巳' => '식신', '午' => '상관', '辰' => '편재', '戌' => '편재', '丑' => '정재', '未' => '정재', '申' => '편관', '酉' => '정관', '亥' => '편인', '子' => '정인'],
            '乙' => ['卯' => '비견', '寅' => '겁재', '午' => '식신', '巳' => '상관', '丑' => '편재', '未' => '편재', '辰' => '정재', '戌' => '정재', '酉' => '편관', '申' => '정관', '子' => '편인', '亥' => '정인'],
            '丙' => ['巳' => '비견', '午' => '겁재', '辰' => '식신', '戌' => '식신', '丑' => '상관', '未' => '상관', '申' => '편재', '酉' => '정재', '亥' => '편관', '子' => '정관', '寅' => '편인', '卯' => '정인'],
            '丁' => ['午' => '비견', '巳' => '겁재', '丑' => '식신', '未' => '식신', '辰' => '상관', '戌' => '상관', '酉' => '편재', '申' => '정재', '子' => '편관', '亥' => '정관', '卯' => '편인', '寅' => '정인'],
            '戊' => ['辰' => '비견', '戌' => '비견', '丑' => '겁재', '未' => '겁재', '申' => '식신', '酉' => '상관', '亥' => '편재', '子' => '정재', '寅' => '편관', '卯' => '정관', '巳' => '편인', '午' => '정인'],
            '己' => ['丑' => '비견', '未' => '비견', '辰' => '겁재', '戌' => '겁재', '酉' => '식신', '申' => '상관', '子' => '편재', '亥' => '정재', '卯' => '편관', '寅' => '정관', '午' => '편인', '巳' => '정인'],
            '庚' => ['申' => '비견', '酉' => '겁재', '亥' => '식신', '子' => '상관', '寅' => '편재', '卯' => '정재', '巳' => '편관', '午' => '정관', '辰' => '편인', '戌' => '편인', '丑' => '정인', '未' => '정인'],
            '辛' => ['酉' => '비견', '申' => '겁재', '子' => '식신', '亥' => '상관', '卯' => '편재', '寅' => '정재', '午' => '편관', '巳' => '정관', '丑' => '편인', '未' => '편인', '辰' => '정인', '戌' => '정인'],
            '壬' => ['亥' => '비견', '子' => '겁재', '寅' => '식신', '卯' => '상관', '巳' => '편재', '午' => '정재', '辰' => '편관', '戌' => '편관', '丑' => '정관', '未' => '정관', '申' => '편인', '酉' => '정인'],
            '癸' => ['子' => '비견', '亥' => '겁재', '卯' => '식신', '寅' => '상관', '午' => '편재', '巳' => '정재', '丑' => '편관', '未' => '편관', '辰' => '정관', '戌' => '정관', '酉' => '편인', '申' => '정인']
        ]
    ];

    /**
     * Saju 객체를 기반으로 십신 계산기를 초기화하고,
     * 사주 원국의 십신을 계산하여 속성으로 저장합니다.
     *
     * @param Saju $saju
     * @return self
     */
    public function withSaju(Saju $saju): self
    {
        $this->saju = $saju;
        // 1. 계산의 기준이 되는 일간(Day Master)을 설정
        $this->dayMaster = $saju->get_h('day');

        // 2. 내부 초기화 시에는 self::cal을 직접 호출하여 더 명확하고 효율적으로 처리
        $this->year  = new SipsinPillar(
            self::cal($this->dayMaster, $saju->get_h('year'), 'h'),
            self::cal($this->dayMaster, $saju->get_e('year'), 'e')
        );
        $this->month = new SipsinPillar(
            self::cal($this->dayMaster, $saju->get_h('month'), 'h'),
            self::cal($this->dayMaster, $saju->get_e('month'), 'e')
        );
        $this->day   = new SipsinPillar(
            '일원',
            self::cal($this->dayMaster, $saju->get_e('day'), 'e')
        );
        $this->hour  = new SipsinPillar(
            self::cal($this->dayMaster, $saju->get_h('hour'), 'h'),
            self::cal($this->dayMaster, $saju->get_e('hour'), 'e')
        );

        return $this;
    }

    /**
     * [핵심 메소드]
     * 기준이 되는 일간(Day Master)과 특정 간지(천간/지지)의 십신 관계를 반환합니다.
     * 이 메소드는 사주 원국 분석 뿐만 아니라, 대운, 세운 분석 등 외부에서도 자유롭게 사용할 수 있습니다.
     *
     * @param string $targetGanji 십신 관계를 알고 싶은 간지 글자 (예: '甲', '子')
     * @param string $type 'h'(천간) 또는 'e'(지지)
     * @return string|null 십신 이름 (예: '정재', '편관') 또는 관계를 찾지 못하면 null
     */
    public function getRelation(string $targetGanji, string $type = 'h'): ?string
    {
        if (!$this->dayMaster) {
            return null;
        }
        return self::cal($this->dayMaster, $targetGanji, $type);
    }

    /**
     * [편의 메소드]
     * 여러 개의 천간 또는 지지에 대한 십신 관계를 배열로 반환합니다.
     * 대운, 세운 분석 시 유용하게 사용될 수 있습니다.
     *
     * @param array $ganjiArray 분석할 간지 배열 (예: ['甲', '乙', '丙'])
     * @param string $type 'h' 또는 'e'
     * @return array 각 간지에 대한 십신 배열 (예: ['편재', '정재', '편관'])
     */
    public function getRelations(array $ganjiArray, string $type = 'h'): array
    {
        $results = [];
        foreach ($ganjiArray as $ganji) {
            $results[] = $this->getRelation($ganji, $type);
        }
        return $results;
    }


    /**
     * 지장간(地藏干)에 숨겨진 십신을 분석하여 반환합니다.
     * @return object (예: $result->year->초기->'정재')
     */
    public function getZizanganSipsin(): object
    {
        if (!isset($this->saju->zizangan)) {
            $this->saju->zizangan(); // 지장간 데이터가 없으면 계산
        }

        $zizangan = $this->saju->zizangan;
        $result = new \stdClass();
        $pillars = ['year', 'month', 'day', 'hour'];

        foreach ($pillars as $pillar) {
            $result->{$pillar} = new \stdClass();
            foreach (['초기', '중기', '정기'] as $gi) {
                if (isset($zizangan->{$pillar}->{$gi})) {
                    $gan = $zizangan->{$pillar}->{$gi};
                    $result->{$pillar}->{$gi} = self::calculate($this->dayMaster, $gan, 'h');
                }
            }
        }
        return $result;
    }

    /**
     * 사주 원국에 드러난 십신(천간+지지)의 개수를 요약하여 반환합니다.
     * '일원'은 제외하고, 개수가 많은 순서대로 정렬됩니다.
     *
     * @return array ['십신이름' => 개수, ...]
     */
    public function getSipsinCountSummary(): array
    {
        $summary = [];
        $pillars = ['year', 'month', 'day', 'hour'];
        foreach ($pillars as $pillar) {
            if (isset($this->{$pillar})) {
                if ($this->{$pillar}->h !== '일원') {
                    $summary[$this->{$pillar}->h] = ($summary[$this->{$pillar}->h] ?? 0) + 1;
                }
                $summary[$this->{$pillar}->e] = ($summary[$this->{$pillar}->e] ?? 0) + 1;
            }
        }
        arsort($summary);
        return $summary;
    }

    /**
     * 특정 운(대운/세운)의 천간/지지가 들어왔을 때, 원국과의 십신 관계를 반환합니다.
     * @param string $unGan 운의 천간 (예: '甲')
     * @param string $unJiji 운의 지지 (예: '子')
     * @return SipsinPillar
     */
    public function getUnSipsin(string $unGan, string $unJiji): SipsinPillar
    {
        return new SipsinPillar(
            self::calculate($this->dayMaster, $unGan, 'h'),
            self::calculate($this->dayMaster, $unJiji, 'e')
        );
    }

    /**
     * @param string $dayMaster : 출생일의 일간
     * @param string $targetGanji : 천간 혹은 지지
     * @param string $type : h: 천간  e: 지지
     * @return string|null
    */
    public static function cal(string $dayMaster, string $targetGanji, string $type): ?string
    {
        // 지지의 경우, 지장간을 고려하지 않고 대표 십신만 반환합니다.
        return self::$sipsinRules[$type][$dayMaster][$targetGanji] ?? null;
    }
}
