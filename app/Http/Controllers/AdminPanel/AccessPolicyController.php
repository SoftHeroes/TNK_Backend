<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AccessPolicy;
use App\Models\Admin;
use App\Models\PortalProvider;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccessPolicyController extends Controller
{
    public function getAccessPolicy(Request $request)
    {
        try {
            $portalProviderModel = new PortalProvider();
            $adminModel = new Admin();
            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');
            $providerData = $portalProviderModel->getPortalProviderByUUID($sessionData['portalProviderUUID']);

            if ($providerData->count(DB::raw('1')) == 0) {
                return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
            } else {
                //To get the accessibility of the admin policy tab based on the admin id
                $adminData = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessAccessPolicy', 'isAllowAll')->get();
                $accessibility = $adminData[0]->accessAccessPolicy;
                $isAllowAll = $adminData[0]->isAllowAll;

                if ($request->cookie('includeDeleted')) {
                    $accessPolicyData = AccessPolicy::withTrashed()->get();
                } else {
                    $accessPolicyData = AccessPolicy::get();
                }
                $portalProviderData = $portalProviderModel->getPortalProviders()->get();
                return view('adminPanel/accessPolicy', compact('accessPolicyData', 'portalProviderData', 'accessibility', 'isAllowAll'));
            }
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function createAccessPolicy(Request $request)
    {
        $accessPolicyModel = new AccessPolicy();
        // compulsory parameters check
        $rules = array(
            'name' => 'required|unique:accessPolicy,name,NULL,PID,deletedAt,NULL|max:'.config('constants.string_max_length'),
            'isAllowAll' => 'required',
            'isActive' => 'required',
            'accessAdminPolicy' => 'required',
            'accessAccessPolicy' => 'required',
            'accessAdminInformation' => 'required',
            'accessProviderList' => 'required',
            'accessProviderConfig' => 'required',
            'accessCurrency' => 'required',
            'accessBetRule' => 'required',
            'accessBetSetup' => 'required',
            'accessInvitationSetup' => 'required',
            'accessProviderGameSetup' => 'required',
            'accessProviderRequestList' => 'required',
            'accessProviderRequestBalance' => 'required',
            'accessProviderInfo' => 'required',
            'accessNotification' => 'required',
            'accessHolidayList' => 'required',
            'accessMonetaryLog' => 'required',
            'accessActivityLog' => 'required'
        );

        $messages = array(
            'name.required' => 'name is required.',
            'name.max' => "name shouldn't greater than". config('constants.string_max_length') ." characters.",
            'isAllowAll.required' => 'Allow All is required.',
            'isActive.required' => 'isActive is required.',
            'accessAdminPolicy.required' => 'access Admin Policy is required.',
            'accessAccessPolicy.required' => 'access Access Policy is required.',
            'accessAdminInformation.required' => 'access Admin Information is required.',
            'accessProviderList.required' => 'access Provider List is required.',
            'accessProviderConfig.required' => 'access Provider Config is required.',
            'accessCurrency.required' => 'access Currency is required.',
            'accessBetRule.required' => 'access BetRule is required.',
            'accessBetSetup.required' => 'access Bet Setup is required.',
            'accessInvitationSetup.required' => 'access Invitation Setup is required.',
            'accessProviderGameSetup.required' => 'accessProviderGameSetup is required.',
            'accessProviderRequestList.required' => 'accessProviderRequestList is required.',
            'accessProviderRequestBalance.required' => 'accessProviderRequestBalance is required.',
            'accessProviderInfo.required' => 'accessProviderInfo is required.',
            'accessNotification.required' => 'accessNotification is required.',
            'accessHolidayList.required' => 'accessHolidayList is required.',
            'accessMonetaryLog.required' => 'accessMonetaryLog is required.',
            'accessActivityLog.required' => 'accessActivityLog is required.'
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        try {

            $data['name'] = $request->name;
            $data['isAllowAll'] = $request->isAllowAll;
            $data['isActive'] = $request->isActive;

            $data['accessAdminPolicy'] = $request->accessAdminPolicy;
            $data['accessAccessPolicy'] = $request->accessAccessPolicy;
            $data['accessAdminInformation'] = $request->accessAdminInformation;
            $data['accessProviderList'] = $request->accessProviderList;
            $data['accessProviderConfig'] = $request->accessProviderConfig;
            $data['accessCurrency'] = $request->accessCurrency;
            $data['accessBetRule'] = $request->accessBetRule;
            $data['accessBetSetup'] = $request->accessBetSetup;
            $data['accessInvitationSetup'] = $request->accessInvitationSetup;
            $data['accessProviderGameSetup'] = $request->accessProviderGameSetup;
            $data['accessProviderRequestList'] = $request->accessProviderRequestList;
            $data['accessProviderRequestBalance'] = $request->accessProviderRequestBalance;
            $data['accessProviderInfo'] = $request->accessProviderInfo;
            $data['accessNotification'] = $request->accessNotification;
            $data['accessHolidayList'] = $request->accessHolidayList;
            $data['accessMonetaryLog'] = $request->accessMonetaryLog;
            $data['accessActivityLog'] = $request->accessActivityLog;

            if ($request->portalProviderID != "") {
                $portalProviderIDs = $request->portalProviderID;
                $data['portalProviderIDs'] = implode(",", $portalProviderIDs); // Joining all the cells in the array together => 1, 2, 3
            }

            // insert to DB
            $response = $accessPolicyModel->create($data);
            if ($response) {
                return redirect()->back()->with('message', 'Access Policy Created Successfully');
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function updateAccessPolicy(Request $request)
    {
        $accessPolicyPID = $request->accessPolicyPID;

        try {
            $accessPolicyModel = new AccessPolicy();

            $rules = array(
                'name' => "required|unique:accessPolicy,name,{$accessPolicyPID},PID,deletedAt,NULL",
                'isAllowAll' => 'required',
                'isActive' => 'required',
                'accessAdminPolicy' => 'required',
                'accessAccessPolicy' => 'required',
                'accessAdminInformation' => 'required',
                'accessProviderList' => 'required',
                'accessProviderConfig' => 'required',
                'accessCurrency' => 'required',
                'accessBetRule' => 'required',
                'accessBetSetup' => 'required',
                'accessInvitationSetup' => 'required',
                'accessProviderGameSetup' => 'required',
                'accessProviderRequestList' => 'required',
                'accessProviderRequestBalance' => 'required',
                'accessProviderInfo' => 'required',
                'accessNotification' => 'required',
                'accessHolidayList' => 'required',
                'accessMonetaryLog' => 'required',
                'accessActivityLog' => 'required'
            );

            $messages = array(
                'name.required' => 'name is required.',
                'isAllowAll.required' => 'Allow All is required.',
                'isActive.required' => 'isActive is required.',
                'accessAdminPolicy.required' => 'access Admin Policy is required.',
                'accessAccessPolicy.required' => 'access Access Policy is required.',
                'accessAdminInformation.required' => 'access Admin Information is required.',
                'accessProviderList.required' => 'access Provider List is required.',
                'accessProviderConfig.required' => 'access Provider Config is required.',
                'accessCurrency.required' => 'access Currency is required.',
                'accessBetRule.required' => 'access BetRule is required.',
                'accessBetSetup.required' => 'access Bet Setup is required.',
                'accessInvitationSetup.required' => 'access Invitation Setup is required.',
                'accessProviderGameSetup.required' => 'accessProviderGameSetup is required.',
                'accessProviderRequestList.required' => 'accessProviderRequestList is required.',
                'accessProviderRequestBalance.required' => 'accessProviderRequestBalance is required.',
                'accessProviderInfo.required' => 'accessProviderInfo is required.',
                'accessNotification.required' => 'accessNotification is required.',
                'accessHolidayList.required' => 'accessHolidayList is required.',
                'accessMonetaryLog.required' => 'accessMonetaryLog is required.',
                'accessActivityLog.required' => 'accessActivityLog is required.'
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            }

            $data['isAllowAll'] = $request->isAllowAll;
            $data['name'] = $request->name;
            $data['isActive'] = $request->isActive;

            $data['accessAdminPolicy'] = $request->accessAdminPolicy;
            $data['accessAccessPolicy'] = $request->accessAccessPolicy;
            $data['accessAdminInformation'] = $request->accessAdminInformation;
            $data['accessProviderList'] = $request->accessProviderList;
            $data['accessProviderConfig'] = $request->accessProviderConfig;
            $data['accessCurrency'] = $request->accessCurrency;
            $data['accessBetRule'] = $request->accessBetRule;
            $data['accessBetSetup'] = $request->accessBetSetup;
            $data['accessInvitationSetup'] = $request->accessInvitationSetup;
            $data['accessProviderGameSetup'] = $request->accessProviderGameSetup;
            $data['accessProviderRequestList'] = $request->accessProviderRequestList;
            $data['accessProviderRequestBalance'] = $request->accessProviderRequestBalance;
            $data['accessProviderInfo'] = $request->accessProviderInfo;
            $data['accessNotification'] = $request->accessNotification;
            $data['accessHolidayList'] = $request->accessHolidayList;
            $data['accessMonetaryLog'] = $request->accessMonetaryLog;
            $data['accessActivityLog'] = $request->accessActivityLog;

            if ($request->portalProviderID != "") {
                $portalProviderIDs = $request->portalProviderID;
                $data['portalProviderIDs'] = implode(",", $portalProviderIDs); // Joining all the cells in the array together => 1, 2, 3
            }

            $response = $accessPolicyModel->updateAccessPolicy($request->accessPolicyPID, $data);
            if ($response) {
                return redirect()->back()->with('message', 'Access Policy Update Successfully');
            }
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function deleteAccessPolicy(Request $request)
    {
        try {
            $accessPolicyModel = new AccessPolicy();
            $accessPolicy = $accessPolicyModel->findByAccessPolicyId($request->accessPolicyPID)->delete();
            if ($accessPolicy) {
                return redirect()->back()->with('message', 'Access Policy Deleted Successfully');
            }
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function restoreAccessPolicy(Request $request)
    {
        try {
            if ($request->accessPolicyID != '') {
                $accessPolicy = AccessPolicy::withTrashed()->find($request->accessPolicyID)->restore();
                if ($accessPolicy) {
                    return redirect()->back()->with('message', 'Access Policy Restored Successfully');
                }
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }
}
