<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStockTimestampInStockHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stockHistory', function (Blueprint $table) {
            $table->decimal('stockValue', 15, 5)->change(); 
            $table->dateTime('stockTimestamp')->after('stockValue')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stockHistory', function (Blueprint $table) {
            $table->dropColumn('stockTimestamp');
        });
    }
}
