<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnHolidayList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::table('holidayList', function (Blueprint $table) {
            $table->string('className')->after('date');
            $table->double('id')->after('className');
            $table->string('title')->after('id');
            $table->date('start')->after('title');
            $table->date('end')->nullable()->after('start');
            $table->enum('stick', ['true','false'])->default('false');
            $table->date('updatedAt')->after('createdAt'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('holidayList', function (Blueprint $table) {
            $table->dropColumn('className');
            $table->dropColumn('id');
            $table->dropColumn('title');
            $table->dropColumn('start');
            $table->dropColumn('end');
            $table->dropColumn('stick');
            $table->dropColumn('updatedAt');
        });
    }
}
