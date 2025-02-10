<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientUserIdAssignedPersonIdToClientloginInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clientlogin_inventory', function (Blueprint $table) {
            $table->integer('client_user_id')->nullable();
            $table->integer('assigned_person_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clientlogin_inventory', function (Blueprint $table) {
            $table->dropColumn('client_user_id');
            $table->dropColumn('assigned_person_id');
        });
    }
}
