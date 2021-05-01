<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->string('name', 150)->collation('utf8mb4_general_ci');
            $table->tinyInteger('is_open')->length(2)->nullable()->default(0);
            $table->integer('channel_id')->length(10)->unsigned();
            $table->tinyInteger('user_level')->length(2)->default(1);
            $table->integer('max_users')->length(4)->default(0);
        });

        Schema::table('queues', function($table) {
            $table->foreign('channel_id')->references('id')->on('channels');
        });

        DB::table('queues')->insert([
            'name' => 'temp_default_queue',
            'is_open' => 0,
            'channel_id' => 1,
            'user_level' => 1
        ]);

        Schema::table('channels', function($table) {
            $table->foreign('active')->references('id')->on('queues');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('queues');
    }
}
