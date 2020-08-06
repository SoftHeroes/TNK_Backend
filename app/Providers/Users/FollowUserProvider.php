<?php

namespace App\Providers\Users;

use Exception;
use App\Models\User;
use App\Models\FollowUser;
use App\Models\FollowBetRule;
use App\Models\PortalProvider;
use App\Models\ProviderConfig;
use Illuminate\Support\Facades\DB;
use App\Jobs\AutomaticallyUnfollowJob;
use Illuminate\Support\ServiceProvider;
use App\Providers\Users\NotificationProvider;
use App\Http\Controllers\ResponseController as Res;

class FollowUserProvider extends ServiceProvider
{
    public function followUser($portalProviderUUID, $followerUUID, $followToUUID, $method, $followBetRule, $unFollowBetRule)
    {

        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        try {

            $userModel = new User();
            $providerModel = new PortalProvider();
            $followBetRuleModel = new FollowBetRule();
            $providerConfigModel = new ProviderConfig();

            // Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $portalProviderID = $providerData->first()->PID;
            $response['portalProviderID'] = $portalProviderID;

            // User UUID valid check
            $followerData = $userModel->getUserByUUID($followerUUID)->select('PID', 'portalProviderID', 'userName')->first();
            $followToData = $userModel->getUserByUUID($followToUUID)->select('PID', 'portalProviderID')->first();
            if (isEmpty($followerData) || isEmpty($followToData) || $followerData->count(DB::raw('1')) == 0 || $followToData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], "Check userUUID and followToUUID, either of them does not exist");
                return $response;
            }
            $followerID = $followerData->PID;
            $followToID = $followToData->PID;
            $response['userID'] = $followerID;

            //user following himself/herself check
            if ($followerID == $followToID) {
                $response['res'] = Res::badRequest([], "followerID and followToID can't be the same");
                return $response;
            }

            // Check if both users belong to portal Provider
            if ($portalProviderID != $followerData->portalProviderID || $portalProviderID != $followToData->portalProviderID) {
                $response['res'] = Res::badRequest([], 'Invalid Request! Please contact your provider');
                return $response;
            }

            //provider config select.
            $selectConfig = [
                'providerConfig.followBetSetupID',
                'followBetSetup.followBetRuleID',
                'followBetSetup.minFollowBetRuleSelect',
                'followBetSetup.maxFollowBetRuleSelect',
                'followBetSetup.unFollowBetRuleID',
                'followBetSetup.minUnFollowBetRuleSelect',
                'followBetSetup.maxUnFollowBetRuleSelect',
                'followBetSetup.maxFollowLimit'
            ];
            //Check if provider has access to this feature - get provider follow and un-follow rules from provider config
            $providerConfigData = $providerConfigModel->getProviderConfigRuleByPID($portalProviderID)->select($selectConfig)->first();

