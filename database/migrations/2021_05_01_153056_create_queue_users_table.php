<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueueUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue_users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
            $table->integer('queue_id')->length(10)->unsigned();
            $table->integer('user_id')->length(10)->unsigned();
            $table->string('message', 150)->collation('utf8mb4_general_ci');
            $table->tinyInteger('user_level')->length(2)->default(1);
        });

        Schema::table('queue_users', function($table) {
            $table->foreign('queue_id')->references('id')->on('queues');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('queue_users');
    }
}
