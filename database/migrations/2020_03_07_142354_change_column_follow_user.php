<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeColumnFollowUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE followUser CHANGE followAmount value DOUBLE');

        DB::statement('ALTER TABLE followUser ADD followType enum("Amount","Rate") NOT NULL AFTER isFollowing');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE followUser CHANGE value followAmount DOUBLE');

        DB::statement('ALTER TABLE followUser DROP COLUMN followType');
    }
}
