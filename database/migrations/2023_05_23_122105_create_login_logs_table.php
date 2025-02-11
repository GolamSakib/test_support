<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->timestamp('login_time');
            $table->timestamp('logout_time');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('login_ip')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('area_address')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('login_logs');
    }
}
