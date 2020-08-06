<?php

namespace App\Providers\Admin;

use App\Models\Admin;
use App\Models\AdminPolicy;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AdminProvider extends ServiceProvider
{
    public static function getAuthAdminPolicy(Request $request)
    {
        $adminData = AdminProvider::getAuthData($request);
        $adminPolicy = AdminPolicy::findByPolicyId($adminData->adminPolicyID)->first();
        return $adminPolicy;
    }

    public static function getAuthData(Request $request)
    {
        $admin = new Admin();
        $auth = $request->adminData;
        $adminData = $admin->getAdminDetails($auth["adminPID"])->first();
        return $adminData;
    }

    public static function createAdminPolicy($name, $userLockTime, $invalidAttemptsAllowed, $otpValidTimeInSeconds, $passwordResetTime, $access, $source, $isActive)
    {
        try {

            $data['name'] = $name;
            $data['otpValidTimeInSeconds'] = $otpValidTimeInSeconds;
            $data['userLockTime'] = $userLockTime;
            $data['invalidAttemptsAllowed'] = $invalidAttemptsAllowed;
            $data['passwordResetTime'] = $passwordResetTime;
            $data['access'] = $access;
            $data['source'] = $source;
            $data['isActive'] = $isActive;

            $response = AdminPolicy::create($data);
            if ($response) {
                return redirect()->back()->with('message', 'Admin Policy Created Successfully');
            }
        } catch (Exception $e) {
            return redirect()->back()->withErrors(config('constants.default_error_response'));
        }
    }

    public static function updateAdminPolicyByPID($adminPolicyPID, $name, $userLockTime, $invalidAttemptsAllowed, $otpValidTimeInSeconds, $passwordResetTime, $access, $source, $isActive)
    {
        try {

            $data['name'] = $name;
            $data['otpValidTimeInSeconds'] = $otpValidTimeInSeconds;
            $data['userLockTime'] = $userLockTime;
            $data['invalidAttemptsAllowed'] = $invalidAttemptsAllowed;
            $data['passwordResetTime'] = $passwordResetTime;
            $data['access'] = $access;
            $data['source'] = $source;
            $data['isActive'] = $isActive;

            $adminPolicy = new AdminPolicy();
            $response = $adminPolicy->updateAdminPolicy($adminPolicyPID, $data);

            if ($response) {
                return redirect()->back()->with('message', 'Admin Policy Updated Successfully');
            }
        } catch (Exception $e) {
            return redirect()->back()->withErrors(config('constants.default_error_response'));
        }
    }

    public function markLoginFail($adminID, $incAttempt = FALSE)
    {
        try {

            //add invalid attempt count
            if ($incAttempt) {
                $admin = Admin::find($adminID);
                $admin->increment('invalidAttemptsCount', 1);
                return TRUE;
            } else {
                $adminPolicyModel = new AdminPolicy();
                $adminModel = new Admin;
                $block = FALSE;

                //check if admin is block or not
                //check the max attempt form respective admin policy
                $response = $adminPolicyModel->getAdminPolicyByAdminPID($adminID)
                    ->select('admin.blockTime','adminPolicy.userLockTime', 'admin.invalidAttemptsCount', 'adminPolicy.invalidAttemptsAllowed')
                    ->get();

                $blockTime = $response[0]->blockTime;
                if (isEmpty($blockTime)) {
                    //admin is not blocked

                    $invalidAttemptsCount = $response[0]->invalidAttemptsCount;
                    $invalidAttemptsAllowed = $response[0]->invalidAttemptsAllowed;

                    //if invalid count is greater than
                    if (!isEmpty($invalidAttemptsAllowed) && $invalidAttemptsCount >= $invalidAttemptsAllowed) {

                        $block = TRUE;
                        $endTime = microtimeToDateTime(getCurrentTimeStamp(), false);
                        $adminModel->updateAdmin($adminID, ['blockTime' => $endTime]);
                    }
                } else {
                    //admin is blocked
                    $userLockTime = $response[0]->userLockTime;
                    $intervalDiff = timeDiffBetweenTwoDateTimeObjects($blockTime);

                    $min = $intervalDiff->d * 24;
                    $min = ($min + $intervalDiff->h) * 60;
                    $min = $min + $intervalDiff->i;

                    if (!isEmpty($userLockTime)) {
                        if ($min >= $userLockTime) {
                            $block = FALSE;
                            $adminModel->updateAdmin($adminID, ['invalidAttemptsCount' => 0, 'blockTime' => null]);
                        } else {
                            $block = TRUE;
                        }
                    }
                }

                return $block;
            }
        } catch (Exception $e) {
            return redirect()->back()->withErrors(config('constants.default_error_response'));
        }
    }
}
