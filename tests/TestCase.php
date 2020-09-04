<?php

namespace Square1\LaravelPassportFirebaseAuth\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Square1\LaravelPassportFirebaseAuth\LaravelPassportFirebaseAuthServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelPassportFirebaseAuthServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        /*
        include_once __DIR__.'/../database/migrations/create_laravel_passport_firebase_auth_table.php.stub';
        (new \CreatePackageTable())->up();
        */
    }
}
