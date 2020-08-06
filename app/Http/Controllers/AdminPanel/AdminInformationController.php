<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\AccessPolicy;
use App\Models\Admin;
use App\Models\AdminPolicy;
use App\Models\PortalProvider;
use App\Providers\Admin\AdminProvider;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class AdminInformationController extends Controller
{
    public function getAdminInformation(Request $request)
    {
        try {
            $portalProviderModel = new PortalProvider();
            $adminModel = new Admin();
            $adminPolicyModel = new AdminPolicy();

            $providerIDs = array();

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
            if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
            } else if ($sessionData['isAllowAll'] == 'false') {
                if (isEmpty($sessionData['portalProviderIDs'])) {
                    throw new ValidationError("You don't have access to any of Portal Provider!!");
                }
                $providerIDs = explode(',', $sessionData['portalProviderIDs']);
            }

            $portalProviderUUID = $sessionData['portalProviderUUID'];

            $providerData = $portalProviderModel->getPortalProviderByUUID($portalProviderUUID);

            if ($providerData->count(DB::raw('1')) == 0) {
                return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
            } else {

                //To get the accessibility of the admin policy tab based on the admin id
                $adminInfo = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessAdminInformation', 'isAllowAll')->get();
                $accessibility = $adminInfo[0]->accessAdminInformation;
                $isAllowAll = $adminInfo[0]->isAllowAll;

                // $authData = AdminProvider::getAuthData($request);

                //get all available active admin policies
                $adminPolicies = $adminPolicyModel->getAllActivePolicies()->select('PID', 'name')->get();

                //get all available active portal providers
                $portalProviders = $portalProviderModel->getPortalProviders()->select('PID', 'name', 'UUID')->get();

                // $totalProviders = explode(',', $authData->providerAccess);

                $selectColumn = [
                    'admin.PID as adminID',
                    'admin.adminPolicyID',
                    'admin.firstName',
                    'admin.lastName',
                    'admin.emailID',
                    'admin.username',
                    'admin.invalidAttemptsCount',
                    DB::raw("CONCAT('" . config("constants.image_path_admin") . "',admin.profileImage) as profileImage"),
                    'admin.lastPasswordResetTime',
                    'admin.isActive',
                    'admin.deletedAt',
                    'portalProvider.UUID as portalProviderUUID',
                    'portalProvider.name as portalProviderName',
                    'admin.accessPolicyID'
                ];

                if ($request->cookie('includeDeleted')) {
                    $adminData[] = $adminModel->getAllAdminByPortalProviderWithTrashed($providerIDs)->withTrashed()->select($selectColumn)->get();
                } else {
                    $adminData[] = $adminModel->getAllAdminByPortalProvider($providerIDs)->select($selectColumn)->get();
                }

                $accessPolicyModel = new AccessPolicy();
                $accessPolicy = $accessPolicyModel->getAllAccessPolicy()->select('accessPolicy.PID as accessPolicyID', 'accessPolicy.name')->get();
                return view('adminPanel.adminInformation', compact('adminData', 'adminPolicies', 'portalProviders', 'accessPolicy', 'accessibility', 'isAllowAll'));
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function updateAdminInformation(Request $request)
    {
        // compulsory parameters check
        $rules = array(
            'adminPolicyID' => 'required',
            'portalProviderID' => 'required',
            'isActive' => 'required',
            'profileImage' => 'mimes:jpeg,jpg,png|max:' . config("app.valid_image_size_in_kilo_bytes") . '|file',
            'accessPolicyID' => 'required'
        );

        $messages = array(
            'adminPolicyID.required' => 'Admin Policy ID is required.',
            'portalProviderID.required' => 'Portal Provider ID is required.',
            'isActive.required' => 'Active Status is required',
            'profileImage.file' => 'version is required for file type.',
            'profileImage.mimes' => 'Only jpeg, jpg, png are allowed.',
            'profileImage.max' => 'Image size should not be greater than ' . config('app.valid_image_size_in_kilo_bytes') . 'KB',
            'accessPolicyID.required' => 'access Policy ID is required'

        );

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        } else {

            try {
                $adminModel = new Admin();

                //check whether admin details is available
                $adminDataProfile = $adminModel->getAdminDetails($request->adminID);

                if ($adminDataProfile->count(DB::raw('1')) != 0) {

                    $data['adminPolicyID'] = $request->adminPolicyID;
                    $data['portalProviderID'] = $request->portalProviderID;
                    $data['isActive'] = $request->isActive;
                    $data['accessPolicyID'] = $request->accessPolicyID;

                    if ($request->firstName != "") {
                        $data['firstName'] = $request->firstName;
                    }

                    if ($request->lastName != "") {
                        $data['lastName'] = $request->lastName;
                    }

                    if (!isEmpty($request->profileImage)) {

                        //save image and generate path
                        $files = $request->file('profileImage');
                        $destinationPath =  config("constants.image_path_admin"); // upload path
                        $profileImage = uniqid() . date('YmdHis') . "." . $files->getClientOriginalExtension();
                        $files->move($destinationPath, $profileImage);
                        //$imagePath = $destinationPath . $profileImage;
                        $data['profileImage'] = $profileImage;

                        if (!isEmpty($adminDataProfile[0]->profileImage)) {
                            //deleting existing profile of user if new one is being uploaded
                            File::delete($adminDataProfile[0]->profileImage);
                        }
                    }
                    if (!isEmpty($data)) {
                        $adminModel->updateAdmin($request->adminID, $data);
                        return redirect()->back()->with('message', 'Update Profile Successfully');
                    } else {
                        return redirect()->back()->withErrors('No input found..!!');
                    }
                } else {
                    return redirect()->back()->withErrors('Admin Information not found');
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

    public function addAdminInformation(Request $request)
    {
        // compulsory parameters check
        $rules = array(
            'emailID' => 'required|max:'.config('constants.string_max_length'),
            'adminPolicyID' => 'required',
            'portalProviderID' => 'required',
            'isActive' => 'required',
            'profileImage' => 'mimes:jpeg,jpg,png|max:' . config("app.valid_image_size_in_kilo_bytes") . '|file',
            'username' => 'required|unique:admin,username,NULL,PID,deletedAt,NULL|max:'.config('constants.string_max_length').' characters',
            'password' => 'required|max:'.config('constants.string_max_length').'|min:6',
            'confirm_password' => 'required|max:'.config('constants.string_max_length').'|min:6',
            'accessPolicyID' => 'required'
        );

        $messages = array(

            'emailID.required' => 'Email is required.',
            'emailID.max' => "Email shouldn't greater than ".config('constants.string_max_length').' characters.',
            'adminPolicyID.required' => 'Admin Policy ID is required.',
            'portalProviderID.required' => 'Portal Provider ID is required.',
            'isActive.required' => 'Active Status is required',
            'profileImage.file' => 'version is required for file type.',
            'profileImage.mimes' => 'Only jpeg, jpg, png are allowed.',
            'profileImage.max' => 'Image size should not be greater than ' . config('app.valid_image_size_in_kilo_bytes') . 'KB',
            'username.required' => 'username is required.',
            'password.required' => 'password is required.',
            'confirm_password.required' => 'confirm password is required.',
            'accessPolicyID.required' => 'access Policy ID is required',
            'username.max' => "username shouldn't greater than ".config('constants.string_max_length').' characters.',
            'password.max' => "password shouldn't greater than ".config('constants.string_max_length').' characters.',
            'confirm_password.max' => "confirm_password shouldn't greater than ".config('constants.string_max_length').' characters.',
        );
        $adminPolicyModel = new AdminPolicy();

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        } else {

            try {
                $adminModel = new Admin();
                // check emailID
                $adminEmailID = $adminModel->where('emailID', $request->emailID)->get();
                if ($adminEmailID->count(DB::raw('1')) != 0) {
                    return redirect()->back()->withErrors(config('Email address is already available'));
                } else if ($request->password != $request->confirm_password) {
                    return redirect()->back()->withErrors(['The password entered is not match !!']);
                } else {
                    $data['adminPolicyID'] = $request->adminPolicyID;
                    $data['portalProviderID'] = $request->portalProviderID;
                    $data['emailID'] = $request->emailID;
                    $data['isActive'] = $request->isActive;
                    $data['accessPolicyID'] = $request->accessPolicyID;
                    $data['username'] = $request->username;
                    $data['password'] = Crypt::encrypt($request->password);

                    if ($request->firstName != "") {
                        $data['firstName'] = $request->firstName;
                    }

                    if ($request->lastName != "") {
                        $data['lastName'] = $request->lastName;
                    }

                    if (!isEmpty($request->profileImage)) {
                        //save image and generate path
                        $files = $request->file('profileImage');
                        $destinationPath = config("constants.image_path_admin"); // upload path
                        $profileImage = uniqid() . date('YmdHis') . "." . $files->getClientOriginalExtension();
                        $files->move($destinationPath, $profileImage);
                        //$imagePath = $destinationPath . $profileImage;
                        $data['profileImage'] = $profileImage;
                    }

                    if (!isEmpty($data)) {
                        $adminModel->create($data);
                        return redirect()->back()->with('message', 'Create Profile Successfully');
                    } else {
                        return redirect()->back()->withErrors('No input found..!!');
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

    public function deleteAdminInformation(Request $request)
    {
        try {
            $admin = Admin::where('PID', $request->adminID)->delete();
            if ($admin) {
                return redirect()->back()->with('message', 'Delete Admin Successfully');
            }
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function changePasswordAdminInformation(Request $request)
    {
        // compulsory parameters check
        $rules = array(
            'newpassword' => 'required',
            'newconfirm_password' => 'required'
        );

        $messages = array(
            'newpassword.required' => 'new password is required.',
            'newconfirm_password.required' => 'new confirm password is required.'
        );

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        } else {
            try {
                $adminModel = new Admin();
                //check whether admin details is available
                $adminDataProfile = $adminModel->getAdminDetails($request->adminID);

                if ($adminDataProfile->count(DB::raw('1')) != 0) {
                    if ($request->newpassword != $request->newconfirm_password) {
                        return redirect()->back()->withErrors(['The new password entered is not match !!']);
                    } else {
                        $currentDateTime = microtimeToDateTime(getCurrentTimeStamp());
                        $data['password'] = Crypt::encrypt($request->newpassword);
                        $data['lastPasswordResetTime'] = $currentDateTime;

                        if (!isEmpty($data)) {
                            $adminModel->updateAdmin($request->adminID, $data);
                            return redirect()->back()->with('message', 'Update Profile Successfully');
                        }
                    }
                } else {
                    return redirect()->back()->withErrors('Admin Information not found');
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

    public function restoreAdminInformation(Request $request)
    {
        try {
            if ($request->adminID != '') {
                $adminData = Admin::withTrashed()->find($request->adminID)->restore();
                if ($adminData) {
                    return redirect()->back()->with('message', 'Admin Information Restored Successfully');
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
