<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AccessPolicy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('accessPolicy')) {
            Schema::create('accessPolicy', function (Blueprint $table) {
                $table->bigIncrements('PID');
                $table->string('name')->unique();
                $table->enum('isAllowAll', ['true', 'false'])->default('false');
                $table->longText('portalProviderIDs')->nullable();
                $table->enum('isActive', ['active', 'inactive'])->default('active');
                $table->dateTime('createdAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
                $table->dateTime('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP()'));
                $table->softDeletes('deletedAt');
            });
        }

        Schema::table('admin', function (Blueprint $table) {
            if (!Schema::hasColumn('admin', 'accessPolicyID')) {
                $table->bigInteger('accessPolicyID')->unsigned();
            }
        });

        Artisan::call('db:seed', ['--class' => 'SeedForAccessPolicy']);

        $check = DB::select('SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?', [env('DB_DATABASE'), 'admin', 'admin_accesspolicyid_foreign']);
        if (count($check) == 0) {
            Schema::table('admin', function (Blueprint $table) {
                $table->foreign('accessPolicyID')->references('PID')->on('accessPolicy');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('admin', function (Blueprint $table) {
            $table->dropForeign('admin_accesspolicyid_foreign');
            $table->dropColumn('accessPolicyID');
        });

        Schema::dropIfExists('accessPolicy');
    }
}
