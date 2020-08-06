<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IpWhitelist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ipWhitelist')) {
            Schema::create('ipWhitelist', function (Blueprint $table) {
                $table->bigIncrements('PID');
                $table->string('name');
                $table->string('IP');
                $table->bigInteger('adminID')->unsigned();
                $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
                $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
                $table->softDeletes('deletedAt');
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
        Schema::dropIfExists('ipWhitelist');
    }
}
