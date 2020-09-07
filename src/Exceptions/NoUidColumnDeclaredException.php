<?php

namespace Square1\LaravelPassportFirebaseAuth\Exceptions;

use InvalidArgumentException;

class NoUidColumnDeclaredException extends InvalidArgumentException
{
    public static function create()
    {
        return new static("Can't find the table column for firebase UID. You need to declare it in your config/laravel-passport-firebase-auth.php file as instructed in the installation process.");
    }
}
