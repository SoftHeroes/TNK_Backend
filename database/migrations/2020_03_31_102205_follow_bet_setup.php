<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FollowBetSetup extends Migration {
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('followBetSetup');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('followBetSetup', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('followBetRuleID')->nullable();
            $table->string('unFollowBetRuleID')->nullable();
            $table->integer('minFollowBetRuleSelect')->comment('minimum number of rules have to be selected')->nullable();
            $table->integer('maxFollowBetRuleSelect')->comment('maximum number of rules can be selected')->nullable();
            $table->integer('minUnFollowBetRuleSelect')->comment('minimum number of rules have to be selected')->nullable();
            $table->integer('maxUnFollowBetRuleSelect')->comment('maximum number of rules can be selected')->nullable();
            $table->enum('isActive', ['active','inactive'])->default('active');
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
        });
    }
}
