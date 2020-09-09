<?php

namespace Square1\LaravelPassportFirebaseAuth\Tests;

use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens;

    public $timestamps = false;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'firebase_uid', 'name', 'avatar', 'role', 'username',
    ];
}
