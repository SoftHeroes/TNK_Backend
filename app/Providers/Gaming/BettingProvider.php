<?php

namespace App\Providers\Gaming;

use Exception;
use App\Models\User;
use App\Models\Game;
use App\Models\Rule;
use Ramsey\Uuid\Uuid;
use App\Models\Betting;
use App\Models\DynamicOdd;
use App\Models\PortalProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use App\Events\Backend\PostBetPlacedEvent;
use App\Http\Controllers\ResponseController as Res;
use App\Models\Stock;
use Illuminate\Support\Facades\Validator;
use App\Events\Socket\BalanceUpdateEvent;


class BettingProvider extends ServiceProvider
{
    public function storeBet($portalProviderUUID, $userUUID, $betData)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        try {
            $userModel = new User();
            $gameModel = new Game();
            $dynamicOddModel = new DynamicOdd();
            $ruleModel = new Rule();
            $providerModel = new PortalProvider();
            $bettingModel = new Betting();


            // Getting source
            $adminData = request()->get('adminData');
            $source = $adminData[0]->source;

            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $response['portalProviderID'] = $providerData[0]->PID;

            // Check is user exists and is active and getting user balance.
            $userData = $userModel->getUserByUUID($userUUID)->select('balance', 'PID', 'portalProviderID')->get();

            if ($userData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'UserUUID does not exist.');
                return $response;
            }
            $usedID = $userData[0]->PID; //getting user id from userUUID
            $response['userID'] = $usedID;

            $sum = array_sum(array_column($betData, 'betAmount'));
            if ($sum  > $userData[0]->balance) {
                $response['res'] = Res::badRequest([], 'Not enough balance.');
                return $response;
            }

            $result = [];
            foreach ($betData as $singleBet) {
                $singleBet['betUUID'] = null;
                $singleBet['payout'] = null;
                $singleBet['status'] = false;
                $singleBet['createdDate'] = null;
                $singleBet['createdTime'] = null;

                // Validator for betData Array
                $rules = array(
                    'gameUUID' => 'required|uuid',
                    'ruleID' => 'required|integer',
                    'betAmount' => 'required|integer|min:100|max:10000'
                );

                $messages = array(
                    'gameUUID.uuid' => 'gameUUID should be a valid UUID.',
                    'gameUUID.required' => 'gameUUID is required.',
                    'ruleID.required' => 'ruleID is required.',
                    'ruleID.integer' => 'ruleID should be an integer.',
                    'betAmount.required' => 'betAmount is required.',
                    'betAmount.integer' => 'betAmount should be an integer.',
                    'betAmount.min' => 'betAmount should be greater than 100.',
                    'betAmount.max' => 'betAmount should be smaller than 10000.'
                );

                $validator = Validator::make($singleBet, $rules, $messages);

                // Checking if user betting on game of his own Provider
                if (($userData[0]->portalProviderID != 1) && ($userData[0]->portalProviderID != $providerData[0]->PID)) {
                    $singleBet['message'] = ['Invalid UUID! Please contact your provider'];
                    $result[] = $singleBet;
                    continue;
                }

                if ($validator->fails()) {
                    $error = (array) $validator->errors();
                    $msg = [];
                    foreach ($error as $key => $value) {
                        $msg[] = $value;
                    }
                    $msg = array_values($msg[0]);
                    $error_msg = [];
                    foreach ($msg as $key => $value) {
                        $error_msg = array_merge($error_msg, $value);
                    }

                    $singleBet['message'] = $error_msg;
                    $result[] = $singleBet;
                    continue;
                } else {
                    // Check the game exist in portal provider
                    if (!$gameModel->getGameByPortalProviderID($portalProviderUUID, $singleBet['gameUUID'])) {
                        $singleBet['message'] = ['Invalid Request! Please contact your provider'];
                        $result[] = $singleBet;
                        continue;
                    }

                    // Check if rule id is valid
                    $ruleName = $ruleModel->getRuleData($singleBet['ruleID']);
                    if ($ruleName->count(DB::raw('1')) == 0) {
                        $singleBet['message'] = ['ruleID does not exist.'];
                        $result[] = $singleBet;
                        continue;
                    }

                    // Check game close time and allow if bet has to be placed
                    $gameData = $gameModel->getActiveGameDetails($singleBet['gameUUID']);
                    if ($gameData->count(DB::raw('1')) == 0) {
                        $singleBet['message'] = ['Game id does not exist.'];
                        $result[] = $singleBet;
                        continue;
                    }

                    $gameID = $gameData[0]->PID; // Getting gameID from gameUUID

                    // Check game is exist or not.
                    if (!$gameModel->getGameByPortalProviderID($portalProviderUUID, $singleBet['gameUUID'])->select('game.UUID as gameUUID')->first()) {
                        $singleBet['message'] = ['Requested game does not exist.'];
                        $result[] = $singleBet;
                        continue;
                    }

                    //check max  amount for particular game rule
                    $betCountData = $bettingModel->getTotalBetOnRuleByUserID($gameID, $singleBet['ruleID'], $usedID);
                    if (!$betCountData->count(DB::raw('1')) == 0) {
                        if ($betCountData[0]->betTotal + $singleBet['betAmount'] > 10000) {
                            $singleBet['message'] = ['Cannot place more then 10000 on a single bet rule.'];
                            $result[] = $singleBet;
                            continue;
                        }
                    }

                    // Check the user is belongs to portal provider
                    if ($providerData[0]->PID != $userData[0]->portalProviderID || $providerData[0]->PID != $gameData[0]->portalProviderID) {
                        $singleBet['message'] = ['User does not exist.'];
                        $result[] = $singleBet;
                        continue;
                    }

                    // Check available balance
                    if ($userData[0]->portalProviderID != 1 && $singleBet['betAmount'] > $userData[0]->balance) {
                        $singleBet['message'] = ['Not enough balance.'];
                        $result[] = $singleBet;
                        continue;
                    } else {
                        // Get payout from dynamic odds table using gameID
                        $payoutData = $dynamicOddModel->dynamicOddByRule($gameID, $singleBet['ruleID']);
                        $payout = $payoutData[0]->payout;

                        $createdDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');
                        $createdTime = microtimeToDateTime(getCurrentTimeStamp(), false, 'H:i:s');

                        // RollingAmount and betResult will be updated after the bet result is generated.
                        $bet = array(
                            'gameID' => $gameID,
                            'userID' => $usedID,
                            'ruleID' => $singleBet['ruleID'],
                            'betAmount' => $singleBet['betAmount'],
                            'isBot' => $userData[0]->portalProviderID == 1 ? 1 : 0,
                            'payout' => $payout,
                            'source' => $source,
                            'createdDate' => $createdDate,
                            'createdTime' => $createdTime,
                            'UUID' => Uuid::uuid4()
                        );

                        DB::beginTransaction();

                        $userBalance = $userData[0]->balance - $bet['betAmount'];

                        // Debit the bet amount from user balance.
                        if ($userData[0]->portalProviderID != 1) {
                            $userData[0]->decrement('balance', $bet['betAmount']);
                        }

                        // Insert into bet
                        $bet['PID'] = Betting::insertGetId($bet);

                        // Triggering event to copy the bets for the following users and then calculate the dynamic odd change
                        event(new PostBetPlacedEvent($gameID, $usedID, $bet, $singleBet['gameUUID'], $portalProviderUUID, $userUUID, $gameData[0]->stockUUID, $gameData[0]->stockLoop));

                        DB::commit();
                        //call balanceUpdateEvent
                        if (!isEmpty($userUUID) && !isEmpty($userBalance)) {
                            $data['userUUID'] = $userUUID;
                            $data['userBalance'] = $userBalance;
                            $socketData = Res::success($data);
                            broadcast(new BalanceUpdateEvent($socketData));
                        }
                        $singleBet['ruleName'] = $ruleName[0]->name;
                        $singleBet['payout'] = $bet['payout'];
                        $singleBet['betUUID'] = $bet['UUID'];
                        $singleBet['message'] = ['Bet placed successfully'];
                        $singleBet['status'] = true;
                        $singleBet['createdDate'] = $createdDate;
                        $singleBet['createdTime'] = $createdTime;
                        $result[] = $singleBet;
                    }
                }
            }

