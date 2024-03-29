<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DynamicOdd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynamicOdd', function (Blueprint $table) {
            $table->bigIncrements('PID');
            $table->bigInteger('gameID')->unsigned();
            $table->bigInteger('stockID')->unsigned()->nullable();
            $table->enum('isActive', ['active', 'inactive'])->default('active');
            $table->double('FD_BIG')->nullable();
            $table->double('FD_SMALL')->nullable();
            $table->double('FD_ODD')->nullable();
            $table->double('FD_EVEN')->nullable();
            $table->double('FD_HIGH')->nullable();
            $table->double('FD_MIDDLE')->nullable();
            $table->double('FD_LOW')->nullable();
            $table->double('FD_0')->nullable();
            $table->double('FD_1')->nullable();
            $table->double('FD_2')->nullable();
            $table->double('FD_3')->nullable();
            $table->double('FD_4')->nullable();
            $table->double('FD_5')->nullable();
            $table->double('FD_6')->nullable();
            $table->double('FD_7')->nullable();
            $table->double('FD_8')->nullable();
            $table->double('FD_9')->nullable();
            $table->double('LD_BIG')->nullable();
            $table->double('LD_SMALL')->nullable();
            $table->double('LD_ODD')->nullable();
            $table->double('LD_EVEN')->nullable();
            $table->double('LD_HIGH')->nullable();
            $table->double('LD_MIDDLE')->nullable();
            $table->double('LD_LOW')->nullable();
            $table->double('LD_0')->nullable();
            $table->double('LD_1')->nullable();
            $table->double('LD_2')->nullable();
            $table->double('LD_3')->nullable();
            $table->double('LD_4')->nullable();
            $table->double('LD_5')->nullable();
            $table->double('LD_6')->nullable();
            $table->double('LD_7')->nullable();
            $table->double('LD_8')->nullable();
            $table->double('LD_9')->nullable();
            $table->double('TD_BIG')->nullable();
            $table->double('TD_SMALL')->nullable();
            $table->double('TD_ODD')->nullable();
            $table->double('TD_EVEN')->nullable();
            $table->double('TD_HIGH')->nullable();
            $table->double('TD_MIDDLE')->nullable();
            $table->double('TD_LOW')->nullable();
            $table->double('TD_0')->nullable();
            $table->double('TD_1')->nullable();
            $table->double('TD_2')->nullable();
            $table->double('TD_3')->nullable();
            $table->double('TD_4')->nullable();
            $table->double('TD_5')->nullable();
            $table->double('TD_6')->nullable();
            $table->double('TD_7')->nullable();
            $table->double('TD_8')->nullable();
            $table->double('TD_9')->nullable();
            $table->double('TD_10')->nullable();
            $table->double('TD_11')->nullable();
            $table->double('TD_12')->nullable();
            $table->double('TD_13')->nullable();
            $table->double('TD_14')->nullable();
            $table->double('TD_15')->nullable();
            $table->double('TD_16')->nullable();
            $table->double('TD_17')->nullable();
            $table->double('TD_18')->nullable();
            $table->double('TD_19')->nullable();
            $table->double('TD_20')->nullable();
            $table->double('TD_21')->nullable();
            $table->double('TD_22')->nullable();
            $table->double('TD_23')->nullable();
            $table->double('TD_24')->nullable();
            $table->double('TD_25')->nullable();
            $table->double('TD_26')->nullable();
            $table->double('TD_27')->nullable();
            $table->double('TD_28')->nullable();
            $table->double('TD_29')->nullable();
            $table->double('TD_30')->nullable();
            $table->double('TD_31')->nullable();
            $table->double('TD_32')->nullable();
            $table->double('TD_33')->nullable();
            $table->double('TD_34')->nullable();
            $table->double('TD_35')->nullable();
            $table->double('TD_36')->nullable();
            $table->double('TD_37')->nullable();
            $table->double('TD_38')->nullable();
            $table->double('TD_39')->nullable();
            $table->double('TD_40')->nullable();
            $table->double('TD_41')->nullable();
            $table->double('TD_42')->nullable();
            $table->double('TD_43')->nullable();
            $table->double('TD_44')->nullable();
            $table->double('TD_45')->nullable();
            $table->double('TD_46')->nullable();
            $table->double('TD_47')->nullable();
            $table->double('TD_48')->nullable();
            $table->double('TD_49')->nullable();
            $table->double('TD_50')->nullable();
            $table->double('TD_51')->nullable();
            $table->double('TD_52')->nullable();
            $table->double('TD_53')->nullable();
            $table->double('TD_54')->nullable();
            $table->double('TD_55')->nullable();
            $table->double('TD_56')->nullable();
            $table->double('TD_57')->nullable();
            $table->double('TD_58')->nullable();
            $table->double('TD_59')->nullable();
            $table->double('TD_60')->nullable();
            $table->double('TD_61')->nullable();
            $table->double('TD_62')->nullable();
            $table->double('TD_63')->nullable();
            $table->double('TD_64')->nullable();
            $table->double('TD_65')->nullable();
            $table->double('TD_66')->nullable();
            $table->double('TD_67')->nullable();
            $table->double('TD_68')->nullable();
            $table->double('TD_69')->nullable();
            $table->double('TD_70')->nullable();
            $table->double('TD_71')->nullable();
            $table->double('TD_72')->nullable();
            $table->double('TD_73')->nullable();
            $table->double('TD_74')->nullable();
            $table->double('TD_75')->nullable();
            $table->double('TD_76')->nullable();
            $table->double('TD_77')->nullable();
            $table->double('TD_78')->nullable();
            $table->double('TD_79')->nullable();
            $table->double('TD_80')->nullable();
            $table->double('TD_81')->nullable();
            $table->double('TD_82')->nullable();
            $table->double('TD_83')->nullable();
            $table->double('TD_84')->nullable();
            $table->double('TD_85')->nullable();
            $table->double('TD_86')->nullable();
            $table->double('TD_87')->nullable();
            $table->double('TD_88')->nullable();
            $table->double('TD_89')->nullable();
            $table->double('TD_90')->nullable();
            $table->double('TD_91')->nullable();
            $table->double('TD_92')->nullable();
            $table->double('TD_93')->nullable();
            $table->double('TD_94')->nullable();
            $table->double('TD_95')->nullable();
            $table->double('TD_96')->nullable();
            $table->double('TD_97')->nullable();
            $table->double('TD_98')->nullable();
            $table->double('TD_99')->nullable();
            $table->double('BD_BIG')->nullable();
            $table->double('BD_SMALL')->nullable();
            $table->double('BD_ODD')->nullable();
            $table->double('BD_EVEN')->nullable();
            $table->double('BD_HIGH')->nullable();
            $table->double('BD_MIDDLE')->nullable();
            $table->double('BD_LOW')->nullable();
            $table->double('BD_0')->nullable();
            $table->double('BD_1')->nullable();
            $table->double('BD_2')->nullable();
            $table->double('BD_3')->nullable();
            $table->double('BD_4')->nullable();
            $table->double('BD_5')->nullable();
            $table->double('BD_6')->nullable();
            $table->double('BD_7')->nullable();
            $table->double('BD_8')->nullable();
            $table->double('BD_9')->nullable();
            $table->double('BD_10')->nullable();
            $table->double('BD_11')->nullable();
            $table->double('BD_12')->nullable();
            $table->double('BD_13')->nullable();
            $table->double('BD_14')->nullable();
            $table->double('BD_15')->nullable();
            $table->double('BD_16')->nullable();
            $table->double('BD_17')->nullable();
            $table->double('BD_18')->nullable();
            $table->double('TD_TIE')->nullable();
            $table->double('BD_TIE')->nullable();
            $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
            $table->foreign('gameID')->references('PID')->on('game');
            $table->foreign('stockID')->references('PID')->on('stock');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dynamicOdd');
    }
}
