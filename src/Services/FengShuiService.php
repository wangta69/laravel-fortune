<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Facades\Saju as SajuFacade;

class FengShuiService
{
    /**
     * 사용자의 사주($saju) 또는 프로필($profile)을 받아 정밀 팔택풍수 분석
     */
    public function analyze($sajuOrProfile)
    {
        // 1. 연도 및 성별 추출 (입춘 절기 자동 보정)
        if (is_object($sajuOrProfile) && isset($sajuOrProfile->year)) {
            $year = (int) substr($sajuOrProfile->solar, 0, 4);
            $gender = $sajuOrProfile->gender ?? 'M';
        } else {
            $birthYm = $sajuOrProfile->birth_ym ?? ($sajuOrProfile['birth_ym'] ?? null);
            if ($birthYm) {
                $cleanDate = str_replace(['-', '.', ' ', ':'], '', trim($birthYm));
                $ymdhi = (strlen($cleanDate) === 8) ? $cleanDate.'1200' : $cleanDate;
                $saju = SajuFacade::ymdhi($ymdhi)->create();
                $year = (int) substr($saju->solar, 0, 4);
            } else {
                $year = (int) ($sajuOrProfile->birth_year ?? 1990);
            }
            $gender = $sajuOrProfile->gender ?? ($sajuOrProfile['gender'] ?? 'M');
        }

        $isFemale = in_array(strtoupper($gender), ['F', 'W', '여']);

        // 2. 2000년대생 분기 처리된 정밀 본명궁 계산
        $kuaNum = $this->calculateKuaNumber($year, $isFemale);
        $kuaGroup = in_array($kuaNum, [1, 3, 4, 9]) ? '동사택(東四宅)' : '서사택(西四宅)';

        // 3. 고도화된 8괘 메타데이터 및 8방위 길흉 추출
        $kuaData = $this->getKuaMapping($kuaNum);
        $directions = $this->getBaZhaiDirections($kuaNum);

        return [
            // [기존 MasterHubController 호환 키]
            'element_ko' => $kuaData['element_ko'],
            'element_key' => $kuaData['element_key'],
            'core_tools' => [
                'kua' => [
                    'number' => $kuaNum,
                    'group' => $kuaGroup,
                ],
                'sleeping' => $directions['yan_nian'].'쪽',
                'diagnosis_title' => $kuaData['element_ko'].'의 기운을 다스리는 공간',
                'diagnosis_content' => '귀하는 타고난 <b>'.$kuaData['element_ko'].'</b>의 에너지를 가지고 있습니다. '.
                                       '현재 가장 필요한 기운은 '.$directions['sheng_chi'].'쪽 방향에서 들어오는 생기(生氣)입니다.',
            ],

            // [고도화된 본명괘 정보 - J047/J048 luck_code 직접 호환]
            'kua_info' => [
                'number' => $kuaNum,
                'group' => $kuaGroup,
                'trigram_ko' => $kuaData['trigram_ko'],     // '건', '곤', '감', '이'... (luck_code 매칭용)
                'trigram_hanja' => $kuaData['trigram_hanja'],  // '乾', '坤', '坎', '離'...
                'trigram_symbol' => $kuaData['trigram_symbol'], // '☰', '☷', '☵', '☲'... (UI 심볼용)
                'trigram' => $kuaData['trigram'],        // '감(坎)', '건(乾)'...
                'element_saju' => $kuaData['element_saju'],   // '수', '토', '목', '금', '화'
            ],

            // 4대 길방 / 4대 흉방
            'auspicious' => [
                'sheng_chi' => ['dir' => $directions['sheng_chi'].'쪽', 'title' => '생기(生氣)', 'desc' => '사업 번창과 재물운을 부르는 방향'],
                'tian_yi' => ['dir' => $directions['tian_yi'].'쪽',   'title' => '천의(天醫)', 'desc' => '건강 회복과 질병 치유를 돕는 방향'],
                'yan_nian' => ['dir' => $directions['yan_nian'].'쪽',  'title' => '연년(延年)', 'desc' => '부부 화합 및 숙면(침대 머리) 방향'],
                'fu_wei' => ['dir' => $directions['fu_wei'].'쪽',    'title' => '복위(伏位)', 'desc' => '집중력 강화 및 공부방(책상) 방향'],
            ],
            'inauspicious' => [
                'jue_ming' => ['dir' => $directions['jue_ming'].'쪽', 'title' => '절명(絶命)', 'desc' => '기운이 꺾이고 손재수가 있는 가장 흉한 방향'],
                'wu_gui' => ['dir' => $directions['wu_gui'].'쪽',   'title' => '오귀(五鬼)', 'desc' => '다툼과 구설을 부르는 방향'],
                'liu_sha' => ['dir' => $directions['liu_sha'].'쪽',  'title' => '육살(六煞)', 'desc' => '스캔들과 지연을 부르는 방향'],
                'huo_hai' => ['dir' => $directions['huo_hai'].'쪽',  'title' => '화해(禍害)', 'desc' => '피로와 잔병을 부르는 방향'],
            ],
            'interior_tips' => [
                'sleeping_head' => $directions['yan_nian'].'쪽 또는 '.$directions['tian_yi'].'쪽',
                'desk_facing' => $directions['fu_wei'].'쪽 또는 '.$directions['sheng_chi'].'쪽',
            ],
        ];
    }

