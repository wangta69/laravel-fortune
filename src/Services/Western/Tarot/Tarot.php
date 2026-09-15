<?php

namespace Pondol\Fortune\Services\Western\Tarot;

use InvalidArgumentException;

class Tarot
{
    /**
     * 메이저 아르카나 22장 마스터 정의 (0: 바보 ~ 21: 세계)
     */
    private const MAJOR_CARDS = [
        0 => ['id' => 0, 'name_ko' => '바보', 'name_en' => 'The Fool', 'arcana' => 'major', 'suit' => null, 'number' => 0,
            'keywords_upright' => ['새로운 시작', '순수함', '자유', '모험', '잠재력'],
            'keywords_reversed' => ['경솔함', '무모함', '위험 감수', '어리석음', '방황'],
            'meaning_upright' => '순수한 마음으로 새로운 여정을 시작할 최적의 타이밍입니다. 두려움 없이 첫발을 내딛으십시오.',
            'meaning_reversed' => '준비 없는 무모한 뛰어듦은 위험을 초래합니다. 현실적인 안전장치를 먼저 확보하십시오.'],
        1 => ['id' => 1, 'name_ko' => '마법사', 'name_en' => 'The Magician', 'arcana' => 'major', 'suit' => null, 'number' => 1,
            'keywords_upright' => ['창조력', '실행력', '다재다능', '자신감', '새로운 기회'],
            'keywords_reversed' => ['기만', '재능 낭비', '속임수', '우유부단', '미숙함'],
            'meaning_upright' => '모든 자원과 도구가 당신의 손안에 갖추어져 있습니다. 의지를 가지고 능력을 펼치십시오.',
            'meaning_reversed' => '재주만 믿고 잔꾀를 부리다 신뢰를 잃을 수 있습니다. 진실된 실천이 요구됩니다.'],
        2 => ['id' => 2, 'name_ko' => '여사제', 'name_en' => 'The High Priestess', 'arcana' => 'major', 'suit' => null, 'number' => 2,
            'keywords_upright' => ['직관', '지혜', '비밀', '통찰력', '고요함'],
            'keywords_reversed' => ['비밀 누설', '표면적 판단', '감정 억압', '독단'],
            'meaning_upright' => '내면의 직관과 침묵의 목소리에 귀를 기울이십시오. 눈에 보이지 않는 진실이 해답입니다.',
            'meaning_reversed' => '선입견에 사로잡혀 상황을 왜곡하지 마십시오. 비밀이 드러나 곤경에 처할 수 있습니다.'],
        3 => ['id' => 3, 'name_ko' => '여황제', 'name_en' => 'The Empress', 'arcana' => 'major', 'suit' => null, 'number' => 3,
            'keywords_upright' => ['풍요', '다산', '모성애', '자연', '창의적 결실'],
            'keywords_reversed' => ['낭비', '과보호', '의존성', '정체', '창작의 벽'],
            'meaning_upright' => '풍성한 물질적, 감정적 수확의 시기입니다. 당신이 가꾼 프로젝트가 만개합니다.',
            'meaning_reversed' => '나태함과 과소비로 기반을 잃지 않도록 주의하십시오. 자기 절제가 필요한 때입니다.'],
        4 => ['id' => 4, 'name_ko' => '황제', 'name_en' => 'The Emperor', 'arcana' => 'major', 'suit' => null, 'number' => 4,
            'keywords_upright' => ['권위', '체계', '통제력', '안정', '리더십'],
            'keywords_reversed' => ['폭정', '독재', '경직됨', '통제력 상실', '고집'],
            'meaning_upright' => '규율과 단호한 결단력으로 조직과 상황을 장악할 때입니다. 책임감을 가지십시오.',
            'meaning_reversed' => '지나친 억압과 고집은 반발을 부릅니다. 유연한 리더십으로 타인을 포용하십시오.'],
        5 => ['id' => 5, 'name_ko' => '교황', 'name_en' => 'The Hierophant', 'arcana' => 'major', 'suit' => null, 'number' => 5,
            'keywords_upright' => ['전통', '자문', '도덕', '영적 지도', '신뢰'],
            'keywords_reversed' => ['교조주의', '낡은 관습', '부당한 간섭', '반항'],
            'meaning_upright' => '믿을 만한 멘토나 전문가의 조언을 따르십시오. 전통과 규범에 따를 때 안전합니다.',
            'meaning_reversed' => '시대에 뒤떨어진 관습을 맹목적으로 따르지 마십시오. 본인만의 주관이 필요합니다.'],
        6 => ['id' => 6, 'name_ko' => '연인', 'name_en' => 'The Lovers', 'arcana' => 'major', 'suit' => null, 'number' => 6,
            'keywords_upright' => ['사랑', '조화', '선택', '가치관 일치', '파트너십'],
            'keywords_reversed' => ['불화', '갈등', '잘못된 선택', '유혹', '관계 결렬'],
            'meaning_upright' => '깊은 유대와 사랑, 중요한 인생의 올바른 선택 앞에 섰습니다. 마음의 소리를 따르십시오.',
            'meaning_reversed' => '감정과 유혹에 휘둘려 잘못된 결정을 내릴 수 있습니다. 이성적인 점검이 필요합니다.'],
        7 => ['id' => 7, 'name_ko' => '전차', 'name_en' => 'The Chariot', 'arcana' => 'major', 'suit' => null, 'number' => 7,
            'keywords_upright' => ['승리', '의지력', '추진력', '극복', '단호한 전진'],
            'keywords_reversed' => ['방향 상실', '통제 불능', '좌절', '공격성'],
            'meaning_upright' => '장애물을 뚫고 강력하게 돌진하여 승리를 쟁취할 때입니다. 흔들림 없이 나아가십시오.',
            'meaning_reversed' => '폭주하는 에너지를 통제하지 못하면 사고가 납니다. 속도를 줄이고 방향을 재정비하십시오.'],
        8 => ['id' => 8, 'name_ko' => '힘', 'name_en' => 'Strength', 'arcana' => 'major', 'suit' => null, 'number' => 8,
            'keywords_upright' => ['내면의 용기', '인내', '자비', '자기 통제', '유연한 힘'],
            'keywords_reversed' => ['자신감 결여', '무력감', '분노 폭발', '두려움'],
            'meaning_upright' => '거친 상황을 완력이 아닌 부드러움과 지혜로운 인내로 다스려 승리하게 됩니다.',
            'meaning_reversed' => '내면의 불안과 의심이 행동을 주저하게 만듭니다. 본연의 용기를 되찾으십시오.'],
        9 => ['id' => 9, 'name_ko' => '은둔자', 'name_en' => 'The Hermit', 'arcana' => 'major', 'suit' => null, 'number' => 9,
            'keywords_upright' => ['성찰', '탐구', '고독', '내면의 빛', '자기 성찰'],
            'keywords_reversed' => ['고립', '외로움', '폐쇄성', '거부', '방황'],
            'meaning_upright' => '세속의 소음을 벗어나 내면을 돌아보고 연구에 매진할 시간입니다. 지혜가 차오릅니다.',
            'meaning_reversed' => '지나친 은둔은 현실과의 단절을 낳습니다. 세상과의 소통 창구를 열어두십시오.'],
        10 => ['id' => 10, 'name_ko' => '운명의 수레바퀴', 'name_en' => 'Wheel of Fortune', 'arcana' => 'major', 'suit' => null, 'number' => 10,
            'keywords_upright' => ['운명의 전환점', '행운', '새로운 주기', '필연적 변화'],
            'keywords_reversed' => ['불운', '예상 밖 지연', '변화 저항', '악순환'],
            'meaning_upright' => '운명의 주기가 상승 국면에 접어들었습니다. 찾아온 흐름을 타고 큰 도약을 이루십시오.',
            'meaning_reversed' => '일시적인 정체와 하강의 주기입니다. 무리하지 말고 다음 순환을 차분히 준비하십시오.'],
        11 => ['id' => 11, 'name_ko' => '정의', 'name_en' => 'Justice', 'arcana' => 'major', 'suit' => null, 'number' => 11,
            'keywords_upright' => ['공정', '진실', '균형', '인과응보', '명확한 판결'],
            'keywords_reversed' => ['불공정', '편견', '책임 회피', '법적 문제'],
            'meaning_upright' => '공정하고 정직하게 처신하면 반드시 그에 합당한 정당한 보상과 승리를 얻게 됩니다.',
            'meaning_reversed' => '사리사욕이나 편견으로 일을 그르치지 않도록 주의하십시오. 진실이 밝혀집니다.'],
        12 => ['id' => 12, 'name_ko' => '매달린 사람', 'name_en' => 'The Hanged Man', 'arcana' => 'major', 'suit' => null, 'number' => 12,
            'keywords_upright' => ['희생', '새로운 시각', '정지', '깨달음', '인내'],
            'keywords_reversed' => ['무의미한 희생', '정체', '저항', '이기심'],
            'meaning_upright' => '발상을 전환하고 다른 각도에서 세상을 바라보십시오. 일시적 정지가 큰 도약이 됩니다.',
            'meaning_reversed' => '쓸데없는 고집으로 시간만 낭비하고 있습니다. 묶여 있는 끈을 스스로 풀 때입니다.'],
        13 => ['id' => 13, 'name_ko' => '죽음', 'name_en' => 'Death', 'arcana' => 'major', 'suit' => null, 'number' => 13,
            'keywords_upright' => ['종결', '새로운 탄생', '근본적 변혁', '과거와의 결별'],
            'keywords_reversed' => ['변화 거부', '미련', '정체', '두려움'],
            'meaning_upright' => '묵은 상황이 완전히 끝나고 새로운 생명이 시작됩니다. 낡은 것을 미련 없이 보내주십시오.',
            'meaning_reversed' => '끝난 일에 매달려 앞으로 나아가지 못하고 있습니다. 과감한 단절이 필요합니다.'],
        14 => ['id' => 14, 'name_ko' => '절제', 'name_en' => 'Temperance', 'arcana' => 'major', 'suit' => null, 'number' => 14,
            'keywords_upright' => ['균형', '조화', '치유', '중용', '인내심 있는 결합'],
            'keywords_reversed' => ['극단', '불균형', '과도함', '갈등'],
            'meaning_upright' => '서로 다른 요소들이 아름답게 조화를 이루어 평온과 치유가 깃드는 길한 시기입니다.',
            'meaning_reversed' => '과음, 과식, 감정적 폭발 등 균형을 잃은 생활이 화를 부르니 절제하십시오.'],
        15 => ['id' => 15, 'name_ko' => '악마', 'name_en' => 'The Devil', 'arcana' => 'major', 'suit' => null, 'number' => 15,
            'keywords_upright' => ['유혹', '중독', '물질적 집착', '속박', '그림자'],
            'keywords_reversed' => ['속박 해제', '자유', '각성', '극복'],
            'meaning_upright' => '달콤하지만 위험한 유혹이나 집착에 얽매여 있지 않은지 점검하십시오. 자각이 필요합니다.',
            'meaning_reversed' => '오랜 중독이나 나쁜 습관, 속박에서 벗어나 자유를 되찾는 희망찬 국면입니다.'],
        16 => ['id' => 16, 'name_ko' => '탑', 'name_en' => 'The Tower', 'arcana' => 'major', 'suit' => null, 'number' => 16,
            'keywords_upright' => ['급격한 붕괴', '각성', '예상 밖 충격', '거짓의 파괴'],
            'keywords_reversed' => ['재난 회피', '지연된 위기', '붕괴 두려움'],
            'meaning_upright' => '거짓된 기반이 무너지는 충격이 따르나, 썩은 살을 도려내고 새로운 터전을 닦게 됩니다.',
            'meaning_reversed' => '위기를 억지로 덮으려 하지 마십시오. 근본적인 재건축을 시작해야 안전합니다.'],
        17 => ['id' => 17, 'name_ko' => '별', 'name_en' => 'The Star', 'arcana' => 'major', 'suit' => null, 'number' => 17,
            'keywords_upright' => ['희망', '영감', '치유', '신념', '밝은 미래'],
            'keywords_reversed' => ['절망', '자신감 상실', '비관론', '불안'],
            'meaning_upright' => '어둠을 뚫고 찬란한 희망의 별이 떠올랐습니다. 당신의 이상과 꿈이 현실로 다가옵니다.',
            'meaning_reversed' => '스스로에 대한 믿음을 잃지 마십시오. 먹구름 너머에 여전히 별이 빛나고 있습니다.'],
        18 => ['id' => 18, 'name_ko' => '달', 'name_en' => 'The Moon', 'arcana' => 'major', 'suit' => null, 'number' => 18,
            'keywords_upright' => ['불안', '착각', '무의식', '안개', '숨겨진 진실'],
            'keywords_reversed' => ['안개 걷힘', '진실 발견', '두려움 극복'],
            'meaning_upright' => '상황이 불투명하고 마음이 불안할 수 있습니다. 섣부른 결정을 피하고 안개가 걷히길 기다리십시오.',
            'meaning_reversed' => '혼란스러웠던 일들의 실체가 밝혀지고 마음에 평화가 찾아옵니다.'],
        19 => ['id' => 19, 'name_ko' => '태양', 'name_en' => 'The Sun', 'arcana' => 'major', 'suit' => null, 'number' => 19,
            'keywords_upright' => ['성공', '기쁨', '활력', '명확함', '축복'],
            'keywords_reversed' => ['일시적 지연', '과신', '우울함', '작은 성공'],
            'meaning_upright' => '최고의 성공과 생명력이 넘쳐나는 대길의 운세입니다. 만인이 당신의 성취를 축복합니다.',
            'meaning_reversed' => '구름이 잠시 해를 가렸으나 곧 밝아집니다. 지나친 자만심만 경계하십시오.'],
        20 => ['id' => 20, 'name_ko' => '심판', 'name_en' => 'Judgement', 'arcana' => 'major', 'suit' => null, 'number' => 20,
            'keywords_upright' => ['부활', '보상', '결단', '새로운 소명', '면죄'],
            'keywords_reversed' => ['자기 비하', '후회', '기회 놓침', '우유부단'],
            'meaning_upright' => '과거의 성실한 노력에 대한 찬란한 보답의 나팔이 울립니다. 새로운 차원으로 도약하십시오.',
            'meaning_reversed' => '과거의 후회에 얽매이지 말고 주어진 마지막 기회를 놓치지 마십시오.'],
        21 => ['id' => 21, 'name_ko' => '세계', 'name_en' => 'The World', 'arcana' => 'major', 'suit' => null, 'number' => 21,
            'keywords_upright' => ['완성', '통합', '성공적 종결', '여행', '완벽한 조화'],
            'keywords_reversed' => ['미완성', '마무리 부족', '지연', '답보 상태'],
            'meaning_upright' => '하나의 거대한 주기가 완벽하게 결실을 맺고 대단원의 막을 내렸습니다. 완전한 성취입니다.',
            'meaning_reversed' => '마지막 한 조각이 부족합니다. 끝까지 방심하지 말고 마무리에 만전을 기하십시오.'],
    ];

