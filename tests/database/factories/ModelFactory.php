<?php

use \Faker\Generator;
use Square1\LaravelPassportFirebaseAuth\Tests\User;

/* @var Illuminate\Database\Eloquent\Factory $factory */
$factory->define(User::class, function (Generator $faker) {
    return [
        'email' => $faker->safeEmail,
        'firebase_uid' => $faker->md5,
    ];
});
