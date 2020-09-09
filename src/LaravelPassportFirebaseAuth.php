<?php

namespace Square1\LaravelPassportFirebaseAuth;

use Firebase\Auth\Token\Exception\InvalidToken;
use Illuminate\Foundation\Auth\User;
use Kreait\Firebase\Auth\UserRecord;
use Laravel\Passport\PersonalAccessTokenResult;

class LaravelPassportFirebaseAuth
{
    /**
     * Find firebase user record or return 401 if invalid token
     *
     * @param string $token
     */
    public function getUserFromToken(string $token)
    {
        // Get Kreait\Firebase\Auth instance from the container
        $auth = app('firebase.auth');

        try { // Try to verify the Firebase credential token with Google

            $verifiedIdToken = $auth->verifyIdToken($token);
        } catch (\InvalidArgumentException $e) { // If the token has the wrong format

            return response()->json([
                'message' => 'Unauthorized - Can\'t parse the token: ' . $e->getMessage(),
            ], 401);
        } catch (InvalidToken $e) { // If the token is invalid (expired ...)

            return response()->json([
                'message' => 'Unauthorized - Token is invalide: ' . $e->getMessage(),
            ], 401);
        }

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

    /**
     * Find laravel user by firebase uid
     * Create a new User if not found
     *
     * @param string $uid_column
     * @param string $uid
     * @return \Illuminate\Foundation\Auth\User
     */
    public function findOrCreateAnonymousUser(string $uid_column, string $uid) : ?User
    {
        if (! config('laravel-passport-firebase-auth.allow_anonymous_users')) {
            return null;
        }

        /** @psalm-suppress UndefinedMethod */
        $user = config('auth.providers.users.model')::where($uid_column, $uid)->first();

        if (! $user) {
            $data = array_merge(
                config('laravel-passport-firebase-auth.anonymous_columns'),
                [$uid_column => $uid]
            );
            if (key_exists('email', $data)) {
                $data['email'] = $uid.$data['email'];
            }

            /** @psalm-suppress UndefinedMethod */
            $user = config('auth.providers.users.model')::create($data);
        }

        return $user;
    }
}
