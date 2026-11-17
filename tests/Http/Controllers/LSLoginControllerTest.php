<?php

namespace Biigle\Tests\Modules\AuthLSLogin\Http\Controllers;

use Biigle\Modules\AuthLSLogin\LsloginId;
use Biigle\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        Socialite::shouldReceive('driver->user')->andThrow(new InvalidStateException);
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Invalid state during Life Science Login authentication.'
                    && $context['session_state_present'] === false
                    && $context['request_state_present'] === false
                    && $context['authenticated_user'] === null;
            });

        $this->withSessionCookie()
            ->get('auth/lslogin/callback')
            ->assertInvalid(['lslogin-id'])
            ->assertRedirectToRoute('login');
    }

    public function testInvalidStateExceptionWithoutSessionCookie()
    {
        Socialite::shouldReceive('driver->user')->andThrow(new InvalidStateException);
        Log::shouldReceive('error')->never();

        $this->get('auth/lslogin/callback?state=request-state&code=secret')
            ->assertInvalid(['lslogin-id'])
            ->assertRedirectToRoute('login');
    }

    public function testInvalidStateExceptionStateMismatch()
    {
        Socialite::shouldReceive('driver->user')->andThrow(new InvalidStateException);
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Invalid state during Life Science Login authentication.'
                    && $context['session_state_present'] === true
                    && $context['request_state_present'] === true
                    && $context['code_present'] === true
                    && $context['referer_host'] === 'login.aai.lifescience-ri.eu';
            });

        $this->withSessionCookie()
            ->withSession(['state' => 'session-state'])
            ->withHeader('referer', 'https://login.aai.lifescience-ri.eu/oidc/authorize?code=secret&state=request-state')
            ->get('auth/lslogin/callback?state=request-state&code=secret')
            ->assertInvalid(['lslogin-id'])
            ->assertRedirectToRoute('login');
    }

    public function testInvalidStateExceptionDuringConnect()
    {
        config(['biigle.user_registration' => true]);
        $user = User::factory()->create();
        Socialite::shouldReceive('driver->user')->andThrow(new InvalidStateException);
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($user) {
                return $message === 'Invalid state during Life Science Login authentication.'
                    && $context['authenticated_user'] === $user->id;
            });

        $this->be($user);
        $this->withSessionCookie()
            ->get('auth/lslogin/callback')
            ->assertInvalid(['lslogin-id'])
            ->assertRedirectToRoute('settings-authentication');
    }

    public function testUnexpectedExceptionDuringCallback()
    {
        $exception = new Exception;
        Socialite::shouldReceive('driver->user')->andThrow($exception);
        Log::shouldReceive('error')
            ->once()
            ->with('Unexpected error during Life Science Login authentication.', [
                'exception' => $exception,
            ]);

        $this->get('auth/lslogin/callback')
            ->assertInvalid(['lslogin-id'])
            ->assertRedirectToRoute('login');
    }

    public function testCallbackWithError()
    {
        Socialite::shouldReceive('driver->user')->never();
        Log::shouldReceive('warning')
            ->once()
            ->with('Life Science Login returned an error.', [
                'error' => 'access_denied',
                'description' => 'User denied consent',
            ]);

        $this->get('auth/lslogin/callback?error=access_denied&error_description=User+denied+consent')
            ->assertInvalid(['lslogin-id'])
            ->assertRedirectToRoute('login');
    }

    protected function withSessionCookie()
    {
        // The test client does not send a session cookie by default. A valid session ID
        // format is used so StartSession does not discard it.
        return $this->withCookie(config('session.cookie'), Str::random(40));
    }
}
