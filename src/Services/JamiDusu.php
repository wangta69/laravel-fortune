<?php

namespace Pondol\Fortune\Services;

use Pondol\Fortune\Traits\Jami\JamiBaseTrait;
use Pondol\Fortune\Traits\Jami\JamiFortuneTrait;
use Pondol\Fortune\Traits\Jami\JamiMappingTrait;
use Pondol\Fortune\Traits\Jami\JamiStarTrait;

class JamiDusu
{
    // 원본 로직을 분리한 4개의 트레이트 사용
    use JamiBaseTrait, JamiFortuneTrait, JamiMappingTrait, JamiStarTrait;

    /**
     * 12궁 인덱스 상수 (하위 호환 유지)
     */
    public const GUNG_IN = 0;

    public const GUNG_MYO = 1;

    public const GUNG_JIN = 2;

    public const GUNG_SA = 3;

    public const GUNG_O = 4;

    public const GUNG_MI = 5;

    public const GUNG_SIN = 6;

    public const GUNG_YU = 7;

    public const GUNG_SUL = 8;

    public const GUNG_HAE = 9;

    public const GUNG_JA = 10;

    public const GUNG_CHUK = 11;

    /**
     * [PUBLIC] 전체 명반 데이터 생성
     * 기존 사용자가 호출하는 핵심 메소드입니다. 파라미터 구조를 유지합니다.
     */
    public function myungbanData($saju, $today)
    {
        $gender = $saju->gender;
        $year_h = $saju->get_h('year');
        $year_e = $saju->get_e('year');
        $month_e = $saju->get_e('month');
        $hour_e = $saju->get_e('hour');
        $age = $saju->korean_age;
        [$lYear, $lMonth, $lDay] = explode('-', $saju->lunar);
        $lunar_month = sprintf('%02d', $lMonth); // 01, 02... 포맷
        $lunar_day = (int) $lDay;

        $data = new \stdClass;
        $data->arr = [];

        // 1. 기초 명반 (BaseTrait)
        $gabja = $this->basic($year_h);
        $data->arr['gabja'] = $gabja;
        $myungsin = $this->myungsin($hour_e, (int) $lMonth);

        // 1. 내부 배열(arr)에 저장함과 동시에
        // 엔진 로직(getPalaceInfo 등)이 참조할 수 있도록 객체 직속 속성으로도 할당합니다.
        $data->myung = $myungsin['myung'];
        $data->sin = $myungsin['sin'];
        $data->gabja = $gabja;

        $data->arr['myung'] = $myungsin['myung'];
        $data->arr['sin'] = $myungsin['sin'];
        $data->myung_guk = $this->getMyeongguk($data->arr['myung'], $gabja);
        $data->myung_ju = $this->myung_ju($data->arr['myung']);
        $data->sin_ju = $this->sin_ju($year_e);
        $yangum = $this->yangum($gender, $year_h);

        // 2. 14주성 배치 및 묘왕리함 (StarTrait + MappingTrait)
        $data->arr['jami'] = $this->jami($data->myung_guk, $lDay);
        $data->arr['jami_14'] = $this->getJungsungStatus($data->arr['jami'], $this->STAR_STATUS_DEFAULT['jami']);

        $jamiStars = $this->jamis($data->arr['jami']);
        foreach (['cheanbu', 'chengi', 'taeyang', 'mugok', 'chendong', 'yeamjung'] as $s) {
            $data->arr[$s] = $jamiStars[$s];
            $data->arr[$s.'_14'] = $this->getJungsungStatus($data->arr[$s], $this->STAR_STATUS_DEFAULT[$s]);
        }

        $cheanbuStars = $this->cheanbus($data->arr['cheanbu']);
        foreach (['taeum', 'tamrang', 'geamun', 'cheansang', 'cheanryang', 'chilsal', 'pagun'] as $s) {
            $data->arr[$s] = $cheanbuStars[$s];
            $data->arr[$s.'_14'] = $this->getJungsungStatus($data->arr[$s], $this->STAR_STATUS_DEFAULT[$s]);
        }

        // 3. 엔진 내부(getPalaceInfo)에서 차성안궁 및 사화 처리를 위해 14주성 통합 속성이 필요함
        $data->jusung14 = [
            'jami' => $data->arr['jami'],
            'cheanbu' => $data->arr['cheanbu'],
            'chengi' => $data->arr['chengi'],
            'taeyang' => $data->arr['taeyang'],
            'mugok' => $data->arr['mugok'],
            'chendong' => $data->arr['chendong'],
            'yeamjung' => $data->arr['yeamjung'],
            'taeum' => $data->arr['taeum'],
            'tamrang' => $data->arr['tamrang'],
            'geamun' => $data->arr['geamun'],
            'cheansang' => $data->arr['cheansang'],
            'cheanryang' => $data->arr['cheanryang'],
            'chilsal' => $data->arr['chilsal'],
            'pagun' => $data->arr['pagun'],
        ];

        // 3. 12사항궁 및 기타 보조성/신살 배치 (StarTrait)
        $data->arr['gung'] = $this->gung($data->arr['myung']);

        // 5:27PM 원본에 있던 모든 보조성 배치 로직 실행
        // (JamiStarTrait 내부에 구현된 배치 함수들을 여기서 호출합니다.)
        $this->processExtraStars(
            $data,
            $year_h,
            $year_e,
            $month_e,
            $hour_e,
            $lunar_month, // "01" 형태의 문자열 권장 이미 위에서 sprintf('%02d', $lunar_month) 처리가 되어 있어야 함
            (int) $lunar_day,
            $yangum
        );

        // 4. 운로 및 유년 (FortuneTrait)
        $data->arr['daehan'] = $this->daehan($data->arr['myung'], $yangum, $data->myung_guk);
        $data->current_daehan = $this->current_daehan($data->arr['daehan'], $gabja, $yangum, $age);
        $data->arr['unsung'] = $this->unsung($yangum, $data->myung_guk);
        $data->arr['age'] = $this->ages($age, $today->get_e('year'));
        $data->arr['youyeon'] = $this->youyeon($year_e, $gabja);

        return $data;
    }

