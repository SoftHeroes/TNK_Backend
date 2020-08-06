<?php

namespace App\Http\Controllers\AdminPanel;

use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Jobs\MailJob;
use DB;
use App\Models\OtpCheck;
use Log;
use App\Models\Admin;
use File;
use Illuminate\Support\Facades\Validator;
use App\Models\PendingSessionUpdate;
use App\Http\Controllers\ResponseController as Res;
use App\Providers\Admin\AdminProvider;


require_once app_path() . '/Helpers/CommonUtility.php';

class AdminController extends Controller
{
    public function adminLogin(Request $request)
    {
        try {
            $userName = $request->input("username");
            $password = $request->input("password");

            $adminModel = new Admin;
            $adminProvider = new AdminProvider(null);

            if (!isEmpty($userName) && !isEmpty($password)) {
                $adminData = $adminModel->fetchByUsername($userName);

                if ($adminData->count(DB::raw('1')) > 0) {
                    //check number of invalid attempts and block for sometime
                    $isBlock = $adminProvider->markLoginFail($adminData[0]['PID'], FALSE);
                    if ($isBlock) {
                        return redirect()->back()->withErrors(['Too many invalid attempts, please try after sometime']);
                    }
                    //verifying the password
                    if ($password == Crypt::decrypt($adminData[0]['password'])) {
                        //update invalid attempt count = 0 after successful login
                        $adminModel->updateAdmin($adminData[0]['PID'], ['invalidAttemptsCount' => 0, 'blockTime' => null]);

                        //verifying the access rights //allowing only if admin has right to access to Admin panel (1=both, 3=Admin panel)
                        if ($adminData[0]['access'] == 1 || $adminData[0]['access'] == 3) {
                            //creating session
                            $request->session()->put([str_replace(".", "_", $request->ip()) . 'ECGames' => [
                                'adminPID' => $adminData[0]->PID,
                                'adminName' => $adminData[0]->firstName . ' ' . $adminData[0]->lastName,
                                'portalProviderUUID' => $adminData[0]->portalProviderUUID,
                                'profileImage' => $adminData[0]->profileImage,
                                'portalProviderID' => $adminData[0]->portalProviderID,
                                'isAllowAll' => $adminData[0]->isAllowAll,
                                'portalProviderIDs' => $adminData[0]->portalProviderIDs,
                                'accessAdminPolicy' => $adminData[0]->accessAdminPolicy,
                                'accessAccessPolicy' => $adminData[0]->accessAccessPolicy,
                                'accessAdminInformation' => $adminData[0]->accessAdminInformation,
                                'accessProviderList' => $adminData[0]->accessProviderList,
                                'accessProviderGameSetup' => $adminData[0]->accessProviderGameSetup,
                                'accessProviderRequestList' => $adminData[0]->accessProviderRequestList,
                                'accessProviderRequestBalance' => $adminData[0]->accessProviderRequestBalance,
                                'accessProviderInfo' => $adminData[0]->accessProviderInfo,
                                'accessProviderConfig' => $adminData[0]->accessProviderConfig,
                                'accessCurrency' => $adminData[0]->accessCurrency,
                                'accessBetRule' => $adminData[0]->accessBetRule,
                                'accessBetSetup' => $adminData[0]->accessBetSetup,
                                'accessNotification' => $adminData[0]->accessNotification,
                                'accessHolidayList' => $adminData[0]->accessHolidayList,
                                'accessMonetaryLog' => $adminData[0]->accessMonetaryLog,
                                'accessActivityLog' => $adminData[0]->accessActivityLog,
                                'accessInvitationSetup' => $adminData[0]->accessInvitationSetup,
                                'selectedPortalProviderIDs' => null,
                                'includeDeleted' => null
                            ]]); // creating login session

                            //redirect to dashboard.
                            return redirect()->route('vDashboard')->withCookie(Cookie::forget('selectedPortalProviderIDs'));
                        } else {
                            return redirect()->back()->withErrors(['AdminPanel Access is not allowed.']);
                        }
                    } else {

                        //add invalid attempt count
                        $isBlock = $adminProvider->markLoginFail($adminData[0]['PID'], TRUE);
                        return redirect()->back()->withErrors(['Invalid Credentials']);
                    }
                } else {
                    return redirect()->back()->withErrors(['Invalid Credentials']);
                }
            }
            return redirect()->back()->withErrors(['Username and password cannot be blank']);
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
            //log error message $e->getMessage();
        }
    }