    /**
     * 무작위 카드 1장 드로우 (단문 점단)
     */
    public function drawOne(bool $allowReversed = true, bool $majorOnly = true): TarotCard
    {
        $cards = $this->draw(1, $allowReversed, $majorOnly);

        return $cards[0];
    }

    /**
     * 과거 / 현재 / 미래 3카드 스프레드 드로우
     *
     * @return array{past: TarotCard, present: TarotCard, future: TarotCard}
     */
    public function drawThree(bool $allowReversed = true, bool $majorOnly = true): array
    {
        $cards = $this->draw(3, $allowReversed, $majorOnly);

        return [
            'past' => $cards[0],
            'present' => $cards[1],
            'future' => $cards[2],
        ];
    }

    /**
     * 지정된 수만큼 무작위 비복원 추출
     *
     * @return array<int, TarotCard>
     */
    public function draw(int $count = 1, bool $allowReversed = true, bool $majorOnly = true): array
    {
        $pool = $majorOnly ? self::MAJOR_CARDS : $this->getFullDeck();

        if ($count > count($pool)) {
            throw new InvalidArgumentException("Draw count ({$count}) exceeds available deck size.");
        }

        $keys = array_keys($pool);
        shuffle($keys);
        $selectedKeys = array_slice($keys, 0, $count);

        $results = [];
        foreach ($selectedKeys as $key) {
            $isReversed = $allowReversed ? (random_int(0, 1) === 1) : false;
            $results[] = new TarotCard($pool[$key], $isReversed);
        }

        return $results;
    }

