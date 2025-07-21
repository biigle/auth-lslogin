<?php

namespace Biigle\Modules\AuthLSLogin\Http\Controllers;

use Biigle\User;
use Biigle\Http\Controllers\Controller;
use Biigle\Modules\AuthLSLogin\LsloginId;
use Exception;
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
        try {
            $user = Socialite::driver('lifesciencelogin')->user();
        } catch (Exception $e) {
            $route = $request->user() ? 'settings-authentication' : 'login';

            return redirect()
                ->route($route)
                ->withErrors(['lslogin-id' => 'There was an unexpected error. Please try again.']);
        }

        $lslId = LsloginId::with('user')->find($user->id);

        if ($request->user()) {
            // Case: The authenticated user wants to connect the account with LSL.
            if (!$lslId) {
                LsloginId::create([
                    'id' => $user->id,
                    'user_id' => $request->user()->id,
                ]);

                return redirect()->route('settings-authentication')
                    ->with('message', 'Your account is now connected to Life Science Login.')
                    ->with('messageType', 'success');

            // Case: The authenticated user already connected their account with LSL.
            } elseif ($lslId->user_id === $request->user()->id) {
                return redirect()->route('settings-authentication');

            // Case: Another user already connected their account with LSL.
            } else {
                return redirect()
                    ->route('settings-authentication')
                    ->withErrors(['lslogin-id' => 'The Life Science Login ID is already connected to another account.']);
            }
        }

        // Case: The user wants to log in with LSL
        if ($lslId) {
            Auth::login($lslId->user);

            return redirect()->route('home');
        }

        // Case: A new user wants to register using LSL.
        if (config('biigle.user_registration')) {
            $request->session()->put('lslogin-token', $user->token);

            return redirect()->route('lslogin-register-form');
        }

        // Case: The account exists but must be connected first.
        if (User::where('email', $user->email)->exists()) {
            return redirect()
                ->route('login')
                ->withErrors(['lslogin-id' => 'The email has already been taken. You can connect your existing account to Life Science Login in the account authorization settings.']);
        }

        // Case: The account does not exist yet and new registrations are disabled.
        return redirect()
            ->route('login')
            ->withErrors(['lslogin-id' => 'The user does not exist and new registrations are disabled.']);
    }
}
