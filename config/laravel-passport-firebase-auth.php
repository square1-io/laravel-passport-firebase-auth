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
     * Please update the column names in the array values to match your users database columns.
     * Please remove the key pairs you don't need to store, but 'uid' and 'email' are required!
     * For the 'uid' create a nullable string (we'l do this if use this package migrations)
     * Of course make all column fillable in your User model,
     * or we can't save if those columns are protected.
     */
    'map_user_columns' => [
        'uid' => 'firebase_uid', // REQUIRED
        'email' => 'email', // REQUIRED
        'displayName' => 'name',
        'emailVerified' => 'email_verified_at',
        'phoneNumber' => 'phone',
        'photoURL' => 'avatar',
        'provider' => 'provider' // We'll store the provider used in case you need that info
    ],

    /**
     * If you need some mandatory columns in order to store your laravel user model,
     * you can indicate them in here along with the validation rules needed.
     * Of course, you will need to post this keys as indicated in the readme.
     * And of course, make all column fillable in your User model,
     * or we can't save if those columns are protected.
     */
    'extra_user_columns' => [
        // 'username' => 'required|unique:users|max:255',
        // 'birthday' => 'require|date|before:today|date_format:Y-m-d'
    ]
];
