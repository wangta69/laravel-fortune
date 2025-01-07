<?php
namespace Pondol\Fortune\Http\Controllers;

use Pondol\Fortune\Facades\Saju;
use Pondol\Fortune\Facades\Manse;
use App\Http\Controllers\Controller;

class SajuController extends Controller
{
  public function __construct()
  {
  }

  /**
   * @param String $sl : solar | lunar
   */
  public function saju($ymdhi, $sl='solar', $leap=false) {
    // $saju = Saju::create($sl, $ymdhi, $leap);
    $saju = Manse::ymdhi($ymdhi)->sl($sl)->leap($leap)->create()
    ->oheng()
    ->sinsal12()
    ->woonsung12()
    ->zizangan()
    ->sinsal()
    ->daewoon();
    return response()->json($saju, 200, [], JSON_UNESCAPED_UNICODE); 
  }
}