    public function saveSelectPortalProvider(Request $request)
    {
        try {
            $pendingSessionUpdateModelRef = new PendingSessionUpdate();

            $pendingSessionUpdateModelRef->insert([
                'ip' => request()->ip(),
                'tag' => 'selectedPortalProviderIDs',
                'value' => isEmpty($request->PortalProviderIDs) ? null : implode(',', $request->PortalProviderIDs)
            ]);

            return ['status' => true, "message" => 'Selection added Successfully!'];
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return ['status' => true, "message" => config('constants.default_error_response')];
            } else {
                return ['status' => true, "message" => $e->getMessage()];
            }
        }
    }

    public function saveIncludeDeletedSession()
    {
        try {
            $pendingSessionUpdateModelRef = new PendingSessionUpdate();

            $pendingSessionUpdateModelRef->insert([
                'ip' => request()->ip(),
                'tag' => 'includeDeleted',
                'value' => true
            ]);

            return redirect()->back()->withCookies($cookies)->with(array('message' => 'Select successfully!'));
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return ['status' => true, "message" => config('constants.default_error_response')];
            } else {
                return ['status' => true, "message" => $e->getMessage()];
            }
        }
    }

    public function removeIncludeDeletedSession()
    {
        try {
            $pendingSessionUpdateModelRef = new PendingSessionUpdate();

            $pendingSessionUpdateModelRef->insert([
                'ip' => request()->ip(),
                'tag' => 'includeDeleted',
                'value' => false
            ]);

            return redirect()->back()->withCookies($cookies)->with(array('message' => 'Select successfully!'));
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return ['status' => true, "message" => config('constants.default_error_response')];
            } else {
                return ['status' => true, "message" => $e->getMessage()];
            }
        }
    }

    public function updateSession(Request $request)
    {
        $pendingSessionUpdateModelRef = new PendingSessionUpdate();

        $pendingSessionData = $pendingSessionUpdateModelRef->getPendingDetailsByIp(request()->ip())->get();

        $cookies = array();

        if ($pendingSessionData->count(DB::raw('1')) != 0) {

            $cookies = array();
            foreach ($pendingSessionData as $eachValue) {
                array_push($cookies, Cookie::forever($eachValue->tag, $eachValue->value));
                $eachValue->delete();
            }
        }

        return redirect()->back()->withCookies($cookies)->with(array('message' => 'Select successfully!'));
    }


    public function forgetPassword(Request $request)
    {

        $admin = new Admin;
        $toEmailID = $request->input("email");
        $currentDateTime = microtimeToDateTime(getCurrentTimeStamp());

        $adminData = $admin->fetchByEmailId($toEmailID);

        if ($adminData->count(DB::raw('1')) > 0) {
            //otp valid time should be based on the admin's policy
            $otpValidTime = $adminData[0]['otpValidTimeInSeconds'];

            $otpValidDateTime = date('Y-m-d H:i:s', strtotime('+' . $otpValidTime . ' seconds', strtotime($currentDateTime)));

            try {
                if (!isEmpty($toEmailID)) {

                    $otp = rand(1000, 9999);

                    OtpCheck::insert([
                        'adminPID' => $adminData[0]['PID'],
                        'portalProviderID' => $adminData[0]['portalProviderID'],
                        'emailID' => $toEmailID,
                        'otp' => $otp,
                        'createdAt' => $currentDateTime,
                        'validTill' => $otpValidDateTime
                    ]);

                    $msg = "Your Password reset verification code is: " . $otp;

                    $subject = 'Forgot password verification code';

                    try {
                        MailJob::dispatch($toEmailID, $msg, $subject)->onQueue('medium');
                    } catch (\Exception $exception) {
                        Log::error($exception);
                        return redirect()->back()->withErrors(["Error sending in email. Check the logs for more information!!"]);
                    }

                    // redirect them to the password reset page.
                    return view('adminPanel/passwordReset')->with(array('otpVerified' => 'false'));
                }
            } catch (Exception $e) {
                if (IsAuthEnv()) {
                    return redirect()->back()->withErrors(config('constants.default_error_response'));
                } else {
                    return redirect()->back()->withErrors($e->getMessage());
                }
            }
        } else {
            // if the email is not found in the admin table, then redirect them to the same page with error
            return redirect()->back()->withErrors(['EmailID cannot be found']);
        }
    }

    public function otpCheck(Request $request)
    {

        $otpValue = $request->input("otp");

        $otpCheck = new OtpCheck;

        try {
            $otpValid = $otpCheck->checkOtpValid($otpValue)->select('emailID')->get();

            if ($otpValid->count(DB::raw('1')) > 0) {
                return view('adminPanel/passwordReset')->with(array('otpVerified' => 'true', 'passwordResetEmailID' => $otpValid[0]->emailID));
            } else {
                return view('adminPanel/passwordReset')->with(array('otpVerified' => 'false', 'errorMessage' => 'OTP is invalid or expired'));
            }
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function resetPassword(Request $request)
    {

        $email = $request->input("email");
        $newPassword = Crypt::encrypt($request->input("new_password"));
        $currentDateTime = microtimeToDateTime(getCurrentTimeStamp());

        if ($email != "" && $newPassword != "") {
            try {
                Admin::where('emailID', '=', $email)
                    ->update([
                        'password' => $newPassword,
                        'lastPasswordResetTime' => $currentDateTime,
                        'invalidAttemptsCount' => 0,
                        'blockTime' => null
                    ]);

                return redirect()->route('vLogin')->with(array('message' => 'Password Changed Successfully'));
            } catch (Exception $e) {
                if (IsAuthEnv()) {
                    return redirect()->back()->withErrors(config('constants.default_error_response'));
                } else {
                    return redirect()->back()->withErrors($e->getMessage());
                }
            }
        }
    }

    public function changePassword(Request $request)
    {
        $adminModel = new Admin();
        $current_password = $request->input("current_password");
        $new_password = $request->input("new_password");
        $new_confirm_password = $request->input("new_confirm_password");

        // new_confirm_password
        $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');

        if (!isEmpty($sessionData) && !isEmpty($sessionData['adminPID'])) {
            $adminData = Admin::select('password')->where('PID', $sessionData['adminPID'])->where('isActive', 'active')->get();
            $PasswordCheck = Crypt::decrypt($adminData[0]->password);

            $currentDateTime = microtimeToDateTime(getCurrentTimeStamp());

            if ($PasswordCheck != $current_password) {
                return redirect()->back()->withErrors(['The old password entered is not correct !!']);
            } else if ($new_password != $new_confirm_password) {
                return redirect()->back()->withErrors(['The new password entered is not match !!']);
            } else {
                $data = array();
                $data['password'] = Crypt::encrypt($new_password);
                $data['lastPasswordResetTime'] = $currentDateTime;
                $adminModel->updateAdmin($sessionData['adminPID'], $data);
                return redirect()->back()->with('message', 'Password Changed Successfully');
            }
        }
    }

    public function getProfile(Request $request)
    {
        $adminModel = new Admin();
        $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');
        if (!isEmpty($sessionData) && !isEmpty($sessionData['adminPID'])) {
            $adminDataProfile = $adminModel->getAdminDetails($sessionData['adminPID']);
            if (!isEmpty($request->ajaxData)) {
                return Res::success($adminDataProfile);
            } else {
                return view('adminPanel/profile', compact('adminDataProfile'));
            }
        }
    }

    public function updateProfile(Request $request)
    {
        $adminModel = new Admin();

        $firstName = $request->input("firstName");
        $lastName = $request->input("lastName");
        $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');
        if (!isEmpty($sessionData) && !isEmpty($sessionData['adminPID'])) {

            //check if the value is changed
            $adminDataProfile = $adminModel->getAdminDetails($sessionData['adminPID']);
            if ($adminDataProfile->count(DB::raw('1')) != 0) {

                $updateData = array();

                if ($firstName !== $adminDataProfile[0]->firstName) {
                    if (!isEmpty($firstName)) {
                        $updateData['firstName'] =  $firstName;
                    }
                }

                if ($lastName !== $adminDataProfile[0]->lastName) {
                    if (!isEmpty($lastName)) {
                        $updateData['lastName'] =  $lastName;
                    }
                }

                $rules = array(
                    'profileImage' => 'required|mimes:jpeg,jpg,png|max:'.config('app.valid_image_size_in_kilo_bytes'),
                );

                $messages = array(
                    'required' => 'The :attribute field is required.',
                    'mimes' => 'Only jpeg, jpg, png are allowed.',
                    'max' => 'profileImage size is bigger than '.config('app.valid_image_size_in_kilo_bytes'),
                );

                // Now save your file to the storage and file details at database.

                $validator = Validator::make($request->toArray(), $rules, $messages);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator->errors());
                }

                if (!isEmpty($request->profileImage)) {

                    //save image and generate path
                    $files = $request->file('profileImage');
                    $destinationPath = config("constants.image_path_admin"); // upload path
                    $profileImage = uniqid() . date('YmdHis') . "." . $files->getClientOriginalExtension();
                    $files->move($destinationPath, $profileImage);
                    //$imagePath = $destinationPath . $profileImage;
                    $updateData['profileImage'] =  $profileImage;

                    if (!isEmpty($adminDataProfile[0]->profileImage)) {
                        //deleting existing profile of user if new one is being uploaded
                        File::delete($adminDataProfile[0]->profileImage);
                    }
                }

                if (!isEmpty($updateData)) {
                    $adminModel->updateAdmin($sessionData['adminPID'], $updateData);
                    return redirect()->back()->with('message', 'Update Profile Successfully');
                } else {
                    return redirect()->back()->withErrors('No input found..!!');
                }
            } else {
                return redirect()->back()->withErrors([config('constants.default_error_response')]);
            }
        }
    }

    public function adminLogout(Request $request)
    {
        try {

            // destroying admin session
            $request->session()->flush();

            //redirect to login page
            return redirect()->route('vLogin');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['Something went wrong.']);
            //log error message $e->getMessage();
        }
    }
}
