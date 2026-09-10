<?php

namespace Pondol\Fortune\Traits\Jami;

/**
 * 자미두수의 대한(대운), 유년, 소한 및 두군 등
 * 시간의 흐름에 따른 운로를 계산하는 트레이트입니다.
 */
trait JamiFortuneTrait
{
    /**
     * 대한(大限)운 범위 계산 (10년 단위 대운)
     *
     * @param  array  $myung  명궁 배열
     * @param  string  $yangum  양남음녀 등의 구분
     * @param  string  $myung_guk  명국 (수2국, 목3국 등)
     * @return array 12궁별 대한 범위 배열
     */
    public function daehan(array $myung, string $yangum, string $myung_guk): array
    {
        $daehan = array_fill(0, 12, '');
        $gukMap = ['水2局' => 2, '木3局' => 3, '金4局' => 4, '土5局' => 5, '火6局' => 6];
        $start_age = $gukMap[$myung_guk] ?? 0;

        $myung_index = array_search('명', $myung, true);
        if ($myung_index === false) {
            return $daehan;
        }

        // 양남음녀는 시계방향(순행), 음남양녀는 시계반대방향(역행)
        $isSunhaeng = in_array($yangum, ['양남', '음녀']);

        for ($i = 0; $i < 12; $i++) {
            $offset = $isSunhaeng ? $i : -$i;
            $currentIndex = ($myung_index + $offset + 12) % 12;

            $current_start_age = $start_age + ($i * 10);
            $current_end_age = $current_start_age + 9;

            if ($current_start_age < 120) {
                $daehan[$currentIndex] = $current_start_age.'~'.$current_end_age;
            }
        }

        return $daehan;
    }

    /**
     * 현재 나이에 해당하는 대한(大限)의 궁 간지를 찾습니다.
     */
    public function current_daehan(array $daehan, array $gabja, string $yangum, int $current_age): ?string
    {
        foreach ($daehan as $k => $v) {
            if ($v) {
                $range = explode('~', $v);
                if ($current_age >= (int) $range[0] && $current_age <= (int) $range[1]) {
                    return $gabja[$k];
                }
            }
        }

        return null;
    }

    /**
     * 궁별 소한(小限) 및 나이 배치
     */
    protected function ages(int $current_age, string $umyear_e): array
    {
        $ages = array_fill(0, 12, null);
        $start = match ($umyear_e) {
            '子' => 10, '丑' => 11, '寅' => 0, '卯' => 1,
            '辰' => 2,  '巳' => 3,  '午' => 4,  '未' => 5,
            '申' => 6,  '酉' => 7,  '戌' => 8,  '亥' => 9,
            default => 0
        };

        // 1세씩 증가하며 12년을 배치합니다.
        for ($i = 0; $i < 12; $i++) {
            $index = ($start + $i) % 12;
            $ages[$index] = $current_age + $i;
        }

        return $ages;
    }

    /**
     * 유년궁(流年宮) 배치 (유명, 유부, 유복 등 12개)
     */
    protected function youyeon(string $umyear_e, array $gabja): array
    {
        $youArr = ['流命', '流父', '流福', '流田', '流官', '流奴', '流遷', '流疾', '流財', '流子', '流夫', '流형'];
        $you = array_fill(0, 12, null);

        // 지지별 시작 위치 오프셋
        $start_map = [
            '寅' => 0, '卯' => 1, '辰' => 2, '巳' => 3, '午' => 4, '未' => 5,
            '申' => 6, '酉' => 7, '戌' => 8, '亥' => 9, '子' => 10, '丑' => 11,
        ];

        $start_idx = $start_map[$umyear_e] ?? 0;

        for ($i = 0; $i < 12; $i++) {
            $pos = ($start_idx + $i) % 12;
            $you[$pos] = $youArr[$i];
        }

        return $you;
    }

    /**
     * 유월 운세를 보기 위한 두군(斗君)의 월 기준지 산출
     */
    protected function dugunWol(string $you_umyear_e, string $lunar_month): string
    {
        // 12지신 순서 (역순 계산용)
        $jiOrder = ['寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥', '子', '丑'];
        $yearIdx = array_search($you_umyear_e, $jiOrder);
        $monthIdx = (int) $lunar_month - 1;

        // 공식: 유년지 위치에서 생월만큼 역행
        $targetIdx = ($yearIdx - $monthIdx + 12) % 12;

        return $jiOrder[$targetIdx];
    }

    /**
     * 최종 두군(斗君) 위치 결정
     */
    protected function dugun(string $dugun_wol, string $hour_e): array
    {
        $dugun = array_fill(0, 12, null);
        $jiOrder = ['寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥', '子', '丑'];

        $wolIdx = array_search($dugun_wol, $jiOrder);
        $hourIdx = match ($hour_e) {
            '子' => 10, '丑' => 11, '寅' => 0, '卯' => 1, '辰' => 2, '巳' => 3,
            '午' => 4, '未' => 5, '申' => 6, '酉' => 7, '戌' => 8, '亥' => 9, default => 0
        };

        // 공식: 두군월 위치에서 생시만큼 순행
        // (원 로직의 복잡한 if문을 수식화하여 하위 호환성 유지)
        $pos = ($wolIdx + ($hourIdx >= 0 ? $hourIdx : 0)) % 12;
        $dugun[$pos] = '斗君';

        return $dugun;
    }
}
