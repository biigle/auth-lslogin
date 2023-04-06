<?php

namespace Biigle\Modules\AuthLSLogin;

use Biigle\Services\Modules;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class LSLoginServiceProvider extends ServiceProvider
{

   /**
   * Bootstrap the application events.
   *
   * @param Modules $modules
   * @param  Router  $router
   * @return  void
   */
    public function boot(Modules $modules, Router $router)
    {
        // $this->loadViewsFrom(__DIR__.'/resources/views', 'auth-lslogin');

        // $router->group([
        //     'namespace' => 'Biigle\Modules\Module\Http\Controllers',
        //     'middleware' => 'web',
        // ], function ($router) {
        //     require __DIR__.'/Http/routes.php';
        // });

        // $modules->register('module', [
        //     'viewMixins' => [
        //         'dashboardMain',
        //     ],
        // ]);

        // $this->publishes([
        //     __DIR__.'/public/assets' => public_path('vendor/auth-lslogin'),
        // ], 'public');
    }

    /**
    * Register the service provider.
    *
    * @return  void
    */
    public function register()
    {
        //
    }
}
