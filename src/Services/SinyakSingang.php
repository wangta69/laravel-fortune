<?php
namespace Pondol\Fortune\Services;

/**
 * 사주(四柱)의 신강/신약(身强/身弱)을 분석하는 서비스 클래스.
 *
 * 이 클래스는 사주팔자 여덟 글자의 오행 분포와 세력을 계산하여,
 * 사주의 주인공인 일간(日干)이 스스로 강한 힘을 가졌는지(신강),
 * 아니면 주변의 도움이 필요한 약한 상태인지(신약)를 판단합니다.
 */
class SinyakSingang
{
    /**
     * 분석할 사주 객체
     * @var Saju
     */
    private $saju;

    /**
     * 사주의 주인공인 일간(日干)의 오행
     * @var string
     */
    private $dayMaster;

    /**
     * 일간과 오행이 같아 힘이 되는 '아군' (비견/겁재)
     * @var string
     */
    private $myAllyElement;

    /**
     * 일간을 생(生)하여 힘을 더해주는 '지원군' (인성)
     * @var string
     */
    private $mySupporterElement;

    /**
     * 각 위치(궁위)별 오행 세력 가중치.
     * 사주에서 가장 영향력이 큰 월지(태어난 계절)에 가장 높은 점수를 부여합니다.
     * @var array
     */
    private const POINTS = [
        'month_jiji' => 40, // 월지 (득령: 계절의 힘을 얻음)
        'day_jiji'   => 20, // 일지 (득지: 배우자궁의 도움)
        'year_jiji'  => 15, // 년지 (조상궁의 도움)
        'hour_jiji'  => 15, // 시지 (자식궁의 도움)
        'cheon_gan'  => 10  // 천간 (득세: 주변의 도움)
    ];
    
    /**
     * 신강과 신약을 나누는 기준 점수
     * @var int
     */
    private const THRESHOLD = 50;
    
    /**
     * 사주 객체를 받아 분석을 준비합니다.
     * @param Saju $saju 분석할 사주 객체
     * @return self
     */
    public function withSaju(Saju $saju): self
    {
        $this->saju = $saju;
        
        // 사주의 주인공인 일간(日干)의 오행을 찾습니다.
        $this->dayMaster = $this->convertCharToOhaeng($saju->get_h('day'));
        
        // 오행의 상생 관계를 정의합니다. (예: 水生木 -> '木'을 돕는 것은 '水')
        $generationCycle = ['木' => '水', '火' => '木', '土' => '火', '金' => '土', '水' => '金'];
        
        // 일간을 돕는 세력(아군과 지원군)을 설정합니다.
        $this->myAllyElement = $this->dayMaster;
        $this->mySupporterElement = $generationCycle[$this->dayMaster] ?? '';
        
        return $this;
    }

    /**
     * 신강/신약 분석을 실행하고 최종 결과를 객체로 반환합니다.
     * @return object
     */
    public function create(): object
    {
        $allyScores = $this->calculateAllyScores(); // 일간을 돕는 오행들의 총점 계산
        $totalScore = array_sum($allyScores);
        
        // 기준 점수(THRESHOLD)를 넘으면 '신강', 아니면 '신약'으로 판단
        $result = ($totalScore >= self::THRESHOLD) ? '신강' : '신약';
        // 기준점 근처(±10)에 있으면 '중화' 사주로 판단하여 더 세밀한 결과를 제공
        if (abs($totalScore - self::THRESHOLD) < 10) {
            $result = '중화';
        }

        return (object) [
            'total_score' => $totalScore,
            'threshold'   => self::THRESHOLD,
            'result'      => $result,
            'ally_scores_by_element' => $allyScores,
            'day_master'  => $this->dayMaster,
        ];
    }
    
    /**
     * 사주 전체 오행의 세력 점수를 계산하여 반환합니다. (용신 보정 시 사용)
     * @return array
     */
    public function getAllOhaengScores(): array
    {
        $scores = ['木' => 0, '火' => 0, '土' => 0, '金' => 0, '水' => 0];

        foreach (['year', 'month', 'day', 'hour'] as $pillar) {
            // 지지(地支) 오행에 점수 부여
            $jijiElement = $this->convertCharToOhaeng($this->saju->get_e($pillar));
            $point = ($pillar === 'month') ? self::POINTS['month_jiji'] : self::POINTS[$pillar.'_jiji'];
            if($jijiElement) $scores[$jijiElement] += $point;

            // 천간(天干) 오행에 점수 부여
            $cheonGanElement = $this->convertCharToOhaeng($this->saju->get_h($pillar));
            if($cheonGanElement) $scores[$cheonGanElement] += self::POINTS['cheon_gan'];
        }
        
        return $scores;
    }

    /**
     * 일간(日干)을 돕는 세력(인성, 비겁)만의 점수를 계산합니다.
     * @return array
     */
    private function calculateAllyScores(): array
    {
        $scores = ['木' => 0, '火' => 0, '土' => 0, '金' => 0, '水' => 0];

        // 지지(地支)의 아군 점수 계산
        foreach (['year', 'month', 'day', 'hour'] as $pillar) {
            $jijiElement = $this->convertCharToOhaeng($this->saju->get_e($pillar));
            if ($this->isAlly($jijiElement)) {
                $scores[$jijiElement] += self::POINTS[$pillar.'_jiji'];
            }
        }

        // 천간(天干)의 아군 점수 계산 (일간 제외)
        foreach (['year', 'month', 'hour'] as $pillar) {
            $cheonGanElement = $this->convertCharToOhaeng($this->saju->get_h($pillar));
            if ($this->isAlly($cheonGanElement)) {
                $scores[$cheonGanElement] += self::POINTS['cheon_gan'];
            }
        }
        
        return $scores;
    }

    /**
     * 주어진 오행이 일간의 아군(비겁 또는 인성)인지 확인합니다.
     * @param string $element
     * @return bool
     */
    private function isAlly(string $element): bool
    {
        return $element === $this->myAllyElement || $element === $this->mySupporterElement;
    }

    /**
     * 천간/지지 한자를 표준 오행 문자열('木', '火' 등)로 변환하는 헬퍼 메소드.
     * @param string $char 변환할 한자 (예: '甲', '子')
     * @return string
     */
    private function convertCharToOhaeng(string $char): string
    {
        switch($char){
            case '甲': case '乙': case '寅': case '卯': return '木';
            case '丙': case '丁': case '巳': case '午': return '火';
            case '戊': case '己': case '辰': case '戌': case '丑': case '未': return '土';
            case '庚': case '辛': case '申': case '酉': return '金';
            case '壬': case '癸': case '亥': case '子': return '水';
            default: return '';
        }
    }
}