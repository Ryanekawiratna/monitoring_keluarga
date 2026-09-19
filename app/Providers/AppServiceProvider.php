<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Paginator::useBootstrapFive();
    //
    // View::addNamespace(
    //   'template',
    //   app_path('Modules/Template/Views')
    // );

    // View::addNamespace(
    //   'dashboard',
    //   app_path('Modules/Dashboard/Views')
    // );

    // foreach (glob(app_path('Modules/*/Routes/web.php')) as $routeFile) {
    //   if (file_exists($routeFile)) {
    //     require $routeFile;
    //   }
    // }
  }
}
