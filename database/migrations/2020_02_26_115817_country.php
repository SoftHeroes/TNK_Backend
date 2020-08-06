<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Country extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('country', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('countryName', 100);
            $table->integer('countryCode');
            $table->string('alphaTwoCode', 2);
            $table->string('alphaThreeCode', 3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('country');
    }
}
