<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUniqueKeyForAdminPolicyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adminPolicy', function (Blueprint $table) {
            $table->dropUnique('adminpolicy_name_unique');

            $table->unique(['name', 'deletedAt']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adminPolicy', function (Blueprint $table) {
            $table->dropUnique('adminpolicy_name_deletedat_unique');

            $table->unique('name');
        });
    }
}
