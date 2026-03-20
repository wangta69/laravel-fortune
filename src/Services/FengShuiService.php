<?php

namespace Pondol\Fortune\Services;

class FengShuiService
{
    /**
     * 사용자의 프로필을 바탕으로 핵심 풍수 데이터 분석
     */
    public function analyze($profile)
    {
        // 1. 생년 추출 (풍수 본명궁은 입춘 기준이나, 여기서는 단순 연도 계산 로직 적용)
        $year = (int) substr($profile->birth_ym, 0, 4);
        $gender = $profile->gender; // 'M' or 'F'

        // 2. 본명궁(Kua Number) 계산
        $kuaNum = $this->calculateKuaNumber($year, $gender);
        $kuaGroup = in_array($kuaNum, [1, 3, 4, 9]) ? '동사택' : '서사택';

        // 3. 본명궁별 에너지 및 방향 매핑
        $kuaData = $this->getKuaMapping($kuaNum);

        return [
            'element_ko' => $kuaData['element_ko'],
            'element_key' => $kuaData['element_key'],
            'core_tools' => [
                'kua' => [
                    'number' => $kuaNum,
                    'group' => $kuaGroup,
                ],
                'sleeping' => $kuaData['direction'], // 최적 숙면 방향
                'diagnosis_title' => $kuaData['element_ko'].'의 기운을 다스리는 공간',
                'diagnosis_content' => '귀하는 타고난 <b>'.$kuaData['element_ko'].'</b>의 에너지를 가지고 있습니다. '.
                                     '현재 가장 필요한 기운은 '.$kuaData['direction'].' 방향에서 들어오는 생기입니다.',
            ],
        ];
    }

    /**
     * 본명궁 계산 공식
     */
    private function calculateKuaNumber($year, $gender)
    {
        // 연도 숫자를 모두 더해 한 자리가 될 때까지 반복
        $sum = array_sum(str_split($year));
        while ($sum > 9) {
            $sum = array_sum(str_split($sum));
        }

        if ($gender === 'M') {
            $res = 11 - $sum;
        } else {
            $res = 4 + $sum;
        }

        if ($res > 9) {
            $res = array_sum(str_split($res));
        }
        if ($res === 5) {
            return ($gender === 'M') ? 2 : 8;
        } // 5일 경우 남자는 2, 여자는 8로 대체

        return $res;
    }

    /**
     * 본명궁별 속성 데이터
     */
    private function getKuaMapping($num)
    {
        $map = [
            1 => ['element_ko' => '물', 'element_key' => 'water', 'direction' => '북쪽'],
            2 => ['element_ko' => '흙', 'element_key' => 'earth', 'direction' => '남서쪽'],
            3 => ['element_ko' => '나무', 'element_key' => 'wood', 'direction' => '동쪽'],
            4 => ['element_ko' => '나무', 'element_key' => 'wood', 'direction' => '남동쪽'],
            6 => ['element_ko' => '쇠', 'element_key' => 'metal', 'direction' => '북서쪽'],
            7 => ['element_ko' => '쇠', 'element_key' => 'metal', 'direction' => '서쪽'],
            8 => ['element_ko' => '흙', 'element_key' => 'earth', 'direction' => '북동쪽'],
            9 => ['element_ko' => '불', 'element_key' => 'fire', 'direction' => '남쪽'],
        ];

        return $map[$num] ?? $map[1];
    }

    /**
     * 라이프스타일 아이템 리스트 (필요 시 확장)
     */
    public function getLifestyleItems($category, $element)
    {
        // DB나 별도 데이터 셋에서 카테고리별/오행별 아이템 반환 로직
        return [];
    }
}
