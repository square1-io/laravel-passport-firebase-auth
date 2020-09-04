# 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/square1-io/laravel-passport-firebase-auth.svg?style=flat-square)](https://packagist.org/packages/square1-io/laravel-passport-firebase-auth)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/square1-io/laravel-passport-firebase-auth/run-tests?label=tests)](https://github.com/square1-io/laravel-passport-firebase-auth/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/square1-io/laravel-passport-firebase-auth.svg?style=flat-square)](https://packagist.org/packages/square1-io/laravel-passport-firebase-auth)


Create and authenticate users with Firebase Auth providers, and let Laravel Passport handle the rest!

# Work in progres. Do not use in production!

## Installation

You can install the package via composer:

```bash
composer require square1/laravel-passport-firebase-auth
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Square1\LaravelPassportFirebaseAuth\LaravelPassportFirebaseAuthServiceProvider" --tag="migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Square1\LaravelPassportFirebaseAuth\LaravelPassportFirebaseAuthServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage


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
