<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FollowUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followUser', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->bigInteger('followerID')->unsigned();
            $table->bigInteger('followToID')->unsigned();
            $table->enum('isFollowing', ['true','false'])->default('true');
            $table->double('followAmount');
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->softDeletes('deletedAt');
            $table->foreign('followerID')->references('PID')->on('user');
            $table->foreign('followToID')->references('PID')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('followUser');
    }
}
