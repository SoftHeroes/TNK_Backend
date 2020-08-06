<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->tinyInteger("agentType")->nullable()->comment("1 = self distribute; 2 = auto calculation; 3 = commisson");
            $table->tinyInteger("agentPaymentType")->nullable()->comment("1 = prepaid; 2 = credit");
            $table->date('billingDate')->nullable();
            $table->date("pendingBillingDate")->nullable();
            $table->double("mainBalance")->default(0);
            $table->double("creditBalance")->default(0);
            $table->double("limit")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn("agentType");
            $table->dropColumn("agentPaymentType");
            $table->dropColumn('billingDate');
            $table->dropColumn("pendingBillingDate");
            $table->dropColumn("mainBalance");
            $table->dropColumn("creditBalance");
            $table->dropColumn("limit");
        });
    }
}
