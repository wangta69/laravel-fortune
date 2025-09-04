<?php
namespace Pondol\Fortune\Facades;

use Illuminate\Support\Facades\Facade;

class Saju extends Facade
{

  // protected static $cached = false;
  // protected static function getFacadeAccessor()
  // {
  //   return 'saju';
  // }

  // 위의 방식으로는 객체가 두번 생성되지 않아서 아래 방법으로 처리
  public static function __callStatic($method, $args)
    {
        // 1. 서비스 컨테이너에게 'saju' 서비스를 요청합니다.
        //    ServiceProvider의 'bind'에 의해 항상 새로운 인스턴스가 반환됩니다.
        $instance = app('saju');

        // 2. 새로 받은 인스턴스의 요청된 메소드(예: ymdhi)를 호출합니다.
        return $instance->{$method}(...$args);
    }

  // public static function refresh()
  // {
  //   static::clearResolvedInstance(static::getFacadeAccessor());

  //   return static::getFacadeRoot();
  // }
}
