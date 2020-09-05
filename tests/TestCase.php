<?php

namespace Square1\LaravelPassportFirebaseAuth\Tests;

use Laravel\Passport\Passport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Contracts\Config\Repository;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kreait\Laravel\Firebase\ServiceProvider as FirebaseServiceProvider;
use Square1\LaravelPassportFirebaseAuth\LaravelPassportFirebaseAuthFacade;
use Square1\LaravelPassportFirebaseAuth\LaravelPassportFirebaseAuthServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    const KEYS = __DIR__.'/keys';
    const PUBLIC_KEY = self::KEYS.'/oauth-public.key';
    const PRIVATE_KEY = self::KEYS.'/oauth-private.key';
    const FIREBASE_CREDENTIALS = self::KEYS.'/firebase_credentials.json';

    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/database/factories');

        $this->setUpDatabase($this->app);

        Passport::routes();

        // @unlink(self::PUBLIC_KEY);
        // @unlink(self::PRIVATE_KEY);

        // $this->artisan('passport:keys');
        $this->artisan('passport:install');
    }

    public function getEnvironmentSetUp($app)
    {
        $config = $app->make(Repository::class);
        $config->set('auth.defaults.provider', 'users');
        $config->set('auth.guards.api', ['driver' => 'passport', 'provider' => 'users']);

        $app['config']->set('auth.providers.users.model', 'Square1\LaravelPassportFirebaseAuth\Tests\User');

        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('passport.storage.database.connection', 'sqlite');

        $app['config']->set('app.key', 'base64:fakekey/avhnnoiIltExLrEfZvvZx7h1Hb29Pgel2ec=');
        $app['config']->set('passport.private_key', file_get_contents(self::PRIVATE_KEY));
        $app['config']->set('passport.public_key', file_get_contents(self::PUBLIC_KEY));

        $app['config']->set('firebase.credentials.file', self::FIREBASE_CREDENTIALS);

        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            PassportServiceProvider::class,
            FirebaseServiceProvider::class,
            LaravelPassportFirebaseAuthServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'LaravelPassportFirebaseAuth' => LaravelPassportFirebaseAuthFacade::class,
        ];
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $this->artisan('migrate:fresh');

        // Create a light weight version of users table
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        include_once __DIR__.'/../database/migrations/create_laravel_passport_firebase_auth_table.php.stub';
        (new \CreateLaravelPassportFirebaseAuthTable())->up();

    }
}
