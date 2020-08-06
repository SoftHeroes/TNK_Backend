<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ForUpdatingFollowBetRule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('followBetRule', function (Blueprint $table) {
            $table->dropColumn('min', 'max');
        });

        Schema::table('followBetRule', function (Blueprint $table) {
            $table->integer('min');
            $table->integer('max');
        });

        Artisan::call('db:seed', ['--class' => 'updatingFollowBetRules']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
