<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->string('provider', 100);
            $table->string('provider_id', 100);
            $table->string('name', 150)->collation('utf8mb4_general_ci');
            $table->string('displayName', 150)->collation('utf8mb4_general_ci');
        });

        DB::table('users')->insert([
            'provider' => 'temp_default_user',
            'provider_id' => 'temp_default_user',
            'name' => 'temp_default_user',
            'displayName' => 'temp_default_user'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
