<?php

namespace Square1\LaravelPassportFirebaseAuth\Tests\Feature\Http\Controllers;

use LaravelPassportFirebaseAuth;
use Square1\LaravelPassportFirebaseAuth\Tests\TestCase;
use Square1\LaravelPassportFirebaseAuth\Tests\User;

class FirebaseAuthControllerTest extends TestCase
{
    /** @test */
    public function it_create_a_user_in_the_backend_and_return_a_valid_passport_token()
    {
        $firebaseUser = new \Kreait\Firebase\Auth\UserRecord();
        $firebaseUser->uid = 'fake-user-uid';
        $firebaseUser->email = 'fake@email.com';

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);

        $this->assertEquals(0, User::count());
        $response = $this->post('api/v1/create-user-from-firebase', [
            'firebase_token' => 'fake-token',
        ]);
        $response->assertSessionHasNoErrors();

        $response->assertOk();
        $this->assertEquals(1, User::count());

        $this->assertEquals(User::first()->id, $response->getData()->data->user_id);
        $this->assertNotNull($response->getData()->data->access_token);
    }

    /** @test */
    public function it_only_save_other_firebase_user_data_as_decalared_in_config_file()
    {
        $this->app['config']->set('laravel-passport-firebase-auth.map_user_columns', [
            'uid' => 'firebase_uid',
            'email' => 'email',
            'displayName' => 'name',
        ]);

        $firebaseUser = new \Kreait\Firebase\Auth\UserRecord();
        $firebaseUser->uid = 'fake-user-uid';
        $firebaseUser->email = 'fake@email.com';
        $firebaseUser->displayName = 'Test User';
        $firebaseUser->photoURL = 'image.png'; // This will not be save

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);


        $this->assertEquals(0, User::count());
        $response = $this->post('api/v1/create-user-from-firebase', [
            'firebase_token' => 'fake-token',
        ]);

        $response->assertOk();
        $this->assertEquals(1, User::count());

        $user = User::first();
        $this->assertEquals('fake-user-uid', $user->firebase_uid);
        $this->assertEquals('fake@email.com', $user->email);
        $this->assertEquals('Test User', $user->name);
        $this->assertNotEquals('image.png', $user->avatar);
    }

    /** @test */
    public function it_can_save_custom_columns_posted_on_creation_attempt()
    {
        $this->app['config']->set('laravel-passport-firebase-auth.extra_user_columns', [
            'username' => 'required|unique:users|max:255',
        ]);

        $firebaseUser = new \Kreait\Firebase\Auth\UserRecord();
        $firebaseUser->uid = 'fake-user-uid';
        $firebaseUser->email = 'fake@email.com';

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);


        $this->assertEquals(0, User::count());
        $response = $this->post('api/v1/create-user-from-firebase', [
            'firebase_token' => 'fake-token',
            'username' => 'fake-username',
            'role' => 'superadmin',
        ]);

        $response->assertOk();
        $this->assertEquals(1, User::count());

        $user = User::first();
        $this->assertEquals('fake-username', $user->username);
        $this->assertNotEquals('superadmin', $user->role);
    }

    /** @test */
    public function it_can_create_anonymous_users_if_firebase_provider_is_anonymous()
    {
    }
    /** @test */
    public function it_gets_a_valid_passport_token_form_a_firebase_user_token()
    {
        $user = factory(User::class)->create(['firebase_uid' => 'fake-user-uid']);
        $firebaseUser = new \Kreait\Firebase\Auth\UserRecord();
        $firebaseUser->uid = 'fake-user-uid';

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);

        $response = $this->post('api/v1/login-from-firebase', [
            'firebase_token' => 'fake-token',
        ]);

        $response->assertOk();

        $this->assertEquals($user->id, $response->getData()->data->user_id);
        $this->assertNotNull($response->getData()->data->access_token);
        $this->assertTrue($response->getData()->data->expires_at > now()->format('Y-m-d H:i:s'));
    }
}
