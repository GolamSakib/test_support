<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupportPersonIdToAddusersSupportpersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addusers_supportperson', function (Blueprint $table) {
            $table->integer('support_person_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addusers_supportperson', function (Blueprint $table) {
            $table->dropColumn('support_person_id');
        });
    }
}