            if (!$providerConfigData) {
                $response['res'] = Res::badRequest([], "Something went wrong please contact your service provider.");
                return $response;
            } else if (isEmpty($providerConfigData->followBetSetupID)) {
                $response['res'] = Res::badRequest([], "You do not have access to this feature.");
                return $response;
            } else if (isEmpty($providerConfigData->followBetRuleID) && !isEmpty($followBetRule) && count($followBetRule) > 0) {
                $response['res'] = Res::badRequest([], "You do not have access to follow bet rule feature.");
                return $response;
            } else if (isEmpty($providerConfigData->unFollowBetRuleID) && !isEmpty($unFollowBetRule) && count($unFollowBetRule) > 0) {
                $response['res'] = Res::badRequest([], "You do not have access to un-follow bet rule feature.");
                return $response;
            } else { //has access to this feature


                //Building update/insert array
                $record = array(
                    'followerID' => $followerID,
                    'followToID' => $followToID,
                );

                $isFollowed = FollowUser::getFollowerAndFollowTo($followerID, $followToID)->select('isFollowing');

                if ($method == 2) { // follow or un-follow check
                    $record['isFollowing'] = 'false';
                    $record['followBetRuleID'] = null;
                    $record['followRuleValue'] = null;
                    $record['unFollowBetRuleID'] = null;
                    $record['unFollowRuleValue'] = null;
                } else {            //method is follow
                    $record['isFollowing'] = 'true';

                    // Check if user has already followed
                    if ($isFollowed->count(DB::raw('1')) > 0 && $isFollowed->first()->isFollowing == "true") {
                        $response['res'] = Res::badRequest([], "User Already Followed");
                        return $response;
                    }

                    //check if max follow limit reached
                    if (!isEmpty($providerConfigData->maxFollowLimit)) { // if null then skip
                        // get total following count
                        $followUserModel = new FollowUser();
                        $userFollowingData = $followUserModel->getUserFollowersOrFollowing($followerID, 2, 100, 0)->select('user.UUID')->get();
                        if ($userFollowingData->count(DB::raw('1')) >= $providerConfigData->maxFollowLimit) {
                            $response['res'] = Res::badRequest([], "You cant follow more than " . $providerConfigData->maxFollowLimit . " users at a time.");
                            return $response;
                        }
                    }

                    //checking if setup has both follow and un-follow column null (normal follow)
                    if ($providerConfigData->followBetSetupID == 1) {

                        //normal follow insert in followUser table with followBetRuleID and unFollowBetRuleID as null.
                        $record['followBetRuleID'] = null;
                        $record['followRuleValue'] = null;
                        $record['unFollowBetRuleID'] = null;
                        $record['unFollowRuleValue'] = null;
                    } else {
                        // checking follow bet rule section
                        if (!isEmpty($providerConfigData->minFollowBetRuleSelect)) {

                            $followConfigRules = explode(',', $providerConfigData->followBetRuleID);
                            $followBetRuleCount = count((array) $followBetRule);

                            if ((($providerConfigData->minFollowBetRuleSelect != 0) && isEmpty($followBetRule)) || $providerConfigData->minFollowBetRuleSelect > $followBetRuleCount) {
                                $response['res'] = Res::badRequest([], "Minimum " . $providerConfigData->minFollowBetRuleSelect . " follow Bet Rule ID have to be selected.");
                                return $response;
                            } else if ($providerConfigData->maxFollowBetRuleSelect < $followBetRuleCount) {
                                $response['res'] = Res::badRequest([], "Maximum " . $providerConfigData->maxFollowBetRuleSelect . " follow Bet Rule ID can be selected.");
                                return $response;
                            }
                            $followBetRuleIDArr = [];
                            $followRuleValueArr = [];
                            foreach ($followBetRule as $singleBetRule) {

                                //validating follow bet rule id and value
                                if (isEmpty($singleBetRule['id']) || isEmpty($singleBetRule['value'])) {
                                    $response['res'] = Res::badRequest([], "follow bet rule id and value are required");
                                    return $response;
                                }
                                if (!in_array($singleBetRule['id'], $followConfigRules)) {
                                    $response['res'] = Res::badRequest([], "You do not have access to this follow feature or invalid follow rule.");
                                    return $response;
                                }

                                //check minimum and maximum rule limit for respective value from followBetRule
                                $followBetRuleData = $followBetRuleModel->getFollowBetRuleData($singleBetRule['id'])->select('min', 'max')->first();
                                if ($followBetRuleData->count(DB::raw('1')) > 0) {

                                    if ($followBetRuleData->min > $singleBetRule['value']  || $followBetRuleData->max < $singleBetRule['value']) {
                                        $response['res'] = Res::badRequest([], "Follow bet rule value must be between " . $followBetRuleData->min . " and " . $followBetRuleData->max . ".");
                                        return $response;
                                    }
                                } else {
                                    $response['res'] = Res::badRequest([], "Invalid follow rule.");
                                    return $response;
                                }

                                //append bet id and value in proper format
                                $followBetRuleIDArr[] = $singleBetRule['id'];
                                $followRuleValueArr[] = ["id" => $singleBetRule['id'], "value" => $singleBetRule['value']];
                            }

                            $record['followBetRuleID'] = json_encode($followBetRuleIDArr);
                            $record['followRuleValue'] = json_encode($followRuleValueArr);
                        }

                        // checking un-follow bet rule section
                        if (!isEmpty($providerConfigData->minUnFollowBetRuleSelect)) {

                            $unFollowConfigRules = explode(',', $providerConfigData->unFollowBetRuleID);
                            $unFollowBetRuleCount = count((array) $unFollowBetRule);

                            if ((($providerConfigData->minUnFollowBetRuleSelect != 0) && isEmpty($unFollowBetRule)) || $providerConfigData->minUnFollowBetRuleSelect > $unFollowBetRuleCount) {
                                $response['res'] = Res::badRequest([], "Minimum " . $providerConfigData->minUnFollowBetRuleSelect . " un-follow Bet Rule ID have to be selected.");
                                return $response;
                            } else if ($providerConfigData->maxUnFollowBetRuleSelect < $unFollowBetRuleCount) {
                                $response['res'] = Res::badRequest([], "Maximum " . $providerConfigData->maxUnFollowBetRuleSelect . " un-follow Bet Rule ID can be selected.");
                                return $response;
                            }

                            $unFollowBetRuleIDArr = [];
                            $unFollowRuleValueArr = [];
                            foreach ($unFollowBetRule as $singleBetRule) {

                                //validating un-follow bet rule id and value
                                if (isEmpty($singleBetRule['id']) || isEmpty($singleBetRule['value'])) {
                                    $response['res'] = Res::badRequest([], "follow bet rule id and value are required");
                                    return $response;
                                }
                                if (!in_array($singleBetRule['id'], $unFollowConfigRules)) {
                                    $response['res'] = Res::badRequest([], "You do not have access to this un-follow feature or invalid follow rule.");
                                    return $response;
                                }

                                //check minimum and maximum rule limit for respective value from followBetRule
                                $followBetRuleData = $followBetRuleModel->getFollowBetRuleData($singleBetRule['id'])->select('min', 'max')->first();
                                if ($followBetRuleData->count(DB::raw('1')) > 0) {

                                    if ($followBetRuleData->min > $singleBetRule['value']  || $followBetRuleData->max < $singleBetRule['value']) {
                                        $response['res'] = Res::badRequest([], "Un-follow bet rule value must be between " . $followBetRuleData->min . " and " . $followBetRuleData->max . ".");
                                        return $response;
                                    }
                                } else {
                                    $response['res'] = Res::badRequest([], "Invalid un-follow rule.");
                                    return $response;
                                }

                                //append bet id and value in proper format
                                $unFollowBetRuleIDArr[] = $singleBetRule['id'];
                                $unFollowRuleValueArr[] = ["id" => $singleBetRule['id'], "value" => $singleBetRule['value']];
                            }

                            $record['unFollowBetRuleID'] = json_encode($unFollowBetRuleIDArr);
                            $record['unFollowRuleValue'] = json_encode($unFollowRuleValueArr);
                        }
                    }
                }

                //start transaction
                DB::beginTransaction();
                if ($method == 1) {
                    //already have one record with un-follow flag
                    if ($isFollowed->count(DB::raw('1')) > 0 && $isFollowed->first()->isFollowing == "false") {
                        $isFollowed->update($record);
                    } else {
                        FollowUser::insert($record);
                    }
                    $unFollowRuleValueAsArray = json_decode($record['unFollowRuleValue']);
                    foreach ($unFollowRuleValueAsArray as $eachRule) {
                        if ($eachRule->id == 3) {
                            AutomaticallyUnfollowJob::dispatch(
                                $followToID,
                                $portalProviderUUID,
                                $followToUUID
                            )->onQueue('immediate')->delay(now(config('app.timezone'))->addMinute($eachRule->value));
                            break;
                        }
                    }

                    $response['res'] = Res::success([], 'User followed successfully.');

                    //trigger notification: follow notification
                    $notificationProvider = new NotificationProvider(null);
                    $title = 'You have got a new follower!';
                    $message = $followerData->userName . ' has started following you !!';
                    $notificationProvider->addNotification($portalProviderUUID, 1, $followerUUID, $followToUUID, $title, $message);
                } else {
                    if ($isFollowed->count(DB::raw('1')) > 0 && $isFollowed->first()->isFollowing == "true") {
                        $isFollowed->update($record);
                        $response['res'] = Res::success([], 'User un-followed successfully.');
                    } else {
                        $response['res'] = Res::success([], 'You are not following this user currently.');
                    }
                }

                DB::commit();
            }
        } catch (Exception $ex) {

            DB::rollback(); // rollback in case of any error
            $response['exceptionMsg'] = $ex->getMessage();
            $response['res'] = Res::errorException($ex);
        }

        return $response;
    }

    public function followUserList($userUUID, $portalProviderUUID, $followersType, $limit, $offset)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        try {

            $userModel = new User();
            $providerModel = new PortalProvider();
            $followUserModel = new FollowUser();

            // Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) < 1) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $response['portalProviderID'] = $providerData->first()->PID;

            // User UUID valid check
            $userData = $userModel->getUserByUUID($userUUID)->select('PID', 'portalProviderID')->first();
            if ($userData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], "Check userUUID does not exist");
                return $response;
            }
            $response['userID'] = $userData->PID;

            // Check if both users belong to portal Provider
            if ($providerData->first()->PID != $userData->portalProviderID) {
                $response['res'] = Res::badRequest([], 'Invalid Request! Please contact your provider');
                return $response;
            }

            $followUserData = $followUserModel->getUserFollowersOrFollowing($userData->PID, $followersType, $limit, $offset)->select(
                'user.UUID',
                'user.userName',
                DB::raw('concat(user.firstName," ", user.lastName) as fullName'),
                DB::raw("(CASE WHEN user.profileImage IS NULL THEN CONCAT('" . config("constants.image_path_avatar") . "',user.avatar) ELSE CONCAT('" . config("constants.image_path_user") . "',user.profileImage) END) AS profileImage"),
                $followersType == 2 ? DB::raw('1 as isFollowing') : DB::raw("isFollowing( $userData->PID , followUser.followerID ) as isFollowing"),
                'userSetting.isAllowToVisitProfile',
                'followUser.followRuleValue',
                'followUser.unFollowRuleValue'
            )->get();

            if ($followUserData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::success($followUserData, "No " . ($followersType == 1 ? "Followers" : "Following"));
            } else {

                foreach ($followUserData as $singleUserData) {
                    if ($followersType == 1) // Followers
                    {
                        $singleUserData->followRuleValue = [];
                        $singleUserData->unFollowRuleValue = [];
                    } else {
                        $followBetRuleModel = new FollowBetRule();

                        $followRules = $followBetRuleModel->select('PID', 'name')->get();

                        if ($followRules->count(DB::raw('1')) == 0) {
                            throw new Exception('No follow rules are find!!');
                        }
                        $followRulesArray = array();

                        foreach ($followRules as $singleFollowRules) { // making Array of Rule
                            $followRulesArray[$singleFollowRules->PID] = $singleFollowRules->name;
                        }

                        $followRuleValue = json_decode($singleUserData->followRuleValue);
                        foreach ($followRuleValue as $key => $loopOnFollowRule) {
                            $followRuleValue[$key]->{'name'}  = $followRulesArray[$loopOnFollowRule->id];
                        }

                        $unFollowRuleValue = json_decode($singleUserData->unFollowRuleValue);
                        foreach ($unFollowRuleValue as $key => $loopOnUnfollowRule) {
                            $unFollowRuleValue[$key]->{'name'}  = $followRulesArray[$loopOnUnfollowRule->id];
                        }

                        $singleUserData->followRuleValue = $followRuleValue;
                        $singleUserData->unFollowRuleValue = $unFollowRuleValue;
                    }
                }

                $response['res'] = Res::success($followUserData);
            }
        } catch (Exception $ex) {
            $response['exceptionMsg'] = $ex->getMessage();
            $response['res'] = Res::errorException($ex);
        }
        return $response;
    }
}
