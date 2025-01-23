<?php
namespace Pondol\Fortune\Http\Controllers;

use Pondol\Fortune\Facades\Saju;
use App\Http\Controllers\Controller;

class TojungController extends Controller
{
  public function __construct()
  {
  }

  /**
   * @param String $sl : solar | lunar
   */
  public function create($ymdhi, $sl='solar', $leap=false) {
    $saju = Saju::ymdhi($ymdhi)->sl($sl)->leap($leap)->create()->jakque(function($jakque){
      $jakque->set_year(date('Y'));
    });
    return response()->json($saju, 200, [], JSON_UNESCAPED_UNICODE);
  }
}