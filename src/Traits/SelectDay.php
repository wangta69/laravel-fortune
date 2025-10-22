<?php

namespace Pondol\Fortune\Traits;

use Pondol\Fortune\Services\Taekil;

trait SelectDay
{
    /**
     * 지간과 합이 좋은 갑자를 구한다.
     */
    public function _good_he($e)
    {
        #갑자 을축 병인 정묘 무진 기사 경오 신미 임신 계유
        #갑술 을해 병자 정축 무인 기묘 경진 신사 임오 계미
        #갑신 을유 병술 정해 무자 기축 경인 신묘 임진 계사
        #갑오 을미 병신 정유 무술 기해 경자 신축 임인 계묘
        #갑진 을사 병오 정미 무신 기유 경술 신해 임자 계축
        #갑인 을묘 병진 정사 무오 기미 경신 신유 임술 계해
        switch ($e) {
            case '子': case '午': case '卯': case '酉':
                return [
                  '甲子','乙丑','丙寅','丁卯','戊辰','己巳','庚午','辛未','壬申','癸酉',
                  '甲午','乙未','丙申','丁酉','戊戌','己亥','庚子','辛丑','壬寅','癸卯'
                ];
            case '辰': case '戌': case '丑': case '未':
                return [
                  '甲辰','乙巳','丙午','丁未','戊申','己酉','庚戌','辛亥','壬子','癸丑',
                  '甲戌','乙亥','丙子','丁丑','戊寅','己卯','庚辰','辛巳','壬午','癸未'
                ];
            case '寅': case '申': case '巳': case '亥':
                return [
                  '甲寅','乙卯','丙辰','丁巳','戊午','己未','庚申','辛酉','壬戌','癸亥',
                  '甲申','乙酉','丙戌','丁亥','戊子','己丑','庚寅','辛卯','壬辰','癸巳'
                ];
        }

    }

    /**
    * 황도 구하기
    * 가장 길한 날의 하나로 청룡황도, 명당황도, 금궤황도, 대덕황도, 옥당황도, 사명황도가 있음.
     * (천강,하괴등의 흉신을 제할수 있음)
    */
    public function _whangdo($month_e, $day_e, &$titles, &$scores)
    {
        $whangdo_map = [
            '子' => ['申' => '청룡', '酉' => '명당', '子' => '금궤', '丑' => '천덕', '卯' => '옥당', '午' => '사명'],
            '丑' => ['戌' => '청룡', '亥' => '명당', '寅' => '금궤', '卯' => '천덕', '巳' => '옥당', '申' => '사명'],
            '寅' => ['子' => '청룡', '丑' => '명당', '辰' => '금궤', '巳' => '천덕', '未' => '옥당', '戌' => '사명'],
            '卯' => ['寅' => '청룡', '卯' => '명당', '午' => '금궤', '未' => '천덕', '酉' => '옥당', '子' => '사명'],
            '辰' => ['辰' => '청룡', '巳' => '명당', '申' => '금궤', '酉' => '천덕', '亥' => '옥당', '寅' => '사명'],
            '巳' => ['午' => '청룡', '未' => '명당', '戌' => '금궤', '亥' => '천덕', '丑' => '옥당', '辰' => '사명'],
            '午' => ['申' => '청룡', '酉' => '명당', '子' => '금궤', '丑' => '천덕', '卯' => '옥당', '午' => '사명'],
            '未' => ['戌' => '청룡', '亥' => '명당', '寅' => '금궤', '卯' => '천덕', '巳' => '옥당', '申' => '사명'],
            '申' => ['子' => '청룡', '丑' => '명당', '辰' => '금궤', '巳' => '천덕', '未' => '옥당', '戌' => '사명'],
            '酉' => ['寅' => '청룡', '卯' => '명당', '午' => '금궤', '未' => '천덕', '酉' => '옥당', '子' => '사명'],
            '戌' => ['辰' => '청룡', '巳' => '명당', '申' => '금궤', '酉' => '천덕', '亥' => '옥당', '寅' => '사명'],
            '亥' => ['午' => '청룡', '未' => '명당', '戌' => '금궤', '亥' => '천덕', '丑' => '옥당', '辰' => '사명'],
        ];

        if (isset($whangdo_map[$month_e][$day_e])) {
            $name = $whangdo_map[$month_e][$day_e].'황도';
            $titles['whangdo'] = ['ko' => $name, 'desc' => '황제가 다니는 길한 날로, 만사가 순조롭게 풀리는 길일입니다.', 'type' => 'gilsin'];
            $scores['whangdo'] = 30;
        }
    }


