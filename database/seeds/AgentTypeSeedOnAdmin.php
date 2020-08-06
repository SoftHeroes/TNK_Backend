<?php

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\AdminPolicy;

class AgentTypeSeedOnAdmin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminPolicies = [
            array('PID' => 9, 'name' => 'Agent', 'access' => 3, 'otpValidTimeInSeconds' => '300', 'source' => 2),
        ];
        AdminPolicy::insert($adminPolicies);
        $admin = [
            array(
                'PID' => 11,
                'adminPolicyID' => 9,
                'portalProviderID' => 2,
                'accessPolicyID'=>2,
                'emailID' => 'AgentTypeAuto@demo.com',
                'firstName' => 'AgentType',
                'lastName' => 'auto',
                'username' => 'AgentTypeAuto',
                'password' => Crypt::encrypt('FF4873E63C325'),
                'agentType' => 2,
                'agentPaymentType' => 1,
                'billingDate' => "2020-05-06",
            ),
            array(
                'PID' => 12,
                'adminPolicyID' => 9,
                'portalProviderID' => 2,
                'accessPolicyID'=>2,
                'emailID' => 'AgentTypeSell@demo.com',
                'firstName' => 'AgentTypeSell',
                'lastName' => 'Sell',
                'username' => 'AgentTypeSell',
                'password' => Crypt::encrypt('FF4873E63C325'),
                'agentType' => 1,
                'agentPaymentType' => 2,
                'billingDate' => "2020-05-06",
            ),
            array(
                'PID' => 13,
                'adminPolicyID' => 9,
                'portalProviderID' => 2,
                'accessPolicyID'=>2,
                'emailID' => 'AgentTypeCommission@demo.com',
                'firstName' => 'AgentTypeCommission',
                'lastName' => 'Commission',
                'username' => 'AgentTypeCommission',
                'password' => Crypt::encrypt('FF4873E63C325'),
                'agentType' => 3,
                'agentPaymentType' => 1,
                'billingDate' => "2020-05-06",
            ),
        ];
        Admin::insert($admin);
    }
}
