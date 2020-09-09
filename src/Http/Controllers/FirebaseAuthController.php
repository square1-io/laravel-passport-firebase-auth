<?php

namespace Square1\LaravelPassportFirebaseAuth\Http\Controllers;

use Illuminate\Http\Request;
use LaravelPassportFirebaseAuth;
use Illuminate\Http\JsonResponse;
use Kreait\Firebase\Auth\UserRecord;
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
    public function createUserFromFirebase(Request $request): JsonResponse
    {
        $firebaseUser = LaravelPassportFirebaseAuth::getUserFromToken($request->firebase_token);

        // Retrieve the user model linked with the Firebase UID
        try {
            $data = $this->validateAndTrimUserData($firebaseUser, $request);
            /** @psalm-suppress UndefinedMethod */
            $user = config('auth.providers.users.model')::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Unauthorized - Can\'t process some database column: ' . $e->getMessage(),
            ], 401);
        }

        $tokenResult = LaravelPassportFirebaseAuth::createPassportToken($user);

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

    public function loginFromFirebase(Request $request): JsonResponse
    {
        $firebaseUser = LaravelPassportFirebaseAuth::getUserFromToken($request->firebase_token);

        if (LaravelPassportFirebaseAuth::isAnonymousUser($firebaseUser)) {
            $user = LaravelPassportFirebaseAuth::findOrCreateAnonymousUser($this->uid_column, $firebaseUser->uid);
        } else {
            // Retrieve the user model linked with the Firebase UID
            /** @psalm-suppress UndefinedMethod */
            $user = config('auth.providers.users.model')::where($this->uid_column, $firebaseUser->uid)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized - User not found for the given firebase credentials.',
                ], 404);
            }
        }

        $tokenResult = LaravelPassportFirebaseAuth::createPassportToken($user);

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

    private function validateAndTrimUserData(UserRecord $firebaseUser, Request $request): array
    {
        $data = [
            'email' => $firebaseUser->email,
            $this->uid_column => $firebaseUser->uid,
        ];

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


        $extra_user_columns = config('laravel-passport-firebase-auth.extra_user_columns');

        $authenticable_class = config('auth.providers.users.model');
        /** @psalm-suppress UndefinedClass */
        $usersTable = (new $authenticable_class)->getTable();

        $rules = array_merge($extra_user_columns, [
            $this->uid_column => 'required',
            'email' => 'required|unique:' . $usersTable,
        ]);

        $request->request->add($data);
        $request->validate($rules);

        return array_merge($data, $request->only(array_keys($extra_user_columns)));
    }
}
