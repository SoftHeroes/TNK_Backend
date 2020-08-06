<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFollowBetSetupFollowLimit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('followBetSetup', function (Blueprint $table) {
            $table->integer("maxFollowLimit")->nullable()->after('maxUnFollowBetRuleSelect')->default(null)->comment('Maximum number of users a user can follow');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('followBetSetup', function (Blueprint $table) {
            $table->dropColumn('maxFollowLimit');
        });
    }
}
