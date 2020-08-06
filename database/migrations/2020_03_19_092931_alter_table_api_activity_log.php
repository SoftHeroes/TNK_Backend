<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableApiActivityLog extends Migration
{
    public function up()
    {
        // ALTER TABLE apiActivityLog 
        Schema::table('apiActivityLog', function (Blueprint $table) {
            $table->longText('responseMessage')->change(); // Change responseMessage column type
        });
    }

    public function down()
    {
        
    }
}
