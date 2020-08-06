<?php

use App\Models\Admin;
use App\Models\AdminPolicy;
use Illuminate\Database\Seeder;

class createShareHolderWebAPIUser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminPolicies = [
            array('PID' => 8, 'name' => 'Shareholder1_WebPolicy', 'access' => 5, 'otpValidTimeInSeconds' => '300', 'source' => 2),
        ];
        AdminPolicy::insert($adminPolicies);

        $admin = [
            array(
                'PID' => 10,
                'adminPolicyID' => 8,
                'portalProviderID' => 4,
                'emailID' => 'shareHolder1@demo.com',
                'firstName' => 'ShareHolder1',
                'lastName' => 'WebAPI',
                'username' => 'SHolder1_Web',
                'password' => Crypt::encrypt('FF4873E63C325')
            )
        ];
        Admin::insert($admin);
    }
}
