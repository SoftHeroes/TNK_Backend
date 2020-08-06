<?php

namespace App\Http\Controllers\AdminPanel;

use DB;
use App\Http\Controllers\Controller;
use App\Models\AdminPolicy;
use App\Models\Admin;
use App\Models\PortalProvider;
use App\Providers\Admin\AdminProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminPolicyController extends Controller
{
    public function getAdminPolicy(Request $request)
    {
        $portalProviderModel = new PortalProvider();
        $adminModel = new Admin();
        $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');

        $providerData = $portalProviderModel->getPortalProviderByUUID($sessionData['portalProviderUUID']);

        if ($providerData->count(DB::raw('1')) == 0) {
            return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
        } else {
            //To get the accessibility of the admin policy tab based on the admin id
            $adminData = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessAdminPolicy', 'isAllowAll')->get();
            $accessibility = $adminData[0]->accessAdminPolicy;
            $isAllowAll = $adminData[0]->isAllowAll;

            if ($request->cookie('includeDeleted')) {
                $adminPolicy = AdminPolicy::withTrashed()->orderby('createdAt', 'DESC')->get();
            } else {
                $adminPolicy = AdminPolicy::orderby('createdAt', 'DESC')->get();
            }

            $access = ['', 'All', 'app API', 'Admin Panel', 'web Api', 'expose Api'];
            $source = ['', 'All', 'web', 'ios', 'android'];
            foreach ($adminPolicy as $key => $value) {
                $adminPolicyData[] = [
                    'PID' => $value->PID,
                    'name' => $value->name,
                    'userLockTime' => $value->userLockTime,
                    'invalidAttemptsAllowed' => $value->invalidAttemptsAllowed,
                    'otpValidTimeInSeconds' => $value->otpValidTimeInSeconds,
                    'passwordResetTime' => $value->passwordResetTime,
                    'access' => $value->access,
                    'source' => $value->source,
                    'access_name' => $access[$value->access],
                    'source_name' => $source[$value->source],
                    'isActive' => $value->isActive,
                    'createdAt' => date_format($value->createdAt, "Y-m-d H:i:s"),
                    'deletedAt' => ($value->deletedAt == Null) ? "Null" : date_format($value->deletedAt, "Y-m-d H:i:s")
                ];

            }
            $adminPolicyData = json_decode(json_encode($adminPolicyData));
            return view('adminPanel/adminPolicy', compact('adminPolicyData', 'accessibility', 'isAllowAll'));
        }
    }

    public function addAdminPolicy(Request $request)
    {
        $adminPolicyModel = new AdminPolicy();

        $inMaxLength = config('constants.integer_max_length');

        // compulsory parameters check
        $rules = array(
            'name' => 'required|unique:adminPolicy,name,NULL,PID,deletedAt,NULLmax:'.config('constants.string_max_length'),
            'otpValidTimeInSeconds' => "required|max:$inMaxLength",
            'userLockTime' => "nullable|max:$inMaxLength",
            'invalidAttemptsAllowed' => "nullable|max:$inMaxLength",
            'passwordResetTime' => "nullable|max:$inMaxLength",
            'access' => 'required',
            'source' => 'required',
            'isActive' => 'required', 
        );

        $messages = array(
            'name.required' => 'name is required.',
            'name.max' => "name shouldn't greater than". config('constants.string_max_length') ." characters.",
            'otpValidTimeInSeconds.required' => 'otp Valid Time In Seconds is required.',
            'otpValidTimeInSeconds.max' => "otpValidTimeInSeconds is limited by $inMaxLength max",
            'userLockTime.max' => "userLockTime is limited by $inMaxLength max",
            'invalidAttemptsAllowed.max' => "invalidAttemptsAllowed is limited by $inMaxLength max",
            'passwordResetTime.max' => "passwordResetTime is limited by $inMaxLength max",
            'access.required' => 'access is required.',
            'source.required' => 'source is required.',
            'isActive.required' => 'isActive is required.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        try {

            $name = $request->name;
            $otpValidTimeInSeconds = $request->otpValidTimeInSeconds;
            $userLockTime = $request->userLockTime;
            $invalidAttemptsAllowed = $request->invalidAttemptsAllowed;
            $passwordResetTime = $request->passwordResetTime;
            $access = $request->access;
            $source = $request->source;
            $isActive = $request->isActive;

            if ($otpValidTimeInSeconds <= 0) {
                return redirect()->back()->withErrors('OTP valid time should not be in negative');
            }

            if ((!isEmpty($userLockTime)) && $userLockTime < 0) {
                return redirect()->back()->withErrors('user Lock Time should not be in negative');
            }

            if ((!isEmpty($invalidAttemptsAllowed))  && $invalidAttemptsAllowed < 0) {
                return redirect()->back()->withErrors('invalid At tempts Allowed should not be in negative');
            }

            if ((!isEmpty($passwordResetTime))  && $passwordResetTime < 0) {
                return redirect()->back()->withErrors('passwordResetTime should not be in negative');
            }

            $adminProvider = new AdminProvider(null);
            $response = $adminProvider->createAdminPolicy($name, $userLockTime, $invalidAttemptsAllowed, $otpValidTimeInSeconds, $passwordResetTime, $access, $source, $isActive);
            return $response;

        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function updateAdminPolicy(Request $request)
    {

        $adminPolicyPID = $request->adminPolicyPID;

        // compulsory parameters check
        $rules = array(
            'name' => "required|unique:adminPolicy,name,{$adminPolicyPID},PID,deletedAt,NULL",
            'otpValidTimeInSeconds' => 'required',
            'access' => 'required',
            'source' => 'required',
            'isActive' => 'required',
        );

        $messages = array(
            'name.required' => 'name is required.',
            'otpValidTimeInSeconds.required' => 'otp Valid Time In Seconds is required.',
            'access.required' => 'access is required.',
            'source.required' => 'source is required.',
            'isActive.required' => 'isActive is required.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $name = $request->name;
        $otpValidTimeInSeconds = $request->otpValidTimeInSeconds;
        $userLockTime = $request->userLockTime;
        $invalidAttemptsAllowed = $request->invalidAttemptsAllowed;
        $passwordResetTime = $request->passwordResetTime;
        $access = $request->access;
        $source = $request->source;
        $isActive = $request->isActive;


        if ($otpValidTimeInSeconds <= 0) {
            return redirect()->back()->withErrors('OTP valid time should not be in negative');
        }

        if ((!isEmpty($userLockTime)) && $userLockTime < 0) {
            return redirect()->back()->withErrors('user Lock Time should not be in negative');
        }

        if ((!isEmpty($invalidAttemptsAllowed))  && $invalidAttemptsAllowed < 0) {
            return redirect()->back()->withErrors('invalid At tempts Allowed should not be in negative');
        }

        if ((!isEmpty($passwordResetTime))  && $passwordResetTime < 0) {
            return redirect()->back()->withErrors('passwordResetTime should not be in negative');
        }

        $adminProvider = new AdminProvider(null);
        $response = $adminProvider->updateAdminPolicyByPID($adminPolicyPID, $name, $userLockTime, $invalidAttemptsAllowed, $otpValidTimeInSeconds, $passwordResetTime, $access, $source, $isActive);

        return $response;
    }

    public function deleteAdminPolicy(Request $request)
    {
        try {
            $adminPolicy = AdminPolicy::findByPolicyId($request->adminPolicyPID)->delete();
            if ($adminPolicy) {
               return redirect()->back()->with('message', 'Admin Policy Deleted Successfully');
            }
            
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function restoreAdminPolicy(Request $request)
    {
        try {
            if ($request->adminPolicyID != '') {
                $adminPolicy = AdminPolicy::withTrashed()->find($request->adminPolicyID)->restore();
                if ($adminPolicy) {
                    return redirect()->back()->with('message', 'Admin Policy Restored Successfully');
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
