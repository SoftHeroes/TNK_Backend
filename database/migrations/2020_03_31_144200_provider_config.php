<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProviderConfig extends Migration {
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('providerConfig');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('providerConfig', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->bigInteger('portalProviderID')->unsigned();
            $table->bigInteger('followBetSetupID')->nullable();
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->foreign('portalProviderID')->references('PID')->on('portalProvider');

        });
    }
}
