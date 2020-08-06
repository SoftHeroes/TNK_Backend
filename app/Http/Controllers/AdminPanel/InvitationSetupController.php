<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\InvitationSetup;
use App\Providers\Admin\AdminProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvitationSetupController extends Controller
{
    public function getInvitationSetup(Request $request)
    {
        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            $adminModel = new Admin();

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session

            //To get the accessibility of the admin policy tab based on the admin id
            $adminInfo = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessInvitationSetup','isAllowAll')->get();
            $accessibility = $adminInfo[0]->accessInvitationSetup;
            $isAllowAll = $adminInfo[0]->isAllowAll;

            if ($authPolicy->access == 1 || $authPolicy->access == 3) {
                if ($request->cookie('includeDeleted')) {
                    $invitationSetupData = InvitationSetup::withTrashed()->get();
                } else {
                    $invitationSetupData = InvitationSetup::get();
                }
                return view('adminPanel.invitationSetup', compact('invitationSetupData', 'accessibility', 'isAllowAll'));
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function createInvitationSetup(Request $request)
    {
        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            if ($authPolicy->access == 1) {
                $rules = array(
                    'name' => 'required'
                );

                $messages = array(
                    'name.required' => 'Name is required.'
                );

                $validator = Validator::make($request->toArray(), $rules, $messages);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator->errors());
                } else {

                    if (!isEmpty($request->maximumRequestInDay) && $request->maximumRequestInDay < 0) {
                        throw new Exception('Maximum Request In Day not be in negative');
                    }
                    if (!isEmpty($request->requestMin) && $request->requestMin < 0) {
                        throw new Exception('request Min not be in negative');
                    }
                    if (!isEmpty($request->maximumRequestInMin) && $request->maximumRequestInMin < 0) {
                        throw new Exception('Maximum Request In Min not be in negative');
                    }

                    $data['name'] = $request->name;
                    if (!isEmpty($request->maximumRequestInDay)) {
                        $data['maximumRequestInDay'] = $request->maximumRequestInDay;
                    }
                    if (!isEmpty($request->requestMin)) {
                        $data['requestMin'] = $request->requestMin;
                    }
                    if (!isEmpty($request->maximumRequestInMin)) {
                        $data['maximumRequestInMin'] = $request->maximumRequestInMin;
                    }

                    if (!isEmpty($data)) {
                        InvitationSetup::create($data);
                        return redirect()->back()->with('message', 'Create Invitation Setup Successfully');
                    } else {
                        return redirect()->back()->withErrors('No input found..!!');
                    }
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

    public function updateInvitationSetup(Request $request)
    {

        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            if ($authPolicy->access == 1) {
                $rules = array(
                    'name' => 'required'
                );

                $messages = array(
                    'name.required' => 'Name is required.'
                );

                $validator = Validator::make($request->toArray(), $rules, $messages);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator->errors());
                } else {
                    if (!isEmpty($request->maximumRequestInDay) && $request->maximumRequestInDay < 0) {
                        throw new Exception('Maximum Request In Day not be in negative');
                    }
                    if (!isEmpty($request->requestMin) && $request->requestMin < 0) {
                        throw new Exception('request Min not be in negative');
                    }
                    if (!isEmpty($request->maximumRequestInMin) && $request->maximumRequestInMin < 0) {
                        throw new Exception('Maximum Request In Min not be in negative');
                    }

                    $data['name'] = $request->name;
                    if (!isEmpty($request->maximumRequestInDay)) {
                        $data['maximumRequestInDay'] = $request->maximumRequestInDay;
                    }
                    if (!isEmpty($request->requestMin)) {
                        $data['requestMin'] = $request->requestMin;
                    }
                    if (!isEmpty($request->maximumRequestInMin)) {
                        $data['maximumRequestInMin'] = $request->maximumRequestInMin;
                    }

                    if (!isEmpty($data)) {
                        InvitationSetup::where('PID',$request->invitationSetupID)->update($data);
                        return redirect()->back()->with('message', 'Update Invitation Setup Successfully');
                    } else {
                        return redirect()->back()->withErrors('No input found..!!');
                    }
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

    public function deleteInvitationSetup(Request $request)
    {

        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            if ($authPolicy->access == 1) {
                $invitationSetup = InvitationSetup::where('PID', $request->invitationSetupID)->delete();
                if ($invitationSetup) {
                    return redirect()->back()->with('message', 'Delete Invitation Setup Successfully');
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

    public function restoreInvitationSetup(Request $request)
    {
        try {
            if ($request->invitationSetupID != '') {
                $invitationSetup = InvitationSetup::withTrashed()->find($request->invitationSetupID)->restore();
                if ($invitationSetup) {
                    return redirect()->back()->with('message', 'Invitation Setup Restored Successfully');
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
