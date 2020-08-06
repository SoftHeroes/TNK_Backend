<?php

namespace App\Providers\Users;

use File;
use Exception;
use Ramsey\Uuid\Uuid;
use App\Models\User;
use App\Models\Game;
use App\Models\Betting;
use App\Models\IdLookup;
use App\Models\UserPolicy;
use App\Models\FollowUser;
use App\Models\UserSession;
use App\Models\UserSetting;
use App\Models\PortalProvider;
use App\Models\ProviderConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\Backend\PoolLogEvent;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\ResponseController as Res;
use Illuminate\Support\Facades\Route as IlluminateRoute;
use App\Providers\Users\NotificationProvider;
use App\Jobs\LogoutAPICallJob;
use App\Models\Likes;
use App\Events\Socket\BalanceUpdateEvent;

require_once app_path() . '/Helpers/CommonUtility.php';

class UserProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function getUserProfile($portalProviderUUID, $userUUID, $fromDate, $toDate, $isAdminPanel = false)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;
        $response['res']['activeTimeDateWise'] = null;
        $response['res']['currentActiveTime'] = null;

        try {
            $userModel = new User();
            $providerModel = new PortalProvider();
            $userSessionModel = new UserSession();
            $likesModel = new Likes();

            $currentTime = microtimeToDateTime(getCurrentTimeStamp());

            //Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $response['portalProviderID'] = $providerData[0]->PID;

            //User UUID valid check and fetching details
            $userData = $userModel->getUserProfile($userUUID)->select(
                'user.PID',
                'user.userName',
                'user.loginTime',
                'user.portalProviderID',
                'user.firstName',
                'user.lastName',
                'user.email',
                DB::raw("(CASE WHEN user.profileImage IS NULL THEN CONCAT('" . config("constants.image_path_avatar") . "',user.avatar) ELSE CONCAT('" . config("constants.image_path_user") . "',user.profileImage) END) AS profileImage"),
                'user.balance',
                'user.isLoggedIn',
                'user.isActive',
                'user.UUID as userUUID',
                'user.gender',
                'user.country',
                'userSetting.isAllowToVisitProfile',
                'userSetting.isAllowToFollow',
                'userSetting.isAllowToDirectMessage',
                'userSetting.isSound',
                'userSetting.isAllowToLocation',
                DB::raw('SUM(betting.rollingAmount) as rollingAmount')
            )->get();
            if ($userData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'User UUID does not exist.');
                return $response;
            }

            $response['userID'] = $userData[0]->PID;

            //check if user belongs to the provider
            if (!$isAdminPanel && ($userData[0]->portalProviderID != 1) && ($userData[0]->portalProviderID != $providerData[0]->PID)) {
                $response['res'] = Res::badRequest([], 'Invalid Request! Please contact your provider');
                return $response;
            }
            $userPID = $userData[0]['PID'];
            $isUserLoggedIn = $userData[0]['isLoggedIn'];

            // To remove the portal provider id from displaying on the front end response
            unset($userData[0]['portalProviderID']);
            unset($userData[0]['PID']);

            //returning the user details
            $response['res'] = Res::success($userData, 'success', true);
            $response['res']['data']->isSound                 = ($response['res']['data']->isSound == 1);
            $response['res']['data']->isAllowToFollow         = ($response['res']['data']->isAllowToFollow == 1);
            $response['res']['data']->isAllowToLocation       = ($response['res']['data']->isAllowToLocation == 1);
            $response['res']['data']->isAllowToVisitProfile   = ($response['res']['data']->isAllowToVisitProfile == 1);
            $response['res']['data']->isAllowToDirectMessage  = ($response['res']['data']->isAllowToDirectMessage == 1);
            $response['res']['data']['currentActiveTime'] = 'offline';
            $response['res']['data']['activeTimeDateWise'] = array();
            $response['res']['data']['totalLikes'] = $likesModel->getAllLiker($response['userID'])->get()->count();

            $userSessionData = $userSessionModel->findByUserId($userPID);

            if ($userSessionData->count(DB::raw('1')) != 0) {
                $dateOne = $userSessionData[0]['loginTime'];

                $dateTwo = microtimeToDateTime(getCurrentTimeStamp());

                //To find the diff between two datetime objects in minutes
                $timeDiff = timeDiffBetweenTwoDateTimeObjects($dateOne, $dateTwo);

                $response['res']['data']['currentActiveTime'] = (($timeDiff->d) ? $timeDiff->d . " days, " : "") . $timeDiff->h . " hours, " . $timeDiff->i . " minutes, " . $timeDiff->s . " seconds";

                if ($fromDate != "" && $toDate != "") {
                    $userSessionData = $userSessionModel->getUserActiveTime($fromDate, $toDate, $currentTime, $userPID);
                    $response['res']['data']['activeTimeDateWise'] = $userSessionData;
                }
            }

            if ($isUserLoggedIn === 'false') {
                $response['res']['data']['currentActiveTime'] = 'offline';

                if ($fromDate != "" && $toDate != "") {
                    $userSessionData = $userSessionModel->getUserActiveTime($fromDate, $toDate, $currentTime, $userPID);
                    $response['res']['data']['activeTimeDateWise'] = $userSessionData;
                }
            }
        } catch (Exception $e) {
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }
        return $response;
    }

    public function updateUserProfile(Request $request)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        try {

            $userModel = new User();
            $providerModel = new PortalProvider();


            //Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($request->portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $response['portalProviderID'] = $providerData[0]->PID;

            //User UUID valid check
            $userData = $userModel->getUserByUUID($request->userUUID)->select('PID', 'userName', 'profileImage', 'portalProviderID')->get();
            if ($userData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'UserUUID does not exist.');
                return $response;
            }
            $response['userID'] = $userData[0]->PID;

            //check if user belongs to the provider
            if (($userData[0]->portalProviderID != 1) && ($userData[0]->portalProviderID != $providerData[0]->PID)) {
                $response['res'] = Res::badRequest([], 'Invalid Request! Please contact your provider');
                return $response;
            }

            //check all available parameters and add them into update array
            $updateData = array();

            if (!isEmpty($request->userName)) { //keeping userName Unique
                $userNameData = $userModel->checkUserName($request->userName)->select('PID')->get();
                if (($userNameData->count(DB::raw('1')) > 0) && ($userData[0]->PID != $userNameData[0]->PID)) {
                    $response['res'] = Res::badRequest([], 'userName already taken, kindly choose new one');
                    return $response;
                } else {
                    $updateData['userName'] = $request->userName;
                }
            }

            if (!isEmpty($request->firstName))
                $updateData['firstName'] = $request->firstName;

            if (!isEmpty($request->middleName))
                $updateData['middleName'] = $request->middleName;

            if (!isEmpty($request->lastName))
                $updateData['lastName'] = $request->lastName;

            if (!isEmpty($request->gender))
                $updateData['gender'] = $request->gender;

            if (!isEmpty($request->country))
                $updateData['country'] = $request->country;

            if (!isEmpty($request->email))
                $updateData['email'] = $request->email;

            if (!isEmpty($request->profileImage)) {

                //save image and generate path
                $files = $request->file('profileImage');
                $destinationPath = config("constants.image_path_user"); // upload path
                $profileImage = uniqid() . date('YmdHis') . "." . $files->getClientOriginalExtension();
                $files->move($destinationPath, $profileImage);
                //$imagePath = $destinationPath . $profileImage;
                $updateData['profileImage'] =  $profileImage;
                if (!isEmpty($userData[0]->profileImage)) {
                    //deleting existing profile of user if new one is being uploaded
                    File::delete($userData[0]->profileImage);
                }
            }

            //avatar
            if (!isEmpty($request->avatarID))
                $updateData['avatar'] = $request->avatarID . '.jpg';     //need to rewrite this when avatar module is getting final that it will stay.

            //updating if any updateData array is not empty
            if (!isEmpty($updateData)) {
                $data = $userModel->updateUser($userData[0]->PID, $updateData);
                if ($data) {
                    $response['res'] = Res::success();
                    return $response;
                } else {
                    $response['res'] = Res::notFound([], 'User ID not found.');
                    return $response;
                }
            } else {
                $response['res'] = Res::badRequest([], 'No parameters sent to update.');
            }
        } catch (Exception $e) {
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }
        return $response;
    }

    public function getAllUsers($portalProviderUUID)
    {

        $providerModel = new PortalProvider();
        $userModel = new User();

        try {
            //Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);

            if ($providerData->count(DB::raw('1')) == 0) {
                $response =  Res::notFound([], 'portalProviderUUID does not exist.');
            }

            // if TNKMaster is logged in, he should see all the active user's listed irrespective of the portal provider
            if ($providerData[0]['PID'] == 1 && $providerData[0]['name'] == 'TNKMaster') {
                $userData = $userModel->getAllUsers();
            } else {
                $userData = $userModel->getAllUsersByPortalProviderID($providerData[0]['PID'])->get();
            }

            $response = Res::success($userData);
        } catch (Exception $e) {
            $response = Res::errorException($e->getMessage());
        }

        return $response;
    }

    public static function updateUserSetting(Request $request)
    {
        $providerModel = new PortalProvider();
        $userModel = new User();
        $response['res'] = Res::success();
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;


        try {
            //Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($request->portalProviderUUID);
            if ($providerData->count(DB::raw('1')) < 1) {
                $response['res'] =  Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $response['portalProviderID'] = $providerData->first()->PID;


            $userData = $userModel->getUserByUUID($request->userUUID);
            if ($userData->count(DB::raw('1')) < 1) {
                $response['res'] =  Res::notFound([], 'userUUID does not exist.');
                return $response;
            }
            $response['userID'] = $userData->first()->PID;

            // Check if this user belong to portalProviderID or not
            if ($providerData->first()->PID != $userData->first()->portalProviderID) {
                $response['res'] = Res::badRequest([], 'Invalid Request! Please contact your provider');
            } else {
                $userID = $userData->first()->PID;
                $request->request->add(['userID' => $userID]);
                $updateData = $request->only(UserSetting::$updateColumn);

                $isUser = UserSetting::findByUserID($userID);
                $isUpdate = $isUser->update($updateData); // Update user setting

                unset($updateData['userID']);
                if (!$isUpdate) {
                    $response['res'] = Res::badRequest([], "Check Your Input Parameters");
                } else {
                    $response['res'] = Res::success($updateData);
                }
            }
        } catch (Exception $ex) {
            $response['exceptionMsg'] = $ex->getMessage();
            $response['res'] = Res::errorException($ex);
        }
        return $response;
    }

    public function createUser($portalProviderUserID, $portalProviderUUID, $userPolicyID, $firstName = null, $middleName = null, $lastName = null, $email = null, $password = null, $balance = 0)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;
        $response['userUUID'] = null;

        $providerModel = new PortalProvider();
        $userPolicyModel = new UserPolicy();
        $userModel = new User();

        try {

            //Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $response['portalProviderID'] = $providerData[0]->PID;
            $response['providerMainBalance'] = $providerData[0]->mainBalance;

            //User Policy ID valid check
            $userPolicyData = $userPolicyModel->validatePolicyID($userPolicyID);
            if ($userPolicyData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'Invalid user policy ID.');
                return $response;
            }

            //User Already Exist check (PP UUID and PPUserID)
            $userData = $userModel->userAlreadyExists($portalProviderUserID, $providerData[0]->PID);
            if ($userData->count(DB::raw('1')) > 0) {
                $response['userID'] = $userData[0]->PID;
                $response['userUUID'] = $userData[0]->UUID;
                $response['res'] = Res::alreadyExist([], 'User Already present.');
                return $response;
            }

            //creating temp username for new users
            $userName = "User" . date('sdHyim') . rand(0, 99);
            $userUUID = Uuid::uuid4();
            $response['userUUID'] = $userUUID;
            // creating user insert Array
            $user = array(
                'portalProviderUserID' => $portalProviderUserID,
                'portalProviderID' => $providerData[0]->PID,
                'userPolicyID' => $userPolicyID,
                'userName' => $userName,
                'firstName' => $firstName,
                'middleName' => $middleName,
                'lastName' => $lastName,
                'email' => $email,
                'password' => isEmpty($password) ? null : Crypt::encrypt($password),
                'balance' => $balance,
                'lastCalledTime' => microtimeToDateTime(getCurrentTimeStamp()),
                'lastIP' => request()->ip(),
                'loginTime' => microtimeToDateTime(getCurrentTimeStamp()),
                'UUID' => $userUUID
            );

            $userSettings = array(
                'userID' => null,
            );

            // inserting user into DB
            DB::beginTransaction();

            $response['userID'] = User::insertGetId($user); // inserting into user Table
            $userSettings['userID'] = $response['userID'];

            UserSetting::insert($userSettings); // inserting into user Setting Table
            DB::commit();

            //trigger notification: welcome
            $notificationProvider = new NotificationProvider(null);
            $title = 'Welcome to ECGaming';
            $message = 'Get started with the game which can make you win money!';
            $notificationProvider->addNotification($portalProviderUUID, 4, null, $user['UUID'], $title, $message);

            $response['res'] = Res::success($response['userID'], "user login Successfully");
        } catch (Exception $e) {

            DB::rollback(); // rollback in case of any error
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }

        return $response;
    }

    public function createOrLoginUser($portalProviderUUID, $portalProviderUserID, $balance, $adminID, $source)
    {

        $response['portalProviderID'] = null;
        $response['userID'] = null;

        try {
            $tempResponse = $this->createUser($portalProviderUserID, $portalProviderUUID, 1); // calling create user function

            // checking if error = " user already exists" then skip otherwise show error
            if (!$tempResponse['res']['status'] && $tempResponse['res']['code'] != '409') {
                $response['res'] = Res::badRequest([], $tempResponse['res']['message'][0]);
            } else {
                $userID = $tempResponse['userID'];
                $providerMainBalance = round($tempResponse['providerMainBalance'], 2);
                $response['portalProviderID'] = $tempResponse['portalProviderID'];
                // Check for user balance should be less then portal provider main balance
                if ($providerMainBalance >= $balance || $providerMainBalance == -1) {

                    //call balanceUpdateEvent
                    if (!isEmpty($tempResponse['userUUID']) && !isEmpty($balance)) {
                        $data['userUUID'] = $tempResponse['userUUID'];
                        $data['userBalance'] = $balance;
                        $data = Res::success($data);
                        broadcast(new BalanceUpdateEvent($data));
                    }

                    $response = $this->userLogin($providerMainBalance, $balance, $userID, null, $adminID, $source);  // calling login process of user

                } else {
                    $response['res'] = Res::badRequest([], 'Insufficient balance,Contact your provider.');
                }
            }

        } catch (Exception $e) {
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }
        return $response;
    }

    public function userLogin($providerMainBalance, $balance, $userID, $password = null, $adminID = null, $source = 0)
    {
        $response['userID'] = $userID;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;
        // default response
        // $response = array("error" => false, "msg" => "user login Successfully", "userUUID" => null, "exception" => false);


        // verification of user Credentials : Start
        if (isEmpty($password)) {
            $userCount = User::select('portalProviderID', 'UUID')->where('PID', '=', $userID)->where('isActive', '=', 'active')->whereNull('password')->get();
        } else {
            $userCount = User::select('portalProviderID', 'UUID')->where('PID', '=', $userID)->where('isActive', '=', 'active')->where('password', '=', Crypt::decrypt($password))->get();
        }
        // verification of user Credentials : end


        if ($userCount->count(DB::raw('1')) > 0) {

            $response['portalProviderID'] = $userCount[0]->portalProviderID;

            // verification of user session : Start
            $userSessionModel = new UserSession;
            $userSession = $userSessionModel->checkUserSession($userID);

            if ($userSession->count(DB::raw('1')) > 0) {
                $response['res'] = Res::badRequest([], 'user already have active session wait 5 minutes');
                $this->logoutUser($userCount[0]->UUID);
            }
            // verification of user session : end

            try {

                $query = User::getPortalProviderByUserID($userID);

                // updating balances : Start
                DB::beginTransaction();

                if ($userCount[0]->portalProviderID != 1 && $balance != 0) {
                    $query->decrement('portalProvider.mainBalance', $balance);
                    $query->increment('user.balance', $balance);

                    $tranID = IdLookup::getUniqueId('poolLog', 'transactionId');

                    event(new PoolLogEvent(
                        $userCount[0]->portalProviderID,
                        $userID,
                        $adminID,
                        $providerMainBalance,
                        $providerMainBalance - $balance,
                        $balance,
                        'mainBalance',
                        1, // debit
                        $tranID,
                        IlluminateRoute::getFacadeRoot()->current()->uri(),
                        $source
                    ));
                }

                $query->update(['user.isLoggedIn' => 'true', 'user.loginTime' => microtimeToDateTime(getCurrentTimeStamp())]);

                // updating balances : end

                // creating user session : Start
                UserSession::insert(
                    array(
                        'userID' => $userID,
                        'userIpAddress' => request()->ip(),
                        'balance' => $balance,
                        'loginTime' => microtimeToDateTime(getCurrentTimeStamp())
                    )
                );
                // creating user session : end

                DB::commit();

                $userUUID = $query->select('user.UUID as userUUID')->get();
                $response['res'] = Res::success($userUUID, "user login Successfully", true);
            } catch (Exception $e) {
                DB::rollback(); // rollback in case of any error

                $response['exceptionMsg'] = $e->getMessage();
                $response['res'] = Res::errorException($e);
            }
        } else {
            $response['res'] = Res::badRequest([], 'Invalid Credentials.');
        }

        return $response;
    }

    public function logoutUser($userUUID, $adminID = null, $source = 0, $isAutoSignOut = false)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        try {

            $userModel = new User;
            $userSessionModel = new UserSession;
            $portalProviderModel =  new PortalProvider;

            $userData = $userModel->getUserByUUID($userUUID)->select('balance', 'PID', 'portalProviderID', 'portalProviderUserID', 'loginTime')->get();

            if ($userData->count(DB::raw('1')) > 0) {
                $response['userID'] = $userData[0]->PID;
                $response['portalProviderID'] = $userData[0]->portalProviderID;


                $userDataInSession = $userSessionModel->findByUserId($userData[0]->PID);

                $dateOne = $userData[0]->loginTime;
                $userBalance = $userData[0]->balance;

                $dateTwo = microtimeToDateTime(getCurrentTimeStamp());

                //To find the diff between two datetime objects in minutes
                $timeDiff = timeDiffBetweenTwoDateTimeObjects($dateOne, $dateTwo);

                $intervalInMin = $timeDiff->d * 24 * 60;
                $intervalInMin += $timeDiff->h * 60;
                $intervalInMin += $timeDiff->i;

                if ($userDataInSession->count(DB::raw('1')) > 0) {


                    $newCreditBalance   = $userBalance - $userDataInSession[0]->balance;
                    $mainBalance        = $userBalance;

                    $newCreditBalance = round($newCreditBalance, 2);
                    $mainBalance = round($mainBalance, 2);

                    $query = $portalProviderModel->findByPortalProviderID($userData[0]->portalProviderID)->where('PID', '!=', 1);
                    $portalProviderData = $query->select('PID', 'UUID', 'mainBalance', 'creditBalance')->get();
                    if ($portalProviderData->count(DB::raw('1')) != 0) {

                        //call balanceUpdateEvent
                        if (!isEmpty($userUUID) && !isEmpty($userBalance)) {
                            $data['userUUID'] = $userUUID;
                            $data['userBalance'] = $userBalance;
                            $data = Res::success($data);
                            broadcast(new BalanceUpdateEvent($data));
                        }


                        DB::beginTransaction();

                        $ppdMainBalance = round($portalProviderData[0]->mainBalance, 2);
                        $incrementPpdMainBalance = round($portalProviderData[0]->mainBalance + $mainBalance, 2);

                        $tranID = IdLookup::getUniqueId('poolLog', 'transactionId');
                        if ($isAutoSignOut) {
                            $serviceName = 'autoSignOut';
                            $ipAddress = null;
                        } else {
                            $serviceName = IlluminateRoute::getFacadeRoot()->current()->uri();
                            $ipAddress = \Request::getClientIp();
                        }
                        if ($mainBalance != 0) {
                            $query->increment('mainBalance', $mainBalance);

                            event(new PoolLogEvent( // pool Log code
                                $userData[0]->portalProviderID,
                                $userData[0]->PID,
                                $adminID,
                                $ppdMainBalance,
                                $incrementPpdMainBalance,
                                $mainBalance,
                                'mainBalance',
                                0, // credit
                                $tranID,
                                $serviceName,
                                $source
                            ));
                        }

                        if ($newCreditBalance != 0) {
                            $query->increment('creditBalance', $newCreditBalance);

                            event(new PoolLogEvent( // pool Log code
                                $userData[0]->portalProviderID,
                                $userData[0]->PID,
                                $adminID,
                                $ppdMainBalance,
                                $incrementPpdMainBalance,
                                $newCreditBalance >= 0 ? $newCreditBalance : -1 * $newCreditBalance,
                                'creditBalance',
                                $newCreditBalance >= 0 ? 0 : 1, // credit|debit
                                $tranID,
                                $serviceName,
                                $source
                            ));
                        }
                    }

                    $userUpdate = $userModel->where('PID', '=', $userData[0]->PID);

                    $userUpdate->update([
                        'isLoggedIn' => 'false',
                        'logoutTime' => microtimeToDateTime(getCurrentTimeStamp()),
                        'activeMinutes' => $intervalInMin,
                    ]);

                    $userUpdate->decrement('balance', round($userBalance, 2));

                    //To delete the user's sessions
                    $userSessionModel->where('userID', $userData[0]->PID)->update([
                        'logoutTime' => microtimeToDateTime(getCurrentTimeStamp()),
                        'deletedAt' => microtimeToDateTime(getCurrentTimeStamp())
                    ]);

                    DB::commit();

                    //checking config and env to call logout api
                    $configModel  = new ProviderConfig();
                    $configData  = $configModel->getProviderConfigByPID($userData[0]->portalProviderID)->select('logoutAPICall')->first();
                    if (IsAuthEnv() && ($configData->logoutAPICall == 1)) {
                        // logout call to provider
                        LogoutAPICallJob::dispatch(
                            $serviceName,
                            $portalProviderData[0]->PID,
                            $adminID,
                            $source,
                            $userData[0]->PID,
                            $userData[0]->portalProviderUserID,
                            $userBalance,
                            $ipAddress
                        )->onQueue('logout');
                    }


                    $response['res'] = Res::success([], 'User logged out successfully');
                } else {
                    $response['res'] = Res::badRequest([], 'User session not available!!');
                }
            } else {
                $response['res'] = Res::notFound([], 'Invalid User!!');
            }
        } catch (Exception $e) {
            DB::rollback();
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }
        return $response;
    }

    /**
     * this function call SP and get all leader board list
     *
     * @return mixed
     */
    public function getTopUsers($portalProviderUUID, $userUUID, $startDate = null, $endDate = null, $limit = 10)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        $userModel = new User();
        $providerModel = new PortalProvider();

        try {

            if (isEmpty($startDate)) {
                $startDate = date('Y-m-d', strtotime('-7 days', strtotime(microtimeToDateTime(getCurrentTimeStamp(), false, 'd-m-Y'))));
            }
            if (isEmpty($endDate)) {
                $endDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');
            }
            if (isEmpty($limit)) {
                $limit = 10;
            }

            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $portalProviderID = $providerData[0]->PID;
            $response['portalProviderID'] = $portalProviderID;


            $userData = $userModel->getUserByUUIDAndPortalProviderID($userUUID, $portalProviderID)->select('user.PID')->get();
            if ($userData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'userUUID does not exist.');
                return $response;
            }
            $userID = $userData[0]->PID;
            $response['userID'] = $userID;

            $response['res'] = Res::success($userModel->getTopLeaderBoardUsers($portalProviderID, $userID, $startDate, $endDate, $limit));
        } catch (Exception $e) {
            DB::rollback();
            $response['res'] = Res::errorException($e->getMessage());
            $response['exceptionMsg'] = $e->getMessage();
        }
        return $response;
    }

    /**
     * this function verify portalProviderUUID and userUUID
     *
     * @return mixed
     */
    public function getUserBetAnalysis($portalProviderUUID, $userUUID, $startDate = null, $endDate = null)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        $userModel = new User();
        $providerModel = new PortalProvider();
        $betting = new Betting();

        try {

            if (isEmpty($startDate)) {
                $startDate = date('Y-m-d', strtotime('-31 days', strtotime(microtimeToDateTime(getCurrentTimeStamp(), false, 'd-m-Y'))));
            }
            if (isEmpty($endDate)) {
                $endDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');
            }

            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $portalProviderID = $providerData[0]->PID;
            $response['portalProviderID'] = $portalProviderID;


            $userData = $userModel->getUserByUUIDAndPortalProviderID($userUUID, $portalProviderID)->select('user.PID')->get();
            if ($userData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'userUUID does not exist.');
                return $response;
            }
            $userID = $userData[0]->PID;
            $response['userID'] = $userID;

            $response['res'] = Res::success($betting->getUserBetAnalysis($userID, $startDate, $endDate));
        } catch (Exception $e) {
            DB::rollback();
            $response['res'] = Res::errorException($e->getMessage());
            $response['exceptionMsg'] = $e->getMessage();
        }
        return $response;
    }

    /**
     * this function Logout All users
     *
     * @return mixed
     */
    public function logoutAllUser($portalProviderPID)
    {
        $response = Res::success([], 'Pool cleared Successfully');

        try {
            DB::beginTransaction();
            $ppdModel = new PortalProvider();
            $ppdData = $ppdModel->findByPortalProviderID($portalProviderPID);
            if ($ppdData->count(DB::raw('1')) < 1) {
                return Res::notFound([], 'portalProviderPID does not exist');
            }

            $userModel = new User();
            $getAllUser = $userModel->getAllUsersByPortalProviderID($portalProviderPID)->where('isLoggedIn', 'true')->get();
            if ($getAllUser->count(DB::raw('1')) < 1) {
                return Res::notFound([], "This portal provider don't have any pool to clear");
            }

            // Logout all User
            $logout = null;
            foreach ($getAllUser as $user) {
                $logout = $this->logoutUser($user->UUID);

                if (!$logout['res']['status']) {
                    $response = Res::error([], implode(" | ", $logout['res']['message']));
                }
            }

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            return Res::errorException([], $ex->getMessage());
        }
        return $response;
    }

    /**
     * this function get user balance
     *
     * @return mixed
     */
    public function getUserBalance($portalProviderUUID, $portalProviderUserID)
    {
        $response['portalProviderID'] = null;
        $response['userID'] = null;
        $response['exceptionMsg'] = null;

        try {
            $portalProviderModelRef = new PortalProvider();

            $portalProviderData = $portalProviderModelRef->getPortalProviderByUUID($portalProviderUUID);

            if (count($portalProviderData) == 0) { // check for Portal Provider UUID
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
            } else {

                $response['portalProviderID'] = $portalProviderData[0]->PID;
                $userModelRef = new User();
                $userData = $userModelRef->userAlreadyExists($portalProviderUserID, $response['portalProviderID']);

                if (count($userData) == 0) {
                    $response['res'] = Res::notFound([], 'portalProviderUserID does not exist.');
                } else {
                    $response['userID'] = $userData[0]->PID;
                    $response['res'] = Res::success([array('balance' => $userData[0]->balance)], 'success', true);
                }
            }
        } catch (Exception $e) {
            $response['res'] = Res::errorException($e->getMessage());
        }

        return $response;
    }

    /**
     * this function update user balance
     *
     * @return mixed
     */
    public function updateUserBalance($portalProviderUUID, $portalProviderUserID, $balance)
    {
        $response['portalProviderID'] = null;
        $response['userID'] = null;
        $response['exceptionMsg'] = null;

        try {
            $portalProviderModelRef = new PortalProvider();

            $portalProviderData = $portalProviderModelRef->getPortalProviderByUUID($portalProviderUUID);

            if (count($portalProviderData) == 0) { // check for Portal Provider UUID
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
            } else {

                $response['portalProviderID'] = $portalProviderData[0]->PID;
                $userModelRef = new User();
                $userData = $userModelRef->userAlreadyExists($portalProviderUserID, $response['portalProviderID']);

                if (count($userData) == 0) {
                    $response['res'] = Res::notFound([], 'portalProviderUserID does not exist.');
                } else {
                    $response['userID'] = $userData[0]->PID;

                    $userSessionModelRef = new UserSession();
                    $userSessionData = $userSessionModelRef->checkUserSession($response['userID']);

                    if (count($userSessionData) == 0) {
                        $response['res'] = Res::badRequest([], 'User session not available!!');
                    } else {

                        DB::beginTransaction();
                        $userData[0]->increment('balance', $balance);
                        $userSessionData[0]->increment('balance', $balance);
                        $portalProviderData[0]->decrement('mainBalance', $balance);
                        DB::commit();

                        $response['res'] = Res::success([]);

                        //call balanceUpdateEvent
                        $userBalance = $userData[0]->balance + $balance;
                        if (!isEmpty($userData[0]->UUID) && !isEmpty($userBalance)) {
                            $data['userUUID'] = $userData[0]->UUID;
                            $data['userBalance'] = $userBalance;
                            $data = Res::success($data);
                            broadcast(new BalanceUpdateEvent($data));
                        }
                        //trigger notification: updateBalance notification
                        $notificationProvider = new NotificationProvider(null);
                        $title = 'You have got fresh funds!';
                        $message = $balance . ' chips have been credited to your ECGame account!';
                        $notificationProvider->addNotification($portalProviderUUID, 3, null, $userData[0]->UUID, $title, $message);
                    }
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            $response['res'] = Res::errorException($e->getMessage());
        }

        return $response;
    }

    /**
     * this function logout User and clear it's pool
     *
     * @return mixed
     */
    public function logoutAndClearPool($portalProviderUUID, $portalProviderUserID, $adminID, $source)
    {
        $response['portalProviderID'] = null;
        $response['userID'] = null;
        $response['exceptionMsg'] = null;


        try {
            $portalProviderModelRef = new PortalProvider();

            $portalProviderData = $portalProviderModelRef->getPortalProviderByUUID($portalProviderUUID);

            if (count($portalProviderData) == 0) { // check for Portal Provider UUID
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
            } else {

                $response['portalProviderID'] = $portalProviderData[0]->PID;
                $userModelRef = new User();
                $userData = $userModelRef->userAlreadyExists($portalProviderUserID, $response['portalProviderID']);

                if (count($userData) == 0) {
                    $response['res'] = Res::notFound([], 'portalProviderUserID does not exist.');
                } else {
                    $response = $this->logoutUser($userData[0]->UUID, $adminID, $source);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            $response['res'] = Res::errorException($e->getMessage());
        }

        return $response;
    }

    /**
     * this function send Invitation in World chat or Game chat
     *
     * @return mixed
     */
    public function sendInvitation($portalProviderUUID, $userUUID, $gameUUID = null, $startDate = null, $endDate = null)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        $userModel = new User();
        $providerModel = new PortalProvider();
        $gameModel = new Game();

        try {

            if (isEmpty($startDate)) {
                $startDate = date('Y-m-d', strtotime('-31 days', strtotime(microtimeToDateTime(getCurrentTimeStamp(), false, 'd-m-Y'))));
            }
            if (isEmpty($endDate)) {
                $endDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');
            }

            // check for Provider UUID
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $portalProviderID = $providerData[0]->PID;
            $response['portalProviderID'] = $portalProviderID;

            $selectedColumn = [
                "user.totalInvitationSent",
                "user.totalInvitationSentInDay",
                "user.totalInvitationSentInMin",
                "user.lastInvitationSend",
                "user.lastInvitationMin",
                "user.PID"
            ];
            // check for User UUID
            $userDataBeforeQuery = $userModel->getUserByUUIDAndPortalProviderID($userUUID, $portalProviderID)->select($selectedColumn);
            $userData = $userDataBeforeQuery->get();
            if ($userData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'userUUID does not exist.');
                return $response;
            }

            $userID = $userData[0]->PID;
            $response['userID'] = $userID;

            // Sent Invitation Limit part
            $providerConfigData = ProviderConfig::getInvitationSetup($portalProviderID)->select('maximumRequestInDay', 'requestMin', 'maximumRequestInMin')->get();
            if (count($providerConfigData) > 0) {

                $updateData = array();
                $increaseValue = 1;

                $updateData["totalInvitationSent"] = $userData[0]->totalInvitationSent + $increaseValue;

                $currentDateTime = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d H:i:s');
                $currentDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');

                // If lastInvitationSend is null then increase totalInvitationSent,totalInvitationSentInMin,totalInvitationSentInDay = 1
                if ($currentDate != date('Y-m-d', strtotime($userData[0]->lastInvitationMin))) {
                    $updateData["totalInvitationSentInDay"] = $increaseValue;
                    $updateData["totalInvitationSentInMin"] = $increaseValue;
                    $updateData['lastInvitationSend'] = $currentDateTime;
                    $updateData['lastInvitationMin'] = $currentDateTime;
                } // If date are the same
                else {
                    // Set current invite time + new invite (PER DAY) before dive into if condition
                    $updateData["totalInvitationSentInDay"] = $userData[0]->totalInvitationSentInDay + $increaseValue;

                    //  If request reached limit value per day
                    if (!isEmpty($providerConfigData[0]->maximumRequestInDay) && $updateData["totalInvitationSentInDay"] > $providerConfigData[0]->maximumRequestInDay) {
                        $response["res"] = Res::badRequest([], "Invitation allow " . $providerConfigData[0]->maximumRequestInDay . " request per day");
                        return $response;
                    }

                    $timeRangeInMilliseconds = strtotime($currentDateTime) - strtotime($userData[0]->lastInvitationMin); // get second

                    if (!isEmpty($providerConfigData[0]->requestMin) && ($timeRangeInMilliseconds / 60) >= $providerConfigData[0]->requestMin) {
                        $updateData["totalInvitationSentInMin"] = $increaseValue;
                    } else {
                        // Set current invite time + new invite (PER HOUR) before dive into if condition
                        $updateData["totalInvitationSentInMin"] = $userData[0]->totalInvitationSentInMin + $increaseValue;
                        if (!isEmpty($providerConfigData[0]->maximumRequestInMin) && $updateData["totalInvitationSentInMin"] > $providerConfigData[0]->maximumRequestInMin) {
                            $response["res"] = Res::badRequest([], "Invitation allow " . $providerConfigData[0]->maximumRequestInMin . " request per hour");
                            return $response;
                        }
                    }
                    $updateData['lastInvitationMin'] = $currentDateTime;
                }
                // Update
                $userDataBeforeQuery->update($updateData);
            }


            if (!isEmpty($gameUUID)) {
                // check for Game UUID
                $gameData = $gameModel->getGameByUUIDAndPortalProviderID($gameUUID, $portalProviderID)->select('game.PID')->whereIn('game.gameStatus', [1, 2])->get();

                if ($gameData->count(DB::raw('1')) == 0) {
                    $response['res'] =  Res::notFound([], 'game already finished.');
                    return $response;
                }
            }

            $userDetails = $userModel->getUserDetails($portalProviderID, $userID, $startDate, $endDate);

            if (count($userDetails) == 0) {
                $response['res'] =  Res::notFound([], "You don't any Bet History.");
                return $response;
            }

            $response['res'] = Res::success($userDetails[0]);

            if (!isEmpty($gameUUID)) {
                $response['res']['author'] = $portalProviderUUID . '.' . $gameUUID;
            } else {
                $response['res']['author'] = $portalProviderUUID .  '.global';
            }
        } catch (Exception $e) {
            DB::rollback();
            $response['res'] = Res::errorException($e->getMessage());
            $response['exceptionMsg'] = $e->getMessage();
        }
        return $response;
    }
    /**
     * this function is to
     *
     * @return mixed
     */
    public function userInvitationDetail($portalProviderUUID, $userUUID, $startDate = null, $endDate = null)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        $userModel = new User();
        $providerModel = new PortalProvider();

        try {

            //getting default last one month data if to and from dates are not specified
            if (isEmpty($startDate)) {
                $startDate = date('Y-m-d', strtotime('-31 days', strtotime(microtimeToDateTime(getCurrentTimeStamp(), false, 'd-m-Y'))));
            }
            if (isEmpty($endDate)) {
                $endDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');
            }

            // check for Provider UUID
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $portalProviderID = $providerData[0]->PID;
            $response['portalProviderID'] = $portalProviderID;

            $selectedColumn = [
                "user.PID"
            ];
            // check for User UUID
            $userDataBeforeQuery = $userModel->getUserByUUIDAndPortalProviderID($userUUID, $portalProviderID)->select($selectedColumn);
            $userData = $userDataBeforeQuery->get();
            if ($userData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'userUUID does not exist.');
                return $response;
            }

            $userID = $userData[0]->PID;
            $response['userID'] = $userID;

            //getting user invitation details
            $userDetails = $userModel->getUserDetails($portalProviderID, $userID, $startDate, $endDate);

            if (count($userDetails) == 0) {
                $response['res'] =  Res::notFound([], "You don't any Bet History.");
                return $response;
            }

            $data['userUUID'] = $userDetails[0]->userUUID;
            $data['rank'] = $userDetails[0]->rank;
            $data['followerCount'] = $userDetails[0]->followerCount;
            $data['winRate'] = $userDetails[0]->winRate;

            $response['res'] = Res::success($data);

        } catch (Exception $e) {
            DB::rollback();
            $response['res'] = Res::errorException($e->getMessage());
            $response['exceptionMsg'] = $e->getMessage();
        }
        return $response;
    }

    /**
     * this function send Invitation in World chat or Game chat
     *
     * @return mixed
     */
    public function visitUserProfile($portalProviderUUID, $userUUID, $visitingUserUUID, $fromDate, $toDate)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;
        $response['res']['activeTimeDateWise'] = null;
        $response['res']['currentActiveTime'] = null;

        $userModel = new User();
        $providerModel = new PortalProvider();
        $userSessionModel = new UserSession();
        $userSettingModel = new UserSetting();

        $isFollowing = -1;

        try {
            if (isEmpty($fromDate)) {
                $fromDate = date('Y-m-d', strtotime('-1860‬ days', strtotime(microtimeToDateTime(getCurrentTimeStamp(), false, 'd-m-Y'))));
            }
            if (isEmpty($toDate)) {
                $toDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');
            }

            // check for Provider UUID
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $portalProviderID = $providerData[0]->PID;
            $response['portalProviderID'] = $portalProviderID;

            // check for User UUID
            $userData = $userModel->getUserByUUIDAndPortalProviderID($userUUID, $portalProviderID)->select('user.PID')->get();
            if ($userData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'userUUID does not exist.');
                return $response;
            }
            $userID = $userData[0]->PID;
            $response['userID'] = $userID;

            // check for visiting User UUID
            $visitingUserData = $userModel->getUserByUUIDAndPortalProviderID($visitingUserUUID, $portalProviderID)->select('user.PID', 'user.firstName', 'user.middleName', 'user.lastName', 'user.gender', 'user.country')->get();
            if ($visitingUserData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'visiting User UUID does not exist.');
                return $response;
            }
            $visitingUserID = $visitingUserData[0]->PID;

            $visitingUserSettingData = $userSettingModel->findByUserID($visitingUserID)->select('isAllowToVisitProfile','isAllowToLocation')->get();

            if ($visitingUserSettingData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::errorException([], 'visiting User setting does not exist.');
                return $response;
            }

            if ($visitingUserSettingData[0]->isAllowToVisitProfile != 1 && $visitingUserData[0]->PID != $userData[0]->PID) {
                $response['res'] =  Res::badRequest([], 'User not allow to visit profile.');
                return $response;
            }

            $userDetails = $userModel->getUserDetails($portalProviderID, $visitingUserID, $fromDate,  $toDate);

            if (count($userDetails) == 0) {
                $response['res'] =  Res::errorException([], 'User Details not found.');
                return $response;
            }

            if ($visitingUserID != $userID) {

                $followData =  FollowUser::getFollowerAndFollowTo($userID, $visitingUserID)->where('isFollowing', 'true');

                if ($followData->count(DB::raw('1')) == 1) {
                    $isFollowing = 1;
                } else {
                    $isFollowing = 0;
                }
            }

            $response['res'] = Res::success($userDetails[0]); // creating Success Response

            $response['res']['data']->{'firstName'} = $visitingUserData[0]->firstName;
            $response['res']['data']->{'middleName'} = $visitingUserData[0]->middleName;
            $response['res']['data']->{'lastName'} = $visitingUserData[0]->lastName;
            $response['res']['data']->{'gender'} = $visitingUserData[0]->gender;
            $response['res']['data']->{'country'} = $visitingUserData[0]->country;
            $response['res']['data']->{'isAllowToLocation'} = $visitingUserSettingData[0]->isAllowToLocation;
            $response['res']['data']->{'isFollowing'} = $isFollowing;

            $response['res']['data']->{'activeTimeDateWise'} = array();
            $response['res']['data']->{'currentActiveTime'} = 'offline';

            $userSessionData = $userSessionModel->findByUserId($visitingUserID);

            if ($userSessionData->count(DB::raw('1')) != 0) {
                $dateOne = $userSessionData[0]['loginTime'];

                $dateTwo = microtimeToDateTime(getCurrentTimeStamp());

                //To find the diff between two datetime objects in minutes
                $timeDiff = timeDiffBetweenTwoDateTimeObjects($dateOne, $dateTwo);

                $response['res']['data']->currentActiveTime =  $timeDiff->h . " hours, " . $timeDiff->i . " minutes, " . $timeDiff->s . " seconds";
            }

            $userSessionData = $userSessionModel->getUserActiveTime($fromDate, $toDate, microtimeToDateTime(getCurrentTimeStamp()), $visitingUserID);
            $response['res']['data']->activeTimeDateWise = $userSessionData;

        } catch (Exception $e) {
            DB::rollback();
            $response['res'] = Res::errorException($e->getMessage());
            $response['exceptionMsg'] = $e->getMessage();
        }
        return $response;
    }

    // Piyush: Send like to each users.
    public function sendLike(
        $portalProviderUUID,
        $userUUID,
        $userToUUID,
        $like
    ) {
        $response = [
            'userID' => null,
            'portalProviderID' => null,
            'exceptionMsg' => null
        ];

        try {
            $userModel = new User();
            $providerModel = new PortalProvider();

            // Portal provider UUID valid check.
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');

                return $response;
            }
            $portalProviderID = $providerData[0]->PID;
            $response['portalProviderID'] = $portalProviderID;

            // User UUID valid check.
            $userFirst = $userModel->getUserByUUID($userUUID)->select('PID', 'portalProviderID')->first();
            $userSecond = $userModel->getUserByUUID($userToUUID)->select('PID', 'portalProviderID', 'userName')->first();
            if (isEmpty($userFirst) || isEmpty($userSecond)) {
                $response['res'] = Res::notFound([], "Check your userUUID and userToUUID parameters, either of them does not exist");

                return $response;
            }
            $response['userID'] = $userFirst->PID;

            // UserFirst and UserSecond check same.
            if ($userFirst->PID == $userSecond->PID) {
                $response['res'] = Res::badRequest([], "User can not give a like to himself/herself.");

                return $response;
            }

            // Check if each users belong from portal Provider
            if ($portalProviderID != $userFirst->portalProviderID || $portalProviderID != $userSecond->portalProviderID) {
                $response['res'] = Res::badRequest([], 'Invalid Request! Please contact your provider');

                return $response;
            }

            // Check if liked Or Add it.
            if ($like === true) {
                $like = 'true';
                $likeStatus = 'Liked';
            } elseif ($like === false) {
                $like = 'false';
                $likeStatus = 'Un-liked';
            } else {
                $response['res'] = Res::badRequest([], 'Bad request!');

                return $response;
            }

            $likeModel = new Likes();
            $isFound = $likeModel->isLiked($userFirst->PID, $userSecond->PID);
            if ($isFound) {
                if($isFound->status == $like){
                    $response['res'] = Res::success([], "You already ".$likeStatus);
                    return $response;
                }
            }
            DB::beginTransaction();
                $likeModel->updateOrCreate(
                    [
                        'userFrom'=>$userFirst->PID,
                        'userTo' => $userSecond->PID
                    ],
                    [
                        'userFrom' => $userFirst->PID,
                        'userTo' => $userSecond->PID,
                        'status' => $like
                    ]
                );
            DB::commit();
            $response['res'] = Res::success([], 'You ' . $likeStatus . ' ' . $userSecond->userName);
        } catch (Exception $e) {
            DB::rollback();
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }

        return $response;
    }
}
