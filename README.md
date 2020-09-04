# 

[![Latest Version on Packagist](https://img.shields.io/packagist/v//laravel-passport-firebase-auth.svg?style=flat-square)](https://packagist.org/packages//laravel-passport-firebase-auth)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status//laravel-passport-firebase-auth/run-tests?label=tests)](https://github.com//laravel-passport-firebase-auth/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt//laravel-passport-firebase-auth.svg?style=flat-square)](https://packagist.org/packages//laravel-passport-firebase-auth)


This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

Learn how to create a package like this one, by watching our premium video course:

[![Laravel Package training](https://spatie.be/github/package-training.jpg)](https://laravelpackage.training)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require /laravel-passport-firebase-auth
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

``` php
$laravel-passport-firebase-auth = new Square1\LaravelPassportFirebaseAuth();
echo $laravel-passport-firebase-auth->echoPhrase('Hello, Square1!');
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Emiliano Tisato](https://github.com/EmilianoTisato)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
