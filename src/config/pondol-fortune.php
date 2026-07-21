<?php

return [
    'route_fortune' => [
        'prefix' => 'fortune',
        'as' => 'fortune.',
        'middleware' => ['web'],
    ],
    'route_fortune_admin' => [
        'prefix' => 'fortune/admin',
        'as' => 'fortune.admin.',
        'middleware' => ['web', 'admin'],
    ],
    'component' => ['admin' => ['layout' => 'pondol-fortune::admin', 'lnb' => 'pondol-fortune::partials.admin-lnb']],
    /**
     * 60갑자 일주(日柱)별 별칭 및 유형 데이터.
     * 'nickname'은 천간의 색상과 지지의 동물을 조합한 상징적인 별칭입니다.
     * 'archetype'은 해당 일주의 성향을 나타내는 대표적인 페르소나/유형입니다.
     */
    'ilju_archetypes' => [
        '甲子' => ['nickname' => '푸른 쥐', 'archetype' => '지혜로운 개척가'],
        '乙丑' => ['nickname' => '푸른 소', 'archetype' => '성실한 현실주의자'],
        '丙寅' => ['nickname' => '붉은 호랑이', 'archetype' => '열정적인 선구자'],
        '丁卯' => ['nickname' => '붉은 토끼', 'archetype' => '따뜻한 예술가'],
        '戊辰' => ['nickname' => '황금 용', 'archetype' => '믿음직한 중재자'],
        '己巳' => ['nickname' => '황금 뱀', 'archetype' => '내실 있는 전략가'],
        '庚午' => ['nickname' => '하얀 말', 'archetype' => '정의로운 개혁가'],
        '辛未' => ['nickname' => '하얀 양', 'archetype' => '우아한 장인'],
        '壬申' => ['nickname' => '검은 원숭이', 'archetype' => '다재다능한 협상가'],
        '癸酉' => ['nickname' => '검은 닭', 'archetype' => '날카로운 비평가'],
        '甲戌' => ['nickname' => '푸른 개', 'archetype' => '책임감 있는 낙천가'],
        '乙亥' => ['nickname' => '푸른 돼지', 'archetype' => '친절한 이상주의자'],
        '丙子' => ['nickname' => '붉은 쥐', 'archetype' => '솔직한 양심가'],
        '丁丑' => ['nickname' => '붉은 소', 'archetype' => '지혜로운 봉사자'],
        '戊寅' => ['nickname' => '황금 호랑이', 'archetype' => '신뢰의 리더'],
        '己卯' => ['nickname' => '황금 토끼', 'archetype' => '온화한 원칙주의자'],
        '庚辰' => ['nickname' => '하얀 용', 'archetype' => '의리를 지키는 리더'],
        '辛巳' => ['nickname' => '하얀 뱀', 'archetype' => '세련된 재능가'],
        '壬午' => ['nickname' => '검은 말', 'archetype' => '자유로운 지략가'],
        '癸未' => ['nickname' => '검은 양', 'archetype' => '신중한 현실주의자'],
        '甲申' => ['nickname' => '푸른 원숭이', 'archetype' => '고독한 의리파'],
        '乙酉' => ['nickname' => '푸른 닭', 'archetype' => '진취적인 실용주의자'],
        '丙戌' => ['nickname' => '붉은 개', 'archetype' => '명랑한 중재자'],
        '丁亥' => ['nickname' => '붉은 돼지', 'archetype' => '논리적인 예술가'],
        '戊子' => ['nickname' => '황금 쥐', 'archetype' => '치밀한 완벽주의자'],
        '己丑' => ['nickname' => '황금 소', 'archetype' => '묵묵한 관리자'],
        '庚寅' => ['nickname' => '하얀 호랑이', 'archetype' => '카리스마 있는 팔방미인'],
        '辛卯' => ['nickname' => '하얀 토끼', 'archetype' => '감성적인 리더'],
        '壬辰' => ['nickname' => '검은 용', 'archetype' => '화통한 전략가'],
        '癸巳' => ['nickname' => '검은 뱀', 'archetype' => '지혜로운 활동가'],
        '甲午' => ['nickname' => '푸른 말', 'archetype' => '순수한 도전자'],
        '乙未' => ['nickname' => '푸른 양', 'archetype' => '합리적인 예술가'],
        '丙申' => ['nickname' => '붉은 원숭이', 'archetype' => '낙천적인 재능가'],
        '丁酉' => ['nickname' => '붉은 닭', 'archetype' => '감성적인 원칙주의자'],
        '戊戌' => ['nickname' => '황금 개', 'archetype' => '성실한 신념가'],
        '己亥' => ['nickname' => '황금 돼지', 'archetype' => '논리적인 실속파'],
        '庚子' => ['nickname' => '하얀 쥐', 'archetype' => '고독한 혁신가'],
        '辛丑' => ['nickname' => '하얀 소', 'archetype' => '뚝심 있는 장인'],
        '壬寅' => ['nickname' => '검은 호랑이', 'archetype' => '유연한 포용가'],
        '癸卯' => ['nickname' => '검은 토끼', 'archetype' => '총명한 참모'],
        '甲辰' => ['nickname' => '푸른 용', 'archetype' => '진취적인 리더'],
        '乙巳' => ['nickname' => '푸른 뱀', 'archetype' => '상상력이 풍부한 강직가'],
        '丙午' => ['nickname' => '붉은 말', 'archetype' => '화려한 이상주의자'],
        '丁未' => ['nickname' => '붉은 양', 'archetype' => '정열적인 실천가'],
        '戊申' => ['nickname' => '황금 원숭이', 'archetype' => '고독한 신용가'],
        '己酉' => ['nickname' => '황금 닭', 'archetype' => '재치 있는 생활인'],
        '庚戌' => ['nickname' => '하얀 개', 'archetype' => '따뜻한 카리스마'],
        '辛亥' => ['nickname' => '하얀 돼지', 'archetype' => '지혜로운 탐구자'],
        '壬子' => ['nickname' => '검은 쥐', 'archetype' => '강인한 개척가'],
        '癸丑' => ['nickname' => '검은 소', 'archetype' => '명랑한 전략가'],
        '甲寅' => ['nickname' => '푸른 호랑이', 'archetype' => '순수한 정의파'],
        '乙卯' => ['nickname' => '푸른 토끼', 'archetype' => '부드러운 실천가'],
        '丙辰' => ['nickname' => '붉은 용', 'archetype' => '적극적인 중재자'],
        '丁巳' => ['nickname' => '붉은 뱀', 'archetype' => '순수한 승부사'],
        '戊午' => ['nickname' => '황금 말', 'archetype' => '뚝심 있는 노력가'],
        '己未' => ['nickname' => '황금 양', 'archetype' => '온화한 봉사자'],
        '庚申' => ['nickname' => '하얀 원숭이', 'archetype' => '자신감 넘치는 능력가'],
        '辛酉' => ['nickname' => '하얀 닭', 'archetype' => '고독한 완벽주의자'],
        '壬戌' => ['nickname' => '검은 개', 'archetype' => '호탕한 지식인'],
        '癸亥' => ['nickname' => '검은 돼지', 'archetype' => '자유로운 평화주의자'],
    ],
    'select_day' => [
        /*
        |--------------------------------------------------------------------------
        | 1. 기초 공통 데이터 (날짜 기반 표준 점수 - 모든 달력 공통)
        |--------------------------------------------------------------------------
        */
        'base' => [
            // [민속/날짜 기반]
            'son' => ['ko' => '손 없는 날', 'score' => 20,   'type' => 'gilsin',   'priority' => 10, 'desc' => '악귀가 없는 날로 모든 시작에 길한 민속 길일'],
            'bokdan' => ['ko' => '복단일',     'score' => -100, 'type' => 'hyungsal', 'priority' => 100, 'desc' => '엎어지고 끊긴다는 뜻으로 중요한 일을 시작하기에 부적합한 금기일'],
            'wolgi' => ['ko' => '월기일',     'score' => -30,  'type' => 'hyungsal', 'priority' => 80,  'desc' => '매달 정해진 흉일로 기운이 탁하여 중요한 결정을 삼가야 하는 날'],
            'indong' => ['ko' => '인동일',     'score' => -10,  'type' => 'hyungsal', 'priority' => 30,  'desc' => '질병과 재난을 방비하기 위해 외부 사람의 출입을 삼가는 날'],
            'haeil' => ['ko' => '해일(亥日)', 'score' => -10,  'type' => 'hyungsal', 'priority' => 30,  'desc' => '기운이 급격히 변하기 쉬워 이동 시 주의가 필요한 날'],
            // [귀인/길신 - 중요도 보강]
            // [범용 흉살 - 추가 보강]
            // [그룹] 황도 6성
            'whangdo' => [
                'is_group' => true,
                'items' => [
                    'cheong' => ['ko' => '청룡황도', 'score' => 30, 'type' => 'gilsin', 'priority' => 5, 'desc' => '새로운 계획을 추진하기에 좋은 날'],
                    'myeong' => ['ko' => '명당황도', 'score' => 30, 'type' => 'gilsin', 'priority' => 5, 'desc' => '귀인의 도움을 받아 매사가 평안하게 풀리는 길일'],
                    'geum' => ['ko' => '금궤황도', 'score' => 50, 'type' => 'gilsin', 'priority' => 12, 'desc' => '재물운이 가장 강하여 큰 계약에 최고의 가치를 지님'],
                    'cheon' => ['ko' => '천덕황도', 'score' => 30, 'type' => 'gilsin', 'priority' => 5, 'desc' => '하늘의 덕이 보살펴 재난이 비껴가는 평온한 길일'],
                    'ok' => ['ko' => '옥당황도', 'score' => 30, 'type' => 'gilsin', 'priority' => 5, 'desc' => '지혜와 총명이 빛나 배움이나 문서 업무에 유리함'],
                    'sa' => ['ko' => '사명황도', 'score' => 30, 'type' => 'gilsin', 'priority' => 5, 'desc' => '계획한 일들이 순리대로 척척 이루어지는 실행의 길일'],
                ],
            ],
        ],

        'category_base' => [
            'cheonsa' => ['ko' => '천사성',     'score' => 50,   'type' => 'gilsin',   'priority' => 15, 'desc' => '하늘이 모든 죄를 사하는 날로 새롭게 시작하기에 최적인 대길일'],
            'chenduk' => ['ko' => '천덕귀인',   'score' => 20,   'type' => 'gilsin',   'priority' => 40, 'desc' => '하늘의 복이 내려 재앙을 막아주고 만사가 형통하는 길일'],
            'wolduk' => ['ko' => '월덕귀인',   'score' => 20,   'type' => 'gilsin',   'priority' => 40, 'desc' => '주변의 도움을 받고 장애물이 사라지는 상서로운 날'],
            'gachui' => ['ko' => '가취대흉',   'score' => -100, 'type' => 'hyungsal', 'priority' => 98, 'desc' => '혼인이나 이사 등 모든 대사에 있어 매우 흉한 절대 금기일'],
            'jipa' => ['ko' => '지파(地破)', 'score' => -20,  'type' => 'hyungsal', 'priority' => 30, 'desc' => '땅의 기운이 깨져 약속이나 계약이 틀어질 수 있는 주의일'],
            'hague' => ['ko' => '하괴(河魁)', 'score' => -20,  'type' => 'hyungsal', 'priority' => 30, 'desc' => '뜻밖의 방해나 막힘이 생길 수 있으니 무리한 추진은 금물'],
            'gocho' => ['ko' => '고초살',     'score' => -15,  'type' => 'hyungsal', 'priority' => 25, 'desc' => '기운이 메마르고 결실을 맺기 어려운 에너지가 부족한 날'],
            'pima' => ['ko' => '피마살',     'score' => -20,  'type' => 'hyungsal', 'priority' => 30, 'desc' => '집안에 슬픈 일이 생기거나 액운이 따를 수 있는 주의일'],
            'cheonmun' => ['ko' => '천문개일',   'score' => 20,   'type' => 'gilsin',   'priority' => 4,  'desc' => '하늘의 문이 열려 뜻을 이루기 좋은 길일'],

            'chengu' => [
                'ko' => '천구(天狗)',
                'score' => -20,
                'type' => 'hyungsal',
                'priority' => 30,
                'desc' => '하늘의 개가 짖어 방해한다는 뜻으로, 주변의 시비나 구설에 휘말리기 쉬우니 언행에 신중해야 하는 날입니다.',
            ],
            'chuk' => [
                'ko' => '축음양불장길',
                'score' => 25,
                'type' => 'gilsin',
                'priority' => 45,
                'desc' => '음양의 기운이 조화로워 하늘과 땅의 방해가 없는 날로, 특히 혼인이나 이사에 매우 길한 날입니다.',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | 2. 카테고리별 특화 데이터 (모든 개인화 로직 통합)
        |--------------------------------------------------------------------------
        */
        'category' => [
            // [이사 택일]
            'move' => [
                'base_override' => [
                    'son' => ['score' => 40, 'desc' => '이사하기 가장 좋은 민속 길일'],
                    'haeil' => ['score' => -30, 'desc' => '이동 시 변동과 풍파가 많아 이사에서 특히 꺼리는 날'],
                ],
                'items' => [
                    // --- 개인 공통 로직 ---
                    'ilgan_hap' => ['ko' => '천간합일', 'score' => 50, 'type' => 'gilsin', 'priority' => 90, 'desc' => '나와 날의 기운이 합을 이루어 이사가 순조로운 날'],
                    'ilgan_chung' => ['ko' => '천간충일', 'score' => -50, 'type' => 'hyungsal', 'priority' => 85, 'desc' => '정신적 스트레스나 외부적 충돌을 주의해야 하는 날'],
                    'gongmang' => ['ko' => '공망일', 'score' => -100, 'type' => 'hyungsal', 'priority' => 95, 'desc' => '이사 후 실속이 없고 기운이 비어있는 부적합한 날'],
                    'risk' => ['ko' => '기운충돌', 'score' => -50, 'type' => 'hyungsal', 'priority' => 88, 'desc' => '나의 사주 일지와 충돌이 있어 이사 시 신중해야 함'],
                    'sbc_group' => [
                        'is_group' => true,
                        'items' => [
                            'senggi' => ['ko' => '생기일', 'score' => 30, 'type' => 'gilsin', 'priority' => 40, 'desc' => '새 거처에서 활력이 넘치는 좋은 날'],
                            'bokduk' => ['ko' => '복덕일', 'score' => 30, 'type' => 'gilsin', 'priority' => 40, 'desc' => '복과 덕이 따르는 이사에 아주 좋은 날'],
                            'cheneu' => ['ko' => '천의일', 'score' => 30, 'type' => 'gilsin', 'priority' => 40, 'desc' => '건강 기운이 좋아 가정이 평안해지는 날'],
                        ],
                    ],
                    // --- 이사 전용 특화 ---
                    'singu' => ['ko' => '신구길일', 'score' => 25, 'type' => 'gilsin', 'priority' => 45, 'desc' => '새 집과 헌 집의 기운이 조화로운 이사 길일'],
                    'daejanggun' => ['ko' => '대장군방', 'score' => -100, 'type' => 'hyungsal', 'priority' => 99, 'desc' => '올해 대장군 신이 머무는 방향이므로 절대 금기'],
                    'samsalbang' => ['ko' => '삼살방', 'score' => -100, 'type' => 'hyungsal', 'priority' => 99, 'desc' => '세 가지 재앙이 깃든 방향이므로 이사 주의'],

                    // --- 개인 신살 (Taekil) ---
                    'sangmun' => ['ko' => '상문살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -30, 'priority' => 55, 'desc' => '문상 등을 피해야 하는 주의가 필요한 날'],
                    'jogaek' => ['ko' => '조객살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -30, 'priority' => 55, 'desc' => '언행에 신중하고 타인과의 시비를 삼가야 함'],
                    'sepa' => ['ko' => '세파살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -30, 'priority' => 55, 'desc' => '기운이 깨지는 날로 계약에 차질이 생길 수 있음'],
                    'banum' => ['ko' => '반음살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -20, 'priority' => 40, 'desc' => '일이 반복되거나 지체될 수 있는 날'],
                    'byungbu' => ['ko' => '병부살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -20, 'priority' => 40, 'desc' => '건강 기운이 약해질 수 있으니 과로를 피해야 함'],
                ],
            ],

            // [개업 택일]
            'business' => [
                'base_override' => [
                    'son' => ['score' => 30, 'desc' => '사업장의 액운을 막아주는 길한 날'],
                    'cheonmun' => ['score' => 40, 'desc' => '사업 번창의 문이 열려 뜻을 이루기 가장 좋은 날'],
                ],
                'items' => [
                    // --- 개인 공통 로직 ---
                    'ilgan_hap' => ['ko' => '천간합일', 'score' => 50, 'type' => 'gilsin', 'priority' => 90, 'desc' => '대표자의 기운과 합이 되어 사업 번창을 돕는 날'],
                    'ilgan_chung' => ['ko' => '천간충일', 'score' => -50, 'type' => 'hyungsal', 'priority' => 85, 'desc' => '대외적 관계에서 충돌이나 마찰을 주의해야 하는 날'],
                    'gongmang' => ['ko' => '공망일',   'score' => -100, 'type' => 'hyungsal', 'priority' => 95, 'desc' => '노력에 비해 성과가 적고 기반이 흔들릴 수 있는 날'],
                    'risk' => ['ko' => '기운충돌', 'score' => -50, 'type' => 'hyungsal', 'priority' => 88, 'desc' => '대표자 개인의 기운과 충돌이 있어 결정에 신중해야 함'],
                    'sbc_group' => [
                        'is_group' => true,
                        'items' => [
                            'senggi' => ['ko' => '생기일', 'score' => 30, 'type' => 'gilsin', 'priority' => 40, 'desc' => '사업에 활기를 불어넣는 좋은 날'],
                            'bokduk' => ['ko' => '복덕일', 'score' => 30, 'type' => 'gilsin', 'priority' => 40, 'desc' => '복과 재물이 따르는 개업의 길일'],
                            'cheneu' => ['ko' => '천의일', 'score' => 30, 'type' => 'gilsin', 'priority' => 40, 'desc' => '귀인의 도움으로 어려움이 해결되는 날'],
                        ],
                    ],
                    // --- 개업 전용 특화 ---
                    'woonseong_group' => [
                        'is_group' => true,
                        'items' => [
                            'jangseng' => ['ko' => '장생일', 'score' => 40, 'type' => 'gilsin', 'priority' => 95, 'desc' => '재물의 씨앗이 싹트는 최고의 개업 길일'],
                            'gwandae' => ['ko' => '관대일', 'score' => 40, 'type' => 'gilsin', 'priority' => 95, 'desc' => '사업의 기반이 튼튼해지고 관운이 따르는 날'],
                            'geonrok' => ['ko' => '건록일', 'score' => 40, 'type' => 'gilsin', 'priority' => 95, 'desc' => '재물이 안정적으로 쌓이는 번창의 날'],
                            'jewang' => ['ko' => '제왕일', 'score' => 40, 'type' => 'gilsin', 'priority' => 95, 'desc' => '재물 에너지가 정점에 달하는 최고의 대박 날'],
                        ],
                    ],
                    'singu' => ['ko' => '신구길일', 'score' => 15, 'type' => 'gilsin', 'priority' => 45, 'desc' => '사업장 이전 및 오픈에 좋은 기운의 날'],
                    // --- 개인 신살 (Taekil) ---
                    'sangmun' => ['ko' => '상문살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -20, 'priority' => 55, 'desc' => '부정적인 기운을 피해야 하는 개업 주의일'],
                    'jogaek' => ['ko' => '조객살', 'is_taekil' => true, 'score' => -20, 'priority' => 55, 'desc' => '시비나 구설을 주의해야 하는 날'],
                    'sepa' => ['ko' => '세파살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -20, 'priority' => 55, 'desc' => '계약이나 약속이 틀어질 수 있는 기운'],
                ],
            ],

            // [결혼 택일]
            'marriage' => [
                'items' => [
                    // --- 개인 공통 로직 ---
                    'ilgan_hap' => ['ko' => '천간합일', 'score' => 50, 'type' => 'gilsin', 'priority' => 90, 'desc' => '두 분의 기운이 조화롭게 합쳐지는 좋은 날'],
                    'ilgan_chung' => ['ko' => '천간충일', 'score' => -70, 'type' => 'hyungsal', 'priority' => 92, 'desc' => '부부간 혹은 양가간의 다툼이나 마찰을 주의해야 함'],
                    'gongmang' => ['ko' => '공망일',   'score' => -100, 'type' => 'hyungsal', 'priority' => 95, 'desc' => '결실이 허망하게 돌아갈 수 있는 부적합한 날'],
                    'risk' => ['ko' => '기운충돌', 'score' => -80, 'type' => 'hyungsal', 'priority' => 94, 'desc' => '본인의 자리(일지)를 치는 날로 부부 화합에 방해가 됨'],
                    'sbc_group' => [
                        'is_group' => true,
                        'items' => [
                            'senggi' => ['ko' => '생기일', 'score' => 30, 'type' => 'gilsin', 'priority' => 40, 'desc' => '새로운 가정을 꾸리는 활기찬 날'],
                            'bokduk' => ['ko' => '복덕일', 'score' => 30, 'type' => 'gilsin', 'priority' => 40, 'desc' => '가정에 복과 덕이 넘치는 행복한 날'],
                        ],
                    ],
                    // --- 결혼 전용 특화 ---
                    'dae_group' => [
                        'is_group' => true,
                        'items' => [
                            'daeri' => ['ko' => '대리월', 'score' => 40,  'type' => 'gilsin',   'priority' => 90, 'desc' => '양가 모두에게 해가 없이 가장 길한 최고의 혼인 달'],
                            'bangbuju' => ['ko' => '방부주', 'score' => -40, 'type' => 'hyungsal', 'priority' => 85, 'desc' => '신랑의 기운에 장애가 생기거나 불리한 영향을 미치는 달'],
                            'bangnyeosin' => ['ko' => '방녀신', 'score' => -40, 'type' => 'hyungsal', 'priority' => 85, 'desc' => '신부 본인의 기운에 좋지 않은 영향을 주는 달'],
                            'bangonggo' => ['ko' => '방옹고', 'score' => -20, 'type' => 'hyungsal', 'priority' => 50, 'desc' => '시부모님의 기운과 충돌할 수 있는 시기로, 시댁 어른들과의 화합에 각별한 정성이 필요한 달입니다.'],
                            'bangnyebu' => ['ko' => '방녀부모', 'score' => -20, 'type' => 'hyungsal', 'priority' => 50, 'desc' => '친정 부모님의 기운에 영향을 줄 수 있는 시기로, 부모님의 건강과 안녕을 먼저 살피는 배려가 필요한 달입니다.'],
                            'bangmaessi' => ['ko' => '방매씨',   'score' => 0,   'type' => 'junglip',  'priority' => 10, 'desc' => '중매인에게 불리한 달이나, 신랑 신부 당사자들에게는 무난한 달입니다.'],
                        ],
                    ],

                    'chendukhap' => ['ko' => '천덕합',   'score' => 10,   'type' => 'gilsin',   'priority' => 20, 'desc' => '하늘의 덕과 합이 되어 화합을 돕는 날'],
                    'woldukhap' => ['ko' => '월덕합',   'score' => 10,   'type' => 'gilsin',   'priority' => 20, 'desc' => '달의 은혜와 합이 되어 평안을 주는 날'],
                    // --- 개인 신살 (Taekil) ---
                    'sangmun' => ['ko' => '상문살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -100, 'priority' => 90, 'desc' => '경사스러운 날 슬픈 기운을 멀리해야 하는 주의일'],
                    'jogaek' => ['ko' => '조객살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -100, 'priority' => 90, 'desc' => '부정적인 기운을 타지 않도록 극히 주의해야 하는 날'],
                    'sepa' => ['ko' => '세파살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -50, 'priority' => 80, 'desc' => '약속이 깨질 수 있는 흉한 기운'],
                ],
            ],

            // [신차 택일]
            'car' => [
                'items' => [
                    // --- 개인 공통 로직 ---
                    'ilgan_chung' => ['ko' => '천간충일', 'score' => -30, 'type' => 'hyungsal', 'priority' => 50, 'desc' => '대외적인 충돌이나 가벼운 사고수를 주의해야 함'],
                    'risk' => ['ko' => '기운충돌', 'score' => -50, 'type' => 'hyungsal', 'priority' => 88, 'desc' => '사고수가 있을 수 있으니 출고 및 인수를 삼가야 함'],
                    'sbc_group' => [
                        'is_group' => true,
                        'items' => [
                            'senggi' => ['ko' => '생기일', 'score' => 20, 'type' => 'gilsin', 'priority' => 40, 'desc' => '운전의 활력과 평안을 얻는 좋은 날'],
                        ],
                    ],
                    // --- 차량 전용 특화 ---
                    'car_match' => ['ko' => '기운조화', 'score' => 40, 'type' => 'gilsin', 'priority' => 90, 'desc' => '차량의 오행과 나의 사주 상성이 아주 좋은 날'],
                    'car_safety' => ['ko' => '안전운행', 'score' => 50, 'type' => 'gilsin', 'priority' => 95, 'desc' => '사고 기운이 없고 기류가 안정된 무사고 기원의 날'],
                    'car_yeokma' => ['ko' => '역마합일', 'score' => 30, 'type' => 'gilsin', 'priority' => 50, 'desc' => '이동의 기운인 역마가 합을 이루어 경쾌하고 시원한 주행을 돕는 날'],
                    'gongmang' => ['ko' => '공망일',   'score' => -30, 'type' => 'hyungsal', 'priority' => 50, 'desc' => '기운이 허한 날로 차량 인수에 주의가 필요함'],
                    // --- 개인 신살 (Taekil) ---
                    'sangmun' => ['ko' => '상문살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -20, 'priority' => 20, 'desc' => '장거리 주행 시 차분한 마음가짐이 필요한 날'],
                    'jogaek' => ['ko' => '조객살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -20, 'priority' => 20, 'desc' => '타인과의 시비를 피하고 안전에 유의해야 함'],
                    'sepa' => ['ko' => '세파살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -20, 'priority' => 20, 'desc' => '기계적 결함이나 계약 차질을 주의해야 함'],
                    'byungbu' => ['ko' => '병부살', 'type' => 'hyungsal', 'is_taekil' => true, 'score' => -20, 'priority' => 20, 'desc' => '컨디션 난조로 인한 집중력 저하 주의'],
                ],
            ], 'pet_adoption' => [
                'base_override' => [
                    'son' => ['score' => 30, 'desc' => '아이의 낯선 환경 적응을 돕는 민속 길일입니다.'],
                    'haeil' => ['score' => -30, 'desc' => '이동 중 변동수가 많아 아이가 불안해할 수 있는 날입니다.'],
                ],
                'items' => [
                    // --- 인연의 합 ---
                    'ilgan_hap' => ['ko' => '천간합일', 'score' => 50, 'type' => 'gilsin', 'priority' => 90, 'desc' => '주인님과 아이의 기운이 첫눈에 소통되는 날입니다.'],
                    'jiji_hap' => ['ko' => '지합일', 'score' => 40, 'type' => 'gilsin', 'priority' => 85, 'desc' => '아이와 주인님의 생활 리듬이 찰떡같이 맞는 인연의 날입니다.'],
                    'chuk' => ['ko' => '축음양불장길', 'score' => 30, 'type' => 'gilsin', 'priority' => 50, 'desc' => '하늘과 땅의 방해가 없어 새로운 가족을 맞이하기에 대길합니다.'],

                    // --- 건강 및 주의 (원진살 포함) ---
                    'wonjin' => ['ko' => '원진살', 'score' => -80, 'type' => 'hyungsal', 'priority' => 99, 'desc' => '서로 이유 없이 예민해질 수 있어 초기 적응에 많은 인내가 필요한 날입니다.'],
                    'byungbu' => ['ko' => '병부살', 'is_taekil' => true, 'score' => -60, 'type' => 'hyungsal', 'priority' => 95, 'desc' => '아이의 건강 기운이 약해질 수 있으니 입양 후 컨디션 관리에 유의하세요.'],
                    'sepa' => ['ko' => '세파살', 'is_taekil' => true, 'score' => -40, 'type' => 'hyungsal', 'priority' => 80, 'desc' => '입양 절차나 이동 과정에서 예기치 못한 차질이 생길 수 있습니다.'],
                    'gongmang' => ['ko' => '공망일', 'score' => -50, 'type' => 'hyungsal', 'priority' => 92, 'desc' => '인연의 기운이 겉돌 수 있어 정성을 더 쏟아야 하는 날입니다.'],

                    // --- 활력 (생기복덕) ---
                    'sbc_group' => [
                        'is_group' => true,
                        'items' => [
                            'senggi' => ['ko' => '생기일', 'score' => 30, 'type' => 'gilsin', 'priority' => 40, 'desc' => '아이를 맞이한 후 집안에 생동감과 웃음이 넘치는 날입니다.'],
                            'bokduk' => ['ko' => '복덕일', 'score' => 30, 'type' => 'gilsin', 'priority' => 40, 'desc' => '복과 덕이 함께 들어와 아이가 복덩이 역할을 해주는 날입니다.'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
