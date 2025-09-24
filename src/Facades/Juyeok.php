<?php
namespace Pondol\Fortune\Facades;

use Illuminate\Support\Facades\Facade;

class Juyeok extends Facade
{
  

  public static function __callStatic($method, $args)
    {
        // 1. 서비스 컨테이너에게 'saju' 서비스를 요청합니다.
        //    ServiceProvider의 'bind'에 의해 항상 새로운 인스턴스가 반환됩니다.
        $instance = app('juyeok');

        // 2. 새로 받은 인스턴스의 요청된 메소드(예: ymdhi)를 호출합니다.
        return $instance->{$method}(...$args);
    }
}
