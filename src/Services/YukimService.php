<?php
namespace Pondol\Fortune\Services;
use InvalidArgumentException;

/**
 * YukimService Class (Final Version with Comments)
 *
 * 이 클래스는 사주와 시간 정보를 바탕으로 다양한 방식의 육임 점단 결과를 제공합니다.
 * 모든 720과 데이터는 config/yukim720.php 파일에서 조회하는 것을 전제로 합니다.
 */
class YukimService
{
    // --- 1. 클래스 프로퍼티 (고정 데이터) ---
    /** @var array 12지지(地支) 한자 배열 */
    private array $e_labels = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];
    
    /** @var array 음력 월에 해당하는 월장(月將) 맵 */
    private array $woljangMap = [ 1 => '亥', 2 => '戌', 3 => '酉', 4 => '申', 5 => '未', 6 => '午', 7 => '巳', 8 => '辰', 9 => '卯', 10 => '寅', 11 => '丑', 12 => '子' ];

    /** @var array 차객법(差客法)에서 사용하는 점시 재조정 규칙 맵 */
    private array $chaekgeokMap = [ 
        '子' => ['巳', '子', '卯', '戌', '丑', '申', '亥', '午', '酉', '辰', '未', '寅'], 
        '丑' => ['戌', '丑', '申', '亥', '午', '酉', '辰', '未', '寅', '巳', '子', '卯'], 
        '寅' => ['未', '寅', '巳', '子', '卯', '戌', '丑', '申', '亥', '午', '酉', '辰'], 
        '卯' => ['子', '卯', '戌', '丑', '申', '亥', '午', '酉', '辰', '未', '寅', '巳'], 
        '辰' => ['酉', '辰', '未', '寅', '巳', '子', '卯', '戌', '丑', '申', '亥', '午'], 
        '巳' => ['寅', '巳', '子', '卯', '戌', '丑', '申', '亥', '午', '酉', '辰', '未'], 
        '午' => ['亥', '午', '酉', '辰', '未', '寅', '巳', '子', '卯', '戌', '丑', '申'], 
        '未' => ['辰', '未', '寅', '巳', '子', '卯', '戌', '丑', '申', '亥', '午', '酉'], 
        '申' => ['丑', '申', '亥', '午', '酉', '辰', '未', '寅', '巳', '子', '卯', '戌'], 
        '酉' => ['午', '酉', '辰', '未', '寅', '巳', '子', '卯', '戌', '丑', '申', '亥'], 
        '戌' => ['卯', '戌', '丑', '申', '亥', '午', '酉', '辰', '未', '寅', '巳', '子'], 
        '亥' => ['申', '亥', '午', '酉', '辰', '未', '寅', '巳', '子', '卯', '戌', '丑'] 
    ];

    private array $methodNames = [
        '720gwa' => '정통 720과',
        'bonmyeong' => '본명법',
        'jidu' => '지두법',
        'chaekgeok' => '차객법',
        'ilgangwa' => '일간과법'
    ];

    
    // --- 2. Public Methods (외부와의 유일한 통로) ---
    
    /**
     * 육임 점괘를 생성하는 통합 메인 메소드.
     * 외부에서 생성된 Saju 객체를 받아 처리합니다.
     *
     * @param string      $method       사용할 점법의 종류
     * @param object|null $today        점을 치는 기준 시점의 Saju 객체. null이면 현재 시간으로 자동 생성.
     * @param object|null $saju         'bonmyeong' 점법에 필수인 사용자의 Saju 객체
     * @return object|null
     * @throws InvalidArgumentException
     */
    public function getReading(string $method, ?object $today = null, ?object $saju = null): ?object
    {
        
        $yukimBoard = $this->createYukimBoard($today);
        if (!$yukimBoard) return null;

        $keys = null;
        $resultData = null;

        // 2. 요청된 점법에 따라 핵심 계산을 수행합니다.
        // 각 private 메소드는 이제 계산에 필요한 'keys'와 'resultData'만 반환합니다.
        switch ($method) {
            /**
             * [정통 720과]
             * 원리: 오늘의 '일진(日辰)'과 '국수(局數)'를 조합하여, 미리 계산된 720개의 결과 중 하나를 찾습니다.
             * 용도: 사과, 삼전, 신장 등 육임의 모든 구조를 확인하는 가장 정석적인 점법입니다.
             */
            case '720gwa':
                $calcResult = $this->calculate720Gwa($today, $yukimBoard);
                break;
            /**
             * [본명법(本命法)]
             * 원리: '나의 사주 년지(띠)'와 '오늘의 천지반/신장'을 비교하여 개인에게 미치는 영향을 분석합니다.
             * 용도: 보편적인 오늘의 운세가 '나'라는 개인에게 어떻게 작용하는지 심도있게 볼 때 사용합니다.
             */
            case 'bonmyeong':
                if (!$saju) throw new InvalidArgumentException('Saju object is required for bonmyeong method.');
                $calcResult = $this->calculateBonmyeong($saju, $yukimBoard);
                break;
            /**
             * [지두법(地頭法)]
             * 원리: 오늘의 '월장'과 '점시'만으로 '진하지지(辰下地支)'라는 핵심 키를 찾아냅니다.
             * 용도: '나'의 정보 없이, 오직 그 시간 자체가 가진 길흉을 통해 각 사안(J052 테이블)의 결과를 판단할 때 사용합니다.
             */
            case 'jidu':
                $calcResult = $this->calculateJidu($yukimBoard);
                break;
                /**
             * [차객법(差客法)]
             * 원리: '원래 점시'와 '난수(방문객 등)'를 조합하여 '재조정된 점시'를 만들고, 이를 기준으로 천반을 다시 세웁니다.
             * 용도: 예측 불가능한 변수(客)의 개입을 점사에 반영하는 고유한 점법입니다.
             */
            case 'chaekgeok':
                $calcResult = $this->calculateChaekgeok($today, $yukimBoard);
                break;
            /**
             * [일간과법(日干課法)]
             * 원리: '오늘의 일간(日干)'과 '천반 오궁(午宮)의 글자'를 조합하여 120개의 결과중 하나를 찾습니다.
             * 용도: '오늘의 주체(나)'가 '공적인 상황(오궁)'을 만났을 때의 결과를 판단하는 실용적인 점법입니다.
             */
            case 'ilgangwa':
                $calcResult = $this->calculateIlganGwa($yukimBoard);
                break;
            default:
                throw new InvalidArgumentException("Invalid Yukim method requested: {$method}");
        }

        $keys = $calcResult->keys;
        $resultData = $calcResult->result;
        return $this->formatStandardResponse($method, $yukimBoard, $keys, $resultData);
    }

    /**
     * 육임의 가장 기본적인 보드 정보(일진, 월장, 천반, 지반)만 계산하여 반환합니다.
     *
     * @param string|null $date 'YmdHi' 형식의 특정 날짜/시간. null이면 현재 시간.
     * @return object|null
     */
    public function getBasicBoardInfo(object $saju): ?object
    {
        return $this->createYukimBoard($saju);
    }


    // --- 3. Private Calculation Methods (핵심 점법 계산) ---
    
    /**
     * [720과 점법] 정통 육임 점괘를 config 파일에서 조회하여 생성합니다.
     */
    private function calculate720Gwa(object $today, object $board): ?object
    {
        $iljin = $today->get_h('day') . $today->get_e('day');
        $guk = $this->calculateGuksu($board->woljang, $board->jeomsi);
        $key = "{$iljin}-{$guk}";
        $yukimData = config("yukim720.{$key}");

        if (!$yukimData) return null;

        $keys = (object)['iljin' => $iljin, 'guk' => $guk];
        $result = $this->formatYukimData($yukimData, $today); // formatYukimData는 기존대로 사용

        return (object)['keys' => $keys, 'result' => $result];
    }
    
    /**
     * [본명법] 사용자의 사주(띠)를 기반으로 오늘의 상호작용을 분석합니다.
     */
    private function calculateBonmyeong(object $saju, object $board): object
    {
        $bonmyeong = $saju->get_e('year');
        $bonmyeongIndex = array_search($bonmyeong, $board->jiban);
        $cheonbanOnBonmyeong = ($bonmyeongIndex !== false) ? ($board->cheonban[$bonmyeongIndex] ?? null) : null;
        $sinjang = $this->findSinjang($cheonbanOnBonmyeong, $board->iljin_gan);

        $keys = (object)['bonmyeong_ji' => $bonmyeong, 'iljin_gan' => $board->iljin_gan];
        $result = (object)['cheonban_on_bonmyeong' => $cheonbanOnBonmyeong, 'sinjang' => $sinjang];

        return (object)['keys' => $keys, 'result' => $result];
    }
    
    /**
     * [지두법] 오늘의 시간 정보로 해설 DB를 조회할 핵심 키(진하지지)를 찾습니다.
     */
    private function calculateJidu(object $board): object
    {
        // 진하, 자하, 묘하는 컨트롤러에서 DB 조회를 위해 사용하는 '핵심 키'입니다.
        $keys = $this->getThreeHyeols($board->cheonban, $board->jiban);
        // 국수는 부가 정보이므로 '결과'에 담습니다.
        $result = (object)['guksu' => $this->calculateGuksu($board->woljang, $board->jeomsi)];

        return (object)['keys' => $keys, 'result' => $result];
    }

    /**
     * [차객법] 난수를 이용한 고유 점법으로, 특정 질문에 대한 해설을 DB에서 조회합니다.
     */
    private function calculateChaekgeok(object $today, object $board): ?object
    {
        $adjustedJeomsi = $this->getAdjustedJeomsiByChaekgeok($board->jeomsi);
        $cheonban = $this->createCheonban($board->woljang, $adjustedJeomsi);
        if (empty($cheonban)) return null;
        
        $key1 = $cheonban[6]; // 천반의 辰궁 위치
        $key2 = $today->get_h('day');
        
        $keys = (object)[
            'cheonban_jinha_key' => $cheonban[6], // 천반 '진궁(辰宮)'의 글자 (辰=4)
            'ilgan_key' => $board->iljin_gan
        ];

       // 결과로는 재조정된 정보들을 전달
        $result = (object)[
            'original_jeomsi' => $board->jeomsi,
            'adjusted_jeomsi' => $adjustedJeomsi,
            'adjusted_cheonban' => $cheonban
        ];

        return (object)['keys' => $keys, 'result' => $result];
    }

    /**
     * [일간과법(日干課法)] '일간'과 '천반 오궁' 조합으로 해설을 찾는 점법
     * (기존 본명법 메소드를 대체합니다)
     *
     * @param object $todaySaju 오늘의 Saju 객체
     * @return object
     */
    private function calculateIlganGwa(object $board): object
    {
       $keys = (object)[
            'cheonban_ogong_key' => $board->cheonban[6] ?? null, // 천반 '오궁'의 글자 (午=6)
            'ilgan_key' => $board->iljin_gan
        ];
        
        $result = null;

        return (object)['keys' => $keys, 'result' => $result];
    }

    // --- 4. Private Helper Methods (보조 로직) ---

    /**
     * [신규] 모든 계산 결과를 표준화된 응답 객체로 포장하는 헬퍼 메소드
     */
    private function formatStandardResponse(string $method, object $board, ?object $keys, $resultData): object
    {
        return (object)[
            'method'     => $method,
            'method_ko'  => $this->methodNames[$method] ?? $method,
            'board'      => $board,
            'keys'       => $keys,
            'result'     => $resultData,
        ];
    }
    
    private function createYukimBoard(object $today): ?object
    {
        $woljang = $this->getWoljang($today);
        $jeomsi = $today->get_e('hour');
        if(!$woljang || !$jeomsi) return null;

        $cheonban = $this->createCheonban($woljang, $jeomsi);

        return (object)[
            'iljin'     => $today->get_h('day') . $today->get_e('day'),
            'iljin_gan' => $today->get_h('day'), 
            'iljin_ji'  => $today->get_e('day'), 
            'woljang'   => $woljang, 
            'jeomsi'    => $jeomsi, 
            'jiban'     => $this->e_labels, 
            'cheonban'  => $cheonban 
        ];
    }

    /**
     * [공통 헬퍼] Saju 객체에서 음력 월을 기준으로 월장을 찾습니다.
     */
    private function getWoljang(object $saju): ?string
    {
        list($year, $month, $day) = explode('-', $saju->lunar);
        return $this->woljangMap[(int)$month] ?? null;
    }

    /**
     * [공통 헬퍼] 월장과 점시를 이용해 천반을 생성합니다.
     */
    private function createCheonban(?string $woljang, string $jeomsi): array
    {
        $cheonban = [];
        $woljangIndex = array_search($woljang, $this->e_labels);
        $jeomsiIndex = array_search($jeomsi, $this->e_labels);
        if ($woljangIndex === false || $jeomsiIndex === false) return [];
        for ($i = 0; $i < 12; $i++) {
            $cheonban[$jeomsiIndex] = $this->e_labels[$woljangIndex];
            $woljangIndex = ($woljangIndex + 1) % 12;
            $jeomsiIndex = ($jeomsiIndex + 1) % 12;
        }
        ksort($cheonban);
        return array_values($cheonban);
    }

    /**
     * [공통 헬퍼] 월장과 점시로 국수(局數)를 계산합니다.
     */
    private function calculateGuksu(string $woljang, string $jeomsi): int
    {
        $woljangNum = array_search($woljang, $this->e_labels) + 1;
        $jeomsiNum = array_search($jeomsi, $this->e_labels) + 1;
        $guksu = $jeomsiNum - $woljangNum + 1;
        return $guksu <= 0 ? $guksu + 12 : $guksu;
    }


    /**
     * 주어진 천지반에서 진하, 자하, 묘하를 찾아 반환합니다.
     *
     * @param array $cheonban 계산된 천반 배열
     * @param array $jiban 고정된 지반 배열
     * @return object
     */
    public function getThreeHyeols(array $cheonban, array $jiban): object
    {
        // 천반의 '글자'를 key로, '인덱스'를 value로 하는 맵을 만들어 검색 속도를 높입니다.
        $cheonbanMap = array_flip($cheonban);

        // '자', '묘', '진'의 천반 인덱스를 찾습니다.
        $ziIndex = $cheonbanMap['子'] ?? null;
        $maoIndex = $cheonbanMap['卯'] ?? null;
        $chenIndex = $cheonbanMap['辰'] ?? null;

        return (object)[
            'jaha' => ($ziIndex !== null) ? $jiban[$ziIndex] : '오류',   // 자하(子下)
            'myoha' => ($maoIndex !== null) ? $jiban[$maoIndex] : '오류', // 묘하(卯下)
            'jinha' => ($chenIndex !== null) ? $jiban[$chenIndex] : '오류', // 진하(辰下)
        ];
    }


    /**
     * [본명법 헬퍼] 12신장을 찾습니다.
     */
    private function findSinjang(?string $cheonbanJi, ?string $ilgan): string
    {
        if(!$cheonbanJi || !$ilgan) return '정보 없음';
        if (in_array($ilgan, ['甲', '戊', '庚'])) {
            $gwesins = ['貴人', '螣蛇', '朱雀', '六合', '勾陳', '靑龍', '天空', '白虎', '太常', '玄武', '太陰', '天后'];
        } else {
            $gwesins = ['貴人', '天后', '太陰', '玄武', '太常', '白虎', '天空', '靑龍', '勾陳', '六合', '朱雀', '螣蛇'];
        }
        $index = array_search($cheonbanJi, $this->e_labels);
        return ($index !== false) ? $gwesins[$index] : '정보 없음';
    }


    /**
     * [차객법 헬퍼] 원래 점시와 난수를 이용해 새로운 점시를 계산합니다.
     */
    private function getAdjustedJeomsiByChaekgeok(string $originalJeomsi): string
    {
        $randomIndex = random_int(0, 11);
        return $this->chaekgeokMap[$originalJeomsi][$randomIndex] ?? $originalJeomsi;
    }


    /**
     * [720과 헬퍼] config 배열 데이터를 최종 결과 객체로 변환합니다.
     */
    private function formatYukimData(array $data, object $today): object
    {
        $jeomsi = $today->get_e('hour');
        $isDayTime = in_array($jeomsi, ['卯','辰','巳','午','未','申']);
        
        return (object)[ 'daily_stem_branch' => $data['daily_stem_branch'], 'palace_num' => $data['palace_num'], 'ke1_heaven' => $data['ke1_heaven'], 'ke1_earth' => $data['ke1_earth'], 'ke2_heaven' => $data['ke2_heaven'], 'ke2_earth' => $data['ke2_earth'], 'ke3_heaven' => $data['ke3_heaven'], 'ke3_earth' => $data['ke3_earth'], 'ke4_heaven' => $data['ke4_heaven'], 'ke4_earth' => $data['ke4_earth'], 'chuan1_heaven' => $data['chuan1_heaven'], 'chuan1_earth' => $data['chuan1_earth'], 'chuan2_heaven' => $data['chuan2_heaven'], 'chuan2_earth' => $data['chuan2_earth'], 'chuan3_heaven' => $data['chuan3_heaven'], 'chuan3_earth' => $data['chuan3_earth'], 'day_noble' => $data['day_noble'], 'night_noble' => $data['night_noble'], 'day_noble_direction' => $data['day_noble_direction'], 'night_noble_direction' => $data['night_noble_direction'], 'void_branch_1' => $data['void_branch_1'], 'void_branch_2' => $data['void_branch_2'], 'gwa_type_hanja' => $data['gwa_type_hanja'], 'gwa_type_alias' => $data['gwa_type_alias'], 'day_sequence' => $data['day_sequence'], 'night_sequence' => $data['night_sequence'], 'is_day' => $isDayTime, 'current_noble' => $isDayTime ? $data['day_noble'] : $data['night_noble'] ];
    }
    
}
