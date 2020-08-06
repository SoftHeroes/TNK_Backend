<?php

use App\Models\Admin;
use App\Models\AccessPolicy;
use Illuminate\Database\Seeder;

class SeedForAccessPolicy extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $accessPolicyData = [
            array('PID' => 1, 'name' => 'baseAPI', 'isAllowAll' => 'false', 'portalProviderIDs' => null),
            array('PID' => 2, 'name' => 'TNKMasterPolicy', 'isAllowAll' => 'true', 'portalProviderIDs' => null),
            array('PID' => 3, 'name' => 'testForPP4andPP2', 'isAllowAll' => 'false', 'portalProviderIDs' => "2,4"),
            array('PID' => 4, 'name' => 'shareHolder1Admin', 'isAllowAll' => 'false', 'portalProviderIDs' => "4"),
            array('PID' => 5, 'name' => 'Test_PP_1_Admin', 'isAllowAll' => 'false', 'portalProviderIDs' => "2"),
        ];

        AccessPolicy::insert($accessPolicyData);

        if (Admin::where('username', 'TNKSuper')->count(DB::raw('1')) != 0) {
            Admin::updateOrCreate(
                ['username' => 'TNKSuper'],
                ['accessPolicyID' => 2]
            );
        }

        if (Admin::where('username', 'Test_PortalProvider')->count(DB::raw('1')) != 0) {
            Admin::updateOrCreate(
                ['username' => 'Test_PortalProvider'],
                ['accessPolicyID' => 1]
            );
        }

        if (Admin::where('username', 'Test_PortalProvider2')->count(DB::raw('1')) != 0) {
            Admin::updateOrCreate(
                ['username' => 'Test_PortalProvider2'],
                ['accessPolicyID' => 1]
            );
        }

        if (Admin::where('username', 'ShareHolder1_Admin')->count(DB::raw('1')) != 0) {
            Admin::updateOrCreate(
                ['username' => 'ShareHolder1_Admin'],
                ['accessPolicyID' => 4]
            );
        }

        if (Admin::where('username', 'SHolder1_IOS')->count(DB::raw('1')) != 0) {
            Admin::updateOrCreate(
                ['username' => 'SHolder1_IOS'],
                ['accessPolicyID' => 1]
            );
        }

        if (Admin::where('username', 'tnkWebApi')->count(DB::raw('1')) != 0) {
            Admin::updateOrCreate(
                ['username' => 'tnkWebApi'],
                ['accessPolicyID' => 1]
            );
        }

        if (Admin::where('username', 'TnkAdmin')->count(DB::raw('1')) != 0) {
            Admin::updateOrCreate(
                ['username' => 'TnkAdmin'],
                ['accessPolicyID' => 2]
            );
        }

        if (Admin::where('username', 'tnkIOSApi')->count(DB::raw('1')) != 0) {
            Admin::updateOrCreate(
                ['username' => 'tnkIOSApi'],
                ['accessPolicyID' => 1]
            );
        }

        if (Admin::where('username', 'admin_sandesh')->count(DB::raw('1')) != 0) {
            Admin::updateOrCreate(
                ['username' => 'admin_sandesh'],
                ['accessPolicyID' => 5]
            );
        }

        if (Admin::where('username', 'SHolder1_Web')->count(DB::raw('1')) != 0) {
            Admin::updateOrCreate(
                ['username' => 'SHolder1_Web'],
                ['accessPolicyID' => 1]
            );
        }
    }
}
