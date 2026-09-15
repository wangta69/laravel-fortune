<?php

namespace Pondol\Fortune\Services\Western\Zodiac;

use InvalidArgumentException;

class Zodiac
{
    private const DEFINITIONS = [
        1 => [
            'id' => 1, 'name_ko' => '양자리', 'name_en' => 'Aries', 'symbol' => '♈',
            'start_date' => '03-21', 'end_date' => '04-19',
            'element_en' => 'Fire', 'element_ko' => '불', 'modality' => '활동궁', 'ruler' => '화성', 'polarity' => '양성',
            'keywords' => ['개척', '열정', '용기', '독립심', '추진력'], 'calendar_id' => 3,
        ],
        2 => [
            'id' => 2, 'name_ko' => '황소자리', 'name_en' => 'Taurus', 'symbol' => '♉',
            'start_date' => '04-20', 'end_date' => '05-20',
            'element_en' => 'Earth', 'element_ko' => '흙', 'modality' => '고정궁', 'ruler' => '금성', 'polarity' => '음성',
            'keywords' => ['안정', '인내', '신중', '물질적 풍요', '신뢰'], 'calendar_id' => 4,
        ],
        3 => [
            'id' => 3, 'name_ko' => '쌍둥이자리', 'name_en' => 'Gemini', 'symbol' => '♊',
            'start_date' => '05-21', 'end_date' => '06-21',
            'element_en' => 'Air', 'element_ko' => '공기', 'modality' => '변통궁', 'ruler' => '수성', 'polarity' => '양성',
            'keywords' => ['소통', '호기심', '다재다능', '적응력', '재치'], 'calendar_id' => 5,
        ],
        4 => [
            'id' => 4, 'name_ko' => '게자리', 'name_en' => 'Cancer', 'symbol' => '♋',
            'start_date' => '06-22', 'end_date' => '07-22',
            'element_en' => 'Water', 'element_ko' => '물', 'modality' => '활동궁', 'ruler' => '달', 'polarity' => '음성',
            'keywords' => ['모성애', '보호', '감수성', '가족애', '공감'], 'calendar_id' => 6,
        ],
        5 => [
            'id' => 5, 'name_ko' => '사자자리', 'name_en' => 'Leo', 'symbol' => '♌',
            'start_date' => '07-23', 'end_date' => '08-22',
            'element_en' => 'Fire', 'element_ko' => '불', 'modality' => '고정궁', 'ruler' => '태양', 'polarity' => '양성',
            'keywords' => ['자신감', '리더십', '관대함', '창조성', '명예'], 'calendar_id' => 7,
        ],
        6 => [
            'id' => 6, 'name_ko' => '처녀자리', 'name_en' => 'Virgo', 'symbol' => '♍',
            'start_date' => '08-23', 'end_date' => '09-22',
            'element_en' => 'Earth', 'element_ko' => '흙', 'modality' => '변통궁', 'ruler' => '수성', 'polarity' => '음성',
            'keywords' => ['완벽주의', '분석력', '봉사', '성실', '실용성'], 'calendar_id' => 8,
        ],
        7 => [
            'id' => 7, 'name_ko' => '천칭자리', 'name_en' => 'Libra', 'symbol' => '♎',
            'start_date' => '09-23', 'end_date' => '10-22',
            'element_en' => 'Air', 'element_ko' => '공기', 'modality' => '활동궁', 'ruler' => '금성', 'polarity' => '양성',
            'keywords' => ['균형', '조화', '심미안', '공정함', '사교성'], 'calendar_id' => 9,
        ],
        8 => [
            'id' => 8, 'name_ko' => '전갈자리', 'name_en' => 'Scorpio', 'symbol' => '♏',
            'start_date' => '10-23', 'end_date' => '11-22',
            'element_en' => 'Water', 'element_ko' => '물', 'modality' => '고정궁', 'ruler' => '명왕성', 'polarity' => '음성',
            'keywords' => ['통찰력', '집념', '변혁', '신비', '열정'], 'calendar_id' => 10,
        ],
        9 => [
            'id' => 9, 'name_ko' => '사수자리', 'name_en' => 'Sagittarius', 'symbol' => '♐',
            'start_date' => '11-23', 'end_date' => '12-24',
            'element_en' => 'Fire', 'element_ko' => '불', 'modality' => '변통궁', 'ruler' => '목성', 'polarity' => '양성',
            'keywords' => ['자유', '철학', '모험', '낙천성', '탐구'], 'calendar_id' => 11,
        ],
        10 => [
            'id' => 10, 'name_ko' => '염소자리', 'name_en' => 'Capricorn', 'symbol' => '♑',
            'start_date' => '12-25', 'end_date' => '01-19',
            'element_en' => 'Earth', 'element_ko' => '흙', 'modality' => '활동궁', 'ruler' => '토성', 'polarity' => '음성',
            'keywords' => ['책임감', '야망', '규율', '인내', '현실적 성취'], 'calendar_id' => 12,
        ],
        11 => [
            'id' => 11, 'name_ko' => '물병자리', 'name_en' => 'Aquarius', 'symbol' => '♒',
            'start_date' => '01-20', 'end_date' => '02-18',
            'element_en' => 'Air', 'element_ko' => '공기', 'modality' => '고정궁', 'ruler' => '천왕성', 'polarity' => '양성',
            'keywords' => ['독창성', '인도주의', '진보', '자유', '지적 탐구'], 'calendar_id' => 1,
        ],
        12 => [
            'id' => 12, 'name_ko' => '물고기자리', 'name_en' => 'Pisces', 'symbol' => '♓',
            'start_date' => '02-19', 'end_date' => '03-20',
            'element_en' => 'Water', 'element_ko' => '물', 'modality' => '변통궁', 'ruler' => '해왕성', 'polarity' => '음성',
            'keywords' => ['직관', '동정심', '예술성', '영성', '낭만'], 'calendar_id' => 2,
        ],
    ];