    /**
    * 생기복덕천의 산출(81세까지)
    *  $my_age 생기[$senggi_01 $senggi_02]  복덕[$bokduk_01  $bokduk_02 ] 천의[$cheneu_01  $cheneu_02]
    * _cal2
    */
    public function _senggiBokdukCheneu($my_age, $gender)
    {
        switch ($gender) {
            case 'M':
                switch ($my_age) {
                    case 2: case 10: case 18: case 26: case 34: case 42: case 50: case 58: case 66: case 74:
                        return ['senggi' => ['戌', '亥'], 'bokduk' => ['未', '申'], 'cheneu' => ['午']];
                    case 9: case 17: case 25: case 33: case 41: case 49: case 57: case 65: case 73: case 81:
                        return ['senggi' => ['丑', '寅'], 'bokduk' => ['酉'], 'cheneu' => ['辰', '巳']];
                    case 1: case 8: case 16: case 24: case 32: case 40: case 48: case 56: case 64: case 72: case 80:
                        return ['senggi' => ['卯'], 'bokduk' => ['辰', '巳'], 'cheneu' => ['酉']];
                    case 7: case 15: case 23: case 31: case 39: case 47: case 55: case 63: case 71: case 79:
                        return ['senggi' => ['子'], 'bokduk' => ['午'], 'cheneu' => ['未', '申']];
                    case 6: case 14: case 22: case 30: case 38: case 46: case 54: case 62: case 70: case 78:
                        return ['senggi' => ['午'], 'bokduk' => ['戌', '亥'], 'cheneu' => ['子']];
                    case 5: case 13: case 21: case 29: case 37: case 45: case 53: case 61: case 69: case 77:
                        return ['senggi' => ['未', '申'], 'bokduk' => ['戌', '亥'], 'cheneu' => ['子']];
                    case 4: case 12: case 20: case 28: case 36: case 44: case 52: case 60: case 68: case 76:
                        return ['senggi' => ['辰', '巳'], 'bokduk' => ['卯'], 'cheneu' => ['丑', '寅']];
                    case 3: case 11: case 19: case 27: case 35: case 43: case 51: case 59: case 67: case 75:
                        return ['senggi' => ['酉'], 'bokduk' => ['丑', '寅'], 'cheneu' => ['卯']];
                }
                break;
            case 'W':
                switch ($my_age) {
                    case 3: case 10: case 18: case 26: case 34: case 42: case 50: case 58: case 66: case 74:
                        return ['senggi' => ['戌', '亥'], 'bokduk' => ['未', '申'], 'cheneu' => ['午', '']];
                    case 4: case 11: case 19: case 27: case 35: case 43: case 51: case 59: case 67: case 75:
                        return ['senggi' => ['丑', '寅'], 'bokduk' => ['酉', ''], 'cheneu' => ['辰', '巳']];
                    case 5: case 12: case 20: case 28: case 36: case 44: case 52: case 60: case 68: case 76:
                        return ['senggi' => ['卯', ''], 'bokduk' => ['辰', '巳'], 'cheneu' => ['酉', '']];
                    case 6: case 13: case 21: case 29: case 37: case 45: case 53: case 61: case 69: case 77:
                        return ['senggi' => ['子', ''], 'bokduk' => ['午', ''], 'cheneu' => ['未', '申']];
                    case 7: case 14: case 22: case 30: case 38: case 46: case 54: case 62: case 70: case 78:
                        return ['senggi' => ['午'], 'bokduk' => ['戌', '亥'], 'cheneu' => ['子']];
                    case 15: case 23: case 31: case 39: case 47: case 55: case 63: case 71: case 79:
                        return ['senggi' => ['未', '申'], 'bokduk' => ['戌', '亥'], 'cheneu' => ['子']];
                    case 1: case 8: case 16: case 24: case 32: case 40: case 48: case 56: case 64: case 72: case 80:
                        return ['senggi' => ['辰', '巳'], 'bokduk' => ['卯'], 'cheneu' => ['丑', '寅']];
                    case 2: case 9: case 17: case 25: case 33: case 41: case 49: case 57: case 65: case 73: case 81:
                        return ['senggi' => ['酉'], 'bokduk' => ['丑', '寅'], 'cheneu' => ['卯']];
                }
                break;
        }

        return ['senggi' => [], 'bokduk' => [], 'cheneu' => []];

    }

    /**
     * 이사 방향 구하기
     */
    public function _direction($my_age, $gender)
    {
        switch ($gender) {
            case 'M':
                $directions = ['동','동남','중','서북','서','동북','남','북','서남'];
                $mod = mod_zero_to_mod($my_age, 9) - 1;
                return arr_reverse_rotate($directions, $mod);
            case 'W':
                $directions = ['동남','중','서북','서','동북','남','북','서남','동'];
                $mod = mod_zero_to_mod($my_age, 9) - 1;
                return arr_reverse_rotate($directions, $mod);
        }

        return $directions;
    }


    /**
    * 십악대패 구하기
    * 십악대패살(十惡大敗煞)은 악할 악(惡), 깨뜨릴 패(敗)를 쓰며,
    * 10가지의 악한 기운으로 인해 크게 실패하게 된다는 흉살을 의미합니다.
    */
    public function _sipak($year_h, $day_he, $month_e, &$titles, &$scores)
    {
        $sipak_map = [
            '甲' => ['辰' => '戊戌', '申' => '癸亥', '亥' => '丙申', '子' => '辛亥'], '己' => ['辰' => '戊戌', '申' => '癸亥', '亥' => '丙申', '子' => '辛亥'],
            '乙' => ['巳' => '壬申', '戌' => '乙巳'], '庚' => ['巳' => '壬申', '戌' => '乙巳'],
            '丙' => ['辰' => '辛巳', '戌' => '庚辰'], '辛' => ['辰' => '辛巳', '戌' => '庚辰'],
            '丁' => [], '壬' => [], // 해당 없음
            '戊' => ['未' => '己丑'], '癸' => ['未' => '己丑'],
        ];

        if (isset($sipak_map[$year_h][$month_e]) && $sipak_map[$year_h][$month_e] === $day_he) {
            $titles['sipak'] = ['ko' => '십악대패', 'desc' => '열 가지 큰 실패를 부를 수 있는 매우 흉한 날입니다. 중요한 일은 피하는 것이 좋습니다.', 'type' => 'hyungsal'];
            $scores['sipak'] = -40;
        }
    }

    /**
    * 길신 > 천덕
    */
    public function _chenduk($month_e, $day_e, $day_h, &$titles, &$scores)
    {
        if (Taekil::hasChunduk($month_e, $day_h, $day_e)) {
            $titles['chenduk'] = ['ko' => '천덕귀인', 'desc' => '하늘의 덕이 함께하는 길일로, 어려움이 닥쳐도 순조롭게 풀립니다.', 'type' => 'gilsin'];
            $scores['chenduk'] = 10;
        }
    }

    /**
    * 길신 > 월덕
    */
    public function _wolduk($month_e, $day_h, &$titles, &$scores)
    {
        $result = Taekil::hasWolduk($month_e, $day_h);
        if ($result) {
            $titles['wolduk'] = ['ko' => '월덕귀인', 'desc' => '달의 덕이 함께하는 길일로, 주변의 도움을 받기 쉽습니다.', 'type' => 'gilsin'];
            $scores['wolduk'] = 10;
        }
    }

    /**
    * 길신 > 천덕합
    */
    public function _chendukhap($month_e, $day_e, $day_h, &$titles, &$scores)
    {
        if (Taekil::hasChendukHap($month_e, $day_e)) { // [오류 수정] day_h 대신 day_e 전달
            $titles['chendukhap'] = ['ko' => '천덕합', 'desc' => '천덕귀인의 기운과 합이 되는 날입니다.', 'type' => 'gilsin'];
            $scores['chendukhap'] = 5;
        }
    }


