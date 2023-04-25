<?php

namespace Biigle\Tests\Modules\AuthLSLogin\Http\Controllers;

use Biigle\Modules\AuthLSLogin\LsloginId;
use Biigle\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Session;
use TestCase;


class LSLoginControllerTest extends TestCase
{
    public function testRedirect()
    {
        $this->get('auth/lslogin/redirect')
            ->assertRedirectContains('https://proxy.aai.lifescience-ri.eu');
    }

    public function testCallbackNewUser()
    {
        $user = new SocialiteUser;
        $user->setToken('mytoken');
        Socialite::shouldReceive('driver->user')->andReturn($user);

        $this->get('auth/lslogin/callback')
            ->assertSessionHas('lslogin-token', 'mytoken')
            ->assertRedirectToRoute('lslogin-register-form');
    }

    public function testCallbackConflictingNewEmail()
    {
        // The LSLogin ID does not exist yet but a user with the email address exists.
        // Show an error message and suggest to connect the existing account
        // (i.e. log in to the account and then connect via settings).
        $this->markTestIncomplete();
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
        $this->get('auth/lslogin/callback')->assertRedirectToRoute('home');
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(LsloginId::where('user_id', $user->id)->where('id', 'myspecialid')->exists());
        $this->markTestIncomplete('redirect to the third party auth settings view');
    }

    public function testCallbackConnectConflictingIDExists()
    {
        // A user is already authenticated but the LSLogin ID is already connected to a
        // different user. Show an error message.
        $this->markTestIncomplete();
    }

    public function testCallbackConnectAlreadyConnected()
    {
        // A user is authenticated and the LSLogin ID is already connected to the user.
        // Redirect to the dashboard and do nothing.
        $this->markTestIncomplete();
    }
}
