<?php

namespace Square1\LaravelPassportFirebaseAuth\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use LaravelPassportFirebaseAuth;
use Illuminate\Http\JsonResponse;
use Kreait\Firebase\Auth\UserRecord;
use Illuminate\Support\Facades\Validator;
use Firebase\Auth\Token\Exception\InvalidToken;
use Square1\LaravelPassportFirebaseAuth\Exceptions\NoUidColumnDeclaredException;

class FirebaseAuthController
{
    private string $uid_column;

    public function __construct()
    {
        $this->uid_column = config('laravel-passport-firebase-auth.map_user_columns.uid');

        if (! $this->uid_column) {
            throw NoUidColumnDeclaredException::create();
        }
    }

    public function createUserFromFirebase(Request $request) : JsonResponse
    {
        $firebaseUser = LaravelPassportFirebaseAuth::getUserFromToken($request->firebase_token);

        try {
            $is_anonymous = LaravelPassportFirebaseAuth::isAnonymousUser($firebaseUser) && config('laravel-passport-firebase-auth.allow_anonymous_users');
            $data = array_merge(
                $this->buildUserData($firebaseUser, $is_anonymous),
                $request->only(
                    array_keys($extra_user_columns = config('laravel-passport-firebase-auth.extra_user_columns'))
                )
            );

            $authenticable_class = config('auth.providers.users.model');
            /** @psalm-suppress UndefinedClass */
            $usersTable = (new $authenticable_class)->getTable();
            $rules = array_merge($extra_user_columns, [
                $this->uid_column => 'required:unique:'.$usersTable,
                'email' => 'required|unique:'.$usersTable,
            ]);

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            /** @psalm-suppress UndefinedMethod */
            $user = config('auth.providers.users.model')::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Unauthorized - Can\'t process some database column: '.$e->getMessage(),
            ], 401);
        }

        $tokenResult = LaravelPassportFirebaseAuth::createPassportToken($user);

        return response()->json([
            'status' => 'success',
            'data' => [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => $tokenResult->token->expires_at,
                'user' => $user->only(config('laravel-passport-firebase-auth.expose_user_columns')),
            ],
        ]);
    }

    public function loginFromFirebase(Request $request) : JsonResponse
    {
        try { // Try to verify the Firebase credential token with Google
            $firebaseUser = LaravelPassportFirebaseAuth::getUserFromToken($request->firebase_token);
        } catch (\InvalidArgumentException $e) { // If the token has the wrong format
            return response()->json([
                'message' => 'Unauthorized - Can\'t parse the token: '.$e->getMessage(),
            ], 401);
        } catch (InvalidToken $e) { // If the token is invalid (expired ...)
            return response()->json([
                'message' => 'Unauthorized - Token is invalide: '.$e->getMessage(),
            ], 401);
        }

        // Retrieve the user model linked with the Firebase UID
        /** @psalm-suppress UndefinedMethod */
        $user = config('auth.providers.users.model')::where($this->uid_column, $firebaseUser->uid)->first();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized - User not found for the given firebase credentials.',
            ], 404);
        }

        $tokenResult = LaravelPassportFirebaseAuth::createPassportToken($user);

        return response()->json([
            'status' => 'success',
            'data' => [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => $tokenResult->token->expires_at,
                'user' => $user->only(config('laravel-passport-firebase-auth.expose_user_columns')),
            ],
        ]);
    }

    private function buildUserData(UserRecord $firebaseUser, $anonymous = false) : array
    {
        $data = [];
        // Add firebase user data
        foreach (config('laravel-passport-firebase-auth.map_user_columns') as $firebaseKey => $column) {
            if (property_exists($firebaseUser, $firebaseKey)) {
                $data[$column] = $firebaseUser->{$firebaseKey};
            }
            if ($firebaseKey == 'provider') {
                if (property_exists($firebaseUser, 'providerData') && $firebaseUser->providerData != null) {
                    $data[$column] = $firebaseUser->providerData[0]->providerId;
                }
            }
            if ($firebaseKey == 'emailVerified') {
                $data[$column] = $firebaseUser->{$firebaseKey} ? now()->format('Y-m-d') : false;
            }
        }

        if ($anonymous) {
            $dataOverride = array_merge(
                config('laravel-passport-firebase-auth.anonymous_columns'),
                [$this->uid_column => $firebaseUser->uid]
            );
            if (key_exists('email', $dataOverride)) {
                $dataOverride['email'] = $firebaseUser->uid.$dataOverride['email'];
            }
        } else {
            $dataOverride = [
                'email' => $firebaseUser->email,
                $this->uid_column => $firebaseUser->uid,
            ];
        }
        $data = array_merge($data, $dataOverride);

        $data['password'] = Str::random(32);

        return $data;
    }
}
