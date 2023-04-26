<?php

namespace Biigle\Modules\AuthLSLogin\Http\Controllers;

use Biigle\Http\Controllers\Auth\RegisterController as BaseController;
use Biigle\Modules\AuthLSLogin\LsloginId;
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
        $token = $request->session()->get('lslogin-token');
        if (!$token) {
            // error
        }

        $user = Socialite::driver('lifesciencelogin')->userFromToken($token);

        $request->merge([
            'id' => $user->id,
            'email' => $user->email,
            'firstname' => $user->given_name,
            'lastname' => $user->family_name,
            'password' => Str::random(8),
        ]);

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
