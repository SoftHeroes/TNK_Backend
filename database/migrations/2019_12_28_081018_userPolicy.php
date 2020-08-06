<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserPolicy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userPolicy', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('name')->unique();
            $table->integer('userLockTime')->nullable();
            $table->integer('invalidAttemptsAllowed')->nullable();
            $table->integer('otpValidTime')->nullable();
            $table->integer('passwordResetTime')->nullable();
            $table->integer('sessionLifetime')->default(300)->comment("this value is in Seconds");
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
        Schema::dropIfExists('userPolicy');
    }
}
