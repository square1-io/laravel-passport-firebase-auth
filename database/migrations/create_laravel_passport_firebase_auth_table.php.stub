<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaravelPassportFirebaseAuthTable extends Migration
{
    public function up()
    {
        // In case the project is using a different model than User
        $authenticable_class = config('auth.providers.users.model');

        Schema::table((new $authenticable_class)->getTable(), function (Blueprint $table) {
            $table->string('firebase_uid')->nullable();
        });
    }
}
