<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAddColumnsToAccessPolicyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessPolicy', function (Blueprint $table) {
            $table->tinyInteger('accessAdminPolicy')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete')->after('portalProviderIDs');
            $table->tinyInteger('accessAccessPolicy')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete')->after('accessAdminPolicy');
            $table->tinyInteger('accessAdminInformation')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete')->after('accessAccessPolicy');
            $table->tinyInteger('accessProviderList')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete')->after('accessAdminInformation');
            $table->tinyInteger('accessProviderConfig')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete')->after('accessProviderList');
            $table->tinyInteger('accessCurrency')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete')->after('accessProviderConfig');
            $table->tinyInteger('accessBetRule')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete')->after('accessCurrency');
            $table->tinyInteger('accessBetSetup')->default(1)->comment('0 = read ,1 = read/write, 2 = read/write/delete')->after('accessBetRule');
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
            $table->dropColumn('accessAdminPolicy');
            $table->dropColumn('accessAccessPolicy');
            $table->dropColumn('accessAdminInformation');
            $table->dropColumn('accessProviderList');
            $table->dropColumn('accessProviderConfig');
            $table->dropColumn('accessCurrency');
            $table->dropColumn('accessBetRule');
            $table->dropColumn('accessBetSetup');
        });
    }
}
