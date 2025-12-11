<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Facades\Lunar;
use Pondol\Fortune\Facades\Saju;

class TojeongJakgwae
{
    /**
     * 60갑자에 따른 태세(년), 월건(월), 일진(일)의 수치표입니다.
     * 이 값은 변하지 않으므로 상수로 정의합니다.
     * 형식: '간지' => [태세수, 월건수, 일진수]
     */
    private const TAEWOLIL_SUCHI = [
        '甲子' => [20, 18, 18], '甲戌' => [22, 14, 20], '甲申' => [21, 16, 19], '甲午' => [18, 18, 16], '甲辰' => [22, 14, 20], '甲寅' => [19, 16, 17],
        '乙丑' => [21, 16, 19], '乙亥' => [19, 12, 17], '乙酉' => [20, 14, 18], '乙未' => [21, 16, 19], '乙巳' => [17, 12, 15], '乙卯' => [18, 14, 16],
        '丙寅' => [17, 14, 15], '丙子' => [18, 16, 16], '丙戌' => [20, 12, 18], '丙申' => [19, 14, 17], '丙午' => [16, 16, 14], '丙辰' => [20, 12, 18],
        '丁卯' => [16, 12, 14], '丁丑' => [19, 14, 17], '丁亥' => [17, 10, 15], '丁酉' => [18, 12, 16], '丁未' => [19, 14, 17], '丁巳' => [15, 10, 13],
        '戊辰' => [18, 10, 16], '戊寅' => [15, 12, 13], '戊子' => [16, 14, 14], '戊戌' => [18, 10, 16], '戊申' => [17, 12, 15], '戊午' => [14, 14, 12],
        '己巳' => [18, 13, 16], '己卯' => [19, 15, 17], '己丑' => [22, 17, 20], '己亥' => [20, 13, 18], '己酉' => [21, 15, 19], '己未' => [22, 17, 20],
        '庚午' => [17, 17, 15], '庚辰' => [21, 13, 19], '庚寅' => [18, 15, 16], '庚子' => [19, 17, 17], '庚戌' => [21, 13, 19], '庚申' => [20, 15, 18],
        '辛未' => [20, 15, 18], '辛巳' => [16, 11, 14], '辛卯' => [17, 13, 15], '辛丑' => [20, 15, 18], '辛亥' => [18, 11, 16], '辛酉' => [19, 13, 17],
        '壬申' => [18, 13, 16], '壬午' => [15, 15, 13], '壬辰' => [19, 11, 17], '壬寅' => [16, 13, 14], '壬子' => [17, 15, 15], '壬戌' => [19, 11, 17],
        '癸酉' => [17, 11, 15], '癸未' => [18, 13, 16], '癸巳' => [14, 9, 12], '癸卯' => [15, 11, 13], '癸丑' => [18, 13, 16], '癸亥' => [16, 9, 14]
    ];

    private $saju;

    // 계산 과정에서 나온 중간 값들을 저장하는 변수
    public int $this_year;   // [추가] 토정비결을 보는 해당 년도
    public object $now;         // [추가] 해당 년도 생일의 만세력 정보
    public int $age;         // 한국 나이
    public int $taeseSu;     // 태세수 (년)
    public int $dalSu;       // 달수 (음력 해당월의 일수 29 or 30)
    public int $wolgeonSu;   // 월건수 (월)
    public int $iljinSu;     // 일진수 (일)

    // 최종 결과
    public array $gwae;         // 괘 결과 [상괘, 중괘, 하괘]
    public string $totalGwae;   // 최종 괘 (예: '111')


    public function withSaju($saju)
    {
        $this->saju = $saju;
        return $this;
    }


    /**
     * 주어진 사주 정보를 바탕으로 토정비결 괘를 생성합니다.
     *
     * @param int|null $fortuneYear 토정비결을 보고자 하는 년도. 기본값은 현재 년도.
     * @return $this
     */
    public function create(int $fortuneYear = null)
    {
        $fortuneYear = $fortuneYear ?? (int)date('Y');
        $this->this_year = $fortuneYear;
        [, $lunarBirthMonth, $lunarBirthDay] = explode('-', $this->saju->lunar);

        // 보고자 하는 년도의 음력 생일에 해당하는 만세력 정보를 구합니다.
        $fortuneYearManse = Saju::ymdhi($fortuneYear . '-' . $lunarBirthMonth . '-' . $lunarBirthDay)
            ->sl('lunar')
            ->create();

        $this->now = $fortuneYearManse;

        // 상괘, 중괘, 하괘를 각각 계산합니다.
        $sangGwae = $this->getSangGwae($fortuneYear, $fortuneYearManse);
        $jungGwae = $this->getJungGwae($fortuneYearManse);
        $haGwae   = $this->getHaGwae($fortuneYearManse, (int)$lunarBirthDay);

        // 계산된 결과를 클래스 속성에 저장합니다.
        $this->gwae = [$sangGwae, $jungGwae, $haGwae];
        $this->totalGwae = implode('', $this->gwae);

        return $this;
    }



