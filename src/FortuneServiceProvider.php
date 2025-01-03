<?php
namespace Pondol\Fortune;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

// use Pondol\Kftc\Console\Commands\InstallCommand;
use Pondol\Fortune\Services\LunarSolar\Lunar;

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
    // $this->app->singleton('meta', function () {
    //   return new Meta();
    // });
    $this->app->bind('lunar', function () {
      return new Lunar();
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
    if (!config()->has('pondol-saju')) {
      $this->publishes([
        __DIR__ . '/config/pondol-saju.php' => config_path('pondol-saju.php'),
      ], 'config');  
    } 
      
    $this->mergeConfigFrom(
      __DIR__ . '/config/pondol-saju.php',
      'pondol-saju'
    );

    // Register migrations
    $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    $this->loadViewsFrom(__DIR__.'/resources/views', 'pondol-saju');

    // $this->commands([
    //   InstallCommand::class
    // ]);

    $this->loadSajuRoutes();
  }

  private function loadSajuRoutes()
  {

    $config = config('pondol-saju.route_saju');
    Route::prefix($config['prefix'])
      ->as($config['as'])
      ->middleware($config['middleware'])
      ->namespace('Pondol\Saju\Http\Controllers')
      ->group(__DIR__ . '/routes/saju.php');


    $config = config('pondol-saju.route_saju_admin');
    Route::prefix($config['prefix'])
      ->as($config['as'])
      ->middleware($config['middleware'])
      ->namespace('Pondol\Saju\Http\Controllers\Admin')
      ->group(__DIR__ . '/routes/saju-admin.php');
  }
}
