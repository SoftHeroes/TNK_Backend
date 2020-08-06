<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PortalProviderServerIplistRate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portalProvider', function (Blueprint $table) {
            $table->string('server')->after('UUID');
            $table->longText('ipList')->after('server');
            $table->double('rate')->after('ipList');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portalProvider', function (Blueprint $table) {
            $table->dropColumn('server');
            $table->dropColumn('ipList');
            $table->dropColumn('rate');
        });
    }
}
