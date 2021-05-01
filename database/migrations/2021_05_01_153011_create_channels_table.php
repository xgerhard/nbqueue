<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->string('provider', 100);
            $table->string('provider_id', 100); 
            $table->integer('active')->length(10)->unsigned()->default(1);
            $table->integer('user_id')->length(10)->unsigned()->nullable();
        });

        Schema::table('channels', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });

        DB::table('channels')->insert([
            'provider' => '',
            'provider_id' => '',
            'active' => 1,
            'user_id' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channels');
    }
}
