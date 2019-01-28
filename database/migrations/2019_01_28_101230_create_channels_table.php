<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->timestamps();
            $table->string('provider', 100);
            $table->string('provider_id', 100); 
            $table->integer('active')->length(10)->unsigned();

            $table->foreign('active')->references('id')->on('queues');
        });

        DB::table('channels')->insert(
            array(
                'id' => 0,
                'provider' => 'twitch',
                'provider_id' => 'twitch_0',
                'active' => 0
            )
        );
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
