<?php

namespace Square1\LaravelPassportFirebaseAuth;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Square1\LaravelPassportFirebaseAuth\Commands\LaravelPassportFirebaseAuthCommand;
use Square1\LaravelPassportFirebaseAuth\Http\Controllers\FirebaseAuthController;

class LaravelPassportFirebaseAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-passport-firebase-auth.php' => config_path('laravel-passport-firebase-auth.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/laravel-passport-firebase-auth'),
            ], 'views');

            $migrationFileName = 'create_laravel_passport_firebase_auth_table.php';
            if (! $this->migrationFileExists($migrationFileName)) {
                $this->publishes([
                    __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
                ], 'migrations');
            }

            $this->commands([
                LaravelPassportFirebaseAuthCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-passport-firebase-auth');

        Route::prefix(config('laravel-passport-firebase-auth.api_prefix'))->group(function () {
            Route::post('create-user-from-firebase', [FirebaseAuthController::class, 'createUserFromFirebase']);
            Route::post('login-from-firebase', [FirebaseAuthController::class, 'loginFromFirebase']);
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-passport-firebase-auth.php', 'laravel-passport-firebase-auth');

        $this->app->bind(LaravelPassportFirebaseAuth::class, function () {
            return new LaravelPassportFirebaseAuth;
        });

        $this->app->alias(LaravelPassportFirebaseAuth::class, 'laravel-passport-firebase-auth');
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path("migrations/*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }
}
