<?php

return [
    /**
     * We will load all the package routes under your api prefix for consistency
     */
    'api_prefix' => env('API_PREFIX', 'api/v1'),

    /**
     * Declare the amount of minutes in the future. Laravel Passport will create a token that endures
     * that long. See: https://laravel.com/docs/7.x/passport#token-lifetimes
     */
    'token_expiration_in_minutes' => 60 * 24 * 7, // Default 1 week expiration

    /**
     * Please update the column names in the array (the values) to match your users database columns.
     * Remove all the key pairs you don't need to store, but leave 'uid' and 'email', we need them!
     * For the 'uid' create a nullable string (we'll do this if use this package migrations)
     * Of course, make all columns fillable in your User model
     * or we can't save them if those columns are protected.
     */
    'map_user_columns' => [
        'uid' => 'firebase_uid', // REQUIRED
        'email' => 'email', // REQUIRED
        'displayName' => 'name',
        'emailVerified' => 'email_verified_at',
        'phoneNumber' => 'phone',
        'photoURL' => 'avatar',
        'provider' => 'provider', // e.g facebook, google, password
    ],

    /**
     * If you need some mandatory columns in order to store your laravel user model,
     * you can indicate them in here along with the validation rules you need.
     * Of course, you will need to post this keys as indicated in the readme.
     * And of course, make all columns fillable in your User model,
     * or we can't save them if those columns are protected.
     */
    'extra_user_columns' => [
        // 'username' => 'required|unique:users|max:255',
        // 'birthday' => 'require|date|before:today|date_format:Y-m-d'
    ],

    /**
     * Please indicate if anonymous firebase users capability is alowed in your application.
     * If so, we will need to olso create an "anonymous" user in your database in order
     * to let Laravel Passport issue a token for that particular user.
     */
    'allow_anonymous_users' => false,

    /**
     * Indicate the mandatory fields for anonymous user creation in your laravel database.
     * For the email, we'll concatenate (prefix) with the UID from firebase.
     */
    'anonymous_columns' => [
        'email' => '@anonymous.com', // This will end up being firebasetoken@anonymous.com
        'name' => 'Anonymous',
        'anonymous' => true,
        // 'avatar' => 'sample_anonymous_avatar.png'
    ],
];
