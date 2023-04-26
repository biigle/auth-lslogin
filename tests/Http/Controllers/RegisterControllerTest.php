<?php

namespace Biigle\Tests\Modules\AuthLSLogin\Http\Controllers;

use Biigle\Modules\AuthLSLogin\LsloginId;
use Biigle\Role;
use Biigle\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Session;
use TestCase;

class RegisterControllerTest extends TestCase
{
    public function testShowRegistrationForm()
    {
        $this->withSession(['lslogin-token' => 'mytoken'])
            ->get('auth/lslogin/register')
            ->assertSuccessful();
    }

    public function testShowRegistrationFormWithoutToken()
    {
        $this->get('auth/lslogin/register')->assertRedirectToRoute('register');
    }

    public function testShowRegistrationFormAuthenticated()
    {
        $user = User::factory()->create();
        $this->be($user);
        $this->get('auth/lslogin/register')->assertRedirectToRoute('home');
    }

    public function testShowRegistrationFormDisabledRegistration()
    {
        config(['biigle.user_registration' => false]);
        $this->get('auth/lslogin/register')->assertStatus(404);
    }

    public function testRegister()
    {
        $user = new SocialiteUser;
        $user->map([
            'id' => 'mylsloginid',
            'given_name' => 'Joe',
            'family_name' => 'User',
            'email' => 'joe@example.com',
        ]);
        Socialite::shouldReceive('driver->userFromToken')
            ->with('mytoken')
            ->andReturn($user);

        $this->withSession(['lslogin-token' => 'mytoken'])
            ->post('auth/lslogin/register', [
                '_token'    => Session::token(),
                'affiliation' => 'something',
            ])
            ->assertSessionMissing('lslogin-token')
            ->assertRedirectToRoute('home');

        $user = User::where('email', 'joe@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Joe', $user->firstname);
        $this->assertEquals('User', $user->lastname);
        $this->assertEquals('something', $user->affiliation);
        $this->assertEquals(Role::editorId(), $user->role_id);

        $this->assertTrue(LsloginId::where('user_id', $user->id)->where('id', 'mylsloginid')->exists());
    }

    public function testRegisterMissingAffiliation()
    {
        // the token should be left in the session
    }

    public function testRegisterEmailTaken()
    {
        // case insensitive
        // suggest to connect in account settings instead
    }

    public function testRegisterIdTaken()
    {
        // show error message
    }

    public function testRegisterWithoutToken()
    {
        // redirect to register route
    }

    public function testRegisterInvalidToken()
    {
        // show error message
    }

    public function testRegisterPrivacy()
    {
        //
    }

    public function testRegisterTerms()
    {
        //
    }

    public function testRegisterDisabledRegistration()
    {
        // not found?
    }

    public function testRegisterAuthenticated()
    {
        // redirect to home
    }
}
