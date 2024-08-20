<?php

namespace Biigle\Tests\Modules\AuthLSLogin\Http\Controllers;

use Biigle\Modules\AuthLSLogin\LsloginId;
use Biigle\Role;
use Biigle\User;
use Exception;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Session;
use TestCase;
use View;

class RegisterControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        config(['biigle.user_registration' => true]);
    }

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
        $this->assertSame('Joe', $user->firstname);
        $this->assertSame('User', $user->lastname);
        $this->assertSame('something', $user->affiliation);
        $this->assertSame(Role::editorId(), $user->role_id);

        $this->assertTrue(LsloginId::where('user_id', $user->id)->where('id', 'mylsloginid')->exists());
    }

    public function testRegisterMissingAffiliation()
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
            ])
            ->assertSessionHas('lslogin-token')
            ->assertInvalid('affiliation');
    }

    public function testRegisterEmailTaken()
    {
        User::factory()->create(['email' => 'joe@example.com']);
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
            ->assertInvalid('email');
    }

    public function testRegisterIdTaken()
    {
        LsloginId::factory()->create(['id' => 'mylsloginid']);
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
            ->assertInvalid('lslogin-id');
    }

    public function testRegisterWithoutToken()
    {
        $this->post('auth/lslogin/register', [
                '_token'    => Session::token(),
                'affiliation' => 'something',
            ])
            ->assertRedirectToRoute('register');
    }

    public function testRegisterInvalidToken()
    {
        Socialite::shouldReceive('driver->userFromToken')->andThrow(Exception::class);
        $this->withSession(['lslogin-token' => 'mytoken'])
            ->post('auth/lslogin/register', [
                '_token'    => Session::token(),
                'affiliation' => 'something',
            ])
            ->assertSessionMissing('lslogin-token')
            ->assertInvalid('lslogin-id');
    }

    public function testRegisterPrivacy()
    {
        View::shouldReceive('exists')->with('privacy')->andReturn(true);
        View::shouldReceive('exists')->with('terms')->andReturn(false);
        View::shouldReceive('share')->passthru();
        View::shouldReceive('make')->andReturn('');
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
            ->assertSessionHas('lslogin-token')
            ->assertInvalid('privacy');

        $this->withSession(['lslogin-token' => 'mytoken'])
            ->post('auth/lslogin/register', [
                '_token'    => Session::token(),
                'affiliation' => 'something',
                'privacy' => '1',
            ])
            ->assertRedirectToRoute('home');
    }

    public function testRegisterTerms()
    {
        View::shouldReceive('exists')->with('privacy')->andReturn(false);
        View::shouldReceive('exists')->with('terms')->andReturn(true);
        View::shouldReceive('share')->passthru();
        View::shouldReceive('make')->andReturn('');
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
            ->assertSessionHas('lslogin-token')
            ->assertInvalid('terms');

        $this->withSession(['lslogin-token' => 'mytoken'])
            ->post('auth/lslogin/register', [
                '_token'    => Session::token(),
                'affiliation' => 'something',
                'terms' => '1',
            ])
            ->assertRedirectToRoute('home');
    }

    public function testRegisterDisabledRegistration()
    {
        config(['biigle.user_registration' => false]);
        $this->post('auth/lslogin/register')->assertStatus(404);
    }

    public function testRegisterAuthenticated()
    {
        $user = User::factory()->create();
        $this->be($user);
        $this->post('auth/lslogin/register')->assertRedirectToRoute('home');
    }
}
