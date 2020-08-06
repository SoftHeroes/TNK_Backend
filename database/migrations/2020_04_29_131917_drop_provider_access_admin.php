<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropProviderAccessAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('admin', 'providerAccess')) {
            Schema::table('admin', function (Blueprint $table) {
                $table->dropColumn('providerAccess');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('admin', 'providerAccess')) {
            Schema::table('admin', function (Blueprint $table) {
                $table->string('providerAccess')->after('portalProviderID');
            });
        }
    }
}
