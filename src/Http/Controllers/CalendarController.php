<?php
namespace Pondol\Fortune\Http\Controllers;

use Pondol\Fortune\Facades\Calendar;
use Pondol\Fortune\Facades\Saju;

use App\Http\Controllers\Controller;

class CalendarController extends Controller
{

  /**
   * @param String $yyyymm  양력 202501
   * 특정월의 카렌다 출력
   */
  public function lunar($yyyymm) {
    $days = Calendar::lunarCalendar($yyyymm);
    return response()->json($days, 200, [], JSON_UNESCAPED_UNICODE); 
  }

  /**
   * 특정년의 절기 출력
   */
  public function season24($yyyy) {
    $season24 = Calendar::season24Calendar($yyyy);
    return response()->json($season24, 200, [], JSON_UNESCAPED_UNICODE); 
  }

  /**
   * 이사택일
   */
  public function move($yyyymm) {
    $user = ['ymdhi'=>'200005051730', 'sl'=>'lunar'];
    $saju = Saju::ymdhi($user['ymdhi'])->sl($user['sl'])->create(); // 본인의 생년월일 생시
    $move = Calendar::move($saju, $yyyymm);
    return response()->json($move, 200, [], JSON_UNESCAPED_UNICODE); 
  }

  /**
   * 결혼택일
   */
  public function marriage($yyyymm) {
    $user = ['ymdhi'=>'200005051730', 'sl'=>'lunar'];
    $partner = ['ymdhi'=>'200005051730', 'sl'=>'lunar'];
    $saju = Saju::ymdhi($user['ymdhi'])->sl($user['sl'])->create(); // 본인의 생년월일 생시
    $p_saju = Saju::ymdhi($partner['ymdhi'])->sl($partner['sl'])->create(); // 상대방의 생년월일 생시
    $move = Calendar::marriage($saju, $p_saju, $yyyymm);
    return response()->json($move, 200, [], JSON_UNESCAPED_UNICODE); 
  }

  /**
   * 3재
   */
  public function samjae($yyyy) {
    $samjae = Calendar::samjae($yyyy);
    return response()->json($samjae, 200, [], JSON_UNESCAPED_UNICODE); 
  }
}


