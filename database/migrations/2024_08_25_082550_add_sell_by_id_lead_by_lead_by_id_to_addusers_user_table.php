<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSellByIdLeadByLeadByIdToAddusersUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addusers_users', function (Blueprint $table) {
            $table->string('sell_by_id')->nullable();
            $table->string('lead_by')->nullable();
            $table->string('lead_by_id')->nullable();
            $table->integer('area_id')->nullable();
            $table->text('pro_img_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addusers_users', function (Blueprint $table) {
            $table->dropColumn('sell_by_id');
            $table->dropColumn('lead_by');
            $table->dropColumn('lead_by_id');
            $table->dropColumn('pro_img_url');
            $table->dropColumn('area_id');
        });
    }
}
