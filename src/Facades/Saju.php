<?php
namespace Pondol\Fortune\Facades;

use Illuminate\Support\Facades\Facade;

class Saju extends Facade
{

  // protected static $cached = false;
  protected static function getFacadeAccessor()
  {
    return 'saju';
  }

  // public static function refresh()
  // {
  //   static::clearResolvedInstance(static::getFacadeAccessor());

  //   return static::getFacadeRoot();
  // }
}