    /**
     * [PUBLIC] 특정 궁의 정보를 가져오는 래퍼 메서드들 (하위 호환)
     */
    public function jusungMyung($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 0);
    }

    public function jusungBumo($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 1);
    }

    public function jusungBokduk($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 2);
    }

    public function jusungJeuntaek($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 3);
    }

    public function jusungGuanrok($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 4);
    }

    public function jusungNobok($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 5);
    }

    public function jusungChene($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 6);
    }

    public function jusungJilaek($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 7);
    }

    public function jusungJaebaek($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 8);
    }

    public function jusungJanyeo($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 9);
    }

    public function jusungBubu($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 10);
    }

    public function jusungHyungjae($jamidusu)
    {
        return $this->getPalaceInfo($jamidusu, 11);
    }

    /**
     * [PUBLIC] 기존 명반의 주성 정보 조회용 (Legacy)
     */
    public function getdefaultData($gender, $yyyymmdd, $year_h, $hour_e)
    {
        [$lYear, $lMonth, $lDay] = explode('-', $yyyymmdd);
        $data = new \stdClass;

        // 1. 기초 명반 생성
        $gabja = $this->basic($year_h);
        $myungsin = $this->myungsin($hour_e, (int) $lMonth);

        // --- [이 부분이 추가되어야 합니다] ---
        // getPalaceInfo 등에서 명궁 위치를 찾기 위해 반드시 필요함
        $data->myung = $myungsin['myung'];
        $data->sin = $myungsin['sin'];
        $data->gabja = $gabja;
        // ----------------------------------

        $data->myung_guk = $this->getMyeongguk($myungsin['myung'], $gabja);

        // 14 주성 찾기
        $data->jusung14 = [];
        $jami = $this->jami($data->myung_guk, $lDay);
        $jamiStars = $this->jamis($jami);
        $cheanbuStars = $this->cheanbus($jamiStars['cheanbu']);

        // 모든 주성 정보를 jusung14에 합침 (getPalaceInfo 내부의 jusung14 메서드 호환용)
        $data->jusung14 = [
            'jami' => $jami,
            'cheanbu' => $jamiStars['cheanbu'],
            'chengi' => $jamiStars['chengi'],
            'taeyang' => $jamiStars['taeyang'],
            'mugok' => $jamiStars['mugok'],
            'chendong' => $jamiStars['chendong'],
            'yeamjung' => $jamiStars['yeamjung'],
            'taeum' => $cheanbuStars['taeum'],
            'tamrang' => $cheanbuStars['tamrang'],
            'geamun' => $cheanbuStars['geamun'],
            'cheansang' => $cheanbuStars['cheansang'],
            'cheanryang' => $cheanbuStars['cheanryang'],
            'chilsal' => $cheanbuStars['chilsal'],
            'pagun' => $cheanbuStars['pagun'],
        ];

        return $data;
    }
}
