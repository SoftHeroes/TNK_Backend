<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableNotificationAddDeletedAtColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification', function (Blueprint $table) {
            if (!Schema::hasColumn('notification', 'deletedAt')) {
                $table->softDeletes('deletedAt');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification', function (Blueprint $table) {
            if (Schema::hasColumn('notification', 'deletedAt')) {
                $table->dropColumn('deletedAt');
            }
        });
    }
}
