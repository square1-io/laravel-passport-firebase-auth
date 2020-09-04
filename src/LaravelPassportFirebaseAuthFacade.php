<?php

namespace Square1\LaravelPassportFirebaseAuth;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Square1\LaravelPassportFirebaseAuth\LaravelPassportFirebaseAuth
 */
class LaravelPassportFirebaseAuthFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-passport-firebase-auth';
    }
}