    /**
    * 길신 > 월덕합
    */
    public function _woldukhap($month_e, $day_e, $day_h, &$titles, &$scores)
    {
        $result = Taekil::hasWoldukHap($month_e, $day_h);
        if ($result) {
            $titles['woldukhap'] = ['ko' => '월덕합', 'desc' => '월덕귀인의 기운과 합이 되는 날입니다.', 'type' => 'gilsin'];
            $scores['woldukhap'] = 5;
        }
    }

    /**
    * 길신 > 생기
    */
    public function _seng($month_e, $day_e, $day_h, &$titles, &$scores)
    {
        $result = Taekil::hasSengi($month_e, $day_e);
        if ($result) {
            $titles['seng'] = ['ko' => '생기', 'ch' => '生氣', 'desc' => '활기찬 생기가 넘치는 날입니다.', 'type' => 'gilsin'];
            $scores['seng'] = 10;
        }
    }

    /**
    * 길신 > 천의
    */
    public function _chen($month_e, $day_e, &$titles, &$scores)
    {
        $result = Taekil::hasChene($month_e, $day_e);
        if ($result) {
            $titles['chen'] = ['ko' => '천의', 'ch' => '天醫', 'desc' => '하늘의 의사가 돕는 날입니다.', 'type' => 'gilsin'];
            $scores['chen'] = 10;
        }
    }

    /**
    * 흉신 > 천강
    */
    public function _chengang($month_e, $day_e, &$titles, &$scores)
    {
        $result = Taekil::hasChengang($month_e, $day_e);
        if ($result) {
            $titles['chengang'] = ['ko' => '천강', 'desc' => '하늘의 기운이 너무 강하여 막힘이 생길 수 있는 흉일입니다. (흑도일)', 'type' => 'hyungsal'];
            $scores['chengang'] = -20;
        }
    }

    /**
    * 흉신 > 하괴
    */
    public function _hague($month_e, $day_e, &$titles, &$scores)
    {
        $result = Taekil::hasHague($month_e, $day_e);
        if ($result) {
            $titles['hague'] = ['ko' => '하괴', 'desc' => '뜻밖의 재앙이나 실패가 따를 수 있는 흉일입니다. (흑도일)', 'type' => 'hyungsal'];
            $scores['hague'] = -20;
        }
    }

