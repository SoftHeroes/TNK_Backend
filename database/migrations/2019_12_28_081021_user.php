<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class User extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('portalProviderUserID');
            $table->bigInteger('portalProviderID')->unsigned();
            $table->bigInteger('userPolicyID')->unsigned();
            $table->string('userName');
            $table->string('firstName')->nullable();
            $table->string('middleName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('email')->nullable();
            $table->string('profileImage')->nullable();
            $table->binary('password')->nullable();
            $table->double('balance')->default(0);
            $table->enum('gender', ['male','female','other'])->nullable();
            $table->string('country')->nullable();
            $table->enum('canLogout', ['true','false'])->default('true')->comment('If user is following any one and has active followed bets then cant logout');
            $table->enum('isLoggedIn', ['true','false'])->default('false');
            $table->enum('isActive', ['active','inactive'])->default('active');
            $table->dateTime('lastCalledTime');
            $table->string('lastIP');
            $table->dateTime('loginTime');
            $table->dateTime('logoutTime')->nullable();
            $table->double('activeMinutes')->default(0);
            $table->string('UUID')->unique();
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->softDeletes('deletedAt');
            $table->foreign('portalProviderID')->references('PID')->on('portalProvider');
            $table->foreign('userPolicyID')->references('PID')->on('userPolicy');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
