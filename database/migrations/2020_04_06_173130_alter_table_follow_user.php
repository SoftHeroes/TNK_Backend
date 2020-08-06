<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableFollowUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //creating new column
        Schema::table('followUser', function (Blueprint $table) {
            $table->string('followBetRuleID')->nullable()->after('isFollowing');
            $table->longText('followRuleValue')->nullable()->after('followBetRuleID');
            $table->string('unFollowBetRuleID')->nullable()->after('followRuleValue');
            $table->longText('unFollowRuleValue')->nullable()->after('unFollowBetRuleID');
        });

        //updating the records in new column
        DB::update("update followUser set followBetRuleID = '[1]' where followType = 'Amount'");
        DB::update("update followUser set followBetRuleID = '[2]' where followType = 'Rate'");

        //deleting old columns
        Schema::table('followUser', function (Blueprint $table) {
            $table->dropColumn('followType');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //creating old column again
        Schema::table('followUser', function (Blueprint $table) {
            $table->enum('followType',['Amount','Rate'])->after('isFollowing');
        });

        //deleting new column
        Schema::table('followUser', function (Blueprint $table) {
            $table->dropColumn('followBetRuleID');
        });
    }
}
