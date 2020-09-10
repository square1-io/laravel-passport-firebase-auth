<?php

namespace Square1\LaravelPassportFirebaseAuth;

use Illuminate\Foundation\Auth\User;
use Kreait\Firebase\Auth\UserRecord;
use Laravel\Passport\PersonalAccessTokenResult;

class LaravelPassportFirebaseAuth
{
    /**
     * Find firebase user record
     *
     * @param string $token
     */
    public function getUserFromToken(string $token) : UserRecord
    {
        // Get Kreait\Firebase\Auth instance from the container
        $auth = app('firebase.auth');

        $verifiedIdToken = $auth->verifyIdToken($token);

        // Retrieve the UID (User ID) from the verified Firebase credential's token
        $uid = $verifiedIdToken->getClaim('sub');

        return $auth->getUser($uid);
    }

    /**
     * Create a valid passport token for the given user
     *
     * @param \Illuminate\Foundation\Auth\User $user
     * @return \Laravel\Passport\PersonalAccessTokenResult
     */
    public function createPassportToken(User $user): PersonalAccessTokenResult
    {
        $tokenResult = $user->createToken('Personal Access Token');
        $tokenResult->token->expires_at = now()->addMinutes(config('laravel-passport-firebase-auth.token_expiration_in_minutes'));
        $tokenResult->token->save();

        return $tokenResult;
    }

    /**
     * Check if firebase user is anonymous
     *
     * @param \Kreait\Firebase\Auth\UserRecord $firebaseUser
     * @return bool
     */
    public function isAnonymousUser(UserRecord $firebaseUser) : bool
    {
        // If user has a valid UID but and empty array as provider
        return $firebaseUser->uid && $firebaseUser->providerData == [];
    }
}
