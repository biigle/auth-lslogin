<?php

namespace Biigle\Modules\AuthLSLogin\Http\Controllers;

use Biigle\Http\Controllers\Controller;
use Biigle\Modules\AuthLSLogin\LsloginId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * @param Request $request
     * @return mixed
     */
    public function callback(Request $request)
    {
        $user = Socialite::driver('lifesciencelogin')->user();

        $lslId = LsloginId::with('user')->find($user->id);
        if ($lslId) {
            Auth::login($lslId->user);

            return redirect()->route('home');
        } elseif ($request->user()) {
            LsloginId::create([
                'id' => $user->id,
                'user_id' => $request->user()->id,
            ]);

            return redirect()->route('home');
        }

        $request->session()->put('lslogin-token', $user->token);

        return redirect()->route('lslogin-register-form');
    }
}
