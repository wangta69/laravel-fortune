<?php
namespace Pondol\Fortune;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

// use Pondol\Kftc\Console\Commands\InstallCommand;
use Pondol\Fortune\Services\LunarSolar\Lunar;
use Pondol\Fortune\Services\Manse;
use Pondol\Fortune\Services\Calendar\Calendar;

class FortuneServiceProvider extends ServiceProvider {


  /**
   * Where the route file lives, both inside the package and in the app (if overwritten).
   *
   * @var string
   */

	/**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {

    $this->app->bind('lunar', function () {
      return new Lunar();
    });

    $this->app->bind('manse', function () {
      return new Manse();
    });

    $this->app->singleton('calendar', function () {
      return new Calendar();
    });
  }

	/**
   * Bootstrap any application services.
   *
   * @return void
   */
	public function boot()
  {

    // Publish config file and merge
    if (!config()->has('pondol-fortune')) {
      $this->publishes([
        __DIR__ . '/config/pondol-fortune.php' => config_path('pondol-fortune.php'),
      ], 'config');  
    } 
      
    $this->mergeConfigFrom(
      __DIR__ . '/config/pondol-fortune.php',
      'pondol-fortune'
    );

    // Register migrations
    $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    $this->loadViewsFrom(__DIR__.'/resources/views', 'pondol-fortune');

    // $this->commands([
    //   InstallCommand::class
    // ]);

    $this->loadFortuneRoutes();
  }

  private function loadFortuneRoutes()
  {

    $config = config('pondol-fortune.route_fortune');
    Route::prefix($config['prefix'])
      ->as($config['as'])
      ->middleware($config['middleware'])
      ->namespace('Pondol\Fortune\Http\Controllers')
      ->group(__DIR__ . '/routes/fortune.php');


    $config = config('pondol-fortune.route_fortune_admin');
    Route::prefix($config['prefix'])
      ->as($config['as'])
      ->middleware($config['middleware'])
      ->namespace('Pondol\fortune\Http\Controllers\Admin')
      ->group(__DIR__ . '/routes/fortune-admin.php');
  }
}
