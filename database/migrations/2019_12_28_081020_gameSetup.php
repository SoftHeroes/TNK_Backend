<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GameSetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gameSetup', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('rulesID',5000);
            $table->string('gameName');
            $table->double('initialOdd');
            $table->double('commission');
            $table->tinyInteger('gameLoop');
            $table->enum('isActive', ['active','inactive'])->default('active');
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->softDeletes('deletedAt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gameSetup');
    }
}
