<?php

namespace Pondol\Fortune\Services\Western\Numerology;

class Numerology
{
    private const ARCHETYPES = [
        1 => ['archetype' => '개척자 (The Leader)', 'traits' => ['독립심', '창의력', '추진력', '개척정신'],
            'desc' => '남들이 가지 않은 길을 여는 타고난 리더입니다. 강한 독립심과 독창적인 추진력으로 세상을 이끕니다.'],
        2 => ['archetype' => '조정자 (The Diplomat)', 'traits' => ['협력', '평화', '직관', '세심함'],
            'desc' => '사람과 사람 사이를 잇는 조화와 평화의 중재자입니다. 뛰어난 공감 능력과 섬세한 감수성을 지녔습니다.'],
        3 => ['archetype' => '표현자 (The Creator)', 'traits' => ['예술성', '낙천주의', '사교성', '영감'],
            'desc' => '세상에 기쁨과 영감을 전파하는 예술가입니다. 밝고 매력적인 화술과 창의적인 표현력을 발휘합니다.'],
        4 => ['archetype' => '건축가 (The Builder)', 'traits' => ['성실', '체계', '인내', '신뢰'],
            'desc' => '견고한 기반을 쌓아 올리는 신뢰의 표상입니다. 흔들림 없는 끈기와 질서정연한 실천력으로 대업을 이룹니다.'],
        5 => ['archetype' => '모험가 (The Free Spirit)', 'traits' => ['자유', '변화', '다재다능', '호기심'],
            'desc' => '경계를 넘어 자유를 탐구하는 모험가입니다. 뛰어난 적응력과 도전 정신으로 끊임없이 혁신을 만듭니다.'],
        6 => ['archetype' => '수호자 (The Caregiver)', 'traits' => ['책임감', '봉사', '치유', '가족애'],
            'desc' => '사랑과 책임감으로 공동체를 품는 수호자입니다. 따뜻한 치유와 헌신으로 주변을 평화롭게 지킵니다.'],
        7 => ['archetype' => '탐구자 (The Seeker)', 'traits' => ['지혜', '분석력', '영성', '통찰력'],
            'desc' => '우주와 인간사의 본질을 파고드는 철학자이자 연구자입니다. 깊은 고독 속에서 찬란한 진리를 밝혀냅니다.'],
        8 => ['archetype' => '지배자 (The Powerhouse)', 'traits' => ['권위', '물질적 성취', '비즈니스', '결단력'],
            'desc' => '물질적 풍요와 권위를 쟁취하는 타고난 경영자입니다. 대범한 배포와 현실적인 통찰력으로 성공을 거머쥡니다.'],
        9 => ['archetype' => '박애주의자 (The Humanitarian)', 'traits' => ['자비', '포용', '완성', '인류애'],
            'desc' => '조건 없는 사랑으로 세상을 품는 성숙한 영혼입니다. 지혜와 자비심으로 인류를 이끄는 큰 스승의 면모를 갖췄습니다.'],
        11 => ['archetype' => '선각자 (The Illuminator - 마스터)', 'traits' => ['영적 직관', '선각자', '영감', '초감각'],
            'desc' => '하늘의 영감을 세상에 전달하는 영적 메신저입니다. 직관과 카리스마로 사람들의 영혼을 일깨웁니다.'],
        22 => ['archetype' => '마스터 빌더 (The Master Builder - 마스터)', 'traits' => ['거대한 이상', '현실화', '세계적 위업'],
            'desc' => '원대한 이상을 현실 세계에 구체적으로 건축해 내는 거인입니다. 역사에 남을 위업을 달성합니다.'],
    ];

    /**
     * 생년월일 기준 수비학 종합 분석
     */
    public function analyze(string $birthYmd, ?string $englishName = null, ?int $targetYear = null): NumerologyResult
    {
        $cleanYmd = preg_replace('/[^0-9]/', '', $birthYmd);
        $year = (int) substr($cleanYmd, 0, 4);
        $month = (int) substr($cleanYmd, 4, 2);
        $day = (int) substr($cleanYmd, 6, 2);

        // 1. Life Path Number (생명수)
        $lifePath = $this->calculateLifePath($year, $month, $day);

        // 2. Birthday Number (생일수: 1~31 -> 1자리)
        $birthdayNum = $this->reduceNumber($day);

        // 3. Attitude Number (태도수: 월 + 일)
        $attitudeNum = $this->reduceNumber($month + $day);

        // 4. Personal Year (올해의 개인 주기: 대상년도 + 월 + 일)
        $curYear = $targetYear ?: (int) date('Y');
        $personalYear = $this->reduceNumber($curYear + $month + $day);

        // 5. 영문 이름 분석 (Destiny & Soul Urge)
        $destinyNum = null;
        $soulUrgeNum = null;
        if ($englishName) {
            $destinyNum = $this->calculateDestinyNumber($englishName);
            $soulUrgeNum = $this->calculateSoulUrgeNumber($englishName);
        }

        $meta = self::ARCHETYPES[$lifePath] ?? self::ARCHETYPES[1];

        return new NumerologyResult([
            'life_path_number' => $lifePath,
            'birthday_number' => $birthdayNum,
            'attitude_number' => $attitudeNum,
            'destiny_number' => $destinyNum,
            'soul_urge_number' => $soulUrgeNum,
            'personal_year' => $personalYear,
            'archetype' => $meta['archetype'],
            'traits' => $meta['traits'],
            'description' => $meta['desc'],
        ]);
    }

    private function calculateLifePath(int $year, int $month, int $day): int
    {
        $y = $this->reduceNumber($year);
        $m = $this->reduceNumber($month);
        $d = $this->reduceNumber($day);

        $sum = $y + $m + $d;

        // 마스터 넘버(11, 22) 보존 판별
        if ($sum === 11 || $sum === 22) {
            return $sum;
        }

        return $this->reduceNumber($sum);
    }

    private function reduceNumber(int $num): int
    {
        while ($num > 9) {
            if ($num === 11 || $num === 22 || $num === 33) {
                break;
            }
            $num = array_sum(str_split((string) $num));
        }

        return $num;
    }

    private function calculateDestinyNumber(string $name): int
    {
        $map = [
            'A' => 1, 'J' => 1, 'S' => 1, 'B' => 2, 'K' => 2, 'T' => 2,
            'C' => 3, 'L' => 3, 'U' => 3, 'D' => 4, 'M' => 4, 'V' => 4,
            'E' => 5, 'N' => 5, 'W' => 5, 'F' => 6, 'O' => 6, 'X' => 6,
            'G' => 7, 'P' => 7, 'Y' => 7, 'H' => 8, 'Q' => 8, 'Z' => 8,
            'I' => 9, 'R' => 9,
        ];

        $letters = str_split(strtoupper(preg_replace('/[^A-Za-z]/', '', $name)));
        $sum = 0;
        foreach ($letters as $char) {
            $sum += $map[$char] ?? 0;
        }

        return $this->reduceNumber($sum);
    }

    private function calculateSoulUrgeNumber(string $name): int
    {
        $vowelMap = ['A' => 1, 'E' => 5, 'I' => 9, 'O' => 6, 'U' => 3];
        $letters = str_split(strtoupper(preg_replace('/[^A-Za-z]/', '', $name)));
        $sum = 0;
        foreach ($letters as $char) {
            if (isset($vowelMap[$char])) {
                $sum += $vowelMap[$char];
            }
        }

        return $this->reduceNumber($sum);
    }
}
