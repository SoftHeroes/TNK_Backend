<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpToHigh extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // change in dynamic odd table
        if (Schema::hasColumn('dynamicOdd', 'FD_UP')) {
            DB::statement("ALTER TABLE dynamicOdd change FD_UP FD_HIGH VARCHAR(64)");
        }
        if (Schema::hasColumn('dynamicOdd', 'LD_UP')) {
            DB::statement("ALTER TABLE dynamicOdd change LD_UP LD_HIGH VARCHAR(64)");
        }
        if (Schema::hasColumn('dynamicOdd', 'TD_UP')) {
            DB::statement("ALTER TABLE dynamicOdd change TD_UP TD_HIGH VARCHAR(64)");
        }
        if (Schema::hasColumn('dynamicOdd', 'BD_UP')) {
            DB::statement("ALTER TABLE dynamicOdd change BD_UP BD_HIGH VARCHAR(64)");
        }


        // change in initial odd table
        if (Schema::hasColumn('initialOdd', 'FD_UP')) {
            DB::statement("ALTER TABLE initialOdd change FD_UP FD_HIGH VARCHAR(64)");
        }
        if (Schema::hasColumn('initialOdd', 'LD_UP')) {
            DB::statement("ALTER TABLE initialOdd change LD_UP LD_HIGH VARCHAR(64)");
        }
        if (Schema::hasColumn('initialOdd', 'TD_UP')) {
            DB::statement("ALTER TABLE initialOdd change TD_UP TD_HIGH VARCHAR(64)");
        }
        if (Schema::hasColumn('initialOdd', 'BD_UP')) {
            DB::statement("ALTER TABLE initialOdd change BD_UP BD_HIGH VARCHAR(64)");
        }

        // change in rule table
        DB::statement("UPDATE rule SET name = 'FD_HIGH' WHERE name = 'FD_UP'");
        DB::statement("UPDATE rule SET name = 'LD_HIGH' WHERE name = 'LD_UP'");
        DB::statement("UPDATE rule SET name = 'TD_HIGH' WHERE name = 'TD_UP'");
        DB::statement("UPDATE rule SET name = 'BD_HIGH' WHERE name = 'BD_UP'");


        //change in providerGameSetup table

        if (Schema::hasColumn('providerGameSetup', 'FD_LowMiddleUpGameID')) {
            Schema::table('providerGameSetup', function (Blueprint $table) {
                $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='FD_LowMiddleUpGameID' AND Key_name = 'providergamesetup_fd_lowmiddleupgameid_foreign'"));
                if ($keyExists) {
                    $table->dropForeign('providergamesetup_fd_lowmiddleupgameid_foreign');
                }
            });
            DB::statement("ALTER TABLE providerGameSetup change FD_LowMiddleUpGameID FD_LowMiddleHighGameID BIGINT(20)");
        }
        Schema::table('providerGameSetup', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='FD_LowMiddleHighGameID'"));
            if (!$keyExists) {
                $table->foreign('FD_LowMiddleHighGameID')->references('PID')->on('gameSetup');
            }
        });



        if (Schema::hasColumn('providerGameSetup', 'LD_LowMiddleUpGameID')) {
            Schema::table('providerGameSetup', function (Blueprint $table) {
                $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='LD_LowMiddleUpGameID' AND Key_name = 'providergamesetup_ld_lowmiddleupgameid_foreign'"));
                if ($keyExists) {
                    $table->dropForeign('providergamesetup_ld_lowmiddleupgameid_foreign');
                }
            });
            DB::statement("ALTER TABLE providerGameSetup change LD_LowMiddleUpGameID LD_LowMiddleHighGameID BIGINT(20)");
        }
        Schema::table('providerGameSetup', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='LD_LowMiddleHighGameID'"));
            if (!$keyExists) {
                $table->foreign('LD_LowMiddleHighGameID')->references('PID')->on('gameSetup');
            }
        });



        if (Schema::hasColumn('providerGameSetup', 'TD_LowMiddleUpGameID')) {
            Schema::table('providerGameSetup', function (Blueprint $table) {
                $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='TD_LowMiddleUpGameID' AND Key_name = 'providergamesetup_td_lowmiddleupgameid_foreign'"));
                if ($keyExists) {
                    $table->dropForeign('providergamesetup_td_lowmiddleupgameid_foreign');
                }
            });
            DB::statement("ALTER TABLE providerGameSetup change TD_LowMiddleUpGameID TD_LowMiddleHighGameID BIGINT(20)");
        }
        Schema::table('providerGameSetup', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='TD_LowMiddleHighGameID'"));
            if (!$keyExists) {
                $table->foreign('TD_LowMiddleHighGameID')->references('PID')->on('gameSetup');
            }
        });



        if (Schema::hasColumn('providerGameSetup', 'BD_LowMiddleUpGameID')) {
            Schema::table('providerGameSetup', function (Blueprint $table) {
                $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='BD_LowMiddleUpGameID' AND Key_name = 'providergamesetup_bd_lowmiddleupgameid_foreign'"));
                if ($keyExists) {
                    $table->dropForeign('providergamesetup_bd_lowmiddleupgameid_foreign');
                }
            });
            DB::statement("ALTER TABLE providerGameSetup change BD_LowMiddleUpGameID BD_LowMiddleHighGameID BIGINT(20)");
        }

        Schema::table('providerGameSetup', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='BD_LowMiddleHighGameID'"));
            if (!$keyExists) {
                $table->foreign('BD_LowMiddleHighGameID')->references('PID')->on('gameSetup');
            }
        });


        // change in gameSetup table
        DB::statement("UPDATE gameSetup SET gameName = 'FD_HighMiddleLow' WHERE gameName = 'FD_UpMiddleLow'");
        DB::statement("UPDATE gameSetup SET gameName = 'LD_HighMiddleLow' WHERE gameName = 'LD_UpMiddleLow'");
        DB::statement("UPDATE gameSetup SET gameName = 'TD_HighMiddleLow' WHERE gameName = 'TD_UpMiddleLow'");
        DB::statement("UPDATE gameSetup SET gameName = 'BD_HighMiddleLow' WHERE gameName = 'BD_UpMiddleLow'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        if (Schema::hasColumn('dynamicOdd', 'FD_HIGH')) {
            DB::statement("ALTER TABLE dynamicOdd change FD_HIGH FD_UP VARCHAR(64)");
        }
        if (Schema::hasColumn('dynamicOdd', 'LD_HIGH')) {
            DB::statement("ALTER TABLE dynamicOdd change LD_HIGH LD_UP VARCHAR(64)");
        }
        if (Schema::hasColumn('dynamicOdd', 'TD_HIGH')) {
            DB::statement("ALTER TABLE dynamicOdd change TD_HIGH TD_UP VARCHAR(64)");
        }
        if (Schema::hasColumn('dynamicOdd', 'BD_HIGH')) {
            DB::statement("ALTER TABLE dynamicOdd change BD_HIGH BD_UP VARCHAR(64)");
        }



        if (Schema::hasColumn('initialOdd', 'FD_HIGH')) {
            DB::statement("ALTER TABLE initialOdd change FD_HIGH FD_UP VARCHAR(64)");
        }
        if (Schema::hasColumn('initialOdd', 'LD_HIGH')) {
            DB::statement("ALTER TABLE initialOdd change LD_HIGH LD_UP VARCHAR(64)");
        }
        if (Schema::hasColumn('initialOdd', 'TD_HIGH')) {
            DB::statement("ALTER TABLE initialOdd change TD_HIGH TD_UP VARCHAR(64)");
        }
        if (Schema::hasColumn('initialOdd', 'BD_HIGH')) {
            DB::statement("ALTER TABLE initialOdd change BD_HIGH BD_UP VARCHAR(64)");
        }


        // change in rule table
        DB::statement("UPDATE rule SET name = 'FD_UP' WHERE name = 'FD_HIGH'");
        DB::statement("UPDATE rule SET name = 'LD_UP' WHERE name = 'LD_HIGH'");
        DB::statement("UPDATE rule SET name = 'TD_UP' WHERE name = 'TD_HIGH'");
        DB::statement("UPDATE rule SET name = 'BD_UP' WHERE name = 'BD_HIGH'");


        // //change in providerGameSetup table

        if (Schema::hasColumn('providerGameSetup', 'FD_LowMiddleHighGameID')) {
            Schema::table('providerGameSetup', function (Blueprint $table) {
                $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='FD_LowMiddleHighGameID' AND Key_name = 'providergamesetup_fd_lowmiddleupgameid_foreign'"));
                if ($keyExists) {
                    $table->dropForeign('providergamesetup_fd_lowmiddleupgameid_foreign');
                }
            });
            DB::statement("ALTER TABLE providerGameSetup change FD_LowMiddleHighGameID FD_LowMiddleUpGameID BIGINT(20)");
        }
        Schema::table('providerGameSetup', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='FD_LowMiddleUpGameID'"));
            if (!$keyExists) {
                $table->foreign('FD_LowMiddleUpGameID')->references('PID')->on('gameSetup');
            }
        });



        if (Schema::hasColumn('providerGameSetup', 'LD_LowMiddleHighGameID')) {
            Schema::table('providerGameSetup', function (Blueprint $table) {
                $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='LD_LowMiddleHighGameID' AND Key_name = 'providergamesetup_ld_lowmiddleupgameid_foreign'"));
                if ($keyExists) {
                    $table->dropForeign('providergamesetup_ld_lowmiddleupgameid_foreign');
                }
            });
            DB::statement("ALTER TABLE providerGameSetup change LD_LowMiddleHighGameID LD_LowMiddleUpGameID BIGINT(20)");
        }
        Schema::table('providerGameSetup', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='LD_LowMiddleUpGameID'"));
            if (!$keyExists) {
                $table->foreign('LD_LowMiddleUpGameID')->references('PID')->on('gameSetup');
            }
        });



        if (Schema::hasColumn('providerGameSetup', 'TD_LowMiddleHighGameID')) {
            Schema::table('providerGameSetup', function (Blueprint $table) {
                $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='TD_LowMiddleHighGameID' AND Key_name = 'providergamesetup_td_lowmiddleupgameid_foreign'"));
                if ($keyExists) {
                    $table->dropForeign('');
                }
            });
            DB::statement("ALTER TABLE providerGameSetup change TD_LowMiddleHighGameID TD_LowMiddleUpGameID BIGINT(20)");
        }
        Schema::table('providerGameSetup', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='TD_LowMiddleUpGameID'"));
            if (!$keyExists) {
                $table->foreign('TD_LowMiddleUpGameID')->references('PID')->on('gameSetup');
            }
        });



        if (Schema::hasColumn('providerGameSetup', 'BD_LowMiddleHighGameID')) {
            Schema::table('providerGameSetup', function (Blueprint $table) {
                $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='BD_LowMiddleHighGameID' AND Key_name = 'providergamesetup_bd_lowmiddleupgameid_foreign'"));
                if ($keyExists) {
                    $table->dropForeign('providergamesetup_bd_lowmiddleupgameid_foreign');
                }
            });
            DB::statement("ALTER TABLE providerGameSetup change BD_LowMiddleHighGameID BD_LowMiddleUpGameID BIGINT(20)");
        }
        Schema::table('providerGameSetup', function (Blueprint $table) {
            $keyExists = DB::select(DB::raw("SHOW KEYS FROM providerGameSetup WHERE Column_name='BD_LowMiddleUpGameID'"));
            if (!$keyExists) {
                $table->foreign('BD_LowMiddleUpGameID')->references('PID')->on('gameSetup');
            }
        });

        // change in gameSetup table
        DB::statement("UPDATE gameSetup SET gameName = 'FD_UpMiddleLow' WHERE gameName = 'FD_HighMiddleLow'");
        DB::statement("UPDATE gameSetup SET gameName = 'LD_UpMiddleLow' WHERE gameName = 'LD_HighMiddleLow'");
        DB::statement("UPDATE gameSetup SET gameName = 'TD_UpMiddleLow' WHERE gameName = 'TD_HighMiddleLow'");
        DB::statement("UPDATE gameSetup SET gameName = 'BD_UpMiddleLow' WHERE gameName = 'BD_HighMiddleLow'");
    }
}
