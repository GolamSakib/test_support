<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftwareTypeIdToClientSupportAdminSoftwarelistallTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_support_admin_softwarelistall', function (Blueprint $table) {
            $table->integer('software_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_support_admin_softwarelistall', function (Blueprint $table) {
            $table->dropColumn('software_type_id');
        });
    }
}
