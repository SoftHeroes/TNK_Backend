<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DatabaseMinorUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // adding foreign keys
        Schema::table('apiActivityLog', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM apiActivityLog WHERE Column_name='adminID'"));
            if (!$keyExists) {
                $table->foreign('adminID')->references('PID')->on('admin');
            }
        });
        Schema::table('poolLog', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM poolLog WHERE Column_name='portalProviderID'"));
            if (!$keyExists) {
                $table->foreign('portalProviderID')->references('PID')->on('portalProvider');
            }
        });
        Schema::table('chat', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM chat WHERE Column_name='userID'"));
            if (!$keyExists) {
                $table->foreign('userID')->references('PID')->on('user');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM chat WHERE Column_name='portalProviderID'"));
            if (!$keyExists) {
                $table->foreign('portalProviderID')->references('PID')->on('portalProvider');
            }
        });
        Schema::table('creditRequest', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM creditRequest WHERE Column_name='adminID'"));
            if (!$keyExists) {
                $table->foreign('adminID')->references('PID')->on('admin');
            }
        });

        //dropping column
        if (Schema::hasColumn('followUser', 'value')) {
            Schema::table('followUser', function (Blueprint $table) {
                $table->dropColumn('value');
            });
        }

        //dropping unique key
        Schema::table('admin', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM admin WHERE Key_name='admin_emailid_unique'"));
            if ($keyExists) {

                DB::select(DB::raw("ALTER TABLE admin DROP INDEX admin_emailid_unique"));
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
        //removing indexes
        Schema::table('apiActivityLog', function (Blueprint $table) {
            $table->dropForeign(['adminID']);
        });
        Schema::table('poolLog', function (Blueprint $table) {
            $table->dropForeign(['portalProviderID']);
        });
        Schema::table('chat', function (Blueprint $table) {
            $table->dropForeign(['userID']);
            $table->dropForeign(['portalProviderID']);
        });
        Schema::table('creditRequest', function (Blueprint $table) {
            $table->dropForeign(['adminID']);
        });

        //adding unique
        Schema::table('admin', function (Blueprint $table) {
            $table->unique('emailID', 'admin_emailid_unique');
        });
    }
}
