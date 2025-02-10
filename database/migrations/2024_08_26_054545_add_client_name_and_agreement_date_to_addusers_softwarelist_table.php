<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientNameAndAgreementDateToAddusersSoftwarelistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addusers_softwarelist', function (Blueprint $table) {
            $table->text('client_name')->nullable();
            $table->timestamp('agreement_date')->nullable();
            $table->timestamp('operation_start_date')->nullable();
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
            //
        });
    }
}
