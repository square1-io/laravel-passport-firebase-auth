# Let Google Firebase create and auth users to your Laravel API using Laravel Passport

[![Latest Version on Packagist](https://img.shields.io/packagist/v/square1-io/laravel-passport-firebase-auth.svg?style=flat-square)](https://packagist.org/packages/square1-io/laravel-passport-firebase-auth)
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
];
```

### Configure Laravel Passport

This package has Laravel Passport as a dependency, if you did not already, please [configure Laravel Passport](https://laravel.com/docs/7.x/passport).

### Configure Firebase

Create a Firebase project in the console [https://console.firebase.google.com/](https://console.firebase.google.com/).

If you did not already have generated your Service Account auth file, do it from this url: [https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk](https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk). You will be asked to select the Firebase Project.
After that, the Firebase Admin SDK screen will ask you to pick a language, just leave `Node.js` selected and click `Generate new private key`.

Once you have downloaded the Service Account JSON file in your project (ATENTION! please git ignore this file as it has sensible credentials), indicate the path to your file in `.env` like this:

```
FIREBASE_CREDENTIALS=storage/firebase_credentials.json
```

#### Configure auth providers

In your firebase project create and configure all providers you want to use: [https://firebase.google.com/docs/auth](https://firebase.google.com/docs/auth)

## Usage

This package will expose 2 endpoints under your api prefix (configurable):

1) *POST*: `yourapp.com/api/v1/create-user-from-firebase` 

In your mobile app or front end, you will allow your users to create an account using the [Firebase client SDK of your choice](https://firebase.google.com/docs/firestore/client/libraries).

Then you will call this endpoint with a valid firebase token using the key `firebase_token` in the payload posted.

This endpoint will reach firebase database, find and validate the user just created in your front end / mobile app, and it will create a user record in your laravel database saving the `firebase_uid` in users table you populated previously in the installation step.

Optionaly, you can perform 2 extra user configuration steps here:

*1 - a)* Conect extra user data from the firebase users payload:
    
In your config/laravel-passport-firebase-auth.php indicate the keys you want to match against your laravel users table using the "map_user_columns" key in the array.

*1 - b)* Pass any other custom data you need for the user creation proces in your laravel database. (e.g. user_plan, username, role, etc.).

For that us the instructions on the "extra_user_columns" key in the config array.

For security reasons, we'll validate and ignore any other values not declared in this "extra_user_columns" array.

----
Example payload posted to `api/v1/create-user-from-firebase`:

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
        'provider' => 'provider'
    ],
    'extra_user_columns' => [
        'username' => 'required|unique:users|max:255',
        'plan' => 'required|in:silver,gold,platinum'
    ]
```

The result will be that, the newly created firebase user will be stored in your database with the uid, email, displayName, photoURL and provider used, and the rest of the firebase metadata will be discarted.

Also the username and plan will be stored, but the `role` manipulation attempt will be ignored.

You will receive a `success` status from the endpoint, along with the backend user ID.

```json
{
    "status": "success",
    "data": {
        "user_id": 1
    }
}
```

2) *POST*: `yourapp.com/api/v1/login-from-firebase` 

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
