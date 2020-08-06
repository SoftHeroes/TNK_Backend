<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Admin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->bigInteger('adminPolicyID')->unsigned();
            $table->bigInteger('portalProviderID')->unsigned();
            $table->string('firstName')->nullable();
            $table->string('middleName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('emailID')->unique();
            $table->string('username')->unique();
            $table->binary('password');
            $table->tinyInteger('invalidAttemptsCount')->default(0);
            $table->string('profileImage')->nullable();
            $table->dateTime('lastPasswordResetTime')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->enum('isActive', ['active','inactive'])->default('active');
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->softDeletes('deletedAt');
            $table->foreign('portalProviderID')->references('PID')->on('portalProvider');
            $table->foreign('adminPolicyID')->references('PID')->on('adminPolicy');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin');
    }
}
