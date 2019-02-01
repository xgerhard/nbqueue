<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels', function($table) {
            $table->foreign('active')->references('id')->on('queues');
        });

        Schema::table('queues', function($table) {
            $table->foreign('channel_id')->references('id')->on('channels');
        });

        Schema::table('queue_users', function($table) {
            $table->foreign('queue_id')->references('id')->on('queues');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}