    private function calculateKuaNumber(int $year, bool $isFemale): int
    {
        $sum = array_sum(str_split((string) $year));
        while ($sum > 9) {
            $sum = array_sum(str_split((string) $sum));
        }

        if ($year >= 2000) {
            $res = $isFemale ? (6 + $sum) : (9 - $sum);
        } else {
            $res = $isFemale ? (4 + $sum) : (11 - $sum);
        }

        while ($res > 9) {
            $res = array_sum(str_split((string) $res));
        }
        if ($res <= 0) {
            $res += 9;
        }

        if ($res === 5) {
            return $isFemale ? 8 : 2;
        }

        return $res;
    }

    /**
     * [고도화] 본명궁별 선천팔괘 메타데이터 풀 정의
     */
    private function getKuaMapping(int $num): array
    {
        $map = [
            1 => [
                'trigram_ko' => '감',
                'trigram_hanja' => '坎',
                'trigram_symbol' => '☵',
                'trigram' => '감(坎)',
                'element_ko' => '물',
                'element_saju' => '수',
                'element_key' => 'water',
            ],
            2 => [
                'trigram_ko' => '곤',
                'trigram_hanja' => '坤',
                'trigram_symbol' => '☷',
                'trigram' => '곤(坤)',
                'element_ko' => '흙',
                'element_saju' => '토',
                'element_key' => 'earth',
            ],
            3 => [
                'trigram_ko' => '진',
                'trigram_hanja' => '震',
                'trigram_symbol' => '☳',
                'trigram' => '진(震)',
                'element_ko' => '나무',
                'element_saju' => '목',
                'element_key' => 'wood',
            ],
            4 => [
                'trigram_ko' => '손',
                'trigram_hanja' => '巽',
                'trigram_symbol' => '☴',
                'trigram' => '손(巽)',
                'element_ko' => '나무',
                'element_saju' => '목',
                'element_key' => 'wood',
            ],
            6 => [
                'trigram_ko' => '건',
                'trigram_hanja' => '乾',
                'trigram_symbol' => '☰',
                'trigram' => '건(乾)',
                'element_ko' => '쇠',
                'element_saju' => '금',
                'element_key' => 'metal',
            ],
            7 => [
                'trigram_ko' => '태',
                'trigram_hanja' => '兌',
                'trigram_symbol' => '☱',
                'trigram' => '태(兌)',
                'element_ko' => '쇠',
                'element_saju' => '금',
                'element_key' => 'metal',
            ],
            8 => [
                'trigram_ko' => '간',
                'trigram_hanja' => '艮',
                'trigram_symbol' => '☶',
                'trigram' => '간(艮)',
                'element_ko' => '흙',
                'element_saju' => '토',
                'element_key' => 'earth',
            ],
            9 => [
                'trigram_ko' => '이',
                'trigram_hanja' => '離',
                'trigram_symbol' => '☲',
                'trigram' => '이(離)',
                'element_ko' => '불',
                'element_saju' => '화',
                'element_key' => 'fire',
            ],
        ];

        return $map[$num] ?? $map[1];
    }

    private function getBaZhaiDirections(int $kua): array
    {
        $map = [
            1 => ['sheng_chi' => '동남', 'tian_yi' => '동', 'yan_nian' => '남', 'fu_wei' => '북', 'huo_hai' => '서', 'liu_sha' => '서북', 'wu_gui' => '동북', 'jue_ming' => '남서'],
            2 => ['sheng_chi' => '동북', 'tian_yi' => '서', 'yan_nian' => '서북', 'fu_wei' => '남서', 'huo_hai' => '동', 'liu_sha' => '남', 'wu_gui' => '동남', 'jue_ming' => '북'],
            3 => ['sheng_chi' => '남', 'tian_yi' => '북', 'yan_nian' => '동남', 'fu_wei' => '동', 'huo_hai' => '남서', 'liu_sha' => '동북', 'wu_gui' => '서북', 'jue_ming' => '서'],
            4 => ['sheng_chi' => '북', 'tian_yi' => '남', 'yan_nian' => '동', 'fu_wei' => '동남', 'huo_hai' => '서북', 'liu_sha' => '서', 'wu_gui' => '남서', 'jue_ming' => '동북'],
            6 => ['sheng_chi' => '서', 'tian_yi' => '동북', 'yan_nian' => '남서', 'fu_wei' => '서북', 'huo_hai' => '동남', 'liu_sha' => '북', 'wu_gui' => '동', 'jue_ming' => '남'],
            7 => ['sheng_chi' => '서북', 'tian_yi' => '남서', 'yan_nian' => '동북', 'fu_wei' => '서', 'huo_hai' => '북', 'liu_sha' => '동남', 'wu_gui' => '남', 'jue_ming' => '동'],
            8 => ['sheng_chi' => '남서', 'tian_yi' => '서북', 'yan_nian' => '서', 'fu_wei' => '동북', 'huo_hai' => '남', 'liu_sha' => '동', 'wu_gui' => '북', 'jue_ming' => '동남'],
            9 => ['sheng_chi' => '동', 'tian_yi' => '동남', 'yan_nian' => '북', 'fu_wei' => '남', 'huo_hai' => '동북', 'liu_sha' => '남서', 'wu_gui' => '서', 'jue_ming' => '서북'],
        ];

        return $map[$kua] ?? $map[1];
    }
}
