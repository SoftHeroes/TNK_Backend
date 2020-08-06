<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Stock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->string('name')->unique();
            $table->string('ReferenceURL');
            $table->string('url');
            $table->string('method');
            $table->string('country');
            $table->tinyInteger('stockLoop');
            $table->Integer('betCloseSec')->comment('bet close time in seconds.');
            $table->string('closeDays')->comment('0 = Sunday,1 = Monday,2 = Tuesday,3 = Wednesday,4 = Thursday,5 = Friday,6 = Saturday');
            $table->string('limitTag');
            $table->string('openTimeRange')->nullable();
            $table->string('timeZone');
            $table->string('category');
            $table->integer('precision');
            $table->string('responseType');
            $table->string('responseStockOpenTag');
            $table->string('responseStockTimeTag');
            $table->string('responseStockTimeZone');
            $table->string('responseStockTimeFormat');
            $table->string('responseStockDataTag')->nullable();
            $table->json('replaceJsonRules')->nullable();
            $table->string('liveStockUrl'); // Live Stock API detail : START
            $table->string('liveStockResponseType');
            $table->string('liveStockOpenTag')->nullable();
            $table->string('liveStockTimeTag')->nullable();
            $table->string('splitString')->nullable();
            $table->integer('openValueIndex')->nullable();
            $table->string('dateValueIndex')->nullable();
            $table->string('timeValueIndex')->nullable();
            $table->string('liveStockDataTag')->nullable();
            $table->json('liveStockReplaceJsonRules')->nullable(); // Live Stock API detail : END
            $table->string('UUID')->unique();
            $table->enum('isActive', ['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('stock');
    }
}
