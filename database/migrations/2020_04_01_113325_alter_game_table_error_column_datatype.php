<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGameTableErrorColumnDatatype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ALTER TABLE game
        Schema::table('game', function (Blueprint $table) {
            $table->longText('error')->change(); // Change responseMessage column type
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // ALTER TABLE game
        Schema::table('game', function (Blueprint $table) {
            $table->string('error','5000')->nullable()->change();
        });
    }
}
