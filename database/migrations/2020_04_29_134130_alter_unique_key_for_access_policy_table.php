<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUniqueKeyForAccessPolicyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accessPolicy', function (Blueprint $table) {
            $table->dropUnique('accesspolicy_name_unique');

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
        Schema::table('accessPolicy', function (Blueprint $table) {
            $table->dropUnique('accesspolicy_name_deletedat_unique');

            $table->unique('name');
        });
    }
}
