<?php
namespace Pondol\Fortune\Services;
use InvalidArgumentException;


class NameService
{
    /**
     * 한글 초성을 오행으로 변환하는 맵.
     * @var array
     */
    private static $choseongToOhaengMap = [
        'ㄱ' => '목', 'ㅋ' => '목', // 木 (나무)
        'ㄴ' => '화', 'ㄷ' => '화', 'ㄹ' => '화', 'ㅌ' => '화', // 火 (불)
        'ㅇ' => '토', 'ㅎ' => '토', // 土 (흙)
        'ㅅ' => '금', 'ㅈ' => '금', 'ㅊ' => '금', // 金 (쇠)
        'ㅁ' => '수', 'ㅂ' => '수', 'ㅍ' => '수', // 水 (물)
    ];

    /**
     * 주어진 한글 문자에서 초성을 추출합니다.
     * @param string $char
     * @return string|null
     */
    public function getChoseong(string $char): ?string
    {
        // 초성 리스트
        $choseongArr = ['ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅃ', 'ㅅ', 'ㅆ', 'ㅇ', 'ㅈ', 'ㅉ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ'];
        
        // UTF-8 문자의 유니코드 코드포인트 얻기
        $unicode = mb_ord($char, 'UTF-8');

        // 한글 음절 범위 (가 ~ 힣) 인지 확인
        if ($unicode >= 0xAC00 && $unicode <= 0xD7A3) {
            $syllableIndex = $unicode - 0xAC00;
            $choseongIndex = floor($syllableIndex / 588);
            return $choseongArr[$choseongIndex];
        }

        // 한글 음절이 아니면 null 반환
        return null;
    }

    /**
     * 주어진 한글 문자에서 음령오행을 구합니다.
     * @param string $char
     * @return string|null
     */
    public function getEumnyeongOhaeng(string $char): ?string
    {
        $choseong = $this->getChoseong($char);

        if ($choseong && isset(self::$choseongToOhaengMap[$choseong])) {
            return self::$choseongToOhaengMap[$choseong];
        }

        return null;
    }

   
    /**
     * 이름 구조 타입(예: '12')을 결정합니다.
     */
    public function determineNameType(array $input): string
    {
        $type = (isset($input['f2'])) ? '2' : '1';
        $type .= (isset($input['g3'])) ? '3' : ((isset($input['g2'])) ? '2' : '1');
        return $type;
    }

    /**
     * 4격 전체를 계산하여 배열로 반환합니다.
     */
    public function calculateAllGueks(int $hanjano1, int $nameStroke1, int $nameStroke2): array
    {
        $early = $hanjano1 + $nameStroke1;
        $first = $nameStroke1 + $nameStroke2;
        $last = $hanjano1 + $nameStroke1 + $nameStroke2;
        $middle = abs($last - $early);

        return compact('early', 'first', 'middle', 'last');
    }
    
    /**
     * 길흉(吉凶) 등급에 따라 점수와 한글 등급명을 반환합니다.
     */
    public function getScoreByRating(string $rating): array
    {
        switch ($rating) {
            case '吉':   return ['jumsu' => 100, 'title' => '上格'];
            case '中吉': return ['jumsu' => 80,  'title' => '中上格'];
            case '中凶': return ['jumsu' => 60,  'title' => '中下格'];
            case '凶':   return ['jumsu' => 40,  'title' => '下格'];
            default:   return ['jumsu' => 0,   'title' => ''];
        }
    }
    
    /**
     * 획수(수리오행)에 따른 오행 문자열을 반환합니다.
     */
    public function getOhaengByStrokes(int $strokes): string
    {
        $lastDigit = $strokes % 10;
        if ($lastDigit == 0) $lastDigit = 10;
        
        if ($lastDigit <= 2) return '木';
        if ($lastDigit <= 4) return '火';
        if ($lastDigit <= 6) return '土';
        if ($lastDigit <= 8) return '金';
        return '水';
    }

    /**
     * 신강/신약 결과에 따라 보완할 오행(용신) 후보군을 우선순위 배열로 반환합니다.
     */
    public function findYongsin(object $strengthResult, $saju): array
    {
        $generationCycle = ['木'=>'水', '火'=>'木', '土'=>'火', '金'=>'土', '水'=>'金'];
        $overcomingCycle = ['木'=>'金', '火'=>'水', '土'=>'木', '金'=>'火', '水'=>'土'];
        $expressionCycle = array_flip($generationCycle);
        
        $dayMaster = $strengthResult->day_master;
        $candidates = [];

        if ($strengthResult->result === '신강') {
            $candidates[$overcomingCycle[$dayMaster]] = 100;
            $candidates[$expressionCycle[$dayMaster]] = 90;
            $candidates[array_search($overcomingCycle[$dayMaster], $generationCycle, true)] = 80;
        } else {
            $candidates[$generationCycle[$dayMaster]] = 100;
            $candidates[$dayMaster] = 90;
        }

        $allOhaengScores = (new SinyakSingang())->withSaju($saju)->getAllOhaengScores();
        foreach($allOhaengScores as $element => $power) {
            if ($power > 40 && isset($candidates[$element])) {
                $candidates[$element] -= 30;
            }
        }
        arsort($candidates);
        return array_slice(array_keys($candidates), 0, 2);
    }
    
    /**
     * 주어진 조건에 맞는 길한 이름 획수 조합을 필터링합니다.
     */
    public function filterGoodStrokes($ohengs, $gender, $hanjano1): array
    {
        $oheng2 = mb_substr($ohengs, 1, 1);
        $oheng3 = mb_substr($ohengs, 2, 1);
        
        $suri_no = ($gender === 'M')
            ? [1,3,5,6,7,8,11,13,15,16,18,21,23,24,25,29,31,32,33,35,37,39,41,45,47,48,52,57,61,63,65,67,68,81]
            : [1,3,5,6,7,8,11,13,15,16,17,18,24,25,29,31,35,37,38,41,45,47,48,52,57,58,61,63,65,67,68,81];

        $result = [];
        for ($j = 1; $j < 30; $j++) {
            if ($this->getOhaengByStrokes($j) !== $oheng2) continue;
            for ($k = 1; $k < 30; $k++) {
                if ($this->getOhaengByStrokes($k) !== $oheng3) continue;

                $gueks = $this->calculateAllGueks($hanjano1, $j, $k);
                
                $isAllSuriGood = true;
                foreach($gueks as $guekVal){
                    if(!in_array($this->correctMod(81, $guekVal), $suri_no)){
                        $isAllSuriGood = false;
                        break;
                    }
                }
                if ($isAllSuriGood) {
                     $result[] = ['first' => $j, 'second' => $k];
                }
            }
        }
        return $result;
    }
    
    /**
     * 81수리 계산을 위해 나머지를 보정하는 함수.
     */
    public function correctMod(int $mod, int $value): int
    {
        if ($value <= 0) return 1;
        $result = $value % $mod;
        return ($result === 0) ? $mod : $result;
    }
}