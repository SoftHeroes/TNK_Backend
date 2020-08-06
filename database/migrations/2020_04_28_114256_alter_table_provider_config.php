<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableProviderConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providerConfig', function (Blueprint $table) {
            $table->tinyInteger('logoutAPICall')->default(0)->after('followBetSetupID');
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
            $table->dropColumn('logoutAPICall');
        });
    }
}
