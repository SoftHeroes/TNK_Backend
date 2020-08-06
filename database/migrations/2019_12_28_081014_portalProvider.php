<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PortalProvider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('portalProvider', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('name')->unique();
            $table->bigInteger('currencyID')->unsigned();
            $table->double('creditBalance');
            $table->double('mainBalance');
            $table->string('UUID')->unique();
            $table->enum('isActive', ['active','inactive'])->default('active');
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->softDeletes('deletedAt');
            $table->foreign('currencyID')->references('PID')->on('currency');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portalProvider');
    }
}
