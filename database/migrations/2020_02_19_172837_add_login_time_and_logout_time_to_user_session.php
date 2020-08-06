<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoginTimeAndLogoutTimeToUserSession extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('userSession', function (Blueprint $table) {
            $table->dateTime('loginTime')->after('balance')->nullable();
            $table->dateTime('logoutTime')->after('loginTime')->nullable();
            $table->dateTime('deletedAt')->after('updatedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('userSession', function (Blueprint $table) {
            $table->dropColumn('loginTime');
            $table->dropColumn('logoutTime');
            $table->dropColumn('deletedAt');
        });
    }
}
