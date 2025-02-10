<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeadByLeadByIdSellBySellByIdToAddusersSoftwarelistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addusers_softwarelist', function (Blueprint $table) {
            $table->string('lead_by_id')->nullable();
            $table->string('sell_by')->nullable();
            $table->string('sell_by_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addusers_softwarelist', function (Blueprint $table) {
             $table->dropColumn('lead_by_id');
             $table->dropColumn('sell_by');
             $table->dropColumn('sell_by_id');
        });
    }
}
