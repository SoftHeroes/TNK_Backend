<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableUserAddInvitationSetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->integer('totalInvitationSent')->default(0);
            $table->integer('totalInvitationSentInDay')->default(0);
            $table->integer('totalInvitationSentInMin')->default(0);
            $table->dateTime('lastInvitationSend')->default('2019-12-05 05:50:20');
            $table->dateTime('lastInvitationMin')->default('2019-12-05 05:50:20');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('totalInvitationSent');
            $table->dropColumn('totalInvitationSentInDay');
            $table->dropColumn('totalInvitationSentInMin');
            $table->dropColumn('lastInvitationSend');
            $table->dropColumn('lastInvitationMin');
        });
    }
}
