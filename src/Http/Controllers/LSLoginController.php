<?php

namespace Biigle\Modules\AuthLSLogin\Http\Controllers;

use Biigle\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class LSLoginController extends Controller
{
    /**
     * Redirect to the authentication provider
     *
     * @return mixed
     */
    public function redirect()
    {
        return Socialite::driver('lifesciencelogin')->redirect();
    }

    /**
     * Handle the authentication response
     *
     * @return mixed
     */
    public function callback()
    {
        $user = Socialite::driver('lifesciencelogin')->user();

        //...
    }
}
