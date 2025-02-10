<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleProblemTypeIdClientUserIdAcceptedSupportIdHelpedByIdToSupportadminProblemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supportadmin_problems', function (Blueprint $table) {
            $table->text('title')->nullable();
            $table->integer('problem_type_id')->nullable();
            $table->integer('client_user_id')->nullable();
            $table->integer('accepted_support_id')->nullable();
            $table->integer('helped_by_id')->nullable();
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
            $table->dropColumn('title');
            $table->dropColumn('problem_type_id');
            $table->dropColumn('client_user_id');
            $table->dropColumn('accepted_support_id');
            $table->dropColumn('helped_by_id');
        });
    }
}
