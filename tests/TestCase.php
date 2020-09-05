<?php

namespace Square1\LaravelPassportFirebaseAuth\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kreait\Laravel\Firebase\ServiceProvider as FirebaseServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
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
        // $app['config']->set('passport.private_key', file_get_contents(self::PRIVATE_KEY));
        // $app['config']->set('passport.public_key', file_get_contents(self::PUBLIC_KEY));
        $app['config']->set('passport.public_key', $this->getFakePublicOauth());
        $app['config']->set('passport.private_key', $this->getFakePrivateOauth());

        // $app['config']->set('firebase.credentials.file', self::FIREBASE_CREDENTIALS);

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

    public function getFakePublicOauth()
    {
        return "-----BEGIN PUBLIC KEY-----\nMIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAs7O3V2CP9NOsB9iNl4H1\nbQmZpUHjlV2O6HNGM+3yhy1a9BB16gOpqcegykpVqqaxLDqf1MLX2LSVUAdlREZK\ndNUKcM9t8V9On0mRpgL3GDFCFzO5TMTXxioUfq6HhF0fukYQ/Gx9f827eAuCJ1An\nuF9kYLKuYAxt6DpqTzuEvOcjoNbzmIl1dvnQFCODDZJfjSZfOwuMn9WeQATyL36w\nd487fhaeeDRUka5UfHoS1DUdnlfVrFyZw3NiwGI4ykuvQ8sbE0PYiVi0WsPfy00Y\neCg4olcsTu8+gwHZ3MbD5CeGXBdAEtZSLTFxmz/7OiZIrhg4JeLyJNNIxmQ8DVJY\nAJTYyulAUea0e0IuX6QJNbv4WHc08hNDj+XV5r9t19qyTL/Oa5PIWxcDhW1RHDjM\n97fcesKe/xibA+aiWUUjl4Mv/QevWTM7yJke5cVQyuuyPKUdXrl+EQ21J4+4S1B4\nS/ouGlUJhd14uVsjJwojPd6iELjLKHRQ2lwe/AP+B5UKmtQyJ+uMZLkFT0FbeSXw\ntbN9tN+RHdTqPbgCnZTzVeqMGgh0bukRzRpMJIX/ik2rsDy/VY2LTdGLoBFsOpqN\nelQcd/N56pD4ldvQUAb814nTO+CcxRsY5a4xWqrONO4xFJt906Ms8Bpo+AVITfBQ\njFNe2BGK57CPm9umylg8cwMCAwEAAQ==\n-----END PUBLIC KEY-----";
    }

    public function getFakePrivateOauth()
    {
        return "-----BEGIN RSA PRIVATE KEY-----\n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\n-----END RSA PRIVATE KEY-----";
    }
}
