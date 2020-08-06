<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAccessPolicyAddMoreColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE accessPolicy MODIFY accessAdminPolicy TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessAccessPolicy TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessAdminInformation TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessProviderList TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessProviderConfig TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessCurrency TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessBetRule TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessBetSetup TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessInvitationSetup TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide"');

        Schema::table('accessPolicy', function (Blueprint $table) {
            $table->tinyInteger('accessProviderGameSetup')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide')->after('accessProviderList');
            $table->tinyInteger('accessProviderRequestList')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide')->after('accessProviderGameSetup');
            $table->tinyInteger('accessProviderRequestBalance')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide')->after('accessProviderRequestList');
            $table->tinyInteger('accessProviderInfo')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide')->after('accessProviderRequestBalance');
            $table->tinyInteger('accessNotification')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide')->after('accessBetSetup');
            $table->tinyInteger('accessHolidayList')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide')->after('accessNotification');
            $table->tinyInteger('accessMonetaryLog')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide')->after('accessHolidayList');
            $table->tinyInteger('accessActivityLog')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete, 3 = hide')->after('accessMonetaryLog');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE accessPolicy MODIFY accessAdminPolicy TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessAccessPolicy TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessAdminInformation TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessProviderList TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessProviderConfig TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessCurrency TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessBetRule TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessBetSetup TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete"');
        DB::statement('ALTER TABLE accessPolicy MODIFY accessInvitationSetup TINYINT(4) DEFAULT 1 NOT NULL COMMENT "0 = read ,1 = read/write, 2 = read/write/delete"');
    
        Schema::table('accessPolicy', function (Blueprint $table) {
            $table->dropColumn('accessProviderGameSetup');
            $table->dropColumn('accessProviderRequestList');
            $table->dropColumn('accessProviderRequestBalance');
            $table->dropColumn('accessProviderInfo');
            $table->dropColumn('accessNotification');
            $table->dropColumn('accessHolidayList');
            $table->dropColumn('accessMonetaryLog');
            $table->dropColumn('accessActivityLog');
        });
    }
}
