<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DatabaseIndexing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('apiActivityLog', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM apiActivityLog WHERE Column_name='service'"));
            if (!$keyExists) {
                $table->index('service');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM apiActivityLog WHERE Column_name='errorFound'"));
            if (!$keyExists) {
                $table->index('errorFound');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM apiActivityLog WHERE Column_name='portalProviderID'"));
            if (!$keyExists) {
                $table->index('portalProviderID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM apiActivityLog WHERE Column_name='userID'"));
            if (!$keyExists) {
                $table->index('userID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM apiActivityLog WHERE Column_name='exceptionFound'"));
            if (!$keyExists) {
                $table->index('exceptionFound');
            }
        });

        Schema::table('game', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM game WHERE Column_name='gameStatus'"));
            if (!$keyExists) {
                $table->index('gameStatus');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM game WHERE Column_name='startDate'"));
            if (!$keyExists) {
                $table->index('startDate');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM game WHERE Column_name='endDate'"));
            if (!$keyExists) {
                $table->index('endDate');
            }
        });

        Schema::table('poolLog', function (Blueprint $table) {

            $keyExists = DB::select(DB::raw("SHOW KEYS FROM poolLog WHERE Column_name='userID'"));
            if (!$keyExists) {
                $table->index('userID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM poolLog WHERE Column_name='adminID'"));
            if (!$keyExists) {
                $table->index('adminID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM poolLog WHERE Column_name='operation'"));
            if (!$keyExists) {
                $table->index('operation');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM poolLog WHERE Column_name='serviceName'"));
            if (!$keyExists) {
                $table->index('serviceName');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM poolLog WHERE Column_name='balanceType'"));
            if (!$keyExists) {
                $table->index('balanceType');
            }
        });

        Schema::table('stockHistory', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM stockHistory WHERE Column_name='createdDate'"));
            if (!$keyExists) {
                $table->index('createdDate');
            }
        });

        Schema::table('userSession', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM userSession WHERE Column_name='deletedAt'"));
            if (!$keyExists) {
                $table->index('deletedAt');
            }
        });

        Schema::table('betting', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM betting WHERE Column_name='betResult'"));
            if (!$keyExists) {
                $table->index('betResult');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM betting WHERE Column_name='parentBetID'"));
            if (!$keyExists) {
                $table->index('parentBetID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM betting WHERE Column_name='followToID'"));
            if (!$keyExists) {
                $table->index('followToID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM betting WHERE Column_name='createdDate'"));
            if (!$keyExists) {
                $table->index('createdDate');
            }
        });

        Schema::table('user', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM user WHERE Column_name='isLoggedIn'"));
            if (!$keyExists) {
                $table->index('isLoggedIn');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM user WHERE Column_name='isActive'"));
            if (!$keyExists) {
                $table->index('isActive');
            }
        });

        Schema::table('followUser', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM followUser WHERE Column_name='isFollowing'"));
            if (!$keyExists) {
                $table->index('isFollowing');
            }
        });

        Schema::table('admin', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM admin WHERE Column_name='emailID'"));
            if (!$keyExists) {
                $table->index('emailID');
            }
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM admin WHERE Column_name='isActive'"));
            if (!$keyExists) {
                $table->index('isActive');
            }
        });

        Schema::table('otpCheck', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM otpCheck WHERE Column_name='emailID'"));
            if (!$keyExists) {
                $table->index('emailID');
            }
        });

        Schema::table('mailLog', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM mailLog WHERE Column_name='status'"));
            if (!$keyExists) {
                $table->index('status');
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
        Schema::table('apiActivityLog', function (Blueprint $table) {
            $table->dropIndex('service');
            $table->dropIndex('errorFound');
            $table->dropIndex('portalProviderID');
            $table->dropIndex('userID');
            $table->dropIndex('exceptionFound');
        });
        Schema::table('game', function (Blueprint $table) {
            $table->dropIndex('gameStatus');
            $table->dropIndex('errorFound');
            $table->dropIndex('endDate');
        });
        Schema::table('poolLog', function (Blueprint $table) {
            $table->dropIndex('userID');
            $table->dropIndex('adminID');
            $table->dropIndex('operation');
            $table->dropIndex('serviceName');
            $table->dropIndex('balanceType');
        });
        Schema::table('stockHistory', function (Blueprint $table) {
            $table->dropIndex('createdDate');
        });
        Schema::table('userSession ', function (Blueprint $table) {
            $table->dropIndex('deletedAt');
        });
        Schema::table('betting', function (Blueprint $table) {
            $table->dropIndex('betResult');
            $table->dropIndex('parentBetID');
            $table->dropIndex('followToID');
            $table->dropIndex('createdDate');
        });
        Schema::table('user', function (Blueprint $table) {
            $table->dropIndex('isLoggedIn');
            $table->dropIndex('isActive');
        });
        Schema::table('followUser', function (Blueprint $table) {
            $table->dropIndex('isFollowing');
        });
        Schema::table('admin', function (Blueprint $table) {
            $table->dropIndex('emailID');
            $table->dropIndex('isActive');
        });
        Schema::table('otpCheck', function (Blueprint $table) {
            $table->dropIndex('emailID');
        });
        Schema::table('mailLog', function (Blueprint $table) {
            $table->dropIndex('status');
        });
    }
}
