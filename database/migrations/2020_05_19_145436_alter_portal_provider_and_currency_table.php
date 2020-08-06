<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPortalProviderAndCurrencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('creditRequest', function (Blueprint $table) {
            $table->bigInteger("chipValue")->default(0);
            $table->double("rate")->default(0);
        });

        DB::STATEMENT("ALTER TABLE currency CHANGE rate rate DOUBLE NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('creditRequest', function (Blueprint $table) {
            $table->dropColumn("chipValue");
            $table->dropColumn("rate");
        });

        DB::STATEMENT("ALTER TABLE currency CHANGE rate rate FLOAT NULL");

    }
}
