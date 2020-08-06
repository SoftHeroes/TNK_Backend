<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableBetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('betting', function (Blueprint $table) {
            $table->bigInteger('parentBetID')->unsigned()->nullable();
            $table->bigInteger('followToID')->unsigned()->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('betting', function (Blueprint $table) {
            $table->dropColumn('parentBetID');
            $table->dropColumn('followToID');
        });
    }
}
