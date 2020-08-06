<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FollowBetRule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followBetRule', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->tinyInteger('type')->default(0)->comment('1 = follow , 2 = unFollow');
            $table->string('name');
            $table->string('rule')->nullable();
            $table->string('min');
            $table->string('max');
            $table->enum('isActive', ['active','inactive'])->default('active');
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('followBetRule');
    }
}