    /**
     * 78장 풀 덱 생성 (메이저 22장 + 마이너 56장)
     */
    private function getFullDeck(): array
    {
        $deck = self::MAJOR_CARDS;
        $suits = ['wands' => '완드', 'cups' => '컵', 'swords' => '소드', 'pentacles' => '펜타클'];
        $rankNames = [
            1 => '에이스', 2 => '2', 3 => '3', 4 => '4', 5 => '5',
            6 => '6', 7 => '7', 8 => '8', 9 => '9', 10 => '10',
            11 => '페이지', 12 => '나이트', 13 => '퀸', 14 => '킹',
        ];

        $id = 22;
        foreach ($suits as $suitKey => $suitKo) {
            for ($rank = 1; $rank <= 14; $rank++) {
                $rankKo = $rankNames[$rank];
                $nameKo = "{$suitKo} {$rankKo}";
                $nameEn = ucfirst($rankKo).' of '.ucfirst($suitKey);

                $deck[$id] = [
                    'id' => $id,
                    'name_ko' => $nameKo,
                    'name_en' => $nameEn,
                    'arcana' => 'minor',
                    'suit' => $suitKey,
                    'number' => $rank,
                    'keywords_upright' => ["{$suitKo}의 기운", '새로운 전개', '실행'],
                    'keywords_reversed' => ['지연', '과도함', '불균형'],
                    'meaning_upright' => "{$nameKo} 카드는 일상 속에서 해당 원소의 구체적인 실현을 의미합니다.",
                    'meaning_reversed' => "{$nameKo} 카드의 에너지가 과도하거나 막혀 있음을 나타냅니다.",
                ];
                $id++;
            }
        }

        return $deck;
    }
}
