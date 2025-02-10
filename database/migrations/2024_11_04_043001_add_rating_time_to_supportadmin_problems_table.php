<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRatingTimeToSupportadminProblemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supportadmin_problems', function (Blueprint $table) {
            $table->timestamp('rating_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supportadmin_problems', function (Blueprint $table) {
            $table->dropColumn('rating_time');
        });
    }
}
