<?php

namespace Square1\LaravelPassportFirebaseAuth\Commands;

use Illuminate\Console\Command;

class LaravelPassportFirebaseAuthCommand extends Command
{
    public $signature = 'laravel-passport-firebase-auth';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
