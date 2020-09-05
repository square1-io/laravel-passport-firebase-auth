<?php

namespace Square1\LaravelPassportFirebaseAuth\Http\Controllers;

use Illuminate\Http\Request;
use LaravelPassportFirebaseAuth;

class FirebaseAuthController
{
    public function loginFirebaseUserInPassport(Request $request)
    {
        $uid = LaravelPassportFirebaseAuth::getUidFromToken($request->firebase_token);

        // Retrieve the user model linked with the Firebase UID
        $user = config('auth.providers.users.model')::where('firebase_uid', $uid)->first();

        // Here you could check if the user model exist and if not create it
        // For simplicity we will ignore this step

        // Once we got a valid user model
        // Create a Personnal Access Token
        $tokenResult = $user->createToken('Personal Access Token');

        // Store the created token
        $token = $tokenResult->token;

        // Add a expiration date to the token
        $token->expires_at = now()->addWeeks(1);

        // Save the token to the user
        $token->save();

        // Return a JSON object containing the token datas
        // You may format this object to suit your needs
        return response()->json([
            'id' => $user->id,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => now()->parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
        ]);
    }
}
