<?php
namespace Pondol\Fortune\Facades;

use Illuminate\Support\Facades\Facade;

class JamiDusu extends Facade
{
  protected static function getFacadeAccessor()
  {
      // ServiceProvider에 bind한 이름과 동일하게
    return 'jamidusu';
  }
}