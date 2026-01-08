<?php

namespace Pondol\Fortune\Services;

/**
 * 자미두수 명반(命盤)을 포국하고 분석하는 서비스 클래스
 *
 * @usage
 * $saju = Saju::...->create();
 * $today = Saju::...->create();
 *
 * // 전체 명반 데이터 생성
 * $myungban = JamiDusu::myungbanData($saju, $today);
 *
 * // 특정 궁의 주성만 간단히 확인
 * $jamiData = JamiDusu::getdefaultData(...);
 * $myungGungInfo = JamiDusu::jusungMyung($jamiData); // 명궁 정보
 */
class JamiDusu
{
    /**
     * [PUBLIC] 화면에 표시할 전체 명반(命盤) 데이터를 생성합니다.
     *
     * @param  object  $saju  사용자의 Saju 객체
     * @param  object  $today  오늘의 Saju 객체
     * @return \stdClass 명반 전체 데이터
     */
    public function myungbanData($saju, $today)
    {

        $gender = $saju->gender;
        $yyyymmdd = $saju->lunar;
        $year_h = $saju->get_h('year');
        $hour_e = $saju->get_e('hour');
        $month_e = $saju->get_e('month');
        $year_e = $saju->get_e('year');
        $age = $saju->korean_age;

        // $today = Saju::ymdhi(date('Y-m-d H:i'))->create();
        [$lunar_year, $lunar_month, $lunar_day] = explode('-', $yyyymmdd);

        $data = new \stdclass;
        $data->arr = []; // 12 명반에 들어갈 데이타를 넣어둔다.

        $current_ymd = date('Ymd');
        $umyear_e = $today->get_e('year');

        // 기본적인 명반 만들기
        $gabja = $this->basic($year_h);
        $data->arr['gabja'] = $gabja;

        // 명궁, 신궁찾기
        $myungsin = $this->myungsin($hour_e, (int) $lunar_month);
        $myung = $myungsin['myung']; //  명궁
        $sin = $myungsin['sin']; // 신궁
        $data->arr['myung'] = $myung;
        $data->arr['sin'] = $sin;

        // 명국
        $data->myung_guk = $this->getMyeongguk($myung, $gabja);
        // 명주
        $data->myung_ju = $this->myung_ju($myung);
        // 신주
        $data->sin_ju = $this->sin_ju($year_e);
        // 양음
        $yangum = $this->yangum($gender, $year_h);
        // 대한
        $daehan = $this->daehan($myung, $yangum, $data->myung_guk);
        $data->arr['daehan'] = $daehan;
        // 현재의 대한
        $data->current_daehan = $this->current_daehan($daehan, $gabja, $yangum, $age);
        $data->arr['age'] = $this->ages($age, $today->get_e('year'));

        // 천괘, 천월
        $result = $this->cheangueCheanwol($year_h);
        $data->arr['cheangue'] = $result['cheangue']; // 천괘
        $jamiStatusMap = ['', '묘', '', '', '묘', '', '', '', '', '왕', '왕', '왕'];
        $data->arr['cheangue_14'] = $this->getJungsungStatus($data->arr['cheangue'], $jamiStatusMap);

        $data->arr['cheanwol'] = $result['cheanwol']; // 천월
        $jamiStatusMap = ['왕', '', '', '왕', '', '왕', '묘', '묘', '', '', '', ''];
        $data->arr['cheanwol_14'] = $this->getJungsungStatus($data->arr['cheanwol'], $jamiStatusMap);

        // 1. 자미성 찾기
        $data->arr['jami'] = $this->jami($data->myung_guk, $lunar_day);
        $jamiStatusMap = ['묘', '왕', '함', '왕', '묘', '묘', '왕', '평', '한', '왕', '평', '묘'];
        $data->arr['jami_14'] = $this->getJungsungStatus($data->arr['jami'], $jamiStatusMap);

        // 2. 나머지 자미성계 찾기
        $result = $this->jamis($data->arr['jami']); // (천기, 태양, 무곡, 천동, 염정)
        $data->arr['cheanbu'] = $result['cheanbu']; // 천부

        // 2.1. 천부성 묘왕리함
        $jamiStatusMap = ['묘', '평', '묘', '평', '왕', '묘', '평', '함', '묘', '왕', '묘', '묘'];
        $data->arr['cheanbu_14'] = $this->getJungsungStatus($data->arr['cheanbu'], $jamiStatusMap);

        // 2.2. 천기
        $data->arr['chengi'] = $result['chengi']; // 천기
        $jamiStatusMap = ['왕', '왕', '묘', '평', '묘', '함', '평', '왕', '묘', '평', '묘', '함'];
        $data->arr['chengi_14'] = $this->getJungsungStatus($data->arr['chengi'], $jamiStatusMap);

        // 2.3. 태양
        $data->arr['taeyang'] = $result['taeyang']; // 태양
        $jamiStatusMap = ['왕', '묘', '왕', '왕', '묘', '평', '한', '한', '함', '함', '함', '함'];
        $data->arr['taeyang_14'] = $this->getJungsungStatus($data->arr['taeyang'], $jamiStatusMap);

        // 2.4. 무곡
        $data->arr['mugok'] = $result['mugok']; // 무곡
        $jamiStatusMap = ['한', '함', '묘', '평', '왕', '묘', '평', '왕', '묘', '평', '왕', '묘'];
        $data->arr['mugok_14'] = $this->getJungsungStatus($data->arr['mugok'], $jamiStatusMap);

        // 2.5. 천동
        $data->arr['chendong'] = $result['chendong']; // 천동
        $jamiStatusMap = ['한', '묘', '평', '묘', '함', '함', '왕', '평', '평', '묘', '왕', '함'];
        $data->arr['chendong_14'] = $this->getJungsungStatus($data->arr['chendong'], $jamiStatusMap);

        // 2.6. 염정
        $data->arr['yeamjung'] = $result['yeamjung']; // 염정
        $jamiStatusMap = ['묘', '한', '왕', '함', '평', '묘', '묘', '평', '왕', '함', '평', '왕'];
        $data->arr['yeamjung_14'] = $this->getJungsungStatus($data->arr['yeamjung'], $jamiStatusMap);

        // # 3. 나머지 천부성계 찾기
        $result = $this->cheanbus($data->arr['cheanbu']);

        // 3.1. 태음
        $data->arr['taeum'] = $result['taeum']; // 태음
        $jamiStatusMap = ['한', '함', '한', '함', '함', '평', '평', '왕', '왕', '묘', '묘', '묘'];
        $data->arr['taeum_14'] = $this->getJungsungStatus($data->arr['taeum'], $jamiStatusMap);

        // 3.2. 탐랑
        $data->arr['tamrang'] = $result['tamrang']; // 탐랑
        $jamiStatusMap = ['평', '지', '묘', '함', '왕', '묘', '평', '평', '묘', '함', '왕', '묘'];
        $data->arr['tamrang_14'] = $this->getJungsungStatus($data->arr['tamrang'], $jamiStatusMap);

        // 3.3. 거문
        $data->arr['geamun'] = $result['geamun']; // 거문
        $jamiStatusMap = ['묘', '묘', '평', '평', '왕', '함', '묘', '묘', '왕', '왕', '왕', '왕'];
        $data->arr['geamun_14'] = $this->getJungsungStatus($data->arr['geamun'], $jamiStatusMap);

        // 3.4. 천상
        $data->arr['cheansang'] = $result['cheansang']; // 천상
        $jamiStatusMap = ['묘', '함', '왕', '평', '왕', '한', '묘', '함', '한', '평', '묘', '묘'];
        $data->arr['cheansang_14'] = $this->getJungsungStatus($data->arr['cheansang'], $jamiStatusMap);

        // 3.5. 천량
        $data->arr['cheanryang'] = $result['cheanryang']; // 천량
        $jamiStatusMap = ['묘', '묘', '왕', '함', '묘', '왕', '함', '지', '왕', '함', '묘', '왕'];
        $data->arr['cheanryang_14'] = $this->getJungsungStatus($data->arr['cheanryang'], $jamiStatusMap);

        // 3.6. 칠살
        $data->arr['chilsal'] = $result['chilsal']; // 칠살
        $jamiStatusMap = ['묘', '함', '왕', '평', '왕', '왕', '묘', '한', '묘', '평', '왕', '묘'];
        $data->arr['chilsal_14'] = $this->getJungsungStatus($data->arr['chilsal'], $jamiStatusMap);

        // 3.7. 파군
        $data->arr['pagun'] = $result['pagun']; // 파군
        $jamiStatusMap = ['묘', '한', '왕', '함', '평', '묘', '묘', '평', '왕', '함', '평', '왕'];
        $data->arr['pagun_14'] = $this->getJungsungStatus($data->arr['pagun'], $jamiStatusMap);

        // # 4. 12궁 찾기
        $data->arr['gung'] = $this->gung($myung);

        // # 녹존, 경양 타라 찾기
        $result = $this->nokGungTara($year_h);
        // 5.1. 녹존
        $data->arr['nokjon'] = $result['nokjon']; // 녹존
        $jamiStatusMap = ['묘', '왕', '', '묘', '왕', '', '묘', '왕', '', '묘', '왕', ''];
        $data->arr['nokjon_14'] = $this->getJungsungStatus($data->arr['nokjon'], $jamiStatusMap);
        // 5.2. 경양
        $data->arr['gyungryang'] = $result['gyungryang']; // 경양
        $jamiStatusMap = ['', '함', '묘', '', '평', '묘', '', '함', '묘', '', '함', '묘'];
        $data->arr['gyungryang_14'] = $this->getJungsungStatus($data->arr['gyungryang'], $jamiStatusMap);
        // 5.3. 타라
        $data->arr['tara'] = $result['tara']; // 타라
        $jamiStatusMap = ['함', '', '묘', '함', '', '묘', '함', '', '묘', '함', '', '묘'];
        $data->arr['tara_14'] = $this->getJungsungStatus($data->arr['tara'], $jamiStatusMap);
        // 5.4. 양금
        $data->arr['cheanma'] = $this->cheanma($year_e); // 천마
        $jamiStatusMap = ['왕', '', '', '평', '', '', '왕', '', '', '평', '', ''];
        $data->arr['cheanma_14'] = $this->getJungsungStatus($data->arr['cheanma'], $jamiStatusMap);

        // $data->arr['mungok'] = $this->mungok_h($year_h); // 문곡
        // 5.5. 문곡
        $data->arr['mungok'] = $this->mungok_e($hour_e); // 문곡
        $jamiStatusMap = ['평', '왕', '묘', '묘', '함', '왕', '평', '묘', '함', '왕', '묘', '묘'];
        $data->arr['mungok_14'] = $this->getJungsungStatus($data->arr['mungok'], $jamiStatusMap);
        // 5.6. 문창
        // $data->arr['munchang'] = $this->munchang_h($year_h); // 문창
        $data->arr['munchang'] = $this->munchang_e($hour_e);
        $jamiStatusMap = ['함', '평', '왕', '묘', '함', '평', '왕', '묘', '함', '왕', '왕', '묘'];
        $data->arr['munchang_14'] = $this->getJungsungStatus($data->arr['munchang'], $jamiStatusMap);
        // 5.7. 우필
        $data->arr['upil'] = $this->upil($lunar_month); // 우필
        $jamiStatusMap = ['왕', '함', '묘', '평', '왕', '묘', '한', '함', '묘', '평', '묘', '묘'];
        $data->arr['upil_14'] = $this->getJungsungStatus($data->arr['upil'], $jamiStatusMap);
        // 5.8. 좌보
        $data->arr['jabo'] = $this->jabo($lunar_month); // 좌보
        $jamiStatusMap = ['묘', '함', '묘', '평', '왕', '묘', '평', '함', '묘', '한', '왕', '묘'];
        $data->arr['jabo_14'] = $this->getJungsungStatus($data->arr['jabo'], $jamiStatusMap);

        // # 6. 화성 연성
        $result = $this->whasunYeungsung($year_e, $hour_e);
        // 6.1. 화성
        $data->arr['whasung'] = $result['whasung']; // 화성
        $jamiStatusMap = ['묘', '평', '한', '왕', '묘', '한', '함', '함', '묘', '평', '평', '왕'];
        $data->arr['whasung_14'] = $this->getJungsungStatus($data->arr['whasung'], $jamiStatusMap);

        // 6.2. 연성
        $data->arr['yeungsung'] = $result['yeungsung']; // 연성
        $jamiStatusMap = ['묘', '묘', '왕', '왕', '묘', '왕', '왕', '함', '묘', '묘', '함', '함'];
        $data->arr['yeungsung_14'] = $this->getJungsungStatus($data->arr['yeungsung'], $jamiStatusMap);

        // # 7. 지공 지겁
        $result = $this->jigongJigup($hour_e);
        // 7.1. 지공
        $data->arr['jigong'] = $result['jigong']; // 지공
        $jamiStatusMap = ['함', '평', '함', '묘', '묘', '평', '묘', '묘', '함', '함', '평', '함'];
        $data->arr['jigong_14'] = $this->getJungsungStatus($data->arr['jigong'], $jamiStatusMap);
        // 7.2. 지겁
        $data->arr['jigup'] = $result['jigup']; // 지겁
        $jamiStatusMap = ['평', '평', '함', '한', '묘', '평', '묘', '평', '평', '왕', '함', '함'];
        $data->arr['jigup_14'] = $this->getJungsungStatus($data->arr['jigup'], $jamiStatusMap);

        // # 8. 기타 여러 성
        $data->arr['cheanyo'] = $this->cheanyo($lunar_month); // 천요
        $data->arr['cheanhyung'] = $this->cheanhyung($lunar_month); // 천형
        $data->arr['cheanmu'] = $this->cheanmu($month_e); // 천무
        $data->arr['eumsal'] = $this->eumsal($lunar_month); // 음살
        $data->arr['chenwol'] = $this->chenwol($lunar_month); // 천월
        $data->arr['yeanhae'] = $this->yeanhae($year_e); // 연해
        $data->arr['haesin'] = $this->haesin($lunar_month); // 해신
        $data->arr['eunkwang'] = $this->eunkwang($data->arr['munchang'], $lunar_month); // 은광
        $data->arr['cheungui'] = $this->cheungui($this->mungok_e($hour_e), $lunar_day); // 천귀
        $data->arr['samtae'] = $this->samtae($data->arr['jabo'], $lunar_day); // 삼태
        $data->arr['paljoa'] = $this->paljoa($data->arr['upil'], $lunar_day); // 팔좌
        $data->arr['sungong'] = $this->sungong($year_h, $year_e); // 순공
        $data->arr['jealgong'] = $this->jealgong($year_h); // 절공
        $data->arr['cheanguan'] = $this->cheanguan($year_h); // 천관

        $data->arr['cheanbok'] = $this->cheanbok($year_h); // 천복
        $data->arr['hwagae'] = $this->hwagae($year_e); // 화개
        $data->arr['guepsal'] = $this->guepsal($year_e); // 겁살 // 장성 12살에서 구함
        $data->arr['hamji'] = $this->hamji($year_e); // 함지
        $data->arr['guasuck'] = $this->guasuck($year_e); // 과숙
        $data->arr['gojin'] = $this->gojin($year_e); // 고진

        $data->arr['cheanhee'] = $this->cheanhee($year_e); // 천희
        $data->arr['hongran'] = $this->hongran($year_e); // 홍란
        $data->arr['cheangok'] = $this->cheangok($year_e); // 천곡
        $data->arr['cheanhue'] = $this->cheanhue($year_e); // 천허
        $data->arr['cheansu'] = $this->cheansu($sin, $year_e); // 천수
        $data->arr['cheanjae'] = $this->cheanjae($myung, $year_e); // 천재
        $data->arr['bonggak'] = $this->bonggak($year_e); // 봉각
        $data->arr['yongji'] = $this->yongji($year_e); // 용지

        $data->arr['chensa'] = $this->chensa($data->arr['gung']); // 천사
        $data->arr['chensang'] = $this->chensang($data->arr['gung']); // 천상
        $data->arr['wolduk'] = $this->wolduk($year_e); // 월덕
        $data->arr['cheanduk'] = $this->cheanduk($year_e); // 천덕
        $data->arr['pase'] = $this->pase($year_e); // 파쇄
        $data->arr['daemo'] = $this->daemo($year_e); // 대모
        $data->arr['cheangong'] = $this->cheangong($year_e); // 천공
        $data->arr['bonggo'] = $this->bonggo($data->arr['mungok']); // 봉고
        $data->arr['taebo'] = $this->taebo($data->arr['mungok']); // 태보
        $data->arr['cheanju'] = $this->cheanju($year_h); // 천주
        $data->arr['hongyeam'] = $this->hongyeam($year_h); // 홍염
        $data->arr['biryeum'] = $this->biryeum($year_e); // 비렴

        $data->arr['baksa'] = $this->baksa($data->arr['nokjon'], $yangum); // 박사12신
        $data->arr['jangsung'] = $this->jangsung($year_e); // 장성십이신/
        $data->arr['taese'] = $this->taese($year_e); // 년태세12신
        $data->arr['unsung'] = $this->unsung($yangum, $data->myung_guk); // 십이운성

        // $data->arr['youyeon'] = $this->youyeon($umyear_e, $gabja); //  유년궁 구하기
        $data->arr['youyeon'] = $this->youyeon($year_e, $gabja);

        return $data;

    }

