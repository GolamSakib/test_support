<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientUserIdAssignToIdCancelNoteIsRejectedIsAcceptedToClientloginComplainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clientlogin_complain', function (Blueprint $table) {
            $table->integer('client_user_id')->nullable();
            $table->integer('assign_to_id')->nullable();
            $table->longText('cancel_note')->nullable();
            $table->boolean('is_rejected')->default(0);
            $table->boolean('is_accepted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clientlogin_complain', function (Blueprint $table) {
            $table->dropColumn('client_user_id');
            $table->dropColumn('assign_to_id');
            $table->dropColumn('cancel_note');
            $table->dropColumn('is_rejected');
            $table->dropColumn('is_accepted');
        });
    }
}
