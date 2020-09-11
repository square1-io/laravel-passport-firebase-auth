# Let Google Firebase create and authenticate users to your Laravel API (using Laravel Passport)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/square1/laravel-passport-firebase-auth.svg?style=flat-square)](https://packagist.org/packages/square1/laravel-passport-firebase-auth)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Tests](https://github.com/square1-io/laravel-passport-firebase-auth/workflows/Tests/badge.svg?style=flat-square)](https://github.com/square1-io/laravel-passport-firebase-auth/actions?query=workflow%3ATests+branch%3Amaster)


Create and authenticate users with Firebase Auth providers (Google, Facebook, Apple, email, etc), and let Laravel Passport know and handle your backend secure endpoints!

This is an opinionated way to create Laravel Passport tokens from a Firebase valid token.

## Installation

You can install the package via composer:

```bash
composer require square1/laravel-passport-firebase-auth
```

You will need a `firebase_uid` column on your users table. You can publish and run the migrations and customize that column with:

```bash
php artisan vendor:publish --provider="Square1\LaravelPassportFirebaseAuth\LaravelPassportFirebaseAuthServiceProvider" --tag="migrations"

# after column customization run:
php artisan migrate
```

Publish the config file with:
```bash
php artisan vendor:publish --provider="Square1\LaravelPassportFirebaseAuth\LaravelPassportFirebaseAuthServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
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
        'provider' => 'provider' // e.g facebook, google, password
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
    ]
];
```

### Configure Laravel Passport

This package has Laravel Passport as a dependency, if you did not already, please [configure Laravel Passport](https://laravel.com/docs/7.x/passport).

### Configure Firebase

Create a Firebase project in the console [https://console.firebase.google.com/](https://console.firebase.google.com/).

If you did not already please generated your Service Account auth file, do it from this url: [https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk](https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk). You will be asked to select the Firebase Project.
After that, the Firebase Admin SDK screen will ask you to pick a language, just leave `Node.js` selected and click `Generate new private key`.

Once you have downloaded the Service Account JSON file in your project (**ATENTION! please git ignore this file** as it has sensible credentials), indicate the path to your file in `.env` like this:

```
FIREBASE_CREDENTIALS=storage/firebase_credentials.json
```

#### Configure auth providers

In your firebase project create and configure all providers you want to use: [https://firebase.google.com/docs/auth](https://firebase.google.com/docs/auth)

## Usage

This package will expose 2 endpoints under your api prefix (configurable):

1) **POST**: `api/v1/firebase/user/create` 

In your mobile app or front end, you will allow your users to create an account using the [Firebase client SDK of your choice](https://firebase.google.com/docs/firestore/client/libraries).

Then you will call this endpoint with a valid firebase token using the `firebase_token` key in the payload posted.

This endpoint will reach firebase database, find and validate the user just created in your front end / mobile app, and it will create a user record in your laravel database saving the `firebase_uid` in users table you populated previously in the installation step.

Optionaly, you can perform 2 extra user configuration steps here:

**1 - a) Conect extra user data from the firebase users payload:**
    
In your config/laravel-passport-firebase-auth.php indicate the keys you want to match against your laravel users table using the "map_user_columns" key in the array.

**1 - b) Pass any other custom data you need for the user creation proces in your laravel database:**

An example will be if user creation require some mandatory columns (e.g. user_plan, username, role, etc.). For this you will use the instructions on the "extra_user_columns" key in the config array.

For security reasons, we'll validate this data, and we'll ignore any other values not declared in this "extra_user_columns" array.

----
Example payload posted to `api/v1/firebase/user/create`:

```json
{
    "firebase_token": "super_long_firebase_token_here",
    "username": "tonystark",
    "plan": "platinum",
    "role": "superadmin"
}
```

if in your `config/laravel-passport-firebase-auth.php` file you have the followin configuration:

```php
    'map_user_columns' => [
        'uid' => 'firebase_uid',
        'email' => 'email',
        'displayName' => 'full_name',
        'photoURL' => 'avatar',
    ],
    'extra_user_columns' => [
        'username' => 'required|unique:users|max:255',
        'plan' => 'required|in:silver,gold,platinum'
    ]
```

The result will be that, the newly created firebase user will be stored in your database with the uid, email, displayName as the full_name column, photoURL as the avatar column, and the rest of the firebase metadata will be discarted.

Also the username and plan will be stored, but the `role` manipulation attempt will be ignored.

You will receive a `success` status from the endpoint, along with the backend user ID and valid Laravel Passport access token.

```json
{
    "status": "success",
    "data": {
        "user_id": 1,
        "access_token": "valid_laravel_passport_token",
        "token_type": "Bearer",
        "expires_at": "2020-09-14T23:16:35.000000Z"
    }
}
```

2) **POST**: `api/v1/firebase/user/login` 

You will need to call this endpoint with only a valid firebase token using the key `firebase_token` in the payload posted.

In case we find the user in the laravel database, the result will contain a `success` status along with the passport token to use in furter requests.

```json
{
    "status": "success",
    "data": {
        "user_id": 1,
        "access_token": "valid_laravel_passport_token",
        "token_type": "Bearer",
        "expires_at": "2020-09-14T23:17:02.000000Z"
    }
}
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security related issues, please email emiliano@square1.io instead of using the issue tracker.

## Credits

- [Emiliano Tisato](https://github.com/emilianotisato)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
