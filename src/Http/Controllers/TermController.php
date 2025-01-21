<?php
namespace Pondol\Fortune\Http\Controllers;

use App\Http\Controllers\Controller;
use DB;
class TermController extends Controller
{
  public function __construct()
  {
  }

  public function term($term) {
    $result = DB::table('json_key_values')->where('key', trim($term))->first();
    return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
  }

  /**
   * @param String $sl : solar | lunar
   */
  public function update() {
  }

  
}

