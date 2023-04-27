<?php

namespace Biigle\Modules\AuthLSLogin\Http\Controllers;

use Biigle\Http\Controllers\Auth\RegisterController as BaseController;
use Biigle\Modules\AuthLSLogin\LsloginId;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class RegisterController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function showRegistrationForm()
    {
        if ($this->isRegistrationDisabled()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        if (!session()->has('lslogin-token')) {
            return redirect()->route('register');
        }

        return view('auth-lslogin::register');
    }

    /**
     * {@inheritdoc}
     */
    public function register(Request $request)
    {
        if ($this->isRegistrationDisabled()) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $token = $request->session()->get('lslogin-token');
        if (!$token) {
            // Users should only arrive at the lslogin register form after completing the
            // authentication which sets the token. The case without token should not
            // happen but you never know...
            return redirect()->route('register');
        }

        try {
            $user = Socialite::driver('lifesciencelogin')->userFromToken($token);
        } catch (Exception $e) {
            $request->session()->forget('lslogin-token');

            return redirect()
                ->back()
                ->withErrors(['lslogin-id' => 'Could not retrieve user details from Life Science Login. Invalid token?']);
        }

        $request->merge([
            'id' => $user->id,
            'email' => $user->email,
            'firstname' => $user->given_name,
            'lastname' => $user->family_name,
            'password' => Str::random(8),
        ]);

        if (LsloginId::where('id', $user->id)->exists()) {
            $request->session()->forget('lslogin-token');

            return redirect()
                ->back()
                ->withErrors(['lslogin-id' => 'The Life Science Login ID is already connected with an account.']);
        }

        return parent::register($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function validator(array $data)
    {
        $validator = parent::validator($data);

        $rules = $validator->getRules();
        unset($rules['website']);
        unset($rules['homepage']);

        $validator->setRules($rules);
        $validator->setCustomMessages([
            'email.unique' => 'The email has already been taken. You can connect your existing account to Life Science Login in the account authorization settings.',
        ]);

        return $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function registered(Request $request, $user)
    {
        LsloginId::create([
            'id' => $request->input('id'),
            'user_id' => $user->id,
        ]);

        $request->session()->forget('lslogin-token');

        return parent::registered($request, $user);
    }
}
