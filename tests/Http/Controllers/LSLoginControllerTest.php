<?php

namespace Biigle\Tests\Modules\AuthLSLogin\Http\Controllers;

use Biigle\Modules\AuthLSLogin\LsloginId;
use Biigle\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Session;
use TestCase;

class LSLoginControllerTest extends TestCase
{
    public function testRedirect()
    {
        $this->get('auth/lslogin/redirect')
            ->assertRedirectContains('https://login.aai.lifescience-ri.eu');
    }

    public function testCallbackNewUser()
    {
        config(['biigle.user_registration' => true]);
        $user = new SocialiteUser;
        $user->setToken('mytoken');
        Socialite::shouldReceive('driver->user')->andReturn($user);

        $this->get('auth/lslogin/callback')
            ->assertSessionHas('lslogin-token', 'mytoken')
            ->assertRedirectToRoute('lslogin-register-form');
    }

    public function testCallbackNewUserRegistrationDisabled()
    {
        config(['biigle.user_registration' => false]);
        $user = new SocialiteUser;
        $user->setToken('mytoken');
        Socialite::shouldReceive('driver->user')->andReturn($user);

        $this->get('auth/lslogin/callback')
            ->assertInvalid([
                'lslogin-id' => 'The user does not exist and new registrations are disabled.',
            ])
            ->assertRedirectToRoute('login');
    }

    public function testCallbackNewUserRegistrationDisabledUserExists()
    {
        User::factory()->create(['email' => 'joe@example.com']);
        config(['biigle.user_registration' => false]);
        $user = new SocialiteUser;
        $user->email = 'joe@example.com';
        $user->setToken('mytoken');
        Socialite::shouldReceive('driver->user')->andReturn($user);

        $this->get('auth/lslogin/callback')
            ->assertInvalid([
                'lslogin-id' => 'The email has already been taken. You can connect your existing account to Life Science Login in the account authorization settings.',
            ])
            ->assertRedirectToRoute('login');
    }

    public function testCallbackExistingUser()
    {
        $id = LsloginId::factory()->create();
        $user = new SocialiteUser;
        $user->map(['id' => $id->id]);
        Socialite::shouldReceive('driver->user')->andReturn($user);

        $this->get('auth/lslogin/callback')->assertRedirectToRoute('home');
        $this->assertAuthenticatedAs($id->user);
    }

    public function testCallbackConnectWithUser()
    {

        $user = new SocialiteUser;
        $user->map(['id' => 'myspecialid']);
        Socialite::shouldReceive('driver->user')->andReturn($user);

        $user = User::factory()->create();
        $this->be($user);
        $this->get('auth/lslogin/callback')->assertRedirectToRoute('settings-authentication');
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(LsloginId::where('user_id', $user->id)->where('id', 'myspecialid')->exists());
    }

    public function testCallbackConnectConflictingIDExists()
    {
        $id = LsloginId::factory()->create();
        $user = new SocialiteUser;
        $user->map(['id' => $id->id]);
        Socialite::shouldReceive('driver->user')->andReturn($user);

        $user = User::factory()->create();
        $this->be($user);
        $this->get('auth/lslogin/callback')
            ->assertInvalid(['lslogin-id'])
            ->assertRedirectToRoute('settings-authentication');
        $this->assertAuthenticatedAs($user);
    }

    public function testCallbackConnectAlreadyConnected()
    {
        $id = LsloginId::factory()->create();
        $user = new SocialiteUser;
        $user->map(['id' => $id->id]);
        Socialite::shouldReceive('driver->user')->andReturn($user);

        $this->be($id->user);
        $this->get('auth/lslogin/callback')->assertRedirectToRoute('settings-authentication');
        $this->assertAuthenticatedAs($id->user);
    }

    public function testInvalidStateExceptionDuringLogin()
    {
        config(['biigle.user_registration' => true]);
        Socialite::shouldReceive('driver->user')->andThrow(InvalidStateException::class);

        $this->get('auth/lslogin/callback')
            ->assertInvalid(['lslogin-id'])
            ->assertRedirectToRoute('login');
    }

    public function testInvalidStateExceptionDuringConnect()
    {
        config(['biigle.user_registration' => true]);
        Socialite::shouldReceive('driver->user')->andThrow(InvalidStateException::class);

        $user = User::factory()->create();
        $this->be($user);
        $this->get('auth/lslogin/callback')
            ->assertInvalid(['lslogin-id'])
            ->assertRedirectToRoute('settings-authentication');
    }
}
