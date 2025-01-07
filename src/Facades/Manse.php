<?php
namespace Pondol\Fortune\Facades;

use Illuminate\Support\Facades\Facade;

class Manse extends Facade
{

  protected static $cached = false;
  protected static function getFacadeAccessor()
  {
    return 'manse';
  }

  public static function refresh()
  {
    static::clearResolvedInstance(static::getFacadeAccessor());

    return static::getFacadeRoot();
  }
}
