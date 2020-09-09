<?php

namespace Square1\LaravelPassportFirebaseAuth\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

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
