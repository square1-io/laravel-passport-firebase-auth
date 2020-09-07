<?php

namespace Square1\LaravelPassportFirebaseAuth\Http\Controllers;

use Illuminate\Http\Request;
use LaravelPassportFirebaseAuth;
use Square1\LaravelPassportFirebaseAuth\Exceptions\NoUidColumnDeclaredException;

class FirebaseAuthController
{
    public function createUserFromFirebase()
    {
    }

    public function loginFromFirebase(Request $request)
    {
        $uid_column = config('laravel-passport-firebase-auth.map_user_columns.uid');

        if (! $uid_column) {
            throw NoUidColumnDeclaredException::create();
        }

        /** @psalm-suppress UndefinedClass */
        $uid = LaravelPassportFirebaseAuth::getUidFromToken($request->firebase_token);

        // Retrieve the user model linked with the Firebase UID
        /** @psalm-suppress UndefinedMethod */
        $user = config('auth.providers.users.model')::where($uid_column, $uid)->first();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized - User not found for the given firebase credentials.',
            ], 404);
        }

        $tokenResult = $user->createToken('Personal Access Token');

        $tokenResult->token->expires_at = now()->addMinutes(config('laravel-passport-firebase-auth.token_expiration_in_minutes'));
        $tokenResult->token->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user_id' => $user->id,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => $tokenResult->token->expires_at,
            ],
        ]);
    }
}
