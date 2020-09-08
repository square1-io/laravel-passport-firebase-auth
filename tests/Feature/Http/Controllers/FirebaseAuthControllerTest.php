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
            ->andReturn($firebaseUser);;


        $this->assertDatabaseCount('users', 0);
        $response = $this->post('api/v1/create-user-from-firebase', [
            'firebase_token' => 'fake-token',
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('users', 1);

        $this->assertEquals(User::first()->id, $response->getData()->data->user_id);
        $this->assertNotNull($response->getData()->data->access_token);
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
