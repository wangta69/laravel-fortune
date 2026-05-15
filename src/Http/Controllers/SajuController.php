<?php

namespace Pondol\Fortune\Http\Controllers;

use App\Http\Controllers\Controller;
use Pondol\Fortune\Facades\Saju;

class SajuController extends Controller
{
    public function __construct() {}

    /**
     * @param  string  $sl  : solar | lunar
     */
    public function saju($ymdhi, $sl = 'solar', $leap = false)
    {
        // 1. 먼저 사주 객체를 생성하여 변수에 담습니다.
        $saju = Saju::ymdhi($ymdhi)->sl($sl)->leap($leap)->create();

        // 2. [수정 핵심] 체이닝을 하지 않고 각각 호출합니다.
        // 각 메서드는 $saju 객체 내부의 데이터를 채워주는 역할을 합니다.
        $saju->oheng();
        $saju->sinsal12();
        $saju->woonsung12();
        $saju->zizangan();
        $saju->sinsal();
        $saju->daewoon();
        $saju->saewoon();

        // 3. 모든 데이터가 채워진 $saju 객체를 JSON으로 반환합니다.
        return response()->json($saju, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
