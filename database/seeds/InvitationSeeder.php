<?php

use Illuminate\Database\Seeder;
use App\Models\InvitationSetup;

class InvitationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $invitationSetup = array(
            array('PID'=>1,'maximumRequestInDay'=>10,'requestMin'=>60,'maximumRequestInMin'=>3)
        );
        InvitationSetup::insert($invitationSetup);

        DB::update("UPDATE providerConfig SET invitationSetupID = 1 WHERE PID != 1");

    }
}
