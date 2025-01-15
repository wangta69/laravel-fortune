<?php
namespace Pondol\Fortune\Facades;

use Illuminate\Support\Facades\Facade;

class Calendar extends Facade
{

  protected static $cached = false;
  protected static function getFacadeAccessor()
  {
    return 'calendar';
  }
}
