<?php

namespace Pondol\Fortune\Traits;

/**
 * Sinsal과 Taekil 클래스에서 공통으로 사용하는 신살 판별 로직을 모아놓은 트레이트입니다.
 * 모든 판별 로직의 유일한 원천(Single Source of Truth) 역할을 합니다.
 */
trait SinsalRules
{
    // --- 귀인(貴人) 시리즈 ---

    protected static function isChunduk(string $wolji, string $cheongan, string $jiji): bool
    {
        switch ($wolji) {
            case '子': return $jiji === '巳';
            case '丑': return $cheongan === '庚';
            case '寅': return $cheongan === '丁';
            case '卯': return $jiji === '申';
            case '辰': return $cheongan === '壬';
            case '巳': return $jiji === '申';
            case '午': return $jiji === '亥';
            case '未': return $cheongan === '甲';
            case '申': return $cheongan === '癸';
            case '酉': return $jiji === '寅';
            case '戌': return $cheongan === '丙';
            case '亥': return $cheongan === '乙';
            default: return false;
        }
    }

    protected static function isWolduk(string $wolji, string $cheongan): bool
    {
        if (in_array($wolji, ['亥', '卯', '未'])) {
            return $cheongan === '甲';
        }
        if (in_array($wolji, ['寅', '午', '戌'])) {
            return $cheongan === '丙';
        }
        if (in_array($wolji, ['巳', '酉', '丑'])) {
            return $cheongan === '庚';
        }
        if (in_array($wolji, ['申', '子', '辰'])) {
            return $cheongan === '壬';
        }

        return false;
    }

    protected static function isWoldukHap(string $wolji, string $cheongan): bool
    {
        if (in_array($wolji, ['亥', '卯', '未'])) {
            return $cheongan === '己';
        }
        if (in_array($wolji, ['寅', '午', '戌'])) {
            return $cheongan === '辛';
        }
        if (in_array($wolji, ['巳', '酉', '丑'])) {
            return $cheongan === '乙';
        }
        if (in_array($wolji, ['申', '子', '辰'])) {
            return $cheongan === '丁';
        }

        return false;
    }

    protected static function isChendukHap(string $wolji, string $jiji): bool
    {
        return in_array($wolji.$jiji, ['子申', '丑乙', '寅壬', '卯巳', '辰丁', '巳丙', '午寅', '未己', '申戊', '酉亥', '戌辛', '亥庚']);
    }

    protected static function isChene(string $wolji, string $jiji): bool
    {
        return in_array($wolji.$jiji, ['子亥', '丑子', '寅丑', '卯寅', '辰卯', '巳辰', '午巳', '未午', '申未', '酉申', '戌酉', '亥戌']);
    }

    // --- 지지 간의 특수 관계 판별 (형, 충, 파, 해, 원진) ---

    public static function checkJijiRelation(string $jiji1, string $jiji2): ?string
    {
        // 1. 충 (沖)
        $chung = ['子' => '午', '午' => '子', '丑' => '未', '未' => '丑', '寅' => '申', '申' => '寅', '卯' => '酉', '酉' => '卯', '辰' => '戌', '戌' => '辰', '巳' => '亥', '亥' => '巳'];
        if (isset($chung[$jiji1]) && $chung[$jiji1] === $jiji2) {
            return '충';
        }

        // 2. 원진살 (怨嗔煞)
        $wonjin = ['子' => '未', '未' => '子', '丑' => '午', '午' => '丑', '寅' => '酉', '酉' => '寅', '卯' => '申', '申' => '卯', '辰' => '亥', '亥' => '辰', '巳' => '戌', '戌' => '巳'];
        if (isset($wonjin[$jiji1]) && $wonjin[$jiji1] === $jiji2) {
            return '원진살';
        }

        // 3. 형살 (刑煞)
        $hyeong = ['寅' => '巳', '巳' => '申', '申' => '寅', '丑' => '戌', '戌' => '未', '未' => '丑'];
        if (isset($hyeong[$jiji1]) && $hyeong[$jiji1] === $jiji2) {
            return '형살';
        }

        // 4. 파살 (破煞)
        $pha = ['子' => '酉', '酉' => '子', '丑' => '辰', '辰' => '丑', '寅' => '亥', '亥' => '寅', '卯' => '午', '午' => '卯', '巳' => '申', '申' => '巳', '未' => '戌', '戌' => '未'];
        if (isset($pha[$jiji1]) && $pha[$jiji1] === $jiji2) {
            return '파살';
        }

        // 5. 해살 (害煞)
        $hae = ['子' => '未', '未' => '子', '丑' => '午', '午' => '丑', '寅' => '巳', '巳' => '寅', '卯' => '辰', '辰' => '卯', '申' => '亥', '亥' => '申', '酉' => '戌', '戌' => '酉'];
        if (isset($hae[$jiji1]) && $hae[$jiji1] === $jiji2) {
            return '해살';
        }

        return null;
    }

    // --- 택일에서 주로 사용하는 길흉신 ---

    protected static function isSengi_Taekil(string $wolji, string $jiji): bool
    {
        return in_array($wolji.$jiji, ['子申', '丑酉', '寅戌', '卯亥', '辰子', '巳丑', '午寅', '未卯', '申辰', '酉巳', '戌午', '亥未']);
    }

    protected static function isChengang_Taekil(string $wolji, string $jiji): bool
    {
        return in_array($wolji.$jiji, ['寅巳', '卯子', '辰未', '巳寅', '午酉', '未辰', '申亥', '酉午', '戌丑', '亥申', '子卯', '丑戌']);
    }

    protected static function isHague_Taekil(string $wolji, string $jiji): bool
    {
        return in_array($wolji.$jiji, ['寅亥', '卯午', '辰丑', '巳申', '午卯', '未戌', '申巳', '酉子', '戌未', '亥寅', '子酉', '丑辰']);
    }
}
