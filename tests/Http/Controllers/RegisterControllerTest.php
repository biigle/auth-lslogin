<?php

namespace Biigle\Tests\Modules\AuthLSLogin\Http\Controllers;

use Biigle\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Session;
use TestCase;


class RegisterControllerTest extends TestCase
{
    public function testShowRegistrationForm()
    {
        // user should enter affiliation and check terms/privacy
    }

    public function testShowRegistrationFormWithoutToken()
    {
        //
    }

    public function testShowRegistrationFormAuthenticated()
    {
        //
    }

    public function testShowRegistrationFormDisabledRegistration()
    {
        //
    }

    public function testRegister()
    {
        // should not require honeypot if the token is in the session
    }

    public function testRegisterEmailTaken()
    {
        // case insensitive
        // suggest to connect in account settings instead
    }

    public function testRegisterWithoutToken()
    {
        //
    }

    public function testRegisterDisabledRegistration()
    {
        //
    }

    public function testRegisterAuthenticated()
    {
        //
    }

    public function testRegisterAdminConfirmationDisabled()
    {
        //
    }

    public function testRegisterAdminConfirmationEnabled()
    {
        //
    }
}
