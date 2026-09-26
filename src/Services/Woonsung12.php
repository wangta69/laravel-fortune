<?php

namespace Pondol\Fortune\Services;

/**
 * 12 신살 구하기
 * 12신살은 년지(年支)나 일지(日支)을 기준으로 삼는데, 인(寅)의 경우를 남겨봅니다.
 * 출생년의 년지(年支=띠)로 보고,
 * 출생일의 일지(日支)를 보는 것도 참고하셔야만 합니다.
 */
class Woonsung12
{
    public $year_e;

    public $month_e;

    public $day_e;

    public $hour_e;

    private static $woonsung = [
        '甲' => ['목욕', '관대', '건록', '제왕', '쇠', '병', '사', '묘', '절', '태', '양', '장생'], // 갑
        '乙' => ['병', '쇠', '제왕', '건록', '관대', '목욕', '장생', '양', '태', '절', '묘', '사'], // 을
        '丙' => ['태', '양', '장생', '목욕', '관대', '건록', '제왕', '쇠', '병', '사', '묘', '절'], // 병
        '丁' => ['절', '묘', '사', '병', '쇠', '제왕', '건록', '관대', '목욕', '장생', '양', '태'], // 정
        '戊' => ['태', '양', '장생', '목욕', '관대', '건록', '제왕', '쇠', '병', '사', '묘', '절'], // 무
        '己' => ['절', '묘', '사', '병', '쇠', '제왕', '건록', '관대', '목욕', '장생', '양', '태'], // 기
        '庚' => ['사', '묘', '절', '태', '양', '장생', '목욕', '관대', '건록', '제왕', '쇠', '병'], // 경
        '辛' => ['장생', '양', '태', '절', '묘', '사', '병', '쇠', '제왕', '건록', '관대', '목욕'], // 신
        '壬' => ['제왕', '쇠', '병', '사', '묘', '절', '태', '양', '장생', '목욕', '관대', '건록'], // 임
        '癸' => ['건록', '관대', '목욕', '장생', '양', '태', '절', '묘', '사', '병', '쇠', '제왕'], // 계
    ];

    public function withSaju($saju)
    {
        // [수정] 시주 정보가 있을 때만 12운성을 계산합니다.
        if ($saju->hourKnown) {
            $this->hour_e = self::cal($saju->get_h('day'), $saju->get_e('hour'));
        } else {
            $this->hour_e = null;
        }
        $this->day_e = self::cal($saju->get_h('day'), $saju->get_e('day'));
        $this->month_e = self::cal($saju->get_h('day'), $saju->get_e('month'));
        $this->year_e = self::cal($saju->get_h('day'), $saju->get_e('year'));

        return $this;
    }

    public static function cal($h, $e)
    {
        return self::$woonsung[$h][e_to_serial($e)];
    }

    /**
     * 사주화 정보는 e_code의 1 이 인으로 시작하는데 프로그램상 자 를 1로 변경
     */
    public function trans_to_ch($code)
    {
        switch ($code) {
            case '장생': return '長生';
            case '목욕': return '沐浴';
            case '관대': return '冠帶';
            case '건록': return '乾祿';
            case '제왕': return '帝旺';
            case '쇠': return '衰'; // 쇄
            case '병': return '病';
            case '사': return '死';
            case '묘': return '墓';
            case '절': return '絶'; // 포
            case '태': return '胎';
            case '양': return '養';
        }
    }

    // =========================================================================
    // 12운성 순환 리스트 및 오프셋 시프트(Shift) 연산 메소드
    // =========================================================================

    /**
     * 12운성(十二運星) 포태법의 표준 순환 순서 배열
     *
     * [명리학적 배경]
     * 인간과 만물이 태어나서(생), 자라고(왕), 쇠퇴하여(쇠), 묻히고(묘),
     * 다시 잉태되는(태) '생로병사의 12단계 윤회 주기'를 나타냅니다.
     *
     * @return array<int, string> 12운성 표준 한글 명칭 배열 (0:장생 ~ 11:양)
     */
    public static function getList(): array
    {
        return [
            '장생', // 0: 새롭게 태어남 (시작, 활력)
            '목욕', // 1: 씻고 다듬음 (도화, 감정 기복)
            '관대', // 2: 관과 띠를 두름 (청년기, 성장, 발전)
            '건록', // 3: 벼슬길에 오름 (자립, 번영)
            '제왕', // 4: 최고 정점에 도달 (권력, 극치)
            '쇠',   // 5: 정점을 지나 기운이 꺾임 (내실, 쇠퇴)
            '병',   // 6: 피로와 질병 (휴식 필요, 쇠약)
            '사',   // 7: 생명 활동의 정지 (정지, 침묵)
            '묘',   // 8: 땅속에 묻힘 (저장, 고립)
            '절',   // 9: 인연이 완전히 끊어짐 (단절, 전환)
            '태',   // 10: 어머니 뱃속에 잉태됨 (새로운 잉태, 희망)
            '양',   // 11: 뱃속에서 자람 (양육, 보호)
        ];
    }

    /**
     * 기준 12운성에서 오프셋(단계)만큼 순환 이동한 12운성 명칭을 반환합니다.
     *
     * [이론 및 원리]
     * '오늘의 일진 운성(공통 기준점)'에 사용자의 '년지(띠) 또는 고유 지지 번호'를 결합하여,
     * 만인에게 똑같이 적용되는 일진 운성을 개인별 맞춤 운성으로 회전(Shift)시키는 동적 운세 산출 알고리즘입니다.
     * 음수 오프셋(역행)이 들어오더라도 12진법 순환 수학 공식을 통해 12운성 내에서 안전하게 순환합니다.
     *
     * 3. 일일 애정운 조회   : S006 테이블 ('woonsung' 컬럼)
     *
     * [사용 서비스 클래스]
     * - App\Services\Fortune\Jum\JuyeokService
     * - App\Services\Fortune\Gunghap\SasangMatchService
     * - App\Services\Fortune\Saju\TodaysFortuneService
     *
     * [사용 예시]
     * <code>
     *   // 1. Static 직접 호출 (가장 권장)
     *   $todayWoonsungBase = $todaySaju->woonsung12->day_e; // 오늘 일진의 운성 (예: '제왕')
     *   $targetWoonsung = Woonsung12::shift($todayWoonsungBase, $saju->year->e->num);
     *
     *   // 2. 인스턴스 메소드 호출 (saju 객체 체이닝)
     *   $targetWoonsung = $saju->woonsung12()->shift($todayWoonsungBase, 3);
     * </code>
     *
     * @param  string  $baseName  기준 12운성 명칭 (예: '장생', '건록', '제왕' 등)
     * @param  int  $offset  이동할 단계 수 (보통 사용자의 띠 번호 e->num: 1~12 또는 특정 오프셋)
     * @return string 이동 후 최종 도출된 12운성 한글 명칭
     */
    public static function shift(string $baseName, int $offset): string
    {
        $list = self::getList();
        $baseIdx = array_search($baseName, $list);

        // 기준 운성 명칭이 올바르지 않으면 원본 그대로 반환 (방어 코드)
        if ($baseIdx === false) {
            return $baseName;
        }

        // 12진법 안전 순환 수식 (음수 역행 및 12 이상의 수치 완벽 대응)
        $newIdx = (($baseIdx + $offset) % 12 + 12) % 12;

        return $list[$newIdx];
    }
}