    /**
     * 운세데이타용 기본 명반 만들기
     * gender=M, yyyymmdd=1972-10-07, year_h=壬, hour_e=子, month_e=亥, year_e=子
     */
    public function getdefaultData($gender, $yyyymmdd, $year_h, $hour_e)
    {

        [$lunar_year, $lunar_month, $lunar_day] = explode('-', $yyyymmdd);

        $data = new \stdclass;

        // 기본적인 명반 만들기
        $gabja = $this->basic($year_h);
        $data->gabja = $gabja;

        // 명궁, 신궁찾기
        $myungsin = $this->myungsin($hour_e, (int) $lunar_month);
        $myung = $myungsin['myung']; //  명궁
        $sin = $myungsin['sin']; // 신궁
        $data->myung = $myung;
        $data->sin = $sin;

        // 명국
        $data->myung_guk = $this->getMyeongguk($myung, $gabja);

        // 14 주성 찾기 : 1.자미, 2.천기, 3.태양, 4.태음, 5.무곡, 6.칠살, 7.파군, 8.천동, 9.염정, 10.천부, 11.탐랑, 12.거문, 13.천상, 14천양
        $data->jusung14 = []; //
        // 자미성 찾기
        $data->jusung14['jami'] = $this->jami($data->myung_guk, $lunar_day); // 1. 자미

        // 나머지 자미성계 찾기
        $result = $this->jamis($data->jusung14['jami']); // (천기, 태양, 무곡, 천동, 염정)
        $data->jusung14['cheanbu'] = $result['cheanbu']; // 10.천부
        $data->jusung14['chengi'] = $result['chengi']; // 2.천기
        $data->jusung14['taeyang'] = $result['taeyang']; // 3.태양
        $data->jusung14['mugok'] = $result['mugok']; // 5.무곡
        $data->jusung14['chendong'] = $result['chendong']; // 8.천동
        $data->jusung14['yeamjung'] = $result['yeamjung']; // 9.염정

        // # 나머지 천부성계 찾기
        $result = $this->cheanbus($data->jusung14['cheanbu']);
        $data->jusung14['taeum'] = $result['taeum']; // 4.태음
        $data->jusung14['tamrang'] = $result['tamrang']; // 11.탐랑
        $data->jusung14['geamun'] = $result['geamun']; // 12.거문

        $data->jusung14['cheansang'] = $result['cheansang']; // 13.천상
        $data->jusung14['cheanryang'] = $result['cheanryang']; // 14.천량
        $data->jusung14['chilsal'] = $result['chilsal']; // 6.칠살
        $data->jusung14['pagun'] = $result['pagun']; // 7.파군

        return $data;
    }