            $response['res'] = Res::success($result, 'Bet processed!');
        } catch (Exception $e) {
            DB::rollback();
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e, $e->getMessage());
        }
        return $response;
    }

    public function getAllBets($portalProviderUUID, $betResult, $limit, $offset, $userUUID, $fromDate = null, $toDate = null, $gameUUID = null, $stockUUID = null, $isExposed = false)
    {

        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        try {
            $userModel = new User();
            $providerModel = new PortalProvider();
            $bettingModel = new Betting();
            $gameModel = new Game();
            $stockModel = new Stock();

            if (isEmpty($fromDate)) {
                $fromDate = date('Y-m-d', strtotime('-31 days', strtotime(microtimeToDateTime(getCurrentTimeStamp(), false, 'd-m-Y'))));
            }
            if (isEmpty($toDate)) {
                $toDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');
            }

            // Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {

                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $response['portalProviderID'] = $providerData[0]->PID;


            if ($gameUUID != null) {
                $gameData = $gameModel->getGameByUUIDAndPortalProviderID($gameUUID, $providerData[0]->PID);
                if ($gameData->count(DB::raw('1')) < 1) {
                    $response['res'] = Res::notFound([], 'gameUUID does not exist.');
                    return $response;
                }
            }

            if ($stockUUID != null) {
                $stockData = $stockModel->getStockBaseOnProvider($providerData[0]->PID, $stockUUID);
                if ($stockData->count(DB::raw('1')) < 1) {
                    $response['res'] = Res::notFound([], 'stockUUID does not exist.');
                    return $response;
                }
            }

            // check if user uuid present
            if (!isEmpty($userUUID)) {

                // User UUID valid check
                $userData = $userModel->getUserByUUID($userUUID, $response['portalProviderID'])->select('PID', 'portalProviderID')->get();
                if ($userData->count(DB::raw('1')) == 0) {
                    $response['res'] = Res::notFound([], 'UserUUID does not exist.');
                    return $response;
                }
                $response['userID'] = $userData[0]->PID;

                // Check if user belongs to the provider
                if (($userData[0]->portalProviderID != 1) && ($userData[0]->portalProviderID != $providerData[0]->PID)) {
                    $response['res'] = Res::badRequest([], 'Invalid Request! Please contact your provider');
                    return $response;
                }
            }

            $data = $bettingModel->getAllBets($response['portalProviderID'], $response['userID'], $betResult, $limit, $offset, $fromDate, $toDate, $gameUUID, $stockUUID, false, $isExposed);

            if ($data->count(DB::raw('1')) > 0) {
                // Ending either one of response
                $response['res'] = Res::success($data);
                return $response;
            } else {
                $response['res'] = Res::success([], 'No placed bets yet, Start Betting!');
                return $response;
            }
        } catch (Exception $e) {
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
            return $response;
        }
    }


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
}
