<?php

namespace Square1\LaravelPassportFirebaseAuth\Tests\Feature\Http\Controllers;

use LaravelPassportFirebaseAuth;
use Kreait\Firebase\Auth\UserRecord;
use Square1\LaravelPassportFirebaseAuth\Tests\User;
use Square1\LaravelPassportFirebaseAuth\Tests\TestCase;

class FirebaseAuthControllerTest extends TestCase
{
    /** @test */
    public function it_create_a_user_in_the_backend_and_return_a_valid_passport_token()
    {
        $firebaseUser = $this->createFirebaseUser([
            'uid' => 'fake-user-uid',
            'email' => 'fake@email.com',
        ]);

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);
        LaravelPassportFirebaseAuth::makePartial();

        $this->assertEquals(0, User::count());
        $response = $this->post('api/v1/firebase/user/create', [
            'firebase_token' => 'fake-token',
        ]);
        $response->assertSessionHasNoErrors();

        $response->assertOk();
        $this->assertEquals(1, User::count());

        $this->assertEquals(User::first()->id, $response->getData()->data->user->id);
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

        $firebaseUser = $this->createFirebaseUser([
            'uid' => 'fake-user-uid',
            'email' => 'fake@email.com',
            'displayName' => 'Test User',
            'photoURL' => 'image.png', // This will not be save intentionaly
        ]);


        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);
        LaravelPassportFirebaseAuth::makePartial();


        $this->assertEquals(0, User::count());
        $response = $this->post('api/v1/firebase/user/create', [
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

        $firebaseUser = $this->createFirebaseUser([
            'uid' => 'fake-user-uid',
            'email' => 'fake@email.com',
        ]);

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);
        LaravelPassportFirebaseAuth::makePartial();

        $this->assertEquals(0, User::count());
        $response = $this->post('api/v1/firebase/user/create', [
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
    public function it_validates_fields_on_user_creation()
    {
        $this->app['config']->set('laravel-passport-firebase-auth.extra_user_columns', [
            'username' => 'required',
        ]);

        $firebaseUser = $this->createFirebaseUser([
            'uid' => 'fake-user-uid',
            'email' => 'fake@email.com',
        ]);

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);
        LaravelPassportFirebaseAuth::makePartial();

        $this->assertEquals(0, User::count());
        $response = $this->post('api/v1/firebase/user/create', [
            'firebase_token' => 'fake-token',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'errors' => [
                'username' => ['The username field is required.'],
                ],
            ]);
        $this->assertEquals(0, User::count());
    }

    /** @test */
    public function it_can_create_anonymous_users_if_firebase_provider_is_enabled()
    {
        $this->app['config']->set('laravel-passport-firebase-auth.allow_anonymous_users', true);
        $this->app['config']->set('laravel-passport-firebase-auth.anonymous_columns', [
            'email' => '@testdomain.com',
            'role' => 'anonymous',
        ]);

        $firebaseUser = $this->createFirebaseUser([
            'uid' => 'fake-anonymous-user-uid',
        ], $anonymous = true);

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);
        LaravelPassportFirebaseAuth::makePartial();

        $this->assertEquals(0, User::count());
        $response = $this->post('api/v1/firebase/user/create', [
            'firebase_token' => 'fake-token',
        ]);

        $response->assertOk();
        $this->assertEquals(1, User::count());

        $user = User::first();
        $this->assertEquals('fake-anonymous-user-uid', $user->firebase_uid);
        $this->assertEquals('fake-anonymous-user-uid@testdomain.com', $user->email);
        ;
        $this->assertEquals('anonymous', $user->role);
        ;
    }

    /** @test */
    public function it_gets_a_valid_passport_token_form_a_firebase_user_token()
    {
        $user = factory(User::class)->create(['firebase_uid' => 'fake-user-uid']);
        $firebaseUser = $this->createFirebaseUser([
            'uid' => 'fake-user-uid',
        ]);

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);
        LaravelPassportFirebaseAuth::makePartial();

        $response = $this->post('api/v1/firebase/user/login', [
            'firebase_token' => 'fake-token',
        ]);

        $response->assertOk();

        $this->assertEquals($user->id, $response->getData()->data->user->id);
        $this->assertNotNull($response->getData()->data->access_token);
        $this->assertTrue($response->getData()->data->expires_at > now()->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function test_when_creating_new_user_it_return_user_data_as_decalared_in_config_file()
    {
        $this->app['config']->set('laravel-passport-firebase-auth.map_user_columns', [
            'uid' => 'firebase_uid',
            'email' => 'email',
            'role' => 'role',
            'username' => 'username',
        ]);

        $this->app['config']->set('laravel-passport-firebase-auth.expose_user_columns', [
            'id', 'role', 'username',
        ]);

        $firebaseUser = $this->createFirebaseUser([
            'uid' => 'fake-user-uid',
            'email' => 'fake@email.com',
            'role' => 'premium',
            'username' => 'fake_username',
        ]);

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);
        LaravelPassportFirebaseAuth::makePartial();

        $this->assertEquals(0, User::count());
        $response = $this->post('api/v1/firebase/user/create', [
            'firebase_token' => 'fake-token',
        ]);

        $response->assertOk();

        // only expose tge 3 keys indicated in config
        $this->assertEquals(3, count((array) $response->getData()->data->user));

        $this->assertEquals(User::first()->id, $response->getData()->data->user->id);
        $this->assertEquals(User::first()->role, $response->getData()->data->user->role);
        $this->assertEquals(User::first()->username, $response->getData()->data->user->username);
    }

    /** @test */
    public function test_when_login_existing_user_it_return_user_data_as_decalared_in_config_file()
    {
        $user = factory(User::class)->create(['firebase_uid' => 'fake-user-uid']);
        $firebaseUser = $this->createFirebaseUser([
            'uid' => 'fake-user-uid',
        ]);

        $this->app['config']->set('laravel-passport-firebase-auth.expose_user_columns', [
            'id', 'uid',
        ]);

        LaravelPassportFirebaseAuth::shouldReceive('getUserFromToken')
            ->once()
            ->with('fake-token')
            ->andReturn($firebaseUser);
        LaravelPassportFirebaseAuth::makePartial();

        $response = $this->post('api/v1/firebase/user/login', [
            'firebase_token' => 'fake-token',
        ]);

        $response->assertOk();

        // only expose tge 2 keys indicated in config
        $this->assertEquals(2, count((array) $response->getData()->data->user));

        $this->assertEquals(User::first()->id, $response->getData()->data->user->id);
        $this->assertEquals(User::first()->uid, $response->getData()->data->user->uid);
    }

    /**
     * Factory fake firebase user
     *
     * @param array $data
     * @param bool $anonymous
     * @return \Kreait\Firebase\Auth\UserRecord
     */
    private function createFirebaseUser(array $data, $anonymous = false): UserRecord
    {
        $firebaseUser = new UserRecord();
        foreach ($data as $firebaseKey => $value) {
            $firebaseUser->{$firebaseKey} = $value;
        }

        if (! key_exists('provider', $data)) {
            if ($anonymous) {
                $firebaseUser->providerData = [];
            } else {
                $firebaseUser->providerData = [(object) ['providerId' => 'password']];
            }
        }

        return $firebaseUser;
    }
}
