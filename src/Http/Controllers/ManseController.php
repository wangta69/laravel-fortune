<?php
namespace Pondol\Fortune\Http\Controllers;

use Pondol\Fortune\Facades\Lunar;
use App\Http\Controllers\Controller;

class ManseController extends Controller
{
  public function __construct()
  {
  }

  /**
   * @param String $sl : solar | lunar
   */
  public function manse($ymd, $sl='solar', $leap=false) {
    $manse = new Manse;
    $manse->create($sl, $ymd, $leap);
   

    return response()->json($manse, 200, [], JSON_UNESCAPED_UNICODE);
    
  }
}


class Manse {
  public $sl = 'solar'; // $lunar
  public $solar; // 양력
  public $lunar; // 음력
  public $leap = false; // 윤달여부
  public $year_h; // 연간
  public $year_e; // 연지

  private $year;
  private $month;
  private $day;

  public function __construct()
  {
  }

  public function ymd($ymd) {
    switch($this->sl) {
      case 'solar': $this->solar = $ymd; break;
      case 'lunar': $this->lunar = $ymd; break;
    }
  }

  public function create($sl, $ymd, $leap) {
    $this->sl = $sl;
    $this->leap = $leap;
    $this->ymd($ymd);

    switch($sl) {
      case 'solar':
        $manse = Lunar::tolunar($ymd);
        break;
      case 'lunar':
        $manse = Lunar::toSolar($ymd, $leap);
        break;
    }

    $this->year($manse->year)->month($manse->month)->day($manse->day);
    $this->year_h = $manse->hgan;
    $this->year_e = $manse->hji;
  }

  public function year($year) {
    $this->year = str_pad($year, 4, '0', STR_PAD_LEFT);
    return $this;
  }

  public function month($month) {
    $this->month = str_pad($month, 2, '0', STR_PAD_LEFT);
    return $this;
  }

  public function day($day) {
    $this->day = str_pad($day, 2, '0', STR_PAD_LEFT);
    $ymd = $this->year.$this->month.$this->day; 
    switch($this->sl) {
      case 'solar': $this->lunar = $ymd; break;
      case 'lunar': $this->solar = $ymd; break;
    }
  }
}