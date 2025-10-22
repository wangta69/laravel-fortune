<?php

namespace Pondol\Fortune\Services\Calendar;

// use Illuminate\Http\Request;
use Carbon\Carbon;
use Pondol\Fortune\Facades\Lunar;
use Pondol\Fortune\Facades\Saju;

/**
 * 12실살을 제외한 기타 길신 및 흉신 구하기
 * 일지기준으로 하면 집안일
 * 년지기준으로 하면 바깥일
 */

class Calendar
{
    /**
     * 음력달력출력
     * @param String $yyyymm 202501 (2025년 01월)
     */
    public function lunarCalendar($yyyymm)
    {
        $lunarCalendar = new LunarCalendar();
        return $lunarCalendar->cal($yyyymm);
    }

    /**
     * 특정년의 절기 출력
     */
    public function season24Calendar($yyyy)
    {
        $season24Calendar = new Season24Calendar();
        return $season24Calendar->cal($yyyy);
    }

    /**
     * 월별 이사택일
     */
    public function moveCalendar($saju, $yyyymm, $options)
    {
        $moveCalendar = new MoveCalendar();
        return $moveCalendar->cal($saju, $yyyymm, $options);
    }

    /**
     * 월별 결혼 택일
     * @param object $saju_male   신랑 사주 객체
     * @param object $saju_female 신부 사주 객체
     * @param string $yyyymm      대상 년월 (예: '202511')
     * @param array  $options     추가 옵션
     */
    public function marriageCalendar($saju_male, $saju_female, $yyyymm, $options = [])
    {
        $marriageCalendar = new MarriageCalendar();
        return $marriageCalendar->cal($saju_male, $saju_female, $yyyymm, $options);
    }

    /**
     *  특정년의 3재 출력
     */
    public function samjae($yyyy)
    {
        $samjae = new Samjae();
        return $samjae->cal($yyyy);


    }
}
