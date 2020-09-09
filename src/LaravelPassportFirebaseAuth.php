<?php

namespace Square1\LaravelPassportFirebaseAuth;

use Illuminate\Foundation\Auth\User;
use Firebase\Auth\Token\Exception\InvalidToken;

class LaravelPassportFirebaseAuth
{
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

    public function createPassportToken(User $user)
    {
        $tokenResult = $user->createToken('Personal Access Token');
        $tokenResult->token->expires_at = now()->addMinutes(config('laravel-passport-firebase-auth.token_expiration_in_minutes'));
        $tokenResult->token->save();

        return $tokenResult;
    }
}
