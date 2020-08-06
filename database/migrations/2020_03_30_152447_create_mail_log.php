<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailLog', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->text('to');
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['success','fail']);
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailLog');
    }
}
