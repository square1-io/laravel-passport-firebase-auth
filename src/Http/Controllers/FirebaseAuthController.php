<?php

namespace Square1\LaravelPassportFirebaseAuth\Http\Controllers;

use Illuminate\Http\Request;
use LaravelPassportFirebaseAuth;
use Square1\LaravelPassportFirebaseAuth\Exceptions\NoUidColumnDeclaredException;

class FirebaseAuthController
{
    private $uid_column;

    public function __construct()
    {
        $this->uid_column = config('laravel-passport-firebase-auth.map_user_columns.uid');

        if (!$this->uid_column) {
            throw NoUidColumnDeclaredException::create();
        }
    }
    public function createUserFromFirebase(Request $request)
    {
        /** @psalm-suppress UndefinedClass */
        $firebaseUser = LaravelPassportFirebaseAuth::getUserFromToken($request->firebase_token);

        // return $firebaseUser->uid;
        // return $firebaseUser->email;
        // return $firebaseUser->emailVerified;
        // return $firebaseUser->displayName;
        // return $firebaseUser->photoUrl;
        // return $firebaseUser->phoneNumber;
        // return $firebaseUser->providerData[0]->providerId;
        // Retrieve the user model linked with the Firebase UID
        /** @psalm-suppress UndefinedMethod */
        try {
            $user = config('auth.providers.users.model')::create([
                'email' => $firebaseUser->email,
                $this->uid_column, $firebaseUser->uid
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Unauthorized - Can\'t process some database column: ' . $e->getMessage(),
            ], 401);
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
            ]
        ]);
    }

    public function loginFromFirebase(Request $request)
    {
        /** @psalm-suppress UndefinedClass */
        $firebaseUser = LaravelPassportFirebaseAuth::getUserFromToken($request->firebase_token);

        // Retrieve the user model linked with the Firebase UID
        /** @psalm-suppress UndefinedMethod */
        $user = config('auth.providers.users.model')::where($this->uid_column, $firebaseUser->uid)->first();

        if (!$user) {
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
            ]
        ]);
    }
}
