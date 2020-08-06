<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAddColumnInvitationSetupToAccessPolicyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessPolicy', function (Blueprint $table) {
            $table->tinyInteger('accessInvitationSetup')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete')->after('accessBetSetup');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accessPolicy', function (Blueprint $table) {
            $table->dropColumn('accessInvitationSetup');
        });
    }
}
