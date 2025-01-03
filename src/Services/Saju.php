<?php
namespace Pondol\Fortune\Services;

use Illuminate\Support\Facades\Route;

class Saju
{

 
  
  public function __construct()
  {

    
  }
  

  
  public function get() {
    $route_name = Route::currentRouteName(); 
    // $type='route';
    if(!$route_name) {
      $route_name = request()->path();
      // $type='path';
    }
    $route_params = [];
    foreach(Route::getCurrentRoute()->parameterNames as $p) {
      $route_params[$p] = Route::getCurrentRoute()->originalParameter($p);
    }
    return $this->set($route_name, $route_params);
  }
  
  

  public function title($title) {
    $this->title = $title ?? $this->title;
    return $this;
  }


}

