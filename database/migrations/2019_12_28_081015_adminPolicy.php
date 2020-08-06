<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdminPolicy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adminPolicy', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('name')->unique();
            $table->integer('userLockTime')->nullable();
            $table->integer('invalidAttemptsAllowed')->nullable();
            $table->integer('otpValidTimeInSeconds')->nullable();
            $table->integer('passwordResetTime')->nullable();
            $table->tinyInteger('access')->default(1)->comment('1 = All ,2 = appAPI, 3 = AdminPanel,4 = webApi, 5 = exposeApi');
            $table->tinyInteger('source')->comment('1 = All ,2 = web, 3 = ios, 4 = android');
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
        Schema::dropIfExists('adminPolicy');
    }
}
