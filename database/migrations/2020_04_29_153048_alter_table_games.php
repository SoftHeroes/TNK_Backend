<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterTableGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game', function (Blueprint $table) {
            $table->bigInteger('portalProviderID')->unsigned()->after('PID');
        });

        DB::statement("UPDATE game g INNER JOIN providerGameSetup pgs ON g.providerGameSetupID = pgs.PID SET g.portalProviderID = pgs.portalProviderID WHERE g.portalProviderID = 0");

        // adding foreign keys
        Schema::table('game', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM game WHERE Column_name='portalProviderID'"));
            if (!$keyExists) {
                $table->foreign('portalProviderID')->references('PID')->on('portalProvider');
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
        Schema::table('game', function (Blueprint $table) {
            $table->dropForeign(['portalProviderID']);
            $table->dropColumn('portalProviderID');
        });
    }
}
