<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTableProvider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE portalProvider CHANGE server server VARCHAR(255) NULL');
        DB::statement('ALTER TABLE portalProvider CHANGE ipList ipList VARCHAR(255) NULL');
        DB::statement('ALTER TABLE portalProvider CHANGE rate rate DOUBLE  DEFAULT 0');
        DB::statement('ALTER TABLE portalProvider CHANGE APIKey APIKey VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE portalProvider CHANGE server server VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE portalProvider CHANGE ipList ipList LONGTEXT NOT NULL');
        DB::statement('ALTER TABLE portalProvider CHANGE rate rate DOUBLE NOT NULL');
        DB::statement('ALTER TABLE portalProvider CHANGE APIKey APIKey VARCHAR(255) NOT NULL');
    }
}
