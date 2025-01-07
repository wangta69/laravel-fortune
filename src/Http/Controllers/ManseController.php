<?php
namespace Pondol\Fortune\Http\Controllers;

use Pondol\Fortune\Facades\Manse;
use App\Http\Controllers\Controller;

class ManseController extends Controller
{
  public function __construct()
  {
  }

  /**
   * @param String $sl : solar | lunar
   */
  public function manse($ymdhi, $sl='solar', $leap=false) {
    // $manse = Manse::create($sl, $ymdhi, $leap);
    $manse = Manse::ymdhi($ymdhi)->sl($sl)->leap($leap)->create();
    return response()->json($manse, 200, [], JSON_UNESCAPED_UNICODE);
  }
}