    // ================================================================================
    // [PUBLIC] 12궁 주성 조회 메소드 그룹 (기존과 동일)
    // ================================================================================
    /** 명궁주성 및 궁 코드 가져오기 */
    public function jusungMyung($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 0); // 명궁은 명궁으로부터 0칸
    }

    /** 부모궁 코드 가져오기 */
    public function jusungBumo($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 1); // 부모궁은 명궁으로부터 시계방향 1칸
    }

    /** 복덕주성 및 궁 코드 가져오기 */
    public function jusungBokduk($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 2); // 복덕궁은 명궁으로부터 시계방향 2칸
    }

    /** 전택궁 코드 가져오기 */
    public function jusungJeuntaek($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 3); // 전택궁은 명궁으로부터 시계방향 3칸
    }

    /** 관록궁 코드 가져오기 */
    public function jusungGuanrok($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 4); // 관록궁은 명궁으로부터 시계방향 4칸
    }

    /** 노복궁 코드 가져오기 */
    public function jusungNobok($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 5); // 노복궁은 명궁으로부터 시계방향 5칸
    }

    /** 천이 궁 코드 가져오기 */
    public function jusungChene($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 6); // 노복궁은 명궁으로부터 시계방향 6칸
    }

    /** 질액 궁 코드 가져오기 */
    public function jusungJilaek($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 7); // 질액궁은 명궁으로부터 시계방향 7칸
    }

    /** 재백궁 및 궁 코드 가져오기 */
    public function jusungJaebaek($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 8); // 재잭궁은 명궁으로부터 시계방향 8칸
    }

    /** 자녀궁 및 궁 코드 가져오기 */
    public function jusungJanyeo($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 9); // 자녀궁은 명궁으로부터 시계방향 9칸
    }

    /** 부처(부부)궁 및 궁 코드 가져오기 */
    public function jusungBubu($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 10); // 부부궁은 명궁으로부터 시계방향 10칸
    }

    /** 형재궁 및 궁 코드 가져오기 */
    public function jusungHyungjae($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 11); // 형재궁은 명궁으로부터 시계방향 10칸
    }

    /** 천궁 코드 가져오기 */
    /**
     * 천궁(天宮)의 주성 및 궁 코드를 가져옵니다.
     * 천궁은 항상 술궁(戌宮)에 위치합니다.
     */
    public function jusungChenegung($jamidusu)
    {
        // 1. 술궁(戌宮)의 인덱스는 8입니다.
        // (순서: 寅=0, 卯=1, 辰=2, 巳=3, 午=4, 未=5, 申=6, 酉=7, 戌=8)
        $cheongung_index = 8;
        $gung = '戌';

        // 2. 술궁(인덱스 8)에 위치한 주성을 가져옵니다.
        $jusung14 = $this->jusung14($cheongung_index, $jamidusu->jusung14);

        // 3. 차성안궁: 천궁(술궁)에 주성이 없다면, 대궁인 진궁(辰宮)의 주성을 빌려옵니다.
        // 진궁의 인덱스는 2입니다.
        if (! $jusung14) {
            $opposite_index = 2; // 술궁의 대궁은 진궁
            $jusung14 = $this->jusung14($opposite_index, $jamidusu->jusung14);
        }

        return (object) [
            'gung' => $gung,
            'jusung14' => $jusung14,
        ];
    }

    /**
     * 14 정성의 묘왕리함 상태를 가져오는 범용 함수
     *
     * @param  array  $starPositions  별의 위치 배열 (예: $data->arr['jami'])
     * @param  array  $statusMap  묘왕리함 상태 매핑 배열
     */
    private function getJungsungStatus(array $starPositions, array $statusMap): array
    {
        $result = array_fill(0, 12, null);
        foreach ($starPositions as $index => $star) {
            if ($star && isset($statusMap[$index])) {
                $result[$index] = $statusMap[$index];
            }
        }

        return $result;
    }

    /**
     * 음력 생월을 이용하여 좌보를 구한다.
     */
    private function jabo($lunar_month)
    {
        $jabo = array_fill(0, 12, null);
        $positionMap = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1]; // 1월 -> 辰(2), 2월 -> 巳(3)...
        $index = $positionMap[(int) $lunar_month - 1];
        $jabo[$index] = '좌보';

        return $jabo;
    }

    /**
     * 음력 생월을 이용하여 우필을 구한다.
     */
    private function upil($lunar_month)
    {
        $upil = array_fill(0, 12, null);
        $positionMap = [8, 7, 6, 5, 4, 3, 2, 1, 0, 11, 10, 9]; // 1월 -> 戌(8), 2월 -> 酉(7)...
        $index = $positionMap[(int) $lunar_month - 1];
        $upil[$index] = '우필';

        return $upil;
    }

    /**
     * 천간의 년을 이용하여 문창 문곡 구하기
     * 유년문창
     */
    private function munchang_h($year_h)
    {
        $munchang = array_fill(0, 12, null);

        if ($year_h == '甲') {
            $munchang[3] = '문창';
        }
        if ($year_h == '乙') {
            $munchang[4] = '문창';
        }
        if ($year_h == '丙') {
            $munchang[6] = '문창';
        }
        if ($year_h == '丁') {
            $munchang[7] = '문창';
        }
        if ($year_h == '戊') {
            $munchang[6] = '문창';
        }
        if ($year_h == '己') {
            $munchang[7] = '문창';
        }
        if ($year_h == '庚') {
            $munchang[9] = '문창';
        }
        if ($year_h == '辛') {
            $munchang[10] = '문창';
        }
        if ($year_h == '壬') {
            $munchang[0] = '문창';
        }
        if ($year_h == '癸') {
            $munchang[1] = '문창';
        }

        return $munchang;
    }

    /**
     * 유년문곡
     */
    private function mungok_h($year_h)
    {
        $mungok = array_fill(0, 12, null);
        if ($year_h == '甲') {
            $mungok[7] = '문곡';
        }
        if ($year_h == '乙') {
            $mungok[6] = '문곡';
        }
        if ($year_h == '丙') {
            $mungok[4] = '문곡';
        }
        if ($year_h == '丁') {
            $mungok[3] = '문곡';
        }
        if ($year_h == '戊') {
            $mungok[4] = '문곡';
        }
        if ($year_h == '己') {
            $mungok[3] = '문곡';
        }
        if ($year_h == '庚') {
            $mungok[1] = '문곡';
        }
        if ($year_h == '辛') {
            $mungok[0] = '문곡';
        }
        if ($year_h == '壬') {
            $mungok[10] = '문곡';
        }
        if ($year_h == '癸') {
            $mungok[9] = '문곡';
        }

        return $mungok;
    }

    /**
     * 지지의 월시를 이용하여 문창을 구한다.
     * 유년문창
     */
    private function munchang_e($hour_e)
    {
        $munchang = array_fill(0, 12, null);
        switch ($hour_e) {
            case '子': $munchang[8] = '문창';
                break;
            case '丑': $munchang[7] = '문창';
                break;
            case '寅': $munchang[6] = '문창';
                break;
            case '卯': $munchang[5] = '문창';
                break;
            case '辰': $munchang[4] = '문창';
                break;
            case '巳': $munchang[3] = '문창';
                break;
            case '午': $munchang[2] = '문창';
                break;
            case '未': $munchang[1] = '문창';
                break;
            case '申': $munchang[0] = '문창';
                break;
            case '酉': $munchang[11] = '문창';
                break;
            case '戌': $munchang[10] = '문창';
                break;
            case '亥': $munchang[9] = '문창';
                break;
        }

        return $munchang;

    }

    /**
     * 지지의 월시를 이용하여 문창을 구한다.
     */
    private function mungok_e($hour_e)
    {
        $mungok = array_fill(0, 12, '');
        switch ($hour_e) {
            case '子': $mungok[2] = '문곡';
                break;
            case '丑': $mungok[3] = '문곡';
                break;
            case '寅': $mungok[4] = '문곡';
                break;
            case '卯': $mungok[5] = '문곡';
                break;
            case '辰': $mungok[6] = '문곡';
                break;
            case '巳': $mungok[7] = '문곡';
                break;
            case '午': $mungok[8] = '문곡';
                break;
            case '未': $mungok[9] = '문곡';
                break;
            case '申': $mungok[10] = '문곡';
                break;
            case '酉': $mungok[11] = '문곡';
                break;
            case '戌': $mungok[0] = '문곡';
                break;
            case '亥': $mungok[1] = '문곡';
                break;
        }

        return $mungok;
    }

    /**
     * 년간을 이용하여
     * 녹존 경양 타라 구하기
     * 굳이 my 가아니라 유년, 대한에도 동일 공식이 들어감
     */
    private function nokGungTara($year_h)
    {

        $nokjon = array_fill(0, 12, null);
        $gyungryang = array_fill(0, 12, null);
        $tara = array_fill(0, 12, null);
        // #####경양 타라와 함께계산戊
        switch ($year_h) {
            case '甲':
                $nokjon[0] = '녹존';
                $gyungryang[1] = '경양';
                $tara[11] = '타라';
                break;
            case '乙':
                $nokjon[1] = '녹존';
                $gyungryang[2] = '경양';
                $tara[0] = '타라';
                break;
            case '庚':
                $nokjon[6] = '녹존';
                $gyungryang[7] = '경양';
                $tara[5] = '타라';
                break;
            case '辛':
                $nokjon[7] = '녹존';
                $gyungryang[8] = '경양';
                $tara[6] = '타라';
                break;
            case '壬':
                $nokjon[9] = '녹존';
                $gyungryang[10] = '경양';
                $tara[8] = '타라';
                break;
            case '癸':
                $nokjon[10] = '녹존';
                $gyungryang[11] = '경양';
                $tara[9] = '타라';
                break;
            case '丙': case '戊':
                $nokjon[3] = '녹존';
                $gyungryang[4] = '경양';
                $tara[2] = '타라';
                break;
            case '丁': case '己':
                $nokjon[4] = '녹존';
                $gyungryang[5] = '경양';
                $tara[3] = '타라';
                break;
        }

        return [
            'nokjon' => $nokjon,
            'gyungryang' => $gyungryang,
            'tara' => $tara,
        ];
    }

    // # 천형/천요/해신/연해/천월/음살/천무
    /**
     * 천형
     */
    private function cheanhyung($lunar_month)
    {
        $map = [
            '01' => 7, '02' => 8, '03' => 9, '04' => 10, '05' => 11, '06' => 0,
            '07' => 1, '08' => 2, '09' => 3, '10' => 4, '11' => 5, '12' => 6,
        ];

        $cheanhyung = array_fill(0, 12, null);
        if (isset($map[$lunar_month])) {
            $cheanhyung[$map[$lunar_month]] = '천형';
        }

        return $cheanhyung;
    }

    /**
     * 천요 구하기
     */
    private function cheanyo($lunar_month)
    {
        $cheanyo = array_fill(0, 12, null);
        switch ($lunar_month) {
            case '01': $cheanyo[11] = '천요';
                break;
            case '02': $cheanyo[0] = '천요';
                break;
            case '03': $cheanyo[1] = '천요';
                break;
            case '04': $cheanyo[2] = '천요';
                break;
            case '05': $cheanyo[3] = '천요';
                break;
            case '06': $cheanyo[4] = '천요';
                break;
            case '07': $cheanyo[5] = '천요';
                break;
            case '08': $cheanyo[6] = '천요';
                break;
            case '09': $cheanyo[7] = '천요';
                break;
            case '10': $cheanyo[8] = '천요';
                break;
            case '11': $cheanyo[9] = '천요';
                break;
            case '12': $cheanyo[10] = '천요';
                break;
        }

        return $cheanyo;
    }

    /**
     * 해신 구하기
     */
    private function haesin($lunar_month)
    {
        $haesin = array_fill(0, 12, null);
        switch ($lunar_month) {
            case '01': case '02': $haesin[6] = '해신';
                break;
            case '03': case '04': $haesin[8] = '해신';
                break;
            case '05': case '06': $haesin[10] = '해신';
                break;
            case '07': case '08': $haesin[0] = '해신';
                break;
            case '09': case '10': $haesin[2] = '해신';
                break;
            case '11': case '12': $haesin[4] = '해신';
                break;
        }

        return $haesin;
    }

    /**
     * 연해 구하기
     */
    private function yeanhae($year_e)
    {
        $yeanhae = array_fill(0, 12, null);
        $positionMap = [
            '子' => 9, '丑' => 8, '寅' => 7, '卯' => 6, '辰' => 5, '巳' => 4,
            '午' => 3, '未' => 2, '申' => 1, '酉' => 0, '戌' => 11, '亥' => 10,
        ];

        if (isset($positionMap[$year_e])) {
            $yeanhae[$positionMap[$year_e]] = '연해';
        }

        return $yeanhae;
    }

    /**
     * 천월 구하기
     */
    private function chenwol($lunar_month)
    {
        $chenwol = array_fill(0, 12, null);
        if (($lunar_month == '04') || ($lunar_month == '09') || ($lunar_month == '12')) {
            $chenwol[0] = '천월';
        }
        if (($lunar_month == '05') || ($lunar_month == '08')) {
            $chenwol[5] = '천월';
        }
        if ($lunar_month == '02') {
            $chenwol[3] = '천월';
        }
        if ($lunar_month == '03') {
            $chenwol[2] = '천월';
        }
        if ($lunar_month == '06') {
            $chenwol[1] = '천월';
        }
        if ($lunar_month == '07') {
            $chenwol[9] = '천월';
        }
        if ($lunar_month == '10') {
            $chenwol[4] = '천월';
        }
        if (($lunar_month == '01') || ($lunar_month == '11')) {
            $chenwol[8] = '천월';
        }

        return $chenwol;
    }

    /**
     * 음살 구하기
     */
    private function eumsal($lunar_month)
    {
        $eumsal = array_fill(0, 12, null);
        if (($lunar_month == '01') || ($lunar_month == '07')) {
            $eumsal[0] = '음살';
        }
        if (($lunar_month == '02') || ($lunar_month == '08')) {
            $eumsal[10] = '음살';
        }
        if (($lunar_month == '03') || ($lunar_month == '09')) {
            $eumsal[8] = '음살';
        }
        if (($lunar_month == '04') || ($lunar_month == '10')) {
            $eumsal[6] = '음살';
        }
        if (($lunar_month == '05') || ($lunar_month == '11')) {
            $eumsal[4] = '음살';
        }
        if (($lunar_month == '06') || ($lunar_month == '12')) {
            $eumsal[2] = '음살';
        }

        return $eumsal;
    }

    /**
     * 천무구하기
     */
    private function cheanmu($month_e)
    {
        $cheanmu = array_fill(0, 12, null);
        if (($month_e == '寅') || ($month_e == '午') || ($month_e == '戌')) {
            $cheanmu[3] = '천무';
        }
        if (($month_e == '申') || ($month_e == '子') || ($month_e == '辰')) {
            $cheanmu[0] = '천무';
        }
        if (($month_e == '巳') || ($month_e == '酉') || ($month_e == '丑')) {
            $cheanmu[9] = '천무';
        }
        if (($month_e == '亥') || ($month_e == '卯') || ($month_e == '未')) {
            $cheanmu[6] = '천무';
        }

        return $cheanmu;
    }

    // ####삼태, 팔좌, 은광,천귀
    /**
     * 은광구하기
     */
    private function eunkwang($munchang, $lunar_day)
    {
        $lunar_day_temp = $lunar_day % 12;

        $eunkwang = array_fill(0, 12, '');

        $default = [11, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        foreach ($munchang as $k => $v) {
            if ($v) {

                $start_index = $k;
                $index = $default;
                for ($i = 0; $i < $k; $i++) {
                    $index_k = array_shift($index); // 맨 처음것 빼기
                    array_push($index, $index_k); // 마지막값을 맨 앞으로 넣기
                }

                switch ($lunar_day_temp) {
                    case 1: $eunkwang[$index[0]] = '은광';
                        break;
                    case 2: $eunkwang[$index[1]] = '은광';
                        break;
                    case 3: $eunkwang[$index[2]] = '은광';
                        break;
                    case 4: $eunkwang[$index[3]] = '은광';
                        break;
                    case 5: $eunkwang[$index[4]] = '은광';
                        break;
                    case 6: $eunkwang[$index[5]] = '은광';
                        break;
                    case 7: $eunkwang[$index[6]] = '은광';
                        break;
                    case 8: $eunkwang[$index[7]] = '은광';
                        break;
                    case 9: $eunkwang[$index[8]] = '은광';
                        break;
                    case 10: $eunkwang[$index[9]] = '은광';
                        break;
                    case 11: $eunkwang[$index[10]] = '은광';
                        break;
                    case 0: $eunkwang[$index[11]] = '은광';
                        break;
                }
            }
        }

        return $eunkwang;
    }

    /**
     * 천귀 구하기
     */
    private function cheungui($mungok_e, $lunar_day)
    {
        $lunar_day_temp = (int) $lunar_day % 12;
        $cheungui = array_fill(0, 12, '');
        $index = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        foreach ($mungok_e as $k => $v) {
            if ($v) {

                $start_index = $k - 1;
                if ($start_index < 0) {
                    $start_index = 11;
                }

                foreach ($index as $k1 => $v1) {
                    $index[$k1] = ($start_index + $k1) % 12;
                }

                switch ($lunar_day_temp) {
                    case 1: $cheungui[$index[0]] = '천귀';
                        break;
                    case 2: $cheungui[$index[1]] = '천귀';
                        break;
                    case 3: $cheungui[$index[2]] = '천귀';
                        break;
                    case 4: $cheungui[$index[3]] = '천귀';
                        break;
                    case 5: $cheungui[$index[4]] = '천귀';
                        break;
                    case 6: $cheungui[$index[5]] = '천귀';
                        break;
                    case 7: $cheungui[$index[6]] = '천귀';
                        break;
                    case 8: $cheungui[$index[7]] = '천귀';
                        break;
                    case 9: $cheungui[$index[8]] = '천귀';
                        break;
                    case 10: $cheungui[$index[9]] = '천귀';
                        break;
                    case 11: $cheungui[$index[10]] = '천귀';
                        break;
                    case 0: $cheungui[$index[11]] = '천귀';
                        break;
                }
            }
        }

        return $cheungui;

    }

    /**
     * 삼태구하기
     */
    private function samtae($jabo, $lunar_day)
    {
        $lunar_day_temp = (int) $lunar_day % 12;
        $samtae = array_fill(0, 12, '');

        $index = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        foreach ($jabo as $k => $v) {
            if ($v) {

                $start_index = $k;

                foreach ($index as $k1 => $v1) {
                    $index[$k1] = ($start_index + $k1) % 12;
                }

                switch ($lunar_day_temp) {
                    case 1: $samtae[$index[0]] = '삼태';
                        break;
                    case 2: $samtae[$index[1]] = '삼태';
                        break;
                    case 3: $samtae[$index[2]] = '삼태';
                        break;
                    case 4: $samtae[$index[3]] = '삼태';
                        break;
                    case 5: $samtae[$index[4]] = '삼태';
                        break;
                    case 6: $samtae[$index[5]] = '삼태';
                        break;
                    case 7: $samtae[$index[6]] = '삼태';
                        break;
                    case 8: $samtae[$index[7]] = '삼태';
                        break;
                    case 9: $samtae[$index[8]] = '삼태';
                        break;
                    case 10: $samtae[$index[9]] = '삼태';
                        break;
                    case 11: $samtae[$index[10]] = '삼태';
                        break;
                    case 0: $samtae[$index[11]] = '삼태';
                        break;
                }
            }
        }

        return $samtae;
    }

    /**
     * 팔좌 구하기
     */
    private function paljoa($upil, $lunar_day)
    {
        $lunar_day_temp = (int) $lunar_day % 12;

        $paljoa = array_fill(0, 12, '');

        $default = [0, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];
        foreach ($upil as $k => $v) {
            if ($v) {

                $start_index = $k;
                $index = $default;
                for ($i = 0; $i < $k; $i++) {
                    // $index_k = array_shift($index);
                    $index_k = array_pop($index); // 마지막깞 빼기
                    array_unshift($index, $index_k); // 마지막값을 맨 앞으로 넣기

                }

                switch ($lunar_day_temp) {
                    case 1: $paljoa[$index[0]] = '팔좌';
                        break;
                    case 2: $paljoa[$index[1]] = '팔좌';
                        break;
                    case 3: $paljoa[$index[2]] = '팔좌';
                        break;
                    case 4: $paljoa[$index[3]] = '팔좌';
                        break;
                    case 5: $paljoa[$index[4]] = '팔좌';
                        break;
                    case 6: $paljoa[$index[5]] = '팔좌';
                        break;
                    case 7: $paljoa[$index[6]] = '팔좌';
                        break;
                    case 8: $paljoa[$index[7]] = '팔좌';
                        break;
                    case 9: $paljoa[$index[8]] = '팔좌';
                        break;
                    case 10: $paljoa[$index[9]] = '팔좌';
                        break;
                    case 11: $paljoa[$index[10]] = '팔좌';
                        break;
                    case 0: $paljoa[$index[11]] = '팔좌';
                        break;
                }
            }
        }

        return $paljoa;
    }

    // cheanbok천복,cheanguan천관,jealgong절공,sungong순공
    /**
     * 천복 구하기
     */
    private function cheanbok($year_h)
    {
        $cheanbok = array_fill(0, 12, '');
        if ($year_h == '甲') {
            $cheanbok[7] = '천복';
        }
        if ($year_h == '乙') {
            $cheanbok[6] = '천복';
        }
        if ($year_h == '丙') {
            $cheanbok[10] = '천복';
        }
        if ($year_h == '丁') {
            $cheanbok[9] = '천복';
        }
        if ($year_h == '戊') {
            $cheanbok[1] = '천복';
        }
        if ($year_h == '己') {
            $cheanbok[0] = '천복';
        }
        if ($year_h == '庚') {
            $cheanbok[4] = '천복';
        }
        if ($year_h == '辛') {
            $cheanbok[3] = '천복';
        }
        if ($year_h == '壬') {
            $cheanbok[4] = '천복';
        }
        if ($year_h == '癸') {
            $cheanbok[3] = '천복';
        }

        return $cheanbok;
    }

    /**
     * 천관 구하기
     */
    private function cheanguan($year_h)
    {
        $cheanguan = array_fill(0, 12, '');
        if ($year_h == '甲') {
            $cheanguan[5] = '천관';
        }
        if ($year_h == '乙') {
            $cheanguan[2] = '천관';
        }
        if ($year_h == '丙') {
            $cheanguan[3] = '천관';
        }
        if ($year_h == '丁') {
            $cheanguan[9] = '천관';
        }
        if ($year_h == '戊') {
            $cheanguan[1] = '천관';
        }
        if ($year_h == '己') {
            $cheanguan[7] = '천관';
        }
        if ($year_h == '庚') {
            $cheanguan[9] = '천관';
        }
        if ($year_h == '辛') {
            $cheanguan[7] = '천관';
        }
        if ($year_h == '壬') {
            $cheanguan[8] = '천관';
        }
        if ($year_h == '癸') {
            $cheanguan[4] = '천관';
        }

        return $cheanguan;
    }

    /**
     * 절공 구하기
     */
    private function jealgong($year_h)
    {
        $jealgong = array_fill(0, 12, '');
        if ($year_h == '甲') {
            $jealgong[6] = '절공';
        }
        if ($year_h == '乙') {
            $jealgong[4] = '절공';
        }
        if ($year_h == '丙') {
            $jealgong[2] = '절공';
        }
        if ($year_h == '丁') {
            $jealgong[1] = '절공';
        }
        if ($year_h == '戊') {
            $jealgong[10] = '절공';
        }
        if ($year_h == '己') {
            $jealgong[7] = '절공';
        }
        if ($year_h == '庚') {
            $jealgong[4] = '절공';
        }
        if ($year_h == '辛') {
            $jealgong[3] = '절공';
        }
        if ($year_h == '壬') {
            $jealgong[0] = '절공';
        }
        if ($year_h == '癸') {
            $jealgong[11] = '절공';
        }

        return $jealgong;
    }

    /**
     * 순공 구하기
     */
    private function sungong($year_h, $year_e)
    {
        $year_he = $year_h.$year_e;
        $sungong = array_fill(0, 12, '');

        switch ($year_he) {
            case '甲子': case '丙寅': case '戊辰': case '庚午': case '壬申':
                $sungong[8] = '순공';
                break;
            case '乙丑': case '丁卯': case '己巳': case '辛未': case '癸酉':
                $sungong[9] = '순공';
                break;
            case '甲戌': case '丙子': case '戊寅': case '庚辰': case '壬午':
                $sungong[6] = '순공';
                break;
            case '乙亥': case '丁丑': case '己卯': case '辛巳': case '癸未':
                $sungong[7] = '순공';
                break;
            case '甲申': case '丙戌': case '戊子': case '庚寅': case '壬辰':
                $sungong[4] = '순공';
                break;
            case '乙酉': case '丁亥': case '己丑': case '辛卯': case '癸巳':
                $sungong[5] = '순공';
                break;
            case '甲午': case '丙申': case '戊戌': case '庚子': case '壬寅':
                $sungong[2] = '순공';
                break;
            case '乙未': case '丁酉': case '己亥': case '辛丑': case '癸卯':
                $sungong[3] = '순공';
                break;
            case '甲辰': case '丙午': case '戊申': case '庚戌': case '壬子':
                $sungong[0] = '순공';
                break;
            case '乙巳': case '丁未': case '己酉': case '辛亥': case '癸丑':
                $sungong[1] = '순공';
                break;
            case '甲寅': case '丙辰': case '戊午': case '庚申': case '壬戌':
                $sungong[10] = '순공';
                break;
            case '乙卯': case '丁巳': case '己未': case '辛酉': case '癸亥':
                $sungong[11] = '순공';
                break;
        }

        return $sungong;
    }

    /**
     * 천주구하기
     */
    private function cheanju($year_h)
    {
        $cheanju = array_fill(0, 12, '');
        if ($year_h == '甲') {
            $cheanju[3] = '천주';
        }
        if ($year_h == '丁') {
            $cheanju[3] = '천주';
        }
        if ($year_h == '己') {
            $cheanju[6] = '천주';
        }
        if ($year_h == '癸') {
            $cheanju[9] = '천주';
        }
        if ($year_h == '乙') {
            $cheanju[4] = '천주';
        }
        if ($year_h == '戊') {
            $cheanju[4] = '천주';
        }
        if ($year_h == '辛') {
            $cheanju[4] = '천주';
        }
        if ($year_h == '庚') {
            $cheanju[0] = '천주';
        }
        if ($year_h == '丙') {
            $cheanju[10] = '천주';
        }
        if ($year_h == '壬') {
            $cheanju[7] = '천주';
        }

        return $cheanju;
    }

    // hamji/guepsal/hwagae
    /**
     * 함지 구하기
     */
    private function hamji($year_e)
    {
        // 삼합(三合)에 따른 위치를 맵으로 정의
        $positionMap = [
            '寅' => 1, '午' => 1, '戌' => 1,
            '申' => 7, '子' => 7, '辰' => 7,
            '巳' => 4, '酉' => 4, '丑' => 4,
            '亥' => 0, '卯' => 0, '未' => 0,
        ];

        $position = $positionMap[$year_e] ?? -1; // 해당 지지가 없으면 -1

        return $this->placeStarByMap('함지', $position);
    }

    /**
     * 화개 구하기
     */
    private function hwagae($year_e)
    {
        $hwagae = array_fill(0, 12, '');

        if (($year_e == '寅') || ($year_e == '午') || ($year_e == '戌')) {
            $hwagae[8] = '화개';
        }
        if (($year_e == '申') || ($year_e == '子') || ($year_e == '辰')) {
            $hwagae[2] = '화개';
        }
        if (($year_e == '巳') || ($year_e == '酉') || ($year_e == '丑')) {
            $hwagae[11] = '화개';
        }
        if (($year_e == '亥') || ($year_e == '卯') || ($year_e == '未')) {
            $hwagae[5] = '화개';
        }

        return $hwagae;
    }

    /**
     * 고진
     */
    private function gojin($year_e)
    {
        $gojin = array_fill(0, 12, '');

        if (($year_e == '寅') || ($year_e == '卯') || ($year_e == '辰')) {
            $gojin[3] = '고진';
        }
        if (($year_e == '巳') || ($year_e == '午') || ($year_e == '未')) {
            $gojin[6] = '고진';
        }
        if (($year_e == '申') || ($year_e == '酉') || ($year_e == '戌')) {
            $gojin[9] = '고진';
        }
        if (($year_e == '亥') || ($year_e == '子') || ($year_e == '丑')) {
            $gojin[0] = '고진';
        }

        return $gojin;
    }

    /**
     * 과숙
     */
    private function guasuck($year_e)
    {
        $guasuck = array_fill(0, 12, '');
        if (($year_e == '寅') || ($year_e == '卯') || ($year_e == '辰')) {
            $guasuck[11] = '과숙';
        }
        if (($year_e == '巳') || ($year_e == '午') || ($year_e == '未')) {
            $guasuck[2] = '과숙';
        }
        if (($year_e == '申') || ($year_e == '酉') || ($year_e == '戌')) {
            $guasuck[5] = '과숙';
        }
        if (($year_e == '亥') || ($year_e == '子') || ($year_e == '丑')) {
            $guasuck[8] = '과숙';
        }

        return $guasuck;
    }

    // # cheanhue/cheangok/hongran/cheanhee
    /**
     * 천허
     */
    private function cheanhue($year_e)
    {
        $cheanhue = array_fill(0, 12, '');
        switch ($year_e) {
            case '子': $cheanhue[4] = '천허';
                break;
            case '丑': $cheanhue[5] = '천허';
                break;
            case '寅': $cheanhue[6] = '천허';
                break;
            case '卯': $cheanhue[7] = '천허';
                break;
            case '辰': $cheanhue[8] = '천허';
                break;
            case '巳': $cheanhue[9] = '천허';
                break;
            case '午': $cheanhue[10] = '천허';
                break;
            case '未': $cheanhue[11] = '천허';
                break;
            case '申': $cheanhue[0] = '천허';
                break;
            case '酉': $cheanhue[1] = '천허';
                break;
            case '戌': $cheanhue[2] = '천허';
                break;
            case '亥': $cheanhue[3] = '천허';
                break;
        }

        return $cheanhue;
    }

    /**
     * 천곡
     */
    private function cheangok($year_e)
    {
        $cheangok = array_fill(0, 12, '');
        if ($year_e == '子') {
            $cheangok[4] = '천곡';
        }
        if ($year_e == '丑') {
            $cheangok[3] = '천곡';
        }
        if ($year_e == '寅') {
            $cheangok[2] = '천곡';
        }
        if ($year_e == '卯') {
            $cheangok[1] = '천곡';
        }
        if ($year_e == '辰') {
            $cheangok[0] = '천곡';
        }
        if ($year_e == '巳') {
            $cheangok[11] = '천곡';
        }
        if ($year_e == '午') {
            $cheangok[10] = '천곡';
        }
        if ($year_e == '未') {
            $cheangok[9] = '천곡';
        }
        if ($year_e == '申') {
            $cheangok[8] = '천곡';
        }
        if ($year_e == '酉') {
            $cheangok[7] = '천곡';
        }
        if ($year_e == '戌') {
            $cheangok[6] = '천곡';
        }
        if ($year_e == '亥') {
            $cheangok[5] = '천곡';
        }

        return $cheangok;
    }

    /**
     * 홍란
     */
    private function hongran($year_e)
    {
        $hongran = array_fill(0, 12, '');
        if ($year_e == '子') {
            $hongran[1] = '홍란';
        }
        if ($year_e == '丑') {
            $hongran[0] = '홍란';
        }
        if ($year_e == '寅') {
            $hongran[11] = '홍란';
        }
        if ($year_e == '卯') {
            $hongran[10] = '홍란';
        }
        if ($year_e == '辰') {
            $hongran[9] = '홍란';
        }
        if ($year_e == '巳') {
            $hongran[8] = '홍란';
        }
        if ($year_e == '午') {
            $hongran[7] = '홍란';
        }
        if ($year_e == '未') {
            $hongran[6] = '홍란';
        }
        if ($year_e == '申') {
            $hongran[5] = '홍란';
        }
        if ($year_e == '酉') {
            $hongran[4] = '홍란';
        }
        if ($year_e == '戌') {
            $hongran[3] = '홍란';
        }
        if ($year_e == '亥') {
            $hongran[2] = '홍란';
        }

        return $hongran;
    }

    /**
     * 천희
     */
    private function cheanhee($year_e)
    {
        $cheanhee = array_fill(0, 12, '');
        if ($year_e == '子') {
            $cheanhee[7] = '천희';
        }
        if ($year_e == '丑') {
            $cheanhee[6] = '천희';
        }
        if ($year_e == '寅') {
            $cheanhee[5] = '천희';
        }
        if ($year_e == '卯') {
            $cheanhee[4] = '천희';
        }
        if ($year_e == '辰') {
            $cheanhee[3] = '천희';
        }
        if ($year_e == '巳') {
            $cheanhee[2] = '천희';
        }
        if ($year_e == '午') {
            $cheanhee[1] = '천희';
        }
        if ($year_e == '未') {
            $cheanhee[0] = '천희';
        }
        if ($year_e == '申') {
            $cheanhee[11] = '천희';
        }
        if ($year_e == '酉') {
            $cheanhee[10] = '천희';
        }
        if ($year_e == '戌') {
            $cheanhee[9] = '천희';
        }
        if ($year_e == '亥') {
            $cheanhee[8] = '천희';
        }

        return $cheanhee;
    }

    // yongji/bonggak/cheanjae/cheansu
    /**
     * 용지
     */
    private function yongji($year_e)
    {
        $yongji = array_fill(0, 12, '');
        if ($year_e == '子') {
            $yongji[2] = '용지';
        }
        if ($year_e == '丑') {
            $yongji[3] = '용지';
        }
        if ($year_e == '寅') {
            $yongji[4] = '용지';
        }
        if ($year_e == '卯') {
            $yongji[5] = '용지';
        }
        if ($year_e == '辰') {
            $yongji[6] = '용지';
        }
        if ($year_e == '巳') {
            $yongji[7] = '용지';
        }
        if ($year_e == '午') {
            $yongji[8] = '용지';
        }
        if ($year_e == '未') {
            $yongji[9] = '용지';
        }
        if ($year_e == '申') {
            $yongji[10] = '용지';
        }
        if ($year_e == '酉') {
            $yongji[11] = '용지';
        }
        if ($year_e == '戌') {
            $yongji[0] = '용지';
        }
        if ($year_e == '亥') {
            $yongji[1] = '용지';
        }

        return $yongji;
    }

    /**
     * 봉각
     */
    private function bonggak($year_e)
    {
        $bonggak = array_fill(0, 12, '');
        if ($year_e == '子') {
            $bonggak[8] = '봉각';
        }
        if ($year_e == '丑') {
            $bonggak[7] = '봉각';
        }
        if ($year_e == '寅') {
            $bonggak[6] = '봉각';
        }
        if ($year_e == '卯') {
            $bonggak[5] = '봉각';
        }
        if ($year_e == '辰') {
            $bonggak[4] = '봉각';
        }
        if ($year_e == '巳') {
            $bonggak[3] = '봉각';
        }
        if ($year_e == '午') {
            $bonggak[2] = '봉각';
        }
        if ($year_e == '未') {
            $bonggak[1] = '봉각';
        }
        if ($year_e == '申') {
            $bonggak[0] = '봉각';
        }
        if ($year_e == '酉') {
            $bonggak[11] = '봉각';
        }
        if ($year_e == '戌') {
            $bonggak[10] = '봉각';
        }
        if ($year_e == '亥') {
            $bonggak[9] = '봉각';
        }

        return $bonggak;
    }

    /**
     *천재
     *
     * @param  $year_e  생년지
     */
    private function cheanjae($myung, $year_e)
    {
        $cheanjae = array_fill(0, 12, '');
        $index = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        foreach ($myung as $k => $v) {

            if ($v) {
                foreach ($index as $k1 => $v1) {
                    $index[$k1] = ($k + $v1) % 12;
                }

                switch ($year_e) {
                    case '子': $cheanjae[$index[0]] = '천재';
                        break;
                    case '丑': $cheanjae[$index[1]] = '천재';
                        break;
                    case '寅': $cheanjae[$index[2]] = '천재';
                        break;
                    case '卯': $cheanjae[$index[3]] = '천재';
                        break;
                    case '辰': $cheanjae[$index[4]] = '천재';
                        break;
                    case '巳': $cheanjae[$index[5]] = '천재';
                        break;
                    case '午': $cheanjae[$index[6]] = '천재';
                        break;
                    case '未': $cheanjae[$index[7]] = '천재';
                        break;
                    case '申': $cheanjae[$index[8]] = '천재';
                        break;
                    case '酉': $cheanjae[$index[9]] = '천재';
                        break;
                    case '戌': $cheanjae[$index[10]] = '천재';
                        break;
                    case '亥': $cheanjae[$index[11]] = '천재';
                        break;
                }
            }
        }

        return $cheanjae;
    }

    /**
     * 천수
     *
     * @param  $year_e  : 생년지
     */
    private function cheansu($sin, $year_e)
    {
        $cheansu = array_fill(0, 12, '');
        $index = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        foreach ($sin as $k => $v) {

            if ($v) {
                foreach ($index as $k1 => $v1) {
                    $index[$k1] = ($k + $v1) % 12;
                }

                switch ($year_e) {
                    case '子': $cheansu[$index[0]] = '천수';
                        break;
                    case '丑': $cheansu[$index[1]] = '천수';
                        break;
                    case '寅': $cheansu[$index[2]] = '천수';
                        break;
                    case '卯': $cheansu[$index[3]] = '천수';
                        break;
                    case '辰': $cheansu[$index[4]] = '천수';
                        break;
                    case '巳': $cheansu[$index[5]] = '천수';
                        break;
                    case '午': $cheansu[$index[6]] = '천수';
                        break;
                    case '未': $cheansu[$index[7]] = '천수';
                        break;
                    case '申': $cheansu[$index[8]] = '천수';
                        break;
                    case '酉': $cheansu[$index[9]] = '천수';
                        break;
                    case '戌': $cheansu[$index[10]] = '천수';
                        break;
                    case '亥': $cheansu[$index[11]] = '천수';
                        break;
                }
            }
        }

        return $cheansu;

    }

    // # chensang/chensa
    /**
     * 천상
     */
    private function chensang($gung)
    {
        $chensang = array_fill(0, 12, '');
        foreach ($gung as $k => $v) {
            if ($v == '奴僕') {
                $chensang[$k] = '천상';

                return $chensang;
            }
        }

        return $chensang;
    }

    /**
     * 천사
     */
    private function chensa($gung)
    {
        $chensa = array_fill(0, 12, '');
        foreach ($gung as $k => $v) {
            if ($v == '疾厄') {
                $chensa[$k] = '천사';

                return $chensa;
            }
        }

        return $chensa;
    }

    // # #cheangong/daemo/pase/cheanduk/wolduk
    /**
     * 천공
     */
    private function cheangong($year_e)
    {
        $cheangong = array_fill(0, 12, '');
        if ($year_e == '子') {
            $cheangong[11] = '천공';
        }
        if ($year_e == '丑') {
            $cheangong[0] = '천공';
        }
        if ($year_e == '寅') {
            $cheangong[1] = '천공';
        }
        if ($year_e == '卯') {
            $cheangong[2] = '천공';
        }
        if ($year_e == '辰') {
            $cheangong[3] = '천공';
        }
        if ($year_e == '巳') {
            $cheangong[4] = '천공';
        }
        if ($year_e == '午') {
            $cheangong[5] = '천공';
        }
        if ($year_e == '未') {
            $cheangong[6] = '천공';
        }
        if ($year_e == '申') {
            $cheangong[7] = '천공';
        }
        if ($year_e == '酉') {
            $cheangong[8] = '천공';
        }
        if ($year_e == '戌') {
            $cheangong[9] = '천공';
        }
        if ($year_e == '亥') {
            $cheangong[10] = '천공';
        }

        return $cheangong;
    }

    /**
     * 대모
     */
    private function daemo($year_e)
    {
        $daemo = array_fill(0, 12, '');
        if ($year_e == '子') {
            $daemo[5] = '대모';
        }
        if ($year_e == '丑') {
            $daemo[4] = '대모';
        }
        if ($year_e == '寅') {
            $daemo[7] = '대모';
        }
        if ($year_e == '卯') {
            $daemo[6] = '대모';
        }
        if ($year_e == '辰') {
            $daemo[9] = '대모';
        }
        if ($year_e == '巳') {
            $daemo[8] = '대모';
        }
        if ($year_e == '午') {
            $daemo[11] = '대모';
        }
        if ($year_e == '未') {
            $daemo[10] = '대모';
        }
        if ($year_e == '申') {
            $daemo[1] = '대모';
        }
        if ($year_e == '酉') {
            $daemo[0] = '대모';
        }
        if ($year_e == '戌') {
            $daemo[3] = '대모';
        }
        if ($year_e == '亥') {
            $daemo[2] = '대모';
        }

        return $daemo;
    }

    /**
     * 파쇄
     */
    private function pase($year_e)
    {
        $pase = array_fill(0, 12, '');
        if ($year_e == '子') {
            $pase[3] = '파쇄';
        }
        if ($year_e == '丑') {
            $pase[11] = '파쇄';
        }
        if ($year_e == '寅') {
            $pase[7] = '파쇄';
        }
        if ($year_e == '卯') {
            $pase[3] = '파쇄';
        }
        if ($year_e == '辰') {
            $pase[11] = '파쇄';
        }
        if ($year_e == '巳') {
            $pase[7] = '파쇄';
        }
        if ($year_e == '午') {
            $pase[3] = '파쇄';
        }
        if ($year_e == '未') {
            $pase[11] = '파쇄';
        }
        if ($year_e == '申') {
            $pase[7] = '파쇄';
        }
        if ($year_e == '酉') {
            $pase[3] = '파쇄';
        }
        if ($year_e == '戌') {
            $pase[11] = '파쇄';
        }
        if ($year_e == '亥') {
            $pase[7] = '파쇄';
        }

        return $pase;
    }

    /**
     * 천덕
     */
    private function cheanduk($year_e)
    {
        $cheanduk = array_fill(0, 12, '');
        if ($year_e == '子') {
            $cheanduk[7] = '천덕';
        }
        if ($year_e == '丑') {
            $cheanduk[8] = '천덕';
        }
        if ($year_e == '寅') {
            $cheanduk[9] = '천덕';
        }
        if ($year_e == '卯') {
            $cheanduk[10] = '천덕';
        }
        if ($year_e == '辰') {
            $cheanduk[11] = '천덕';
        }
        if ($year_e == '巳') {
            $cheanduk[0] = '천덕';
        }
        if ($year_e == '午') {
            $cheanduk[1] = '천덕';
        }
        if ($year_e == '未') {
            $cheanduk[2] = '천덕';
        }
        if ($year_e == '申') {
            $cheanduk[3] = '천덕';
        }
        if ($year_e == '酉') {
            $cheanduk[4] = '천덕';
        }
        if ($year_e == '戌') {
            $cheanduk[5] = '천덕';
        }
        if ($year_e == '亥') {
            $cheanduk[6] = '천덕';
        }

        return $cheanduk;
    }

    /**
     * 월덕
     */
    private function wolduk($year_e)
    {
        $wolduk = array_fill(0, 12, '');
        if ($year_e == '子') {
            $wolduk[3] = '월덕';
        }
        if ($year_e == '丑') {
            $wolduk[4] = '월덕';
        }
        if ($year_e == '寅') {
            $wolduk[5] = '월덕';
        }
        if ($year_e == '卯') {
            $wolduk[6] = '월덕';
        }
        if ($year_e == '辰') {
            $wolduk[7] = '월덕';
        }
        if ($year_e == '巳') {
            $wolduk[8] = '월덕';
        }
        if ($year_e == '午') {
            $wolduk[9] = '월덕';
        }
        if ($year_e == '未') {
            $wolduk[10] = '월덕';
        }
        if ($year_e == '申') {
            $wolduk[11] = '월덕';
        }
        if ($year_e == '酉') {
            $wolduk[0] = '월덕';
        }
        if ($year_e == '戌') {
            $wolduk[1] = '월덕';
        }
        if ($year_e == '亥') {
            $wolduk[2] = '월덕';
        }

        return $wolduk;
    }

    // 태보, 봉고
    /**
     * 태보
     */
    private function taebo($mungok)
    {
        $taebo = array_fill(0, 12, '');
        foreach ($mungok as $k => $v) {
            if ($v) {
                $taebo_k = ($k + 2) % 12;
                $taebo[$taebo_k] = '태보';
            }
        }

        return $taebo;
    }

    /**
     * 봉고
     */
    private function bonggo($mungok)
    {
        $bonggo = array_fill(0, 12, '');
        foreach ($mungok as $k => $v) {
            if ($v) {
                $bonggo_k = ($k + 10) % 12;
                $bonggo[$bonggo_k] = '봉고';
            }
        }

        return $bonggo;
    }

    /**
     * 홍염
     */
    private function hongyeam($year_h)
    {
        $hongyeam = array_fill(0, 12, '');
        if ($year_h == '甲') {
            $hongyeam[4] = '홍염';
        }
        if ($year_h == '乙') {
            $hongyeam[6] = '홍염';
        }
        if ($year_h == '丙') {
            $hongyeam[0] = '홍염';
        }
        if ($year_h == '丁') {
            $hongyeam[5] = '홍염';
        }
        if ($year_h == '戊') {
            $hongyeam[2] = '홍염';
        }
        if ($year_h == '己') {
            $hongyeam[2] = '홍염';
        }
        if ($year_h == '庚') {
            $hongyeam[8] = '홍염';
        }
        if ($year_h == '辛') {
            $hongyeam[7] = '홍염';
        }
        if ($year_h == '壬') {
            $hongyeam[10] = '홍염';
        }
        if ($year_h == '癸') {
            $hongyeam[6] = '홍염';
        }

        return $hongyeam;
    }

    /**
     * 비렴
     */
    private function biryeum($year_e)
    {
        $biryeum = array_fill(0, 12, '');
        if ($year_e == '寅') {
            $biryeum[8] = '비렴';
        }
        if ($year_e == '卯') {
            $biryeum[3] = '비렴';
        }
        if ($year_e == '辰') {
            $biryeum[4] = '비렴';
        }
        if ($year_e == '巳') {
            $biryeum[5] = '비렴';
        }
        if ($year_e == '午') {
            $biryeum[0] = '비렴';
        }
        if ($year_e == '未') {
            $biryeum[1] = '비렴';
        }
        if ($year_e == '申') {
            $biryeum[2] = '비렴';
        }
        if ($year_e == '酉') {
            $biryeum[9] = '비렴';
        }
        if ($year_e == '戌') {
            $biryeum[10] = '비렴';
        }
        if ($year_e == '亥') {
            $biryeum[11] = '비렴';
        }
        if ($year_e == '子') {
            $biryeum[6] = '비렴';
        }
        if ($year_e == '丑') {
            $biryeum[7] = '비렴';
        }

        return $biryeum;
    }

    // #생년태세12신
    // # taese/
    /**
     * 생년태세12신
     */
    private function taese($year_e)
    {
        $taeseArr = ['태세', '태양', '상문', '태음', '관부', '사부', '세파', '용덕', '백호', '복덕', '조객', '병부'];
        // $taeseArr = ['건태', '회기', '상문', '관색', '관부', '소모', '세파', '대모', '백호', '천덕', '조객', '병부']; // 위와 같은 이름
        $taese = array_fill(0, 12, null);
        switch ($year_e) {
            case '子':  $taese_k = [10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                break;
            case '丑':  $taese_k = [11, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
                break;
            case '寅':  $taese_k = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                break;
            case '卯':  $taese_k = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0];
                break;
            case '辰':  $taese_k = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1];
                break;
            case '巳':  $taese_k = [3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2];
                break;
            case '午':  $taese_k = [4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2, 3];
                break;
            case '未':  $taese_k = [5, 6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4];
                break;
            case '申':  $taese_k = [6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5];
                break;
            case '酉':  $taese_k = [7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5, 6];
                break;
            case '戌':  $taese_k = [8, 9, 10, 11, 0, 1, 2, 3, 4, 5, 6, 7];
                break;
            case '亥':  $taese_k = [9, 10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8];
                break;
        }

        foreach ($taese_k as $k => $v) {
            $taese[$v] = $taeseArr[$k];
        }

        return $taese;
    }

    // ###############################################################################장성십이신/jangsung
    /**
     * 장성십이신/jangsung
     */
    private function jangsung($year_e)
    {

        $jangsungArr = ['장성', '반안', '세역', '식신', '화개', '겁살', '재살', '천살', '지배', '함지', '월살', '망신'];
        $jangsung = array_fill(0, 12, null);

        switch ($year_e) {
            case '寅': case '午': case '戌': $jangsung_k = [4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2, 3];
                break;
            case '申': case '子': case '辰': $jangsung_k = [10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                break;
            case '巳': case '酉': case '丑': $jangsung_k = [7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5, 6];
                break;
            case '亥': case '卯': case '未': $jangsung_k = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0];
                break;
        }

        foreach ($jangsung_k as $k => $v) {
            $jangsung[$v] = $jangsungArr[$k];
        }

        return $jangsung;
    }

    /**
     * 겁살 구하기 (위의 장성 12살의 겁살 구하기와 같은 결론)
     */
    private function guepsal($year_e)
    {
        $guepsal = array_fill(0, 12, '');
        switch ($year_e) {
            case '寅': case '午': case '戌': $guepsal[9] = '겁살';
                break;
            case '申': case '子': case '辰': $guepsal[3] = '겁살';
                break;
            case '巳': case '酉': case '丑': $guepsal[0] = '겁살';
                break;
            case '亥': case '卯': case '未': $guepsal[6] = '겁살';
                break;
        }

        return $guepsal;
    }

    /**
     * 박사12신
     */
    private function baksa($nokjon, $yangum)
    {
        $baksaArr = ['박사', '역사', '청룡', '소모', '장군', '주서', '비렴', '희신', '병부', '대모', '복병', '관부'];
        $baksa = array_fill(0, 12, null);

        foreach ($nokjon as $k => $v) {
            if ($v) {
                switch ($k) {
                    case 0:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                        } else {
                            $baksa_k = [0, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];
                        }
                        break;
                    case 1:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0];
                        } else {
                            $baksa_k = [1, 0, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2];
                        }
                        break;
                    case 2:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1];
                        } else {
                            $baksa_k = [2, 1, 0, 11, 10, 9, 8, 7, 6, 5, 4, 3];
                        }
                        break;
                    case 3:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2];
                        } else {
                            $baksa_k = [3, 2, 1, 0, 11, 10, 9, 8, 7, 6, 5, 4];
                        }
                        break;
                    case 4:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2, 3];
                        } else {
                            $baksa_k = [4, 3, 2, 1, 0, 11, 10, 9, 8, 7, 6, 5];
                        }
                        break;
                    case 5:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [5, 6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4];
                        } else {
                            $baksa_k = [5, 4, 3, 2, 1, 0, 11, 10, 9, 8, 7, 6];
                        }
                        break;
                    case 6:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5];
                        } else {
                            $baksa_k = [6, 5, 4, 3, 2, 1, 0, 11, 10, 9, 8, 7];
                        }
                        break;
                    case 7:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5, 6];
                        } else {
                            $baksa_k = [7, 6, 5, 4, 3, 2, 1, 0, 11, 10, 9, 8];
                        }
                        break;
                    case 8:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [8, 9, 10, 11, 0, 1, 2, 3, 4, 5, 6, 7];
                        } else {
                            $baksa_k = [8, 7, 6, 5, 4, 3, 2, 1, 0, 11, 10, 9];
                        }
                        break;
                    case 9:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [9, 10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8];
                        } else {
                            $baksa_k = [9, 8, 7, 6, 5, 4, 3, 2, 1, 0, 11, 10];
                        }
                        break;
                    case 10:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                        } else {
                            $baksa_k = [10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0, 11];
                        }
                        break;
                    case 11:
                        if (($yangum == '양남') || ($yangum == '음녀')) {
                            $baksa_k = [11, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
                        } else {
                            $baksa_k = [11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0];
                        }
                        break;
                }
            }
        }

        foreach ($baksa_k as $k => $v) {
            $baksa[$v] = $baksaArr[$k];
        }

        return $baksa;
    }

    /**
     * 십이운성베치
     */
    private function unsung($yangum, $myung_guk)
    {

        $unsungArr = ['生', '浴', '帶', '冠', '旺', '衰', '病', '死', '墓', '絶', '胎', '養'];
        $unsung = array_fill(0, 12, null);

        switch ($myung_guk) {
            case '火6局':
                switch ($yangum) {
                    case '양남': case '음녀':
                        $unsung_k = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                        break;
                    case '음남': case '양녀':
                        $unsung_k = [0, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];
                        break;
                }
                break;
            case '土5局':
                switch ($yangum) {
                    case '양남': case '음녀':
                        $unsung_k = [6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5];
                        break;
                    case '음남': case '양녀':
                        $unsung_k = [6, 5, 4, 3, 2, 1, 0, 11, 10, 9, 8, 7];
                        break;
                }
                break;
            case '金4局':
                switch ($yangum) {
                    case '양남': case '음녀':
                        $unsung_k = [3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2];
                        break;
                    case '음남': case '양녀':
                        $unsung_k = [3, 2, 1, 0, 11, 10, 9, 8, 7, 6, 5, 4];
                        break;
                }
                break;
            case '水2局':
                switch ($yangum) {
                    case '양남': case '음녀':
                        $unsung_k = [6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5];
                        break;
                    case '음남': case '양녀':
                        $unsung_k = [6, 5, 4, 3, 2, 1, 0, 11, 10, 9, 8, 7];
                        break;
                }
                break;
            case '木3局':
                switch ($yangum) {
                    case '양남': case '음녀':
                        $unsung_k = [9, 10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8];
                        break;
                    case '음남': case '양녀':
                        $unsung_k = [9, 8, 7, 6, 5, 4, 3, 2, 1, 0, 11, 10];
                        break;
                }
                break;
        }

        foreach ($unsung_k as $k => $v) {
            $unsung[$v] = $unsungArr[$k];
        }

        return $unsung;
    }

    /**
     * 대한운 계산
     * $yangum : 양남, 음남, 양녀, 음녀
     * $myung_guk : 水2局, 木3局, 金4局, 土5局, 火6局
     * $myung : 명궁 배열  [1,0,0,0,0,0,0,0,0,0,0,0] (명궁이 자리에 있으면 1)
     * return : 대한운 배열
     */
    public function daehan($myung, $yangum, $myung_guk)
    {

        // daehan >> yangum:양남, myung_guk:金4局
        $daehan = array_fill(0, 12, '');
        $gukMap = ['水2局' => 2, '木3局' => 3, '金4局' => 4, '土5局' => 5, '火6局' => 6];
        $start_age = $gukMap[$myung_guk] ?? 0;
        $myung_index = array_search('명', $myung, true);
        if ($myung_index === false) {
            return $daehan; // 명궁이 없으면 빈 배열 반환
        }

        $isSunhaeng = in_array($yangum, ['양남', '음녀']); // 순행 여부

        for ($i = 0; $i < 12; $i++) {
            $offset = $isSunhaeng ? $i : -$i;
            $currentIndex = ($myung_index + $offset + 12) % 12;

            $current_start_age = $start_age + ($i * 10);
            $current_end_age = $current_start_age + 9;

            if ($current_start_age < 120) { // 비현실적인 나이는 제외
                $daehan[$currentIndex] = $current_start_age.'~'.$current_end_age;
            }
        }

        return $daehan;
    }

    private function ages($current_age, $umyear_e)
    {
        $ages = array_fill(0, 12, null);
        switch ($umyear_e) {
            case '子': $start = 10;
                break;
            case '丑': $start = 11;
                break;
            case '寅': $start = 0;
                break;
            case '卯': $start = 1;
                break;
            case '辰': $start = 2;
                break;
            case '巳': $start = 3;
                break;
            case '午': $start = 4;
                break;
            case '未': $start = 5;
                break;
            case '申': $start = 6;
                break;
            case '酉': $start = 7;
                break;
            case '戌': $start = 8;
                break;
            case '亥': $start = 9;
                break;
        }
        $end = $current_age + 12;
        $k = 0;
        for ($i = $current_age; $i < $end; $i++) {
            $index = ($start + $k) % 12;
            $ages[$index] = $i;
            $k++;
        }

        return $ages;
    }

    // ###현재의 대한운 계산
    public function current_daehan($daehan, $gabja, $yangum, $current_age)
    {

        $current_daehan = null; // 기본값 설정
        foreach ($daehan as $k => $v) {
            if ($v) {
                $result = explode('~', $v);
                $first_number = $result[0];
                $second_number = $result[1];

                if (($first_number <= $current_age) && ($second_number >= $current_age)) {
                    $current_daehan = $gabja[$k];
                    $c_first_no = $first_number;
                    $c_second_no = $second_number;
                }
            }
        }

        return $current_daehan;
    }

    /**
     * 유년궁 구하기 여기서는 생년의  e를 사용함
     */
    private function youyeon($umyear_e, $gabja)
    {

        $youArr = ['流命', '流父', '流福', '流田', '流官', '流奴', '流遷', '流疾', '流財', '流子', '流夫', '流兄'];
        $you = array_fill(0, 12, null);

        if ($umyear_e == '子') {
            $you_k = [10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        }

        if ($umyear_e == '丑') {
            $you_k = [11, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        }

        if ($umyear_e == '寅') {
            $you_k = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        }

        if ($umyear_e == '卯') {
            $you_k = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0];
        }

        if ($umyear_e == '辰') {
            $you_k = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1];
        }

        if ($umyear_e == '巳') {
            $you_k = [3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2];
        }

        if ($umyear_e == '午') {
            $you_k = [4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2, 3];
        }

        if ($umyear_e == '未') {
            $you_k = [5, 6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4];
        }

        if ($umyear_e == '申') {

            $you_k = [6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5];
        }

        if ($umyear_e == '酉') {
            $you_k = [7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5, 6];
        }

        if ($umyear_e == '戌') {
            $you_k = [8, 9, 10, 11, 0, 1, 2, 3, 4, 5, 6, 7];
        }

        if ($umyear_e == '亥') {
            $you_k = [9, 10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8];
        }

        foreach ($you_k as $k => $v) {
            $you[$v] = $youArr[$k];
        }

        return $you;
    }

    /**
     * 기초 명반및 12궁 천간을 만든다.
     *
     * @param  $year_h  생년간
     */
    private function basic($year_h)
    {
        $map = [
            '甲' => ['丙寅', '丁卯', '戊辰', '己巳', '庚午', '辛未', '壬申', '癸酉', '甲戌', '乙亥', '丙子', '丁丑'],
            '己' => ['丙寅', '丁卯', '戊辰', '己巳', '庚午', '辛未', '壬申', '癸酉', '甲戌', '乙亥', '丙子', '丁丑'],
            '乙' => ['戊寅', '己卯', '庚辰', '辛巳', '壬午', '癸未', '甲申', '乙酉', '丙戌', '丁亥', '戊子', '己丑'],
            '庚' => ['戊寅', '己卯', '庚辰', '辛巳', '壬午', '癸未', '甲申', '乙酉', '丙戌', '丁亥', '戊子', '己丑'],
            '丙' => ['庚寅', '辛卯', '壬辰', '癸巳', '甲午', '乙未', '丙申', '丁酉', '戊戌', '己亥', '庚子', '辛丑'],
            '辛' => ['庚寅', '辛卯', '壬辰', '癸巳', '甲午', '乙未', '丙申', '丁酉', '戊戌', '己亥', '庚子', '辛丑'],
            '丁' => ['壬寅', '癸卯', '甲辰', '乙巳', '丙午', '丁未', '戊申', '己酉', '庚戌', '辛亥', '壬子', '癸丑'],
            '壬' => ['壬寅', '癸卯', '甲辰', '乙巳', '丙午', '丁未', '戊申', '己酉', '庚戌', '辛亥', '壬子', '癸丑'],
            '戊' => ['甲寅', '乙卯', '丙辰', '丁巳', '戊午', '己未', '庚申', '辛酉', '壬戌', '癸亥', '甲子', '乙丑'],
            '癸' => ['甲寅', '乙卯', '丙辰', '丁巳', '戊午', '己未', '庚申', '辛酉', '壬戌', '癸亥', '甲子', '乙丑'],
        ];

        return $map[$year_h] ?? array_fill(0, 12, null);
    }

    /**
     * 명과 신을 구한다.
     *
     * @param  $hour_e  생시지
     * @param  $month  음력 생월(숫자)
     */
    private function myungsin($hour_e, $month)
    {
        // 명궁
        $myung = array_fill(0, 12, null);
        // 신궁
        $sin = array_fill(0, 12, null);
        $m_k = null;
        $s_k = null;
        switch ($hour_e) {
            case '子':
                $m_k = $month + 11;
                $s_k = $month + 11;
                break;
            case '丑':
                $m_k = $month + 10;
                $s_k = $month;
                break;
            case '寅':
                $m_k = $month + 9;
                $s_k = $month + 1;
                break;
            case '卯':
                $m_k = $month + 8;
                $s_k = $month + 2;
                break;
            case '辰':
                $m_k = $month + 7;
                $s_k = $month + 3;
                break;
            case '巳':
                $m_k = $month + 6;
                $s_k = $month + 4;
                break;
            case '午':
                $m_k = $month + 5;
                $s_k = $month + 5;
                break;
            case '未':
                $m_k = $month + 4;
                $s_k = $month + 6;
                break;
            case '申':
                $m_k = $month + 3;
                $s_k = $month + 7;
                break;
            case '酉':
                $m_k = $month + 2;
                $s_k = $month + 8;
                break;
            case '戌':
                $m_k = $month + 1;
                $s_k = $month + 9;
                break;
            case '亥':
                $m_k = $month;
                $s_k = $month + 10;
                break;
        }

        $m_k = $m_k % 12;
        $s_k = $s_k % 12;
        $myung[$m_k] = '명';
        $sin[$s_k] = '신';

        return ['myung' => $myung, 'sin' => $sin];
    }

    /**
     * 천괴 천월 구하기
     */
    private function cheangueCheanwol($year_h)
    {

        $cheangue = array_fill(0, 12, null);
        $cheanwol = array_fill(0, 12, null);
        if (($year_h == '甲') || ($year_h == '戊') || ($year_h == '庚')) {
            $cheangue[11] = '천괴';
            $cheanwol[5] = '천월';
        }
        if (($year_h == '乙') || ($year_h == '己')) {
            $cheangue[10] = '천괴';
            $cheanwol[6] = '천월';
        }
        if (($year_h == '丙') || ($year_h == '丁')) {
            $cheangue[9] = '천괴';
            $cheanwol[7] = '천월';
        }
        if ($year_h == '辛') {
            $cheangue[4] = '천괴';
            $cheanwol[0] = '천월';
        }
        if (($year_h == '壬') || ($year_h == '癸')) {
            $cheangue[1] = '천괴';
            $cheanwol[3] = '천월';
        }

        return [
            'cheangue' => $cheangue,
            'cheanwol' => $cheanwol,
        ];
    }

    /**
     * 천마
     */
    private function cheanma($year_e)
    {
        $cheanma = array_fill(0, 12, null);
        if (($year_e == '寅') || ($year_e == '午') || ($year_e == '戌')) {
            $cheanma[6] = '천마';
        }
        if (($year_e == '申') || ($year_e == '子') || ($year_e == '辰')) {
            $cheanma[0] = '천마';
        }
        if (($year_e == '巳') || ($year_e == '酉') || ($year_e == '丑')) {
            $cheanma[9] = '천마';
        }
        if (($year_e == '亥') || ($year_e == '卯') || ($year_e == '未')) {
            $cheanma[3] = '천마';
        }

        return $cheanma;
    }

    /**
     * 살성 중 [화성, 영성'' 구하기
     */
    private function whasunYeungsung($year_e, $hour_e)
    {
        $whasung = array_fill(0, 12, null);
        $yeungsung = array_fill(0, 12, null);
        if (($year_e == '寅') || ($year_e == '午') || ($year_e == '戌')) {
            if ($hour_e == '子') {
                $whasung[11] = '화성';
                $yeungsung[1] = '영성';
            }

            if ($hour_e == '丑') {
                $whasung[0] = '화성';
                $yeungsung[2] = '영성';
            }
            if ($hour_e == '寅') {
                $whasung[1] = '화성';
                $yeungsung[3] = '영성';
            }
            if ($hour_e == '卯') {
                $whasung[2] = '화성';
                $yeungsung[4] = '영성';
            }
            if ($hour_e == '辰') {
                $whasung[3] = '화성';
                $yeungsung[5] = '영성';
            }
            if ($hour_e == '巳') {
                $whasung[4] = '화성';
                $yeungsung[6] = '영성';
            }
            if ($hour_e == '午') {
                $whasung[5] = '화성';
                $yeungsung[7] = '영성';
            }
            if ($hour_e == '未') {
                $whasung[6] = '화성';
                $yeungsung[8] = '영성';
            }
            if ($hour_e == '申') {
                $whasung[7] = '화성';
                $yeungsung[9] = '영성';
            }
            if ($hour_e == '酉') {
                $whasung[8] = '화성';
                $yeungsung[10] = '영성';
            }
            if ($hour_e == '戌') {
                $whasung[9] = '화성';
                $yeungsung[11] = '영성';
            }
            if ($hour_e == '亥') {
                $whasung[10] = '화성';
                $yeungsung[12] = '영성';
            }
        }

        if (($year_e == '申') || ($year_e == '子') || ($year_e == '辰')) {
            if ($hour_e == '子') {
                $whasung[0] = '화성';
                $yeungsung[8] = '영성';
            }
            if ($hour_e == '丑') {
                $whasung[1] = '화성';
                $yeungsung[9] = '영성';
            }
            if ($hour_e == '寅') {
                $whasung[2] = '화성';
                $yeungsung[10] = '영성';
            }
            if ($hour_e == '卯') {
                $whasung[3] = '화성';
                $yeungsung[11] = '영성';
            }
            if ($hour_e == '辰') {
                $whasung[4] = '화성';
                $yeungsung[0] = '영성';
            }
            if ($hour_e == '巳') {
                $whasung[5] = '화성';
                $yeungsung[1] = '영성';
            }
            if ($hour_e == '午') {
                $whasung[6] = '화성';
                $yeungsung[2] = '영성';
            }
            if ($hour_e == '未') {
                $whasung[7] = '화성';
                $yeungsung[3] = '영성';
            }
            if ($hour_e == '申') {
                $whasung[8] = '화성';
                $yeungsung[4] = '영성';
            }
            if ($hour_e == '酉') {
                $whasung[9] = '화성';
                $yeungsung[5] = '영성';
            }
            if ($hour_e == '戌') {
                $whasung[10] = '화성';
                $yeungsung[6] = '영성';
            }
            if ($hour_e == '亥') {
                $whasung[11] = '화성';
                $yeungsung[7] = '영성';
            }
        }

        if (($year_e == '巳') || ($year_e == '酉') || ($year_e == '丑')) {
            if ($hour_e == '子') {
                $whasung[1] = '화성';
                $yeungsung[8] = '영성';
            }
            if ($hour_e == '丑') {
                $whasung[2] = '화성';
                $yeungsung[9] = '영성';
            }
            if ($hour_e == '寅') {
                $whasung[3] = '화성';
                $yeungsung[10] = '영성';
            }
            if ($hour_e == '卯') {
                $whasung[4] = '화성';
                $yeungsung[11] = '영성';
            }
            if ($hour_e == '辰') {
                $whasung[5] = '화성';
                $yeungsung[0] = '영성';
            }
            if ($hour_e == '巳') {
                $whasung[6] = '화성';
                $yeungsung[1] = '영성';
            }
            if ($hour_e == '午') {
                $whasung[7] = '화성';
                $yeungsung[2] = '영성';
            }
            if ($hour_e == '未') {
                $whasung[8] = '화성';
                $yeungsung[3] = '영성';
            }
            if ($hour_e == '申') {
                $whasung[9] = '화성';
                $yeungsung[4] = '영성';
            }
            if ($hour_e == '酉') {
                $whasung[10] = '화성';
                $yeungsung[5] = '영성';
            }
            if ($hour_e == '戌') {
                $yeungsung_07 = '영성';
                $whasung[11] = '화성';
                $yeungsung[6] = '영성';
            }
            if ($hour_e == '亥') {
                $whasung[0] = '화성';
                $yeungsung[7] = '영성';
            }
        }

        if (($year_e == '亥') || ($year_e == '卯') || ($year_e == '未')) {
            if ($hour_e == '子') {
                $whasung[7] = '화성';
                $yeungsung[8] = '영성';
            }
            if ($hour_e == '丑') {
                $whasung[8] = '화성';
                $yeungsung[9] = '영성';
            }
            if ($hour_e == '寅') {
                $whasung[9] = '화성';
                $yeungsung[12] = '영성';
            }
            if ($hour_e == '卯') {
                $whasung[10] = '화성';
                $yeungsung[11] = '영성';
            }
            if ($hour_e == '辰') {
                $whasung[11] = '화성';
                $yeungsung[0] = '영성';
            }
            if ($hour_e == '巳') {
                $whasung[0] = '화성';
                $yeungsung[1] = '영성';
            }
            if ($hour_e == '午') {
                $whasung[1] = '화성';
                $yeungsung[2] = '영성';
            }
            if ($hour_e == '未') {
                $whasung[2] = '화성';
                $yeungsung[3] = '영성';
            }
            if ($hour_e == '申') {
                $whasung[3] = '화성';
                $yeungsung[4] = '영성';
            }
            if ($hour_e == '酉') {
                $whasung[4] = '화성';
                $yeungsung[5] = '영성';
            }
            if ($hour_e == '戌') {
                $whasung[5] = '화성';
                $yeungsung[6] = '영성';
            }
            if ($hour_e == '亥') {
                $whasung[6] = '화성';
                $yeungsung[7] = '영성';
            }
        }

        return [
            'whasung' => $whasung,
            'yeungsung' => $yeungsung,
        ];
    }

    // # 살성중 [지공, 지겁] 구하기
    private function jigongJigup($hour_e)
    {
        $jigong = array_fill(0, 12, null);
        $jigup = array_fill(0, 12, null);

        switch ($hour_e) {
            case '子':
                $jigong[9] = '지공';
                $jigup[9] = '지겁';
                break;
            case '丑':
                $jigong[8] = '지공';
                $jigup[10] = '지겁';
                break;
            case '寅':
                $jigong[7] = '지공';
                $jigup[11] = '지겁';
                break;
            case '卯':
                $jigong[6] = '지공';
                $jigup[0] = '지겁';
                break;
            case '辰':
                $jigong[5] = '지공';
                $jigup[1] = '지겁';
                break;
            case '巳':
                $jigong[4] = '지공';
                $jigup[2] = '지겁';
                break;
            case '午':
                $jigong[3] = '지공';
                $jigup[3] = '지겁';
                break;
            case '未':
                $jigong[2] = '지공';
                $jigup[4] = '지겁';
                break;
            case '申':
                $jigong[1] = '지공';
                $jigup[5] = '지겁';
                break;
            case '酉':
                $jigong[0] = '지공';
                $jigup[6] = '지겁';
                break;
            case '戌':
                $jigong[11] = '지공';
                $jigup[7] = '지겁';
                break;
            case '亥':
                $jigong[10] = '지공';
                $jigup[8] = '지겁';
                break;
        }

        return [
            'jigong' => $jigong,
            'jigup' => $jigup,
        ];
    }

    /**
     * 두군월 구하기
     *
     * @param  $you_umyear_e  유년 년지
     * @param  $lunar_month  : 음력 생월
     */
    private function dugunWol($you_umyear_e, $lunar_month)
    {
        if ($you_umyear_e == '未') {
            if ($lunar_month == '01') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '申';
            }
        }

        if ($you_umyear_e == '申') {
            if ($lunar_month == '01') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '酉';
            }
        }

        if ($you_umyear_e == '酉') {
            if ($lunar_month == '01') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '戌';
            }
        }
        if ($you_umyear_e == '戌') {
            if ($lunar_month == '01') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '亥';
            }
        }
        if ($you_umyear_e == '亥') {
            if ($lunar_month == '01') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '子';
            }
        }

        if ($you_umyear_e == '子') {
            if ($lunar_month == '01') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '丑';
            }
        }
        if ($you_umyear_e == '丑') {
            if ($lunar_month == '01') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '寅';
            }
        }
        if ($you_umyear_e == '寅') {
            if ($lunar_month == '01') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '卯';
            }
        }
        if ($you_umyear_e == '卯') {
            if ($lunar_month == '01') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '辰';
            }
        }
        if ($you_umyear_e == '辰') {
            if ($lunar_month == '01') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '巳';
            }
        }
        if ($you_umyear_e == '巳') {
            if ($lunar_month == '01') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '未';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '午';
            }
        }
        if ($you_umyear_e == '午') {
            if ($lunar_month == '01') {
                $dugun_wol = '午';
            }
            if ($lunar_month == '02') {
                $dugun_wol = '巳';
            }
            if ($lunar_month == '03') {
                $dugun_wol = '辰';
            }
            if ($lunar_month == '04') {
                $dugun_wol = '卯';
            }
            if ($lunar_month == '05') {
                $dugun_wol = '寅';
            }
            if ($lunar_month == '06') {
                $dugun_wol = '丑';
            }
            if ($lunar_month == '07') {
                $dugun_wol = '子';
            }
            if ($lunar_month == '08') {
                $dugun_wol = '亥';
            }
            if ($lunar_month == '09') {
                $dugun_wol = '戌';
            }
            if ($lunar_month == '10') {
                $dugun_wol = '酉';
            }
            if ($lunar_month == '11') {
                $dugun_wol = '申';
            }
            if ($lunar_month == '12') {
                $dugun_wol = '未';
            }
        }

        return $dugun_wol;
    }

    /**
     * 두군 구하기
     */
    private function dugun($dugun_wol, $hour_e)
    {
        $dugun = array_fill(0, 12, null);

        if ($dugun_wol == '子') {
            if ($hour_e == '子') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[9] = '斗君';
            }
        }
        if ($dugun_wol == '丑') {
            if ($hour_e == '子') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[10] = '斗君';
            }
        }
        if ($dugun_wol == '寅') {
            if ($hour_e == '子') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[11] = '斗君';
            }
        }
        if ($dugun_wol == '卯') {
            if ($hour_e == '子') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[0] = '斗君';
            }
        }
        if ($dugun_wol == '辰') {
            if ($hour_e == '子') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[1] = '斗君';
            }
        }
        if ($dugun_wol == '巳') {
            if ($hour_e == '子') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[2] = '斗君';
            }
        }
        if ($dugun_wol == '午') {
            if ($hour_e == '子') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[3] = '斗君';
            }
        }
        if ($dugun_wol == '未') {
            if ($hour_e == '子') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[4] = '斗君';
            }
        }
        if ($dugun_wol == '申') {
            if ($hour_e == '子') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[5] = '斗君';
            }
        }
        if ($dugun_wol == '酉') {
            if ($hour_e == '子') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[6] = '斗君';
            }
        }
        if ($dugun_wol == '戌') {
            if ($hour_e == '子') {
                $dugun[8] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[7] = '斗君';
            }
        }
        if ($dugun_wol == '亥') {
            if ($hour_e == '子') {
                $dugun[9] = '斗君';
            }
            if ($hour_e == '丑') {
                $dugun[10] = '斗君';
            }
            if ($hour_e == '寅') {
                $dugun[11] = '斗君';
            }
            if ($hour_e == '卯') {
                $dugun[0] = '斗君';
            }
            if ($hour_e == '辰') {
                $dugun[1] = '斗君';
            }
            if ($hour_e == '巳') {
                $dugun[2] = '斗君';
            }
            if ($hour_e == '午') {
                $dugun[3] = '斗君';
            }
            if ($hour_e == '未') {
                $dugun[4] = '斗君';
            }
            if ($hour_e == '申') {
                $dugun[5] = '斗君';
            }
            if ($hour_e == '酉') {
                $dugun[6] = '斗君';
            }
            if ($hour_e == '戌') {
                $dugun[7] = '斗君';
            }
            if ($hour_e == '亥') {
                $dugun[8] = '斗君';
            }
        }

        return $dugun;
    }

    private function jusung14($k, $goong)
    {
        return $goong['taeum'][$k].
        $goong['tamrang'][$k].
        $goong['geamun'][$k].
        $goong['cheansang'][$k].
        $goong['cheanryang'][$k].
        $goong['chilsal'][$k].
        $goong['pagun'][$k].
        $goong['yeamjung'][$k].
        $goong['chendong'][$k].
        $goong['mugok'][$k].
        $goong['taeyang'][$k].
        $goong['chengi'][$k].
        $goong['cheanbu'][$k].
        $goong['jami'][$k];
    }

    /**
     * 미두수의 명국(命局)을 계산합니다.
     */
    public function getMyeongguk($myung, $gabja): string
    {
        $myung_index = array_search('명', $myung, true);
        if ($myung_index === false) {
            return ''; // 명궁을 찾을 수 없을 때
        }

        $ganji_h = mb_substr($gabja[$myung_index], 0, 1); // 천간 (예: '丙')

        // [궁 인덱스][천간] => 명국
        $map = [
            0 => ['丙' => '火6局', '戊' => '土5局', '庚' => '木3局', '壬' => '金4局', '甲' => '水2局'],
            1 => ['丁' => '火6局', '己' => '土5局', '辛' => '木3局', '癸' => '金4局', '乙' => '水2局'],
            2 => ['戊' => '木3局', '庚' => '金4局', '壬' => '水2局', '甲' => '火6局', '丙' => '土5局'],
            3 => ['己' => '木3局', '辛' => '金4局', '癸' => '水2局', '乙' => '火6局', '丁' => '土5局'],
            4 => ['庚' => '土5局', '壬' => '木3局', '甲' => '金4局', '丙' => '水2局', '戊' => '火6局'],
            5 => ['辛' => '土5局', '癸' => '木3局', '乙' => '金4局', '丁' => '水2局', '己' => '火6局'],
            6 => ['壬' => '金4局', '甲' => '水2局', '丙' => '火6局', '戊' => '土5局', '庚' => '木3局'],
            7 => ['癸' => '金4局', '乙' => '水2局', '丁' => '火6局', '己' => '土5局', '辛' => '木3局'],
            8 => ['甲' => '火6局', '丙' => '土5局', '戊' => '木3局', '庚' => '金4局', '壬' => '水2局'],
            9 => ['乙' => '火6局', '丁' => '土5局', '己' => '木3局', '辛' => '金4局', '癸' => '水2局'],
            10 => ['甲' => '金4局', '丙' => '水2局', '戊' => '火6局', '庚' => '土5局', '壬' => '木3局'],
            11 => ['乙' => '金4局', '丁' => '水2局', '己' => '火6局', '辛' => '土5局', '癸' => '木3局'],
        ];

        return $map[$myung_index][$ganji_h] ?? '';
    }

    /**
     * 명주 구하기
     */
    public function myung_ju($myung): string
    {
        $myung_index = array_search('명', $myung, true);
        $map = [
            10 => '貪狼', 11 => '巨門', 9 => '巨門', 0 => '祿存', 8 => '祿存',
            1 => '文曲', 7 => '文曲', 4 => '파군', 2 => '廉貞', 6 => '廉貞',
            3 => '武曲', 5 => '武曲',
        ];

        return $map[$myung_index] ?? '';
    }

    /**
     * 신주 구하기
     *
     * @param  $year_e  생년지
     */
    public function sin_ju($year_e): string
    {
        $map = [
            '子' => '火星', '午' => '鈴星', '丑' => '天相', '未' => '天相',
            '寅' => '天梁', '申' => '天梁', '卯' => '天同', '酉' => '天同',
            '辰' => '文昌', '戌' => '文昌', '巳' => '天機', '亥' => '天機',
        ];

        return $map[$year_e] ?? '';
    }

    /**
     * 양남 양녀 구하기
     *
     * @param  $year_h  : 생년간
     */
    public function yangum($gender, $year_h)
    {
        switch ($gender) {
            case 'M':
                switch ($year_h) {
                    case '甲': case '丙': case '戊': case '庚': case '壬':
                        return '양남';
                    default: // '乙','丁','己','辛','癸'
                        return '음남';
                        break;
                }
                break;
            case 'W':
                switch ($year_h) {
                    case '甲': case '丙': case '戊': case '庚': case '壬':
                        return '양녀';
                    default: // '乙','丁','己','辛','癸'
                        return '음녀';
                        break;
                }
                break;
        }
    }

    // 14 주성 : 1.자미, 2.천기, 3.태양, 4.태음, 5.무곡, 6.칠살, 7.파군, 8.천동, 9.염정, 10.천부, 11.탐랑, 12.거문, 13.천상, 14천양
    /**
     * 1.자미성 찾기
     *
     * @param  $umday  : 생일의 숫자
     */
    private function jami($myung_guk, $umday)
    {

        $jami = array_fill(0, 12, null);
        switch ($myung_guk) {
            case '木3局': $jami_keys = [2, 11, 0, 3, 0, 1, 4, 1, 2, 5, 2, 3, 6, 3, 4, 7, 4, 5, 8, 5, 6, 9, 6, 7, 10, 7, 8, 11, 8, 9];
                break;
            case '火6局': $jami_keys = [7, 4, 9, 2, 11, 0, 8, 5, 10, 3, 0, 1, 9, 6, 11, 4, 1, 2, 10, 7, 0, 5, 2, 3, 11, 8, 1, 6, 3, 4];
                break;
            case '土5局': $jami_keys = [4, 9, 2, 11, 0, 5, 10, 3, 0, 1, 6, 11, 4, 1, 2, 7, 0, 5, 2, 3, 8, 1, 6, 3, 4, 9, 2, 7, 4, 5];
                break;
            case '金4局': $jami_keys = [9, 2, 11, 0, 10, 3, 0, 1, 11, 4, 1, 2, 0, 5, 2, 3, 1, 6, 3, 4, 2, 7, 4, 5, 3, 8, 5, 6, 4, 9];
                break;
            case '水2局': $jami_keys = [11, 0, 0, 1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9, 9, 10, 10, 11, 11, 0, 0, 1, 1, 2];
                break;
        }
        $key = (int) $umday - 1;
        $jami_key = $jami_keys[$key];
        $jami[$jami_key] = '자미';

        return $jami;
    }

    /*
    * 천부성 및 자미성계 찾기(10.천부, 2.천기, 3.태양, 5.무곡, 8.천동, 9.염정)
    */
    private function jamis($jami)
    {
        $cheanbu = array_fill(0, 12, null); // 천부
        $chengi = array_fill(0, 12, null); // 천기
        $taeyang = array_fill(0, 12, null); // 태양
        $mugok = array_fill(0, 12, null); // 무곡
        $chendong = array_fill(0, 12, null); // 천동
        $yeamjung = array_fill(0, 12, null); // 염정
        foreach ($jami as $k => $v) {
            if ($v) {
                // 천부성찾기
                $t = [0, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];
                $cheanbu_k = $t[$k];
                $cheanbu[$cheanbu_k] = '천부';

                // # 나머지 자미성계 찾기
                // ### 천기
                $t = [11, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
                $chengi_k = $t[$k];
                $chengi[$chengi_k] = '천기';

                // ### 태양
                $t = [9, 10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8];
                $taeyang_k = $t[$k];
                $taeyang[$taeyang_k] = '태양';
                // ### 무곡
                $t = [8, 9, 10, 11, 0, 1, 2, 3, 4, 5, 6, 7];
                $mugok_k = $t[$k];
                $mugok[$mugok_k] = '무곡';
                // ### 천동
                $t = [7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5, 6];
                $chendong_k = $t[$k];
                $chendong[$chendong_k] = '천동';
                // ### 염정
                $t = [4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2, 3];
                $yeamjung_k = $t[$k];
                $yeamjung[$yeamjung_k] = '염정';

            }
        }

        return [
            'cheanbu' => $cheanbu,
            'chengi' => $chengi,
            'taeyang' => $taeyang,
            'mugok' => $mugok,
            'chendong' => $chendong,
            'yeamjung' => $yeamjung,
        ];
    }

    /**
     * 나머지 천부성계 찾기
     * 4.태음, 11.탐랑, 12.거문, 13.천상, 14.천량, 6.칠살, 7.파군
     */
    private function cheanbus($cheanbu)
    {
        $taeum = array_fill(0, 12, null);
        $tamrang = array_fill(0, 12, null);
        $geamun = array_fill(0, 12, null);
        $cheansang = array_fill(0, 12, null);
        $cheanryang = array_fill(0, 12, null);
        $chilsal = array_fill(0, 12, null);
        $pagun = array_fill(0, 12, null);
        foreach ($cheanbu as $k => $v) {
            if ($v) {
                // ### 태음
                $t = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0];
                $taeum_k = $t[$k];
                $taeum[$taeum_k] = '태음';
                // ### 탐랑
                $t = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1];
                $tamrang_k = $t[$k];
                $tamrang[$tamrang_k] = '탐랑';
                // ### 거문
                $t = [3, 4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2];
                $geamun_k = $t[$k];
                $geamun[$geamun_k] = '거문';
                // ### 천상
                $t = [4, 5, 6, 7, 8, 9, 10, 11, 0, 1, 2, 3];
                $cheansang_k = $t[$k];
                $cheansang[$cheansang_k] = '천상';
                // ### 천량
                $t = [5, 6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4];
                $cheanryang_k = $t[$k];
                $cheanryang[$cheanryang_k] = '천량';
                // ### 칠살
                $t = [6, 7, 8, 9, 10, 11, 0, 1, 2, 3, 4, 5];
                $chilsal_k = $t[$k];
                $chilsal[$chilsal_k] = '칠살';
                // ### 파군
                $t = [10, 11, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                $pagun_k = $t[$k];
                $pagun[$pagun_k] = '파군';
            }
        }

        return [
            'taeum' => $taeum,
            'tamrang' => $tamrang,
            'geamun' => $geamun,
            'cheansang' => $cheansang,
            'cheanryang' => $cheanryang,
            'chilsal' => $chilsal,
            'pagun' => $pagun,
        ];
    }

    /**
     * 12궁 찾기
     */
    private function gung($myung)
    {
        $gung = array_fill(0, 12, null);
        $gung12 = ['父母', '福德', '田宅', '官祿', '奴僕', '遷移', '疾厄', '財帛', '子女', '夫妻', '兄弟'];
        // [부모, 복덕, 전택, 관룍, 노족, 천이, 칠액, 재백, 자녀, 부처, 형제]
        foreach ($myung as $k => $v) {
            if ($v) {
                for ($i = 0; $i < 11; $i++) { // gung 1 ~ 11, 0 제외
                    $j = $i + ($k + 1);
                    if ($j > 11) {
                        $j = abs(12 - $j);
                    }
                    $gung[$j] = $gung12[$i];
                }
            }
        }

        return $gung;
    }

    private function getPalaceInfo($jamidusu, $palace_offset)
    {
        // 명궁의 인덱스(0~11)를 찾습니다.
        $myung_index = array_search('명', $jamidusu->myung);
        if ($myung_index === false) {
            return (object) ['gung' => null, 'jusung14' => null];
        }

        // 명궁으로부터의 상대적 위치를 계산하여 해당 궁의 인덱스를 찾습니다.
        // (예: 부모궁은 명궁에서 시계방향으로 1칸 -> +1)
        $palace_index = ($myung_index + $palace_offset + 12) % 12;

        $palaceOrder = ['寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥', '子', '丑'];
        $gung = $palaceOrder[$palace_index];

        $jusung14 = $this->jusung14($palace_index, $jamidusu->jusung14);

        // 차성안궁 로직은 각 궁의 대궁(對宮)이 고정되어 있다는 규칙을 이용하면 더 간단해집니다.
        // (대궁의 인덱스 = (현재 궁 인덱스 + 6) % 12)
        if (! $jusung14) {
            $opposite_palace_index = ($palace_index + 6) % 12;
            $jusung14 = $this->jusung14($opposite_palace_index, $jamidusu->jusung14);
        }

        return (object) [
            'gung' => $gung,
            'jusung14' => $jusung14,
        ];
    }

    private function placeStarByMap(string $starName, int $position): array
    {
        $palace = array_fill(0, 12, null);
        if ($position >= 0 && $position < 12) {
            $palace[$position] = $starName;
        }

        return $palace;
    }
}
