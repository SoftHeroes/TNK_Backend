<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('notification', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM notification WHERE Column_name='adminID'"));
            if (!$keyExists) {
                $table->index('adminID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM notification WHERE Column_name='fromID'"));
            if (!$keyExists) {
                $table->index('fromID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM notification WHERE Column_name='toID'"));
            if (!$keyExists) {
                $table->index('toID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM notification WHERE Column_name='type'"));
            if (!$keyExists) {
                $table->index('type');
            }
        });

        Schema::table('logoutCallLog', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM logoutCallLog WHERE Column_name='portalProviderID'"));
            if (!$keyExists) {
                $table->index('portalProviderID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM logoutCallLog WHERE Column_name='adminID'"));
            if (!$keyExists) {
                $table->index('adminID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM logoutCallLog WHERE Column_name='userID'"));
            if (!$keyExists) {
                $table->index('userID');
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
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM notification WHERE Column_name='adminID'"));
            if ($keyExists) {
                $table->dropIndex('adminID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM notification WHERE Column_name='fromID'"));
            if ($keyExists) {
                $table->dropIndex('fromID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM notification WHERE Column_name='toID'"));
            if ($keyExists) {
                $table->dropIndex('toID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM notification WHERE Column_name='type'"));
            if ($keyExists) {
                $table->dropIndex('type');
            }
        });

        Schema::table('logoutCallLog', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM logoutCallLog WHERE Column_name='portalProviderID'"));
            if ($keyExists) {
                $table->dropIndex('portalProviderID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM logoutCallLog WHERE Column_name='adminID'"));
            if ($keyExists) {
                $table->dropIndex('adminID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM logoutCallLog WHERE Column_name='userID'"));
            if ($keyExists) {
                $table->dropIndex('userID');
            }
        });
    }
}
