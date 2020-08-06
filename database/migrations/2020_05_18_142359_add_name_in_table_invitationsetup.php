<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddNameInTableInvitationsetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invitationSetup', function (Blueprint $table) {
            $table->string('name')->after('PID');
        });
        DB::statement('ALTER TABLE invitationSetup CHANGE updateAt updatedAt  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invitationSetup', function (Blueprint $table) {
            $table->dropColumn('name');
        });
        DB::statement('ALTER TABLE invitationSetup CHANGE updatedAt updateAt  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');

    }
}
