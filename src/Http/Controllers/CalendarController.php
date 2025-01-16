<?php
namespace Pondol\Fortune\Http\Controllers;


use Pondol\Fortune\Facades\Calendar;
use App\Http\Controllers\Controller;
use Pondol\Fortune\Facades\Manse;

class CalendarController extends Controller
{
  public function __construct()
  {
  }

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
    $manse = Manse::ymdhi('197210071730')->sl('lunar')->create();
    $move = Calendar::move($manse, $yyyymm);
    return response()->json($move, 200, [], JSON_UNESCAPED_UNICODE); 
  }

  /**
   * 결혼택일
   */
  public function marriage($yyyymm) {
    $manse = Manse::ymdhi('197210071730')->sl('lunar')->create();
    $move = Calendar::marriage($manse, $yyyymm);
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


