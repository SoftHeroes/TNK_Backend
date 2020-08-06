<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableProviderConfigAddInvitationSetupId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providerConfig', function (Blueprint $table) {
            $table->bigInteger('invitationSetupID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providerConfig', function (Blueprint $table) {
            $table->dropColumn('invitationSetupID');
        });
    }
}
