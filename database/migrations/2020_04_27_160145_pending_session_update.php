<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PendingSessionUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('pendingSessionUpdate')) {
            Schema::create('pendingSessionUpdate', function (Blueprint $table) {
                $table->bigIncrements('PID');
                $table->string('ip');
                $table->string('tag');
                $table->longText('value')->nullable();
                $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendingSessionUpdate');
    }
}
