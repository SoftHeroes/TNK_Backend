<?php

use Illuminate\Database\Migrations\Migration;

require_once app_path() . '/Helpers/SqlHelper.php';

class UpdateGetUserDetailSp extends Migration
{

    protected $type = 'Procedure';
    protected $name = 'USP_GetUserDetails';

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
