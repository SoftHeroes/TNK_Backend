<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\FollowBetRule;
use App\Models\FollowBetSetup;
use App\Models\Admin;
use App\Providers\Admin\AdminProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FollowConfigController extends Controller
{
    public function getFollowBetRule(Request $request)
    {
        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            $adminModel = new Admin();

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
            
            if ($authPolicy->access == 1 || $authPolicy->access == 3) {
                $followBetRuleModel = new FollowBetRule();

                if ($request->cookie('includeDeleted')) {
                    $followBetRuleData = $followBetRuleModel->withTrashed()->get();
                } else {
                    $followBetRuleData = $followBetRuleModel->get();
                }

                //To get the accessibility of the admin policy tab based on the admin id
                $adminInfo = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessBetRule','isAllowAll')->get();
                $accessibility = $adminInfo[0]->accessBetRule;
                $isAllowAll = $adminInfo[0]->isAllowAll;

                return view('adminPanel/followBetRule', compact('followBetRuleData','accessibility', 'isAllowAll'));
            } else {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function createFollowBetRule(Request $request)
    {
        try {
            $followBetRuleModel = new FollowBetRule();

            $rules = array(
                'name' => 'required|max:'.config('constants.string_max_length'),
                'type' => 'required',
                'isActive' => 'required',
                'min' => 'required|max:'.config('constants.integer_max_length'),
                'max' => 'required|max:'.config('constants.integer_max_length'),
            );

            $messages = array(
                'name.required' => 'Name is required.',
                'name.max'=>"name shouldn't greater than ".config('constants.string_max_length').' characters' ,
                'type.required' => 'Type is required.',
                'isActive.required' => 'Status Active is required.',
                'min.required' => 'Min is required.',
                'min.max' => "Min shouldn't be bigger than ".config('constants.integer_max_length').' max',
                'max.required' => 'Max is required.',
                'max.max' => "Max shouldn't be bigger than ".config('constants.integer_max_length').' max',
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {

                $data['name'] = $request->name;
                $data['type'] = $request->type;

                if (!isEmpty($request->rule)) {
                    $data['rule'] = $request->rule;
                }

                $data['isActive'] = $request->isActive;
                $data['min'] = $request->min;
                $data['max'] = $request->max;

                if (!isEmpty($data)) {
                    $followBetRuleModel->create($data);
                    return redirect()->back()->with('message', 'Create Follow Bet Rule Successfully');
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

    public function updateFollowBetRule(Request $request)
    {
        try {
            $followBetRuleModel = new FollowBetRule();
            $followBetRuleID = $request->followBetRuleID;

            $rules = array(
                'name' => "required",
                'type' => 'required',
                'isActive' => 'required',
                'min' => 'required',
                'max' => 'required',
            );

            $messages = array(
                'name.required' => 'Name is required.',
                'type.required' => 'Type is required.',
                'isActive.required' => 'Status Active is required.',
                'min.required' => 'Min is required.',
                'max.required' => 'Max is required.',
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {

                $data['name'] = $request->name;
                $data['type'] = $request->type;

                if (!isEmpty($request->rule)) {
                    $data['rule'] = $request->rule;
                }

                $data['isActive'] = $request->isActive;
                $data['min'] = $request->min;
                $data['max'] = $request->max;

                if (!isEmpty($data)) {
                    $followBetRuleModel->updateFollowBetRule($followBetRuleID, $data);
                    return redirect()->back()->with('message', 'Create Follow Bet Rule Successfully');
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

    public function deleteFollowBetRule(Request $request)
    {
        try {
            $followBetRule = FollowBetRule::where('PID', $request->followBetRuleID)->delete();
            if ($followBetRule) {
                return redirect()->back()->with('message', 'Delete Follow Bet Rule Successfully');
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function restoreFollowBetRule(Request $request)
    {
        try {
            if ($request->followBetRuleID != '') {
                $followBetRuleData = FollowBetRule::withTrashed()->find($request->followBetRuleID)->restore();
                if ($followBetRuleData) {
                    return redirect()->back()->with('message', 'Follow Bet Rule Restored Successfully');
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

    public function getFollowBetSetup(Request $request)
    {
        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            $adminModel = new Admin();

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session

            if ($authPolicy->access == 1 || $authPolicy->access == 3) {
                $followBetSetupModel = new FollowBetSetup();

                if ($request->cookie('includeDeleted')) {
                    $followBetSetupData = $followBetSetupModel->withTrashed()->get();
                } else {
                    $followBetSetupData = $followBetSetupModel->get();
                }

                $followBetRuleModel = new FollowBetRule();
                $followBetRuleData = $followBetRuleModel->get();

                //To get the accessibility of the admin policy tab based on the admin id
                $adminInfo = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessBetSetup','isAllowAll')->get();
                $accessibility = $adminInfo[0]->accessBetSetup;
                $isAllowAll = $adminInfo[0]->isAllowAll;

                return view('adminPanel/followBetSetup', compact('followBetSetupData', 'followBetRuleData', 'accessibility', 'isAllowAll'));
            } else {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function createFollowBetSetup(Request $request)
    {
        try {
            $followBetSetupModel = new FollowBetSetup();

            $intMaxLength = config('constants.integer_max_length');

            $rules = array(
                'isActive' => 'required',
                'minFollowBetRuleSelect' => 'nullable|max:'.$intMaxLength,
                'maxFollowBetRuleSelect' => 'nullable|max:'.$intMaxLength,
                'minUnFollowBetRuleSelect' => 'nullable|max:'.$intMaxLength,
                'maxUnFollowBetRuleSelect' => 'nullable|max:'.$intMaxLength,
            );

            $messages = array(
                'isActive.required' => 'Status Active is required.',
                'minFollowBetRuleSelect.max' => 'minFollowBetRuleSelect is limit by '.$intMaxLength.' max',
                'maxFollowBetRuleSelect.max' => 'maxFollowBetRuleSelect is limit by '.$intMaxLength.' max',
                'minUnFollowBetRuleSelect.max' => 'minUnFollowBetRuleSelect is limit by '.$intMaxLength.' max',
                'maxUnFollowBetRuleSelect.max' => 'maxUnFollowBetRuleSelect is limit by '.$intMaxLength.' max',
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {

                if (!isEmpty($request->followBetRuleID)) {
                    $data['followBetRuleID'] = implode(",", $request->followBetRuleID);
                }
                if (!isEmpty($request->unFollowBetRuleID)) {
                    $data['unFollowBetRuleID'] = implode(",", $request->unFollowBetRuleID);
                }

                $minFollowBetRuleSelect = $request->minFollowBetRuleSelect;
                $maxFollowBetRuleSelect = $request->maxFollowBetRuleSelect;
                $minUnFollowBetRuleSelect = $request->minUnFollowBetRuleSelect;
                $maxUnFollowBetRuleSelect = $request->maxUnFollowBetRuleSelect;

                if ((!isEmpty($minFollowBetRuleSelect)) && $minFollowBetRuleSelect < 0) {
                    return redirect()->back()->withErrors('Min Follow Bet Rule Select should not be in negative');
                }
                if ((!isEmpty($maxFollowBetRuleSelect)) && $maxFollowBetRuleSelect < 0) {
                    return redirect()->back()->withErrors('Max Follow Bet Rule Select should not be in negative');
                }
                if ((!isEmpty($minUnFollowBetRuleSelect)) && $minUnFollowBetRuleSelect < 0) {
                    return redirect()->back()->withErrors('Min UnFollow Bet Rule Select should not be in negative');
                }
                if ((!isEmpty($maxUnFollowBetRuleSelect)) && $maxUnFollowBetRuleSelect < 0) {
                    return redirect()->back()->withErrors('Max UnFollow Bet Rule Select should not be in negative');
                }

                if (!isEmpty($minFollowBetRuleSelect)) {
                    $data['minFollowBetRuleSelect'] = $minFollowBetRuleSelect;
                }

                if (!isEmpty($maxFollowBetRuleSelect)) {
                    $data['maxFollowBetRuleSelect'] = $maxFollowBetRuleSelect;
                }

                if (!isEmpty($minUnFollowBetRuleSelect)) {
                    $data['minUnFollowBetRuleSelect'] = $minUnFollowBetRuleSelect;
                }

                if (!isEmpty($maxUnFollowBetRuleSelect)) {
                    $data['maxUnFollowBetRuleSelect'] = $maxUnFollowBetRuleSelect;
                }

                $data['isActive'] = $request->isActive;

                if (!isEmpty($data)) {
                    $followBetSetupModel->create($data);
                    return redirect()->back()->with('message', 'Create Follow Bet Setup Successfully');
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

    public function updateFollowBetSetup(Request $request)
    {
        try {
            $followBetSetupModel = new FollowBetSetup();
            $followBetSetupID = $request->followBetSetupID;

            $rules = array(
                'isActive' => 'required',
            );

            $messages = array(
                'isActive.required' => 'Status Active is required.',
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {

                if (!isEmpty($request->followBetRuleID)) {
                    $data['followBetRuleID'] = implode(",", $request->followBetRuleID);
                }
                if (!isEmpty($request->unFollowBetRuleID)) {
                    $data['unFollowBetRuleID'] = implode(",", $request->unFollowBetRuleID);
                }
                $minFollowBetRuleSelect = $request->minFollowBetRuleSelect;
                $maxFollowBetRuleSelect = $request->maxFollowBetRuleSelect;
                $minUnFollowBetRuleSelect = $request->minUnFollowBetRuleSelect;
                $maxUnFollowBetRuleSelect = $request->maxUnFollowBetRuleSelect;

                if ((!isEmpty($minFollowBetRuleSelect)) && $minFollowBetRuleSelect < 0) {
                    return redirect()->back()->withErrors('Min Follow Bet Rule Select should not be in negative');
                }
                if ((!isEmpty($maxFollowBetRuleSelect)) && $maxFollowBetRuleSelect < 0) {
                    return redirect()->back()->withErrors('Max Follow Bet Rule Select should not be in negative');
                }
                if ((!isEmpty($minUnFollowBetRuleSelect)) && $minUnFollowBetRuleSelect < 0) {
                    return redirect()->back()->withErrors('Min UnFollow Bet Rule Select should not be in negative');
                }
                if ((!isEmpty($maxUnFollowBetRuleSelect)) && $maxUnFollowBetRuleSelect < 0) {
                    return redirect()->back()->withErrors('Max UnFollow Bet Rule Select should not be in negative');
                }

                if (!isEmpty($minFollowBetRuleSelect)) {
                    $data['minFollowBetRuleSelect'] = $minFollowBetRuleSelect;
                }

                if (!isEmpty($maxFollowBetRuleSelect)) {
                    $data['maxFollowBetRuleSelect'] = $maxFollowBetRuleSelect;
                }

                if (!isEmpty($minUnFollowBetRuleSelect)) {
                    $data['minUnFollowBetRuleSelect'] = $minUnFollowBetRuleSelect;
                }

                if (!isEmpty($maxUnFollowBetRuleSelect)) {
                    $data['maxUnFollowBetRuleSelect'] = $maxUnFollowBetRuleSelect;
                }

                $data['isActive'] = $request->isActive;

                if (!isEmpty($data)) {
                    $followBetSetupModel->updateFollowBetSetups($followBetSetupID, $data);
                    return redirect()->back()->with('message', 'Create Follow Bet Setup Successfully');
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

    public function deleteFollowBetSetup(Request $request)
    {
        try {
            $followBetSetup = FollowBetSetup::where('PID', $request->followBetSetupID)->delete();
            if ($followBetSetup) {
                return redirect()->back()->with('message', 'Delete Follow Bet Setup Successfully');
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function restoreFollowBetSetup(Request $request)
    {
        try {
            if ($request->followBetSetupID != '') {
                $followBetSetupData = FollowBetSetup::withTrashed()->find($request->followBetSetupID)->restore();
                if ($followBetSetupData) {
                    return redirect()->back()->with('message', 'Follow Bet Setup Restored Successfully');
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
