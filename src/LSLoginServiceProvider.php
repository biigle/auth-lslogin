<?php

namespace Biigle\Modules\AuthLSLogin;

use Biigle\Services\Modules;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\LifeScienceLogin\LifeScienceLoginExtendSocialite;

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
        $this->loadViewsFrom(__DIR__.'/resources/views', 'auth-lslogin');
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');

        $router->group([
            'namespace' => 'Biigle\Modules\AuthLSLogin\Http\Controllers',
            'middleware' => 'web',
        ], function ($router) {
            require __DIR__.'/Http/routes.php';
        });

        $modules->register('auth-lslogin', [
            'viewMixins' => [
                'loginButton',
                'registerButton',
                'settingsThirdPartyAuthentication',
            ],
        ]);

        $this->publishes([
            __DIR__.'/public/assets' => public_path('vendor/auth-lslogin'),
        ], 'public');

        Event::listen(
            SocialiteWasCalled::class,
            [LifeScienceLoginExtendSocialite::class, 'handle']
        );
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
