<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Facades\Saju as SajuFacade;
use Pondol\Fortune\Traits\SinsalRules;

/**
 * 사주 정보를 바탕으로 특정 날짜의 길흉(擇日)을 분석하는 클래스입니다.
 * 개인의 연지(띠)와 목표 날짜의 간지를 비교하여 그날의 신살을 판별합니다.
 */
class Taekil
{
    use SinsalRules;

    /**
     * 택일 관련 신살의 모든 정적 정보(한자, 유형, 설명)를 중앙에서 관리합니다.
     */
    private const DEFINITIONS = [
        // --- 흉살 (주의가 필요한 날) ---
        '상문살' => ['ch' => '喪門殺', 'type' => 'hyungsal', 'desc' => '초상집 방문이나 문상 등 슬픈 일을 피하는 것이 좋은 날.'],
        '조객살' => ['ch' => '弔客殺', 'type' => 'hyungsal', 'desc' => '타인의 슬픔을 위로할 일이 생길 수 있으니, 언행에 주의가 필요한 날.'],
        '대모살' => ['ch' => '大耗殺', 'type' => 'hyungsal', 'desc' => '큰 재물의 손실이나 예상치 못한 큰 지출이 있을 수 있는 날.'],
        '천모살' => ['ch' => '天耗殺', 'type' => 'hyungsal', 'desc' => '하늘의 기운으로 인해 재물이 소모될 수 있음을 암시하는 날.'],
        '지모살' => ['ch' => '地耗殺', 'type' => 'hyungsal', 'desc' => '땅의 기운으로 인해 재물이 소모될 수 있음을 암시하는 날.'],
        '관부살' => ['ch' => '官符殺', 'type' => 'hyungsal', 'desc' => '관재구설, 법적 문제, 시비 등에 휘말릴 수 있으니 주의가 필요한 날.'],
        '병부살' => ['ch' => '病符殺', 'type' => 'hyungsal', 'desc' => '질병이나 건강 문제가 발생하기 쉬우니, 과로를 피하고 건강을 돌봐야 하는 날.'],
        '소모살' => ['ch' => '小耗殺', 'type' => 'hyungsal', 'desc' => '작은 지출이나 재물의 손실이 있을 수 있는 날. (대모살과 유사)'],
        '반음살' => ['ch' => '返吟殺', 'type' => 'hyungsal', 'desc' => '나의 띠와 같은 띠의 날로, 일이 반복되거나 지체될 수 있는 날.'],
        '세파살' => ['ch' => '歲破殺', 'type' => 'hyungsal', 'desc' => '나의 띠와 정면으로 충돌하는 날로, 계획이 깨지거나 다툼이 발생하기 쉬우니 매사 신중해야 하는 날.'],
        '하괴'   => ['ch' => '天罡',   'type' => 'hyungsal', 'desc' => '하늘의 기운이 막히는 날로, 중요한 결정을 피하는 것이 좋은 날.'],

        // --- 길신 (긍정적인 날) ---
        '생기'   => ['ch' => '生氣',   'type' => 'gilsin', 'desc' => '활기찬 생기가 넘치는 날로, 새로운 일을 시작하기에 좋은 날.'],
        '천강'   => ['ch' => '天罡',   'type' => 'gilsin', 'desc' => '하늘의 굳센 기운이 깃드는 날로, 어려움을 극복하고 추진하기 좋은 날.'],

    ];

    private string $yeonji;

    public function withSaju($saju): self
    {
        $this->yeonji = $saju->get_e('year');
        return $this;
    }

    /**
     * 특정 날짜(YYYY-MM-DD)에 해당하는 택일 정보를 계산하여 반환합니다.
     * @param string $dateString
     * @return array
     */
    public function checkDate(string $dateString): array
    {
        $results = [];

        // 목표 날짜의 사주 정보를 얻기 위해 Saju Facade를 사용합니다.
        $dateSaju = SajuFacade::ymd(str_replace('-', '', $dateString))->create();
        $targetJiji = $dateSaju->get_e('day');

        // 각 신살의 해당 여부를 확인하고 결과에 추가합니다.
        if ($this->isSangmun($this->yeonji, $targetJiji)) {
            $results['sangmun'] = array_merge(['ko' => '상문살'], self::DEFINITIONS['상문살']);
        }
        if ($this->isJogaek($this->yeonji, $targetJiji)) {
            $results['jogaek'] = array_merge(['ko' => '조객살'], self::DEFINITIONS['조객살']);
        }
        if ($this->isGuanbu($this->yeonji, $targetJiji)) {
            $results['guanbu'] = array_merge(['ko' => '관부살'], self::DEFINITIONS['관부살']);
        }
        if ($this->isByungbu($this->yeonji, $targetJiji)) {
            $results['byungbu'] = array_merge(['ko' => '병부살'], self::DEFINITIONS['병부살']);
        }
        if ($this->isSomo($this->yeonji, $targetJiji)) {
            $results['somo'] = array_merge(['ko' => '소모살'], self::DEFINITIONS['소모살']);
        }
        if ($this->isBanum($this->yeonji, $targetJiji)) {
            $results['banum'] = array_merge(['ko' => '반음살'], self::DEFINITIONS['반음살']);
        }
        if ($this->isSepa($this->yeonji, $targetJiji)) {
            $results['sepa'] = array_merge(['ko' => '세파살'], self::DEFINITIONS['세파살']);
        }
        if ($this->isHague($this->yeonji, $targetJiji)) {
            $results['hague'] = array_merge(['ko' => '하괴'], self::DEFINITIONS['하괴']);
        }
        if ($this->isSengi($this->yeonji, $targetJiji)) {
            $results['sengi'] = array_merge(['ko' => '생기'], self::DEFINITIONS['생기']);
        }
        if ($this->isCheongang($dateSaju->get_e('month'), $targetJiji)) {
            $results['cheongang'] = array_merge(['ko' => '천강'], self::DEFINITIONS['천강']);
        }
        if ($this->isDaemo($this->yeonji, $targetJiji)) {
            $results['daemo'] = array_merge(['ko' => '대모살'], self::DEFINITIONS['대모살']);
        }
        if ($this->isCheonmo($this->yeonji, $targetJiji)) {
            $results['cheonmo'] = array_merge(['ko' => '천모살'], self::DEFINITIONS['천모살']);
        }
        if ($this->isJimo($this->yeonji, $targetJiji)) {
            $results['jimo'] = array_merge(['ko' => '지모살'], self::DEFINITIONS['지모살']);
        }
        return $results;
    }

