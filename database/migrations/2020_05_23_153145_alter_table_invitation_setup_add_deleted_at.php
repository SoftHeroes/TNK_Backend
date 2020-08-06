<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableInvitationSetupAddDeletedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invitationSetup', function (Blueprint $table) {
            if (!Schema::hasColumn('invitationSetup', 'deletedAt')) {
                $table->softDeletes('deletedAt');
            }
        });

        Schema::table('providerConfig', function (Blueprint $table) {
            if (!Schema::hasColumn('providerConfig', 'deletedAt')) {
                $table->softDeletes('deletedAt');
            }
        });

        Schema::table('followBetRule', function (Blueprint $table) {
            if (!Schema::hasColumn('followBetRule', 'deletedAt')) {
                $table->softDeletes('deletedAt');
            }
        });

        Schema::table('followBetSetup', function (Blueprint $table) {
            if (!Schema::hasColumn('followBetSetup', 'deletedAt')) {
                $table->softDeletes('deletedAt');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invitationSetup', function (Blueprint $table) {
            if (Schema::hasColumn('invitationSetup', 'deletedAt')) {
                $table->dropColumn('deletedAt');
            }
        });

        Schema::table('providerConfig', function (Blueprint $table) {
            if (Schema::hasColumn('providerConfig', 'deletedAt')) {
                $table->dropColumn('deletedAt');
            }
        });

        Schema::table('followBetRule', function (Blueprint $table) {
            if (Schema::hasColumn('followBetRule', 'deletedAt')) {
                $table->dropColumn('deletedAt');
            }
        });

        Schema::table('followBetSetup', function (Blueprint $table) {
            if (Schema::hasColumn('followBetSetup', 'deletedAt')) {
                $table->dropColumn('deletedAt');
            }
        });
    }
}
