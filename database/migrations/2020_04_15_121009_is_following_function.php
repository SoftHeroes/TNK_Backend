<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once app_path() . '/Helpers/SqlHelper.php';

class IsFollowingFunction extends Migration
{

    protected $type = 'Function';
    protected $name = 'isFollowing';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP " . $this->type . " IF EXISTS `" . $this->name . "`");
        DB::unprepared(getScriptAsSqlString($this->type, $this->name));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP " . $this->type . " IF EXISTS `" . $this->name . "`");
    }
}
