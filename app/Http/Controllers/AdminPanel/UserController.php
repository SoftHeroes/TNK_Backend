<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseController as Res;
use App\Models\Betting;
use App\Models\Country;
use App\Models\PortalProvider;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\UserSession;
use App\Providers\Admin\AdminProvider;
use App\Providers\Users\UserProvider;
use Exception;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use App\Exceptions\ValidationError;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    public function getUserDetails(Request $request)
    {
        try {

            $userModel = new User();
            $userSessionModel = new UserSession();
            $providerIDs = array();
            $currentTime = microtimeToDateTime(getCurrentTimeStamp());
            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
            if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
            } elseif ($sessionData['isAllowAll'] == 'false') {
                if (isEmpty($sessionData['portalProviderIDs'])) {
                    throw new ValidationError("You don't have access to any of Portal Provider!!");
                }
                $providerIDs = explode(',', $sessionData['portalProviderIDs']);
            }
            if ($request->ajax()) {
                $allUserData = $userModel->getAllUserByPortalProvider($providerIDs)->select([
                    'user.PID as userPID',
                    'user.UUID as userUUID',
                    'user.portalProviderUserID as portalProviderUserID',
                    'portalProvider.UUID as portalProviderUUID',
                    'portalProvider.name as portalProviderName',
                    'user.firstName as firstName',
                    'user.lastName as lastName',
                    'user.gender as gender',
                    'country.countryName as country',
                    'user.email as userEmail',
                    'user.balance as userBalance',
                    'user.isLoggedIn as userLoggedInStatus',
                    'user.lastIP as userLastIP'
                    // DB::raw("IFNULL(user.activeMinutes,0) + TIMESTAMPDIFF(MINUTE,userSession.loginTime,'$timestamp') as userOnlineMin"),
                ])->get();

                $userData = array();
                foreach ($allUserData as $eachUser) {
                    //To get total user online active minutes
                    $userOnlineActiveTime = $userSessionModel->getUserTotalActiveTime($eachUser['userPID'], $currentTime);
                    $userData[] = [
                        'userUUID' => [
                            'url' => route('vUserProfile', [base64_encode($eachUser['userUUID'])]),
                            'value' => $eachUser['userUUID']
                        ],
                        'portalProviderUserID' => $eachUser['portalProviderUserID'],
                        'portalProviderUUID' => $eachUser['portalProviderUUID'],
                        'portalProviderName' => $eachUser['portalProviderName'],
                        'firstName' => $eachUser['firstName'],
                        'lastName' => $eachUser['lastName'],
                        'gender' => $eachUser['gender'],
                        'country' => $eachUser['country'],
                        'userEmail' => $eachUser['userEmail'],
                        'userBalance' => $eachUser['userBalance'],
                        'userLoggedInStatus' => $eachUser['userLoggedInStatus'],
                        'userLastIP' => $eachUser['userLastIP'],
                        'userOnlineMin' => minutesToTime($userOnlineActiveTime[0]->activeTimeInMins)
                    ];
                }

                return DataTables::of($userData)->make(true);
            }

            return view('adminPanel.userDetails');
        } catch (ValidationError $e) {
            return redirect()->back()->withErrors($e->getMessage());
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    //To display the user profile when user id is clicked on the all user details page
    public function getUserProfile(Request $request)
    {
        try {
            $value = session(str_replace(".", "_", $request->ip()) . 'ECGames');
            $portalProviderUUID = $value['portalProviderUUID'];

            //Decoding here, since the user id is base 64 encoded when passing through in the blade
            $userUUID = base64_decode($request->userUUID);

            $userModel = new User();
            $bettingModel = new Betting();
            $betResult = ['-1', '0', '1'];
            $isAdminPanel = true;

            $userProvider = new UserProvider(null);

            $userData = $userProvider->getUserProfile($portalProviderUUID, $userUUID, $fromDate = null, $toDate = null, $isAdminPanel);

            if (isset($userData['exception']) && $userData['exception']) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } elseif (!$userData['res']['status']) {
                return redirect()->back()->withErrors([$userData['res']['message']]);
            } else {
                if ($userData['res']['data']->count(DB::raw('1'))) {

                    $userInfo = $userData['res']['data'];
                    $userID = $userData['userID'];
                    // To display default profile picture when no profile picture uploaded by user
                    if ($userData['res']['data']['profileImage'] == "") {
                        $userData['res']['data']['profileImage'] = "/" . config('constants.image_path_user') . "default_profile_pic.jfif";
                    }

                    //To get the winning and loss percentage of the user
                    $winLossValue = $bettingModel->getUserWinLossValue($userID);

                    //Portal provider UUID valid check
                    $providerModel = new PortalProvider();
                    $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
                    if ($providerData->count(DB::raw('1')) == 0) {
                        return Res::notFound([], 'portalProviderUUID does not exist.');
                    }
                    $providerID = $providerData[0]->PID;

                    $fromDate = date('Y-m-d', strtotime('-31 days', strtotime(microtimeToDateTime(getCurrentTimeStamp(), false, 'd-m-Y'))));
                    $toDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');

                    // To display all the bets of the user
                    if ($request->ajax()) {
                        $userBettingData = $bettingModel->getAllBetAdmin($providerID, $userID, $betResult, null, null, true);
                        // DD($userBettingData->get());
                        return DataTables::of($userBettingData)
                            ->addColumn('action', function ($data) {
                                $button = '<button type="button" name="edit" data-toggle="modal" data-target="#modalTC" data-todo="' . $data->betResult . ',' . $data->betAmount . ',' . $data->rollingAmount . ',' . $data->payout . '" class="edit btn btn-primary btn-sm"><i class="fa fa-eye"></i></button>';
                                return  $button;
                            })
                            ->rawColumns(['action'])
                            ->make(true);
                    }

                    return view('adminPanel/userProfile')->with(array('userBasicInfo' => $userInfo, 'userID' => $userID, 'winLossValue' => $winLossValue));}
            }
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    //To display the user online history details
    public function getUserOnlineHistory(Request $request)
    {
        try {
            $value = session(str_replace(".", "_", $request->ip()) . 'ECGames');
            $portalProviderUUID = $value['portalProviderUUID'];
            $currentDay = Carbon::now();

            //Decoding here, since the user id is base 64 encoded when passing through in the blade
            $userUUID = base64_decode($request->userUUID);

            if ($request->input('isUserOnlineHistoryAjax')) {
                // To get the from and to date range from the input field
                $fromDate = $request->input('fromDate');
                $toDate = $request->input('toDate');
            } else if ($request->input('isSortByAjax')) {
                if ($request->input('filterBy') == "Day") {

                    // To get the from and to date range as the current default day
                    $fromDate = $currentDay->format('Y-m-d');
                    $toDate = $currentDay->format('Y-m-d');
                } else if ($request->input('filterBy') == "Year") {

                    // To get the from and to date range as the current default year
                    $fromDate = $currentDay->startOfYear()->format('Y-m-d');
                    $toDate = $currentDay->endOfYear()->format('Y-m-d');
                } else if ($request->input('filterBy') == "Month") {

                    // To get the from and to date range as the current default month
                    $fromDate = $currentDay->startOfMonth()->format('Y-m-d');
                    $toDate = $currentDay->endOfMonth()->format('Y-m-d');
                } else {

                    // To get the from and to date range as the current default week
                    $fromDate = $currentDay->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
                    $toDate = $currentDay->endOfWeek(Carbon::SATURDAY)->format('Y-m-d');
                }
            } else {
                // To get the from and to date range as the current default week
                $fromDate = $currentDay->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
                $toDate = $currentDay->endOfWeek(Carbon::SATURDAY)->format('Y-m-d');
            }

            $userProvider = new UserProvider(null);
            $userUUID = base64_decode($request->input('userUUID'));
            $isAdminPanel = true;

            $userData = $userProvider->getUserProfile($portalProviderUUID, $userUUID, $fromDate, $toDate, $isAdminPanel);

            if (isset($userData['exception']) && $userData['exception']) {
                return config('constants.default_error_response');
            } elseif (!$userData['res']['status']) {
                return [$userData['res']['message']];
            } else {
                if ($userData['res']['data']->count(DB::raw('1'))) {

                    $userInfo = $userData['res']['data'];

                    $activeTime = [];
                    $dateD = [];
                    foreach ($userInfo['activeTimeDateWise'] as $eachData) {
                        $activeTime[] = $eachData['activeTimeInMins'];
                        $dateD[] = $eachData['Date'];
                    }

                    $data = ['activeTimeInMins' => $activeTime, 'Date' => $dateD];

                    if ($request->input('isUserOnlineHistoryAjax') || $request->input('isSortByAjax')) {
                        // ajax request, so only send the response
                        $response = $data;
                    } else {
                        // This case executes on page load, so the blade redirection needs to be done
                        $response = view('adminPanel/userOnlineHistory')->with(array('userUUID' => $request->userUUID, 'activeTimeDateWise' => $data));
                    }
                }
                return $response;
            }
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    // public function addUserDetails(Request $request)
    // {
    //     // Json tag validation block
    //     $rules = array(
    //         "balance" => "required|integer|min:1",
    //         "portalProviderUserID" => "required",
    //         "portalProviderUUID" => "required|uuid",
    //         'profileImage' => 'mimes:jpeg,jpg,png|max:' . config("app.valid_image_size_in_kilo_bytes") . '|file',
    //     );

    //     $messages = [
    //         "balance.required" => "balance field is required.",
    //         "balance.integer" => "balance should be integer type.",
    //         "balance.min" => "balance should be greater then zero(0).",
    //         "portalProviderUserID.required" => "PortalProviderUserID field is required.",
    //         "portalProviderUUID.required" => "PortalProviderUUID field is required.",
    //         "portalProviderUUID.uuid" => "invalid UUID.",
    //         'profileImage.file' => 'profileImage is required for file type.',
    //         'profileImage.mimes' => 'Only jpeg, jpg, png are allowed.',
    //         'profileImage.max' => 'Image size should not be greater than ' . config('app.valid_image_size_in_kilo_bytes') . 'KB',
    //     ];

    //     $validator = Validator::make($request->toArray(), $rules, $messages);
    //     if ($validator->fails()) {
    //         return redirect()->back()->withErrors($validator->errors());
    //     }

    //     try {

    //         $providerModel = new PortalProvider();
    //         $userModel = new User();

    //         $portalProviderUUID = $request->portalProviderUUID;
    //         $portalProviderUserID = $request->portalProviderUserID;

    //         //Portal provider UUID valid check
    //         $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
    //         if ($providerData->count(DB::raw('1')) == 0) {
    //             return redirect()->back()->withErrors('portalProviderUUID does not exist.');
    //         }

    //         //User Already Exist check (PP UUID and PPUserID)
    //         $userData = $userModel->userAlreadyExists($portalProviderUserID, $providerData[0]->PID);
    //         if ($userData->count(DB::raw('1')) > 0) {
    //             return redirect()->back()->withErrors('User Already present.');
    //         }

    //         // creating user insert Array
    //         if (!isEmpty($request->firstName)) {
    //             $user['firstName'] = $request->firstName;
    //         }

    //         if (!isEmpty($request->lastName)) {
    //             $user['lastName'] = $request->lastName;
    //         }

    //         if (!isEmpty($request->gender)) {
    //             $user['gender'] = $request->gender;
    //         }
    //         if (!isEmpty($request->country)) {
    //             $user['country'] = $request->country;
    //         }
    //         if (!isEmpty($request->email)) {
    //             $user['email'] = $request->email;
    //         }

    //         if ((!isEmpty($request->balance)) && $request->balance < 0) {
    //             return redirect()->back()->withErrors('balance should not be in negative');
    //         }

    //         if (!isEmpty($request->profileImage)) {
    //             //save image and generate path
    //             $files = $request->file('profileImage');
    //             $destinationPath = 'images/user/profile/'; // upload path
    //             $profileImage = uniqid() . date('YmdHis') . "." . $files->getClientOriginalExtension();
    //             $files->move($destinationPath, $profileImage);
    //             $imagePath = $destinationPath . $profileImage;
    //             $user['profileImage'] = $imagePath;
    //         }

    //         $user['portalProviderUserID'] = $portalProviderUserID;
    //         $user['portalProviderID'] = $providerData[0]->PID;
    //         $user['userPolicyID'] = 1;
    //         $user['userName'] = "User" . date('sdHyim') . rand(0, 99); //creating temp username for new users
    //         $user['balance'] = $request->balance;
    //         $user['lastCalledTime'] = microtimeToDateTime(getCurrentTimeStamp());
    //         $user['lastIP'] = request()->ip();
    //         $user['loginTime'] = microtimeToDateTime(getCurrentTimeStamp());
    //         $user['UUID'] = Uuid::uuid4();

    //         $userSettings = array(
    //             'userID' => null,
    //         );

    //         // inserting user into DB
    //         $response['userID'] = User::insertGetId($user); // inserting into user Table
    //         $userSettings['userID'] = $response['userID'];

    //         UserSetting::insert($userSettings); // inserting into user Setting Table
    //         if ($response) {
    //             return redirect()->back()->with('message', 'Create New User Successfully');
    //         }
    //     } catch (Exception $e) {
    //         return redirect()->back()->withErrors($e->getMessage());
    //     }
    // }

    // public function updateUserDetails(Request $request)
    // {
    //     // Json tag validation block
    //     $rules = array(
    //         "balance" => "required|integer|min:1",
    //         "portalProviderUserID" => "required",
    //         "portalProviderUUID" => "required|uuid",
    //         'profileImage' => 'mimes:jpeg,jpg,png|max:' . config("app.valid_image_size_in_kilo_bytes") . '|file',
    //     );

    //     $messages = [
    //         "balance.required" => "balance field is required.",
    //         "balance.integer" => "balance should be integer type.",
    //         "balance.min" => "balance should be greater then zero(0).",
    //         "portalProviderUserID.required" => "PortalProviderUserID field is required.",
    //         "portalProviderUUID.required" => "PortalProviderUUID field is required.",
    //         "portalProviderUUID.uuid" => "invalid UUID.",
    //         'profileImage.file' => 'profileImage is required for file type.',
    //         'profileImage.mimes' => 'Only jpeg, jpg, png are allowed.',
    //         'profileImage.max' => 'Image size should not be greater than ' . config('app.valid_image_size_in_kilo_bytes') . 'KB',
    //     ];

    //     $validator = Validator::make($request->toArray(), $rules, $messages);
    //     if ($validator->fails()) {
    //         return redirect()->back()->withErrors($validator->errors());
    //     }

    //     try {

    //         $providerModel = new PortalProvider();
    //         $userModel = new User();

    //         $portalProviderUUID = $request->portalProviderUUID;
    //         $portalProviderUserID = $request->portalProviderUserID;
    //         $userID = $request->userID;

    //         //Portal provider UUID valid check
    //         $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
    //         if ($providerData->count(DB::raw('1')) == 0) {
    //             return redirect()->back()->withErrors('portalProviderUUID does not exist.');
    //         }

    //         //User Already Exist check (PP UUID and PPUserID)
    //         $userData = $userModel->getUserByUserID($userID)->get();
    //         if ($userData->count(DB::raw('1')) > 0) {

    //             // creating user insert Array
    //             if (!isEmpty($request->firstName)) {
    //                 $user['firstName'] = $request->firstName;
    //             }

    //             if (!isEmpty($request->lastName)) {
    //                 $user['lastName'] = $request->lastName;
    //             }

    //             if (!isEmpty($request->gender)) {
    //                 $user['gender'] = $request->gender;
    //             }
    //             if (!isEmpty($request->country)) {
    //                 $user['country'] = $request->country;
    //             }
    //             if (!isEmpty($request->email)) {
    //                 $user['email'] = $request->email;
    //             }

    //             if ((!isEmpty($request->balance)) && $request->balance < 0) {
    //                 return redirect()->back()->withErrors('balance should not be in negative');
    //             }

    //             if (!isEmpty($request->profileImage)) {
    //                 //save image and generate path
    //                 $files = $request->file('profileImage');
    //                 $destinationPath = 'images/user/profile/'; // upload path
    //                 $profileImage = uniqid() . date('YmdHis') . "." . $files->getClientOriginalExtension();
    //                 $files->move($destinationPath, $profileImage);
    //                 $imagePath = $destinationPath . $profileImage;
    //                 $user['profileImage'] = $imagePath;

    //                 if (!isEmpty($userData[0]->profileImage)) {
    //                     //deleting existing profile of user if new one is being uploaded
    //                     File::delete($userData[0]->profileImage);
    //                 }
    //             }

    //             $user['portalProviderUserID'] = $portalProviderUserID;
    //             $user['portalProviderID'] = $providerData[0]->PID;
    //             $user['balance'] = $request->balance;
    //             $user['lastIP'] = request()->ip();
    //             $user['updatedAt'] = microtimeToDateTime(getCurrentTimeStamp());

    //             // Update user into DB
    //             $response = $userModel->updateUser($userID, $user); // update into user Table
    //             if ($response) {
    //                 return redirect()->back()->with('message', 'Update User Information Successfully');
    //             }
    //         } else {
    //             return redirect()->back()->withErrors('User Information not found');
    //         }
    //     } catch (Exception $e) {
    //         return redirect()->back()->withErrors($e->getMessage());
    //     }
    // }

    // public function deleteUserDetails(Request $request)
    // {
    //     try {
    //         $response = User::where('PID', $request->userPID)->delete();
    //         if ($response) {
    //             return redirect()->back()->with('message', 'Delete User Successfully');
    //         }
    //     } catch (Exception $e) {
    //         return redirect()->back()->withErrors(config('constants.default_error_response'));
    //     }
    // }
}
