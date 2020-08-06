<?php

use App\Models\Admin;
use App\Models\AdminPolicy;
use Illuminate\Database\Seeder;

class createISOUser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $adminPolicies = [
            array('PID' => 7, 'name' => 'TNK_IOSApiPolicy', 'access' => 2, 'otpValidTimeInSeconds' => '300', 'source' => 3),
        ];
        AdminPolicy::insert($adminPolicies);

        $admin = [array(
            'adminPolicyID' => 7,
            'portalProviderID' => 1,
            'emailID' => 'vipintnk11@gmail.com',
            'firstName' => '',
            'lastName' => '',
            'username' => 'tnkIOSApi',
            'password' => Crypt::encrypt('Test123!')
        )];

        Admin::insert($admin);
    }
}