    /**
    * 상괘(上卦)를 계산합니다.
    * 공식: (한국 나이 + 태세수) ÷ 8 의 나머지

    * 태세 수는 토정비결 1쪽에서 무조건 본인이 보고자 하는 해의 태세를 찾는다. 즉, 그해의 태세 수는 모든 사람이 같다
    * 현재 본인의 나이에 1에서 나온 태세 수를 더해서 8로 나눈 후 그 나머지 값을 상괘라 한다.
    * 단, 8로 나눠서 나머지 수가 0 이 나올 경우에는 상괘를 8로 한다
    *
    * @param int $fortuneYear 보고자 하는 년도
    * @param object $fortuneYearManse 해당 년도의 만세력 정보
    * @return int
    */
    private function getSangGwae(int $fortuneYear, object $fortuneYearManse): int
    {
        // 나이 구하기
        $birthYear = (int)substr($this->saju->solar, 0, 4);
        $this->age = $fortuneYear - $birthYear + 1;

        $yearGanji = $fortuneYearManse->get_he('year'); // 올해의 간지
        // 토정을 보는 해의 60갑자를 가져와 태세수를 구하고 나이와 더한후 8로 나눈다.
        $this->taeseSu = self::TAEWOLIL_SUCHI[$yearGanji][0];

        return mod_zero_to_mod(($this->age + $this->taeseSu), 8);
    }


    /**
     * 중괘(中卦)를 계산합니다.
     * 공식: (음력 해당월의 일수 + 월건수) ÷ 6 의 나머지
     * 낳은 달수는 본인이 보고자 하는 해의 음력 생일이 큰 달(大)일 경우에는 30이, 작은 달(小)일 경우에는 29가 된다.
     * 월건수는 2쪽의 '월건법'에서 본인이 보고자 하는 해에 해당되는 생월의 간지를 찾은 후, 1쪽에서 그 간지에 나와있는 수를 월건수로 한다.
     * 위의 1번과 2번에서 나온 수를 더해 6으로 나눈 그 나머지 값을 중괘라 한다.
     *
     * @param object $fortuneYearManse 해당 년도의 만세력 정보
     * @return int
     */
    private function getJungGwae(object $fortuneYearManse): int
    {
        // 보고자 하는 년도의 음력 생월이 큰달(30일)인지 작은달(29일)인지 확인합니다.
        $lunarInfo = Lunar::ymd($fortuneYearManse->solar)->tolunar()->create();
        $this->dalSu = $lunarInfo->largemonth ? 30 : 29;

        // 월건수 구하기 (올해에서 내 생월이 포함된 것을 절기를 기준)
        // 2. 만세력에서 바로가져오기
        $monthGanji = $fortuneYearManse->get_he('month'); // 해당 월의 간지
        $this->wolgeonSu = self::TAEWOLIL_SUCHI[$monthGanji][1];

        return mod_zero_to_mod(($this->dalSu + $this->wolgeonSu), 6);
    }

    /**
     * 하괘(下卦)를 계산합니다.
     * 공식: (음력 생일 + 일진수) ÷ 3 의 나머지
     * 일진수는 본인이 보고자 하는 해의 음력 생일에 나와 있는 간지를 찾은 후 1쪽에서 그 간지에서 해당되는 수를 일진수로 한다.(간지는 달력에서 손쉽게 찾아 볼 수 있다.)
    *
    * @param object $fortuneYearManse 해당 년도의 만세력 정보
    * @param int $lunarBirthDay 음력 생일(일)
    * @return int
    */
    private function getHaGwae(object $fortuneYearManse, int $lunarBirthDay): int
    {
        $dayGanji = $fortuneYearManse->get_he('day'); // 해당 일의 간지
        $this->iljinSu = self::TAEWOLIL_SUCHI[$dayGanji][2];

        return mod_zero_to_mod(($lunarBirthDay + $this->iljinSu), 3);
    }

}