    public function find($monthOrYmd, ?int $day = null): ZodiacSign
    {
        if (is_string($monthOrYmd) && str_contains($monthOrYmd, '-')) {
            $parts = explode('-', $monthOrYmd);
            $month = (int) ($parts[1] ?? 1);
            $day = (int) ($parts[2] ?? 1);
        } else {
            $month = (int) $monthOrYmd;
            $day = (int) $day;
        }

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            throw new InvalidArgumentException("Invalid date: month {$month}, day {$day}");
        }

        $md = sprintf('%02d-%02d', $month, $day);

        $signId = match (true) {
            $md >= '03-21' && $md <= '04-19' => 1,
            $md >= '04-20' && $md <= '05-20' => 2,
            $md >= '05-21' && $md <= '06-21' => 3,
            $md >= '06-22' && $md <= '07-22' => 4,
            $md >= '07-23' && $md <= '08-22' => 5,
            $md >= '08-23' && $md <= '09-22' => 6,
            $md >= '09-23' && $md <= '10-22' => 7,
            $md >= '10-23' && $md <= '11-22' => 8,
            $md >= '11-23' && $md <= '12-24' => 9,
            $md >= '01-20' && $md <= '02-18' => 11,
            $md >= '02-19' && $md <= '03-20' => 12,
            default => 10,
        };

        return new ZodiacSign(self::DEFINITIONS[$signId]);
    }

    public function getById(int $id): ZodiacSign
    {
        if (! isset(self::DEFINITIONS[$id])) {
            throw new InvalidArgumentException("Invalid zodiac sign ID: {$id}");
        }

        return new ZodiacSign(self::DEFINITIONS[$id]);
    }

    public function getByName(string $nameKo): ?ZodiacSign
    {
        $nameKo = str_replace('궁수자리', '사수자리', trim($nameKo));

        foreach (self::DEFINITIONS as $def) {
            if ($def['name_ko'] === $nameKo) {
                return new ZodiacSign($def);
            }
        }

        return null;
    }

    /**
     * 두 별자리 간의 원소 기반 조화 점수 및 관계 분석 (0~100점)
     */
    public function getCompatibility(ZodiacSign $signA, ZodiacSign $signB): array
    {
        $elA = $signA->element_en;
        $elB = $signB->element_en;

        // 같은 원소: Trine (최고 궁합 95점)
        if ($elA === $elB) {
            $score = 95;
            $relation = '동일 원소 상합(Trine)으로 성향과 가치관이 깊이 일치합니다.';
        }
        // 불 + 공기, 흙 + 물: 상생 (90점)
        elseif (
            ($elA === 'Fire' && $elB === 'Air') || ($elA === 'Air' && $elB === 'Fire') ||
            ($elA === 'Earth' && $elB === 'Water') || ($elA === 'Water' && $elB === 'Earth')
        ) {
            $score = 90;
            $relation = '상생하는 원소의 조화로 서로의 부족한 점을 보완해 주는 이상적 관계입니다.';
        }
        // 대척점 (Opposition, 6궁 차이): 극과 극 (75점)
        elseif (abs($signA->id - $signB->id) === 6) {
            $score = 75;
            $relation = '대칭점에 위치하여 강한 매력을 느끼지만 성격 차이 조율이 요구됩니다.';
        }
        // 불/공기 vs 흙/물 (Square 등): 긴장 관계 (65점)
        else {
            $score = 65;
            $relation = '서로 다른 기질을 이해하고 배려하는 노력이 관계 유지의 핵심입니다.';
        }

        return [
            'score' => $score,
            'sign_a' => $signA,
            'sign_b' => $signB,
            'relation' => $relation,
        ];
    }

    /**
     * @return array<int, ZodiacSign>
     */
    public function all(): array
    {
        return array_map(fn ($def) => new ZodiacSign($def), self::DEFINITIONS);
    }
}