    // --- private 헬퍼 메서드 (기존 Sinsal.php 로직 번역) ---

    private function isSangmun(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['子寅','丑卯','寅辰','卯巳','辰午','巳未','午申','未酉','申戌','酉亥','戌子','亥丑']);
    }
    private function isJogaek(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['子戌','丑亥','寅子','卯丑','辰寅','巳卯','午辰','未巳','申午','酉未','戌申','亥酉']);
    }
    private function isGuanbu(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['子辰','丑巳','寅午','卯未','辰申','巳酉','午戌','未亥','申子','酉丑','戌寅','亥卯']);
    }
    private function isByungbu(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['子亥','丑子','寅丑','卯寅','辰卯','巳辰','午巳','未午','申未','酉申','戌酉','亥戌']);
    }
    private function isSomo(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['子巳','丑午','寅未','卯申','辰酉','巳戌','午亥','未子','申丑','酉寅','戌卯','亥辰']);
    }
    private function isBanum(string $yeonji, string $targetJiji): bool
    {
        return $yeonji === $targetJiji;
    }
    private function isSepa(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['子酉','丑辰','寅亥','卯午','辰丑','巳申','午卯','未戌','申巳','酉子','戌未','亥寅']);
    }
    private function isHague(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['寅亥','卯午','辰丑','巳申','午卯','未戌','申巳','酉子','戌未','亥寅','子酉','丑辰']);
    }
    private function isSengi(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['寅戌','卯亥','辰子','巳丑','午寅','未卯','申辰','酉巳','戌午','亥未','子申','丑酉']);
    }
    private function isCheongang(string $wolji, string $targetJiji): bool
    {
        return in_array($wolji.$targetJiji, ['寅巳','卯子','辰未','巳寅','午酉','未辰','申亥','酉午','戌丑','亥申','子卯','丑戌']);
    }
    private function isDaemo(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['子午','丑未','寅申','卯酉','辰戌','巳亥','午子','未丑','申寅','酉卯','戌辰','亥巳']);
    }
    private function isCheonmo(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['子申','丑戌','寅子','卯寅','辰辰','巳午','午申','未戌','申子','酉寅','戌辰','亥午']);
    }
    private function isJimo(string $yeonji, string $targetJiji): bool
    {
        return in_array($yeonji.$targetJiji, ['子巳','丑未','寅酉','卯亥','辰丑','巳卯','午巳','未未','申酉','酉亥','戌丑','亥卯']);
    }

    // 3. 모든 헬퍼 메소드들이 SinsalRules 트레이트의 메소드를 호출하도록 수정
    public static function hasChunduk(string $wolji, string $cheongan, string $jiji): bool
    {
        return self::isChunduk($wolji, $cheongan, $jiji);
    }

    public static function hasWolduk(string $wolji, string $cheongan): bool
    {
        return self::isWolduk($wolji, $cheongan);
    }

    public static function hasWoldukHap(string $wolji, string $cheongan): bool
    {
        return self::isWoldukHap($wolji, $cheongan);
    }

    public static function hasChendukHap(string $wolji, string $jiji): bool
    {
        return self::isChendukHap($wolji, $jiji);
    }

    public static function hasSengi(string $wolji, string $jiji): bool
    {
        return self::isSengi_Taekil($wolji, $jiji);
    }

    public static function hasChene(string $wolji, string $jiji): bool
    {
        return self::isChene($wolji, $jiji);
    }

    public static function hasChengang(string $wolji, string $jiji): bool
    {
        return self::isChengang_Taekil($wolji, $jiji);
    }

    public static function hasHague(string $wolji, string $jiji): bool
    {
        return self::isHague_Taekil($wolji, $jiji);
    }
}