    /**
    * 흉신 > 지파
    */
    public function _jipa($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '亥')) ||
            (($month_e == '卯') && ($day_e == '子')) ||
            (($month_e == '辰') && ($day_e == '丑')) ||
            (($month_e == '巳') && ($day_e == '寅')) ||
            (($month_e == '午') && ($day_e == '卯')) ||
            (($month_e == '未') && ($day_e == '辰')) ||
            (($month_e == '申') && ($day_e == '巳')) ||
            (($month_e == '酉') && ($day_e == '午')) ||
            (($month_e == '戌') && ($day_e == '未')) ||
            (($month_e == '亥') && ($day_e == '申')) ||
            (($month_e == '子') && ($day_e == '酉')) ||
            (($month_e == '丑') && ($day_e == '戌'))
        ) {
            $titles['jipa'] = ['ko' => '지파', 'desc' => '땅의 기운이 깨지는 날로, 계약이나 약속이 틀어질 수 있습니다.', 'type' => 'hyungsal'];
            $scores['jipa'] = -20;
        }
    }

    /**
    * 흉신 > 나망
    */
    public function _namang($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '子')) ||
            (($month_e == '卯') && ($day_e == '申')) ||
            (($month_e == '辰') && ($day_e == '巳')) ||
            (($month_e == '巳') && ($day_e == '辰')) ||
            (($month_e == '午') && ($day_e == '戌')) ||
            (($month_e == '未') && ($day_e == '亥')) ||
            (($month_e == '申') && ($day_e == '丑')) ||
            (($month_e == '酉') && ($day_e == '申')) ||
            (($month_e == '戌') && ($day_e == '未')) ||
            (($month_e == '亥') && ($day_e == '子')) ||
            (($month_e == '子') && ($day_e == '巳')) ||
            (($month_e == '丑') && ($day_e == '申'))
        ) {
            $titles['namang'] = ['ko' => '나망', 'desc' => '하늘과 땅의 그물에 갇히는 형상으로, 일이 꼬이고 답답해질 수 있습니다.', 'type' => 'hyungsal'];
            $scores['namang'] = -20;
        }
    }

    /**
    * 흉신 > 멸몰
    */
    public function _myelmol($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '丑')) ||
            (($month_e == '卯') && ($day_e == '子')) ||
            (($month_e == '辰') && ($day_e == '亥')) ||
            (($month_e == '巳') && ($day_e == '戌')) ||
            (($month_e == '午') && ($day_e == '酉')) ||
            (($month_e == '未') && ($day_e == '申')) ||
            (($month_e == '申') && ($day_e == '未')) ||
            (($month_e == '酉') && ($day_e == '午')) ||
            (($month_e == '戌') && ($day_e == '巳')) ||
            (($month_e == '亥') && ($day_e == '辰')) ||
            (($month_e == '子') && ($day_e == '卯')) ||
            (($month_e == '丑') && ($day_e == '寅'))
        ) {
            $titles['myelmol'] = ['ko' => '멸몰', 'desc' => '물이 말라버리는 형상으로, 재물의 손실이나 소모를 의미합니다.', 'type' => 'hyungsal'];
            $scores['myelmol'] = -20;
        }
    }

    /**
    * 흉신 > 중상
    */
    public function _jungsang($month_e, $day_h, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_h == '甲')) ||
            (($month_e == '卯') && ($day_h == '乙')) ||
            (($month_e == '辰') && ($day_h == '己')) ||
            (($month_e == '巳') && ($day_h == '丙')) ||
            (($month_e == '午') && ($day_h == '丁')) ||
            (($month_e == '未') && ($day_h == '己')) ||
            (($month_e == '申') && ($day_h == '庚')) ||
            (($month_e == '酉') && ($day_h == '辛')) ||
            (($month_e == '戌') && ($day_h == '己')) ||
            (($month_e == '亥') && ($day_h == '壬')) ||
            (($month_e == '子') && ($day_h == '癸')) ||
            (($month_e == '丑') && ($day_h == '己'))
        ) {
            $titles['jungsang'] = ['ko' => '중상', 'desc' => '상복을 두 번 입는다는 뜻으로, 초상이나 슬픈 일을 피해야 하는 날입니다.', 'type' => 'hyungsal'];
            $scores['jungsang'] = -20;
        }
    }

    /**
    * 흉신 > 천구
    */
    public function _chengu($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '子')) ||
            (($month_e == '卯') && ($day_e == '丑')) ||
            (($month_e == '辰') && ($day_e == '寅')) ||
            (($month_e == '巳') && ($day_e == '卯')) ||
            (($month_e == '午') && ($day_e == '辰')) ||
            (($month_e == '未') && ($day_e == '巳')) ||
            (($month_e == '申') && ($day_e == '午')) ||
            (($month_e == '酉') && ($day_e == '未')) ||
            (($month_e == '戌') && ($day_e == '申')) ||
            (($month_e == '亥') && ($day_e == '酉')) ||
            (($month_e == '子') && ($day_e == '戌')) ||
            (($month_e == '丑') && ($day_e == '亥'))
        ) {
            $titles['chengu'] = ['ko' => '천구', 'desc' => '하늘의 개가 짖는다는 뜻으로, 시비나 구설에 휘말리기 쉽습니다.', 'type' => 'hyungsal'];
            $scores['chengu'] = -20;
        }
    }

    /**
    * 살구하기 > 천살
    */
    public function _chensal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '戌')) ||
            (($month_e == '卯') && ($day_e == '酉')) ||
            (($month_e == '辰') && ($day_e == '申')) ||
            (($month_e == '巳') && ($day_e == '未')) ||
            (($month_e == '午') && ($day_e == '午')) ||
            (($month_e == '未') && ($day_e == '巳')) ||
            (($month_e == '申') && ($day_e == '辰')) ||
            (($month_e == '酉') && ($day_e == '卯')) ||
            (($month_e == '戌') && ($day_e == '寅')) ||
            (($month_e == '亥') && ($day_e == '丑')) ||
            (($month_e == '子') && ($day_e == '子')) ||
            (($month_e == '丑') && ($day_e == '亥'))
        ) {
            $titles['chensal'] = ['ko' => '천살', 'desc' => '하늘의 재앙을 의미하며, 천재지변이나 예상치 못한 어려움이 있을 수 있습니다.', 'type' => 'hyungsal'];
            $scores['chensal'] = -15;
        }
    }

    /**
    * 살구하기 > 피마살
    */
    public function _pamasal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '子')) ||
            (($month_e == '卯') && ($day_e == '酉')) ||
            (($month_e == '辰') && ($day_e == '午')) ||
            (($month_e == '巳') && ($day_e == '卯')) ||
            (($month_e == '午') && ($day_e == '子')) ||
            (($month_e == '未') && ($day_e == '酉')) ||
            (($month_e == '申') && ($day_e == '午')) ||
            (($month_e == '酉') && ($day_e == '卯')) ||
            (($month_e == '戌') && ($day_e == '子')) ||
            (($month_e == '亥') && ($day_e == '酉')) ||
            (($month_e == '子') && ($day_e == '午')) ||
            (($month_e == '丑') && ($day_e == '卯'))
        ) {
            $titles['pamasal'] = ['ko' => '피마살', 'desc' => '상복을 입을 일이 생길 수 있다는 흉살로, 문상 등을 피하는 것이 좋습니다.', 'type' => 'hyungsal'];
            $scores['pamasal'] = -15;
        }
    }

    /**
    * 살구하기 > 수사살
    */
    public function _susasal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '戌')) ||
            (($month_e == '卯') && ($day_e == '辰')) ||
            (($month_e == '辰') && ($day_e == '亥')) ||
            (($month_e == '巳') && ($day_e == '巳')) ||
            (($month_e == '午') && ($day_e == '子')) ||
            (($month_e == '未') && ($day_e == '午')) ||
            (($month_e == '申') && ($day_e == '丑')) ||
            (($month_e == '酉') && ($day_e == '未')) ||
            (($month_e == '戌') && ($day_e == '寅')) ||
            (($month_e == '亥') && ($day_e == '申')) ||
            (($month_e == '子') && ($day_e == '卯')) ||
            (($month_e == '丑') && ($day_e == '酉'))
        ) {
            $titles['susasal'] = ['ko' => '수사살', 'desc' => '물과 관련된 재난을 암시하는 흉살입니다.', 'type' => 'hyungsal'];
            $scores['susasal'] = -10;
        }
    }

    /**
    * 살구하기 > 망라살
    */
    public function _mangrasal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '子')) ||
            (($month_e == '卯') && ($day_e == '申')) ||
            (($month_e == '辰') && ($day_e == '巳')) ||
            (($month_e == '巳') && ($day_e == '辰')) ||
            (($month_e == '午') && ($day_e == '戌')) ||
            (($month_e == '未') && ($day_e == '亥')) ||
            (($month_e == '申') && ($day_e == '丑')) ||
            (($month_e == '酉') && ($day_e == '申')) ||
            (($month_e == '戌') && ($day_e == '未')) ||
            (($month_e == '亥') && ($day_e == '子')) ||
            (($month_e == '子') && ($day_e == '巳')) ||
            (($month_e == '丑') && ($day_e == '申'))
        ) {
            $titles['mangrasal'] = ['ko' => '망라살', 'desc' => '그물에 걸린 듯 일이 꼬이고 막히는 것을 암시합니다.', 'type' => 'hyungsal'];
            $scores['mangrasal'] = -10;
        }
    }

    /**
    * 살구하기 > 천적살
    */
    public function _chenjeoksal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '辰')) ||
            (($month_e == '卯') && ($day_e == '酉')) ||
            (($month_e == '辰') && ($day_e == '寅')) ||
            (($month_e == '巳') && ($day_e == '未')) ||
            (($month_e == '午') && ($day_e == '子')) ||
            (($month_e == '未') && ($day_e == '巳')) ||
            (($month_e == '申') && ($day_e == '戌')) ||
            (($month_e == '酉') && ($day_e == '卯')) ||
            (($month_e == '戌') && ($day_e == '申')) ||
            (($month_e == '亥') && ($day_e == '丑')) ||
            (($month_e == '子') && ($day_e == '午')) ||
            (($month_e == '丑') && ($day_e == '亥'))
        ) {
            $titles['chenjeoksal'] = ['ko' => '천적살', 'desc' => '하늘의 적과 같다는 뜻으로, 하는 일마다 방해와 막힘이 많을 수 있습니다.', 'type' => 'hyungsal'];
            $scores['chenjeoksal'] = -10;
        }
    }

    /**
    * 살구하기 > 고초살
    */
    public function _gochosal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '辰')) ||
            (($month_e == '卯') && ($day_e == '丑')) ||
            (($month_e == '辰') && ($day_e == '戌')) ||
            (($month_e == '巳') && ($day_e == '未')) ||
            (($month_e == '午') && ($day_e == '卯')) ||
            (($month_e == '未') && ($day_e == '子')) ||
            (($month_e == '申') && ($day_e == '酉')) ||
            (($month_e == '酉') && ($day_e == '午')) ||
            (($month_e == '戌') && ($day_e == '寅')) ||
            (($month_e == '亥') && ($day_e == '亥')) ||
            (($month_e == '子') && ($day_e == '申')) ||
            (($month_e == '丑') && ($day_e == '巳'))
        ) {
            $titles['gochosal'] = ['ko' => '고초살', 'desc' => '마른 풀과 같다는 뜻으로, 고생과 외로움을 겪기 쉬운 날입니다.', 'type' => 'hyungsal'];
            $scores['gochosal'] = -10;
        }
    }

    /**
    * 살구하기 > 귀기살
    */
    public function _gueguesal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '丑')) ||
            (($month_e == '卯') && ($day_e == '寅')) ||
            (($month_e == '辰') && ($day_e == '子')) ||
            (($month_e == '巳') && ($day_e == '丑')) ||
            (($month_e == '午') && ($day_e == '寅')) ||
            (($month_e == '未') && ($day_e == '子')) ||
            (($month_e == '申') && ($day_e == '丑')) ||
            (($month_e == '酉') && ($day_e == '寅')) ||
            (($month_e == '戌') && ($day_e == '子')) ||
            (($month_e == '亥') && ($day_e == '丑')) ||
            (($month_e == '子') && ($day_e == '寅')) ||
            (($month_e == '丑') && ($day_e == '子'))
        ) {
            $titles['gueguesal'] = ['ko' => '귀기살', 'desc' => '귀신이 곡하는 소리가 들린다는 흉일로, 좋지 않은 일이 생길 수 있습니다.', 'type' => 'hyungsal'];
            $scores['gueguesal'] = -15;
        }
    }

    /**
    * 살구하기 > 왕망살
    */
    public function _wangmangsal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '寅')) ||
            (($month_e == '卯') && ($day_e == '巳')) ||
            (($month_e == '辰') && ($day_e == '申')) ||
            (($month_e == '巳') && ($day_e == '亥')) ||
            (($month_e == '午') && ($day_e == '卯')) ||
            (($month_e == '未') && ($day_e == '午')) ||
            (($month_e == '申') && ($day_e == '酉')) ||
            (($month_e == '酉') && ($day_e == '子')) ||
            (($month_e == '戌') && ($day_e == '辰')) ||
            (($month_e == '亥') && ($day_e == '未')) ||
            (($month_e == '子') && ($day_e == '戌')) ||
            (($month_e == '丑') && ($day_e == '丑'))
        ) {
            $titles['wangmangsal'] = ['ko' => '왕망살', 'desc' => '일이 허망하게 끝날 수 있음을 암시하는 흉살입니다.', 'type' => 'hyungsal'];
            $scores['wangmangsal'] = -10;
        }
    }

    /**
    * 살구하기 > 십악살
    */
    public function _sipaksal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '卯')) ||
            (($month_e == '卯') && ($day_e == '寅')) ||
            (($month_e == '辰') && ($day_e == '丑')) ||
            (($month_e == '巳') && ($day_e == '子')) ||
            (($month_e == '午') && ($day_e == '辰')) ||
            (($month_e == '未') && ($day_e == '子')) ||
            (($month_e == '申') && ($day_e == '丑')) ||
            (($month_e == '酉') && ($day_e == '寅')) ||
            (($month_e == '戌') && ($day_e == '卯')) ||
            (($month_e == '亥') && ($day_e == '辰')) ||
            (($month_e == '子') && ($day_e == '巳')) ||
            (($month_e == '丑') && ($day_e == '辰'))
        ) {
            $titles['sipaksal'] = ['ko' => '십악살', 'desc' => '십악대패일과 유사한 개념의 흉살입니다.', 'type' => 'hyungsal'];
            $scores['sipaksal'] = -15;
        }
    }

    /**
    * 살구하기 > 월압살
    */
    public function _wolapsal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '戌')) ||
            (($month_e == '卯') && ($day_e == '酉')) ||
            (($month_e == '辰') && ($day_e == '申')) ||
            (($month_e == '巳') && ($day_e == '未')) ||
            (($month_e == '午') && ($day_e == '午')) ||
            (($month_e == '未') && ($day_e == '巳')) ||
            (($month_e == '申') && ($day_e == '辰')) ||
            (($month_e == '酉') && ($day_e == '卯')) ||
            (($month_e == '戌') && ($day_e == '寅')) ||
            (($month_e == '亥') && ($day_e == '丑')) ||
            (($month_e == '子') && ($day_e == '子')) ||
            (($month_e == '丑') && ($day_e == '亥'))
        ) {
            $titles['wolapsal'] = ['ko' => '월압살', 'desc' => '달의 기운에 억압받는다는 뜻으로, 일이 위축되고 잘 풀리지 않을 수 있습니다.', 'type' => 'hyungsal'];
            $scores['wolapsal'] = -10;
        }
    }

    /**
    * 살구하기 > 월살
    */
    public function _wolsal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '丑')) ||
            (($month_e == '卯') && ($day_e == '戌')) ||
            (($month_e == '辰') && ($day_e == '未')) ||
            (($month_e == '巳') && ($day_e == '辰')) ||
            (($month_e == '午') && ($day_e == '丑')) ||
            (($month_e == '未') && ($day_e == '戌')) ||
            (($month_e == '申') && ($day_e == '未')) ||
            (($month_e == '酉') && ($day_e == '辰')) ||
            (($month_e == '戌') && ($day_e == '丑')) ||
            (($month_e == '亥') && ($day_e == '戌')) ||
            (($month_e == '子') && ($day_e == '未')) ||
            (($month_e == '丑') && ($day_e == '辰'))
        ) {
            $titles['wolsal'] = ['ko' => '월살', 'desc' => '씨앗이 말라붙는 형상으로, 새로운 시작에 장애가 따를 수 있습니다.', 'type' => 'hyungsal'];
            $scores['wolsal'] = -15;
        }
    }

    /**
    * 살구하기 > 황사살
    */
    public function _hwangsasal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && ($day_e == '午')) ||
            (($month_e == '卯') && ($day_e == '寅')) ||
            (($month_e == '辰') && ($day_e == '子')) ||
            (($month_e == '巳') && ($day_e == '午')) ||
            (($month_e == '午') && ($day_e == '寅')) ||
            (($month_e == '未') && ($day_e == '子')) ||
            (($month_e == '申') && ($day_e == '午')) ||
            (($month_e == '酉') && ($day_e == '寅')) ||
            (($month_e == '戌') && ($day_e == '子')) ||
            (($month_e == '亥') && ($day_e == '午')) ||
            (($month_e == '子') && ($day_e == '寅')) ||
            (($month_e == '丑') && ($day_e == '子'))
        ) {
            $titles['hwangsasal'] = ['ko' => '황사살', 'desc' => '누런 모래바람과 같은 재앙을 암시하는 흉살입니다.', 'type' => 'hyungsal'];
            $scores['hwangsasal'] = -10;
        }
    }

    /**
    * 살구하기 > 홍사살
    */
    public function _hongsasal($month_e, $day_e, &$titles, &$scores)
    {
        if (
            (($month_e == '寅') && (($day_e == '申') || ($day_e == '酉'))) ||
            (($month_e == '卯') && (($day_e == '辰') || ($day_e == '巳'))) ||
            (($month_e == '辰') && (($day_e == '子') || ($day_e == '丑'))) ||
            (($month_e == '巳') && (($day_e == '申') || ($day_e == '酉'))) ||
            (($month_e == '午') && (($day_e == '辰') || ($day_e == '巳'))) ||
            (($month_e == '未') && (($day_e == '子') || ($day_e == '丑'))) ||
            (($month_e == '申') && (($day_e == '申') || ($day_e == '酉'))) ||
            (($month_e == '酉') && (($day_e == '辰') || ($day_e == '巳'))) ||
            (($month_e == '戌') && (($day_e == '子') || ($day_e == '丑'))) ||
            (($month_e == '亥') && (($day_e == '申') || ($day_e == '酉'))) ||
            (($month_e == '子') && (($day_e == '辰') || ($day_e == '巳'))) ||
            (($month_e == '丑') && (($day_e == '子') || ($day_e == '丑')))
        ) {
            $titles['hongsasal'] = ['ko' => '홍사살', 'desc' => '붉은 실과 같은 재앙을 암시하며, 주로 화재나 혈광지사를 주의해야 합니다.', 'type' => 'hyungsal'];
            $scores['hongsasal'] = -10;
        }
    }

    /**
    * 축음양불장길일
    */
    public function _chuk($month_e, $day_he, &$titles, &$scores)
    {
        #인월 : 병인 정묘 병자 무인 기묘 무자 기축 경인 신묘 경자 신축
        #묘월 : 을축 병인 병자 무인 무자 기축 경인 무술 경자 경술
        #진월 : 갑자 을축 갑술 병자 을유 무자 기축 정유 무술 기유
        #사월 : 갑자 갑술 병자 갑신 을유 무자 병신 정유 무술 무신 기유
        #오월 : 계유 갑술 계미 갑신 을유 병신 무술 무신
        #미월 : 임신 계유 갑술 임오 계미 갑신 을유 갑오
        #신월 : 임신 계유 임오 계미 갑신 을유 계사 갑오 을사
        #유월 : 신미 임신 신사 임오 계미 갑신 임진 계사 갑오
        #술월 : 경오 신미 경진 신사 임오 계미 신묘 임진 계사 계묘
        #해월 : 경오 경진 신사 임오 경인 신묘 임진 계사 임인 계묘
        #자월 : 정묘 기사 기묘 경진신사 기축 경인 신묘 임진 신축 임인 정사
        #축월 : 병인 정묘 무진 병자 무인 기묘 경진 무자 기축 경인 신묘 경자 신축 병진 정사 기사 신사

        // 음양
        $chuk_map = [
            '寅' => ['丙寅','丁卯','丙子','戊寅','己卯','戊子','己丑','庚寅','辛卯','庚子','辛丑'],
            '卯' => ['乙丑','丙寅','丙子','戊寅','戊子','己丑','庚寅','戊戌','庚子','庚戌'],
            '辰' => ['甲子','乙丑','甲戌','丙子','乙酉','戊子','己丑','丁酉','戊戌','己酉'],
            '巳' => ['甲子','甲戌','丙子','甲申','乙酉','戊子','丙申','丁酉','戊戌','戊申','己酉'],
            '午' => ['癸酉','甲戌','癸未','甲申','乙酉','丙申','戊戌','戊申'],
            '未' => ['壬申','癸酉','甲戌','壬午','癸未','甲申','乙酉','甲午'],
            '申' => ['壬申','癸酉','壬午','癸未','甲申','乙酉','癸巳','甲午','乙巳'],
            '酉' => ['辛未','壬申','辛巳','壬午','癸未','甲申','壬辰','癸巳','甲午'],
            '戌' => ['庚午','辛未','庚辰','辛巳','壬午','癸未','辛卯','壬辰','癸巳','癸卯'],
            '亥' => ['庚午','庚辰','辛巳','壬午','庚寅','辛卯','壬辰','癸巳','壬寅','癸卯'],
            '子' => ['丁卯','己巳','己卯','庚辰','辛巳','己丑','庚寅','辛卯','壬辰','辛丑','壬寅','丁巳'],
            '丑' => ['丙寅','丁卯','戊辰','丙子','戊寅','己卯','庚辰','戊子','己丑','庚寅','辛卯','庚子','辛丑','丙辰','丁巳','己巳','辛巳'], // [오류 수정]
        ];

        if (isset($chuk_map[$month_e]) && in_array($day_he, $chuk_map[$month_e])) {
            $titles['chuk'] = ['ko' => '축음양불장길', 'desc' => '음양의 기운이 조화로워 혼인이나 이사에 특별히 좋다고 알려진 날입니다.', 'type' => 'gilsin'];
            $scores['chuk'] = 25;
        }
    }

    /**
     * 신구(新舊) 길일: 새 집(신가)과 헌 집(구가) 이사에 좋은 날을 판별합니다.
     */
    public function _singu($day_he, &$titles, &$scores)
    {
        // 새 집(신가)과 헌 집(구가) 이사에 모두 좋은 날
        $singu_both_list = [
            '甲子', '乙丑', '丙寅', '庚午', '乙酉', '庚寅', '壬辰', '癸巳',
            '壬寅', '癸卯', '丙午', '庚戌', '乙卯', '丙辰', '丁巳', '己未', '庚申'
        ];

        // 새 집(신가) 이사에만 좋은 날
        $singu_new_only_list = [
            '丁卯', '己巳', '辛未', '甲戌', '乙亥', '癸未', '甲申', '庚子',
            '丁未', '甲寅', '辛酉'
        ];

        if (in_array($day_he, $singu_both_list)) {
            $titles['singu'] = [
                'ko' => '신구길일',
                'ch' => '新舊吉日',
                'desc' => '새 집으로 이사하거나 기존에 살던 집으로 돌아가는 이사 모두에 좋은 길일입니다.',
                'type' => 'gilsin'
            ];
            $scores['singu'] = 25;
        } elseif (in_array($day_he, $singu_new_only_list)) {
            $titles['singu'] = [
                'ko' => '신가길일',
                'ch' => '新家吉日',
                'desc' => '새 집으로 이사하기에 좋은 길일입니다. (헌 집으로 가는 이사에는 해당되지 않음)',
                'type' => 'gilsin'
            ];
            $scores['singu'] = 25;
        }
    }

    /**
    * 대리월 방모씨 방옹고 방녀부모 방부주 방녀신
    -가취월(家取月) : 결혼하기에 좋은 달을 가리는 방법
 .대이월 : 혼레가 아주 길한 달
 .방매씨 : 결혼을 해도 무방한 달
 .방옹고 : 시부모에게 불리합니다 - 단,시부모가 없음면 결혼할 수 있는 달
 .방녀부모 : 친정부모에게 불리한 달
 .방부주 : 신랑에게 불리한 달
 .방녀신 : 신부에게 나쁜 달
    */
    public function _dae($year_e, $month_e, &$titles, &$scores)
    {
        $result = $this->getDaeWolInfo($year_e, $month_e);
        if ($result) {
            $titles['dae'] = ['ko' => $result['ko'], 'desc' => $result['desc'], 'type' => $result['type']];
            $scores['dae'] = $result['score'];
        }

    }

    private function getDaeWolInfo($year_e, $month_e)
    {
        $map = [
            '子' => ['子' => '방녀신', '丑' => '대리월', '寅' => '방매씨', '卯' => '방옹고', '辰' => '방녀부모', '巳' => '방부주'],
            '丑' => ['午' => '대리월', '未' => '방녀신', '申' => '방부주', '酉' => '방녀부모', '戌' => '방옹고', '亥' => '방매씨'],
            '寅' => ['子' => '방녀부모', '丑' => '방부주', '寅' => '방녀신', '卯' => '대리월', '辰' => '방매씨', '巳' => '방옹고'],
            '卯' => ['午' => '방옹고', '未' => '방모씨', '申' => '대리월', '酉' => '방녀신', '戌' => '방부주', '亥' => '방녀부모'],
            '辰' => ['子' => '방모씨', '丑' => '방옹고', '寅' => '방녀부모', '卯' => '방부주', '辰' => '방녀신', '巳' => '대리월'],
            '巳' => ['午' => '방부주', '未' => '방녀부모', '申' => '방옹고', '酉' => '방모씨', '戌' => '대리월', '亥' => '방녀신'],
        ];
        // 육합 관계를 이용하여 나머지 6개 지지 확장
        $map['午'] = $map['子'];
        $map['未'] = $map['丑'];
        $map['申'] = $map['寅'];
        $map['酉'] = $map['卯'];
        $map['戌'] = $map['辰'];
        $map['亥'] = $map['巳'];

        $type = $map[$year_e][$month_e] ?? null;

        switch ($type) {
            case '대리월': return ['ko' => '대리월(大利月)', 'desc' => '신부에게 결혼하기 가장 좋은 달입니다.', 'type' => 'gilsin', 'score' => 20];
            case '방매씨': return ['ko' => '방매씨(妨媒氏)', 'desc' => '중매인에게 불리한 달로, 결혼 당사자에게는 무방합니다.', 'type' => 'junglip', 'score' => 0];
            case '방옹고': return ['ko' => '방옹고(妨翁姑)', 'desc' => '시부모에게 불리할 수 있는 달입니다.', 'type' => 'junglip', 'score' => 0];
            case '방녀부모': return ['ko' => '방녀부모(妨女父母)', 'desc' => '친정부모에게 불리할 수 있는 달입니다.', 'type' => 'junglip', 'score' => 0];
            case '방부주': return ['ko' => '방부주(妨夫主)', 'desc' => '신랑에게 불리할 수 있는 달입니다.', 'type' => 'hyungsal', 'score' => -20];
            case '방녀신': return ['ko' => '방녀신(妨女身)', 'desc' => '신부 본인에게 불리한 달입니다.', 'type' => 'hyungsal', 'score' => -20];
            default: return null;
        }
    }

    /**
     * 천사일: 하늘이 모든 죄를 사하는 대길일
     */
    public function _cheonsa($month_e, $day_he, &$titles, &$scores)
    {
        $cheonsa_map = [
            '寅' => '戊寅', '卯' => '甲午', '辰' => '戊寅', '巳' => '甲午', '午' => '戊寅',
            '未' => '甲午', '申' => '戊申', '酉' => '甲子', '戌' => '戊申', '亥' => '甲子',
            '子' => '戊申', '丑' => '甲子'
        ];
        if (isset($cheonsa_map[$month_e]) && $cheonsa_map[$month_e] === $day_he) {
            $titles['cheonsa'] = ['ko' => '천사일', 'desc' => '하늘이 모든 허물을 용서하는 날로, 만사에 길한 최고의 길일 중 하나입니다.', 'type' => 'gilsin'];
            $scores['cheonsa'] = 50;
        }
    }

    /**
    * 여명부주(女命夫主): 신부 띠 기준으로 남편에게 해로운 날
    * @param string $female_year_e 신부의 년지(띠)
    * @param string $day_he        결혼하려는 날의 일주 간지 (예: '甲子')
    * @param array &$titles
    * @param array &$scores
    */
    public function _yeomyeongbuju($female_year_e, $day_he, &$titles, &$scores)
    {
        $buju_map = [
            '子' => ['巳', '亥'], '丑' => ['辰', '戌'], '寅' => ['卯', '酉'],
            '卯' => ['寅', '申'], '辰' => ['丑', '未'], '巳' => ['子', '午'],
            '午' => ['亥', '巳'], '未' => ['戌', '辰'], '申' => ['酉', '卯'],
            '酉' => ['申', '寅'], '戌' => ['未', '丑'], '亥' => ['午', '子'],
        ];
        if (isset($buju_map[$female_year_e]) && in_array(substr($day_he, 1, 1), $buju_map[$female_year_e])) {
            $titles['yeomyeongbuju'] = ['ko' => '여명부주', 'desc' => '신부의 기운이 신랑에게 해를 끼칠 수 있는 날로, 피하는 것이 좋습니다.', 'type' => 'hyungsal'];
            $scores['yeomyeongbuju'] = -30;
        }
    }

    /**
     *
     * 남명부처(男命夫妻): 신랑 띠 기준으로 아내에게 해로운 날
     */
    public function _nammyeongbucheo($male_year_e, $day_he, &$titles, &$scores)
    {
        $bucheo_map = [
            '子' => ['未', '丑'], '丑' => ['午', '子'], '寅' => ['巳', '亥'],
            '卯' => ['辰', '戌'], '辰' => ['卯', '酉'], '巳' => ['寅', '申'],
            '午' => ['丑', '未'], '未' => ['子', '午'], '申' => ['亥', '巳'],
            '酉' => ['戌', '辰'], '戌' => ['酉', '卯'], '亥' => ['申', '寅'],
        ];

        $day_e = substr($day_he, 1, 1);

        if (isset($bucheo_map[$male_year_e]) && in_array($day_e, $bucheo_map[$male_year_e])) {
            $titles['nammyeongbucheo'] = ['ko' => '남명부처', 'desc' => '신랑의 기운이 신부에게 해를 끼칠 수 있는 날로, 피하는 것이 좋습니다.', 'type' => 'hyungsal'];
            $scores['nammyeongbucheo'] = -30;
        }
    }

    /**
    * 월기일
    * 월기일 매월 초5일 14일 23 일
    */
    public function _wolgi($lunarday, &$titles, &$scores)
    {
        if (($lunarday == '05') || ($lunarday == '14') || ($lunarday == '23')) {
            $titles['wolgi'] = ['ko' => '월기일', 'desc' => '매월 정해진 흉일로, 중요한 일을 피하는 것이 좋습니다.', 'type' => 'hyungsal'];
            $scores['wolgi'] = -30;
        }
    }

    /**
    * 인동일
    * 인동일과 인격일에는 외부사람을 집에 들이지 말아야 한다.

      그날에 사람들을 집에 들이면 까닭모를 질병,재난이 올 수 있다.
    */
    public function _indong($lunarday, &$titles, &$scores)
    {
        if (($lunarday == '01') || ($lunarday == '08') || ($lunarday == '13') || ($lunarday == '18') || ($lunarday == '23') || ($lunarday == 24) || ($lunarday == '28')) {
            $titles['indong'] = ['ko' => '인동일', 'desc' => '외부 사람을 집에 들이면 질병이나 재난이 따를 수 있다고 하여 꺼리는 날입니다.', 'type' => 'hyungsal'];
            $scores['indong'] = -20;
        }
    }

    /**
    * 가취대흉일
    */
    public function _gachui($month_e, $day_he, &$titles, &$scores)
    {
        $has_gachui = false;
        if (
            (($month_e == '寅') || ($month_e == '卯') || ($month_e == '辰')) &&
            (($day_he == '甲子') || ($day_he == '乙丑'))) {
            $has_gachui = true;
        } elseif (
            (($month_e == '巳') || ($month_e == '午') || ($month_e == '未')) &&
            (($day_he == '丙子') || ($day_he == '丁丑'))) {
            $has_gachui = true;
        } elseif (
            (($month_e == '申') || ($month_e == '酉') || ($month_e == '戌')) &&
            (($day_he == '庚子') || ($day_he == '辛丑'))) {
            $has_gachui = true;
        } elseif (
            (($month_e == '亥') || ($month_e == '子') || ($month_e == '丑')) &&
            (($day_he == '壬子') || ($day_he == '癸丑'))) {
            $has_gachui = true;
        }

        if ($has_gachui) {
            $titles['gachui'] = ['ko' => '가취대흉일', 'desc' => '혼인에 매우 흉한 날로, 다른 중요한 일에도 참고합니다.', 'type' => 'hyungsal'];
            $scores['gachui'] = -30;
        }
    }

    /**
    * 해일
    */
    public function _haeil($day_e, &$titles, &$scores)
    {
        if ($day_e == '亥') {
            $titles['haeil'] = ['ko' => '해일', 'desc' => '매월 돼지(亥)날은 일반적으로 이사를 피하는 날 중 하나입니다.', 'type' => 'hyungsal'];
            $scores['haeil'] = -20;
        }
    }
}
