<?php

namespace App\Listeners\Backend;

use DB;
use App\Models\User;
use App\Jobs\MailJob;
use Ramsey\Uuid\Uuid;
use App\Models\Betting;
use App\Models\FollowUser;
use App\Models\FollowBetRule;
use App\Providers\Users\FollowUserProvider;
use Illuminate\Contracts\Queue\ShouldQueue;

require_once app_path() . '/Helpers/CommonUtility.php';

class FollowBetsListener implements ShouldQueue
{
    public $queue = 'immediate'; //Queue Name

    public function queue(QueueManager $handler, $method, $arguments)
    {
        $handler->push($method, $arguments, $this->queue);
    }

    /**
     * Handle the event.
     *
     * @param  FollowBetsEvent  $event
     * @return void
     */
    public function handle($event)
    {
        try {
            $userModel = new User();
            $bettingModel = new Betting();
            $followUserModel = new FollowUser();
            $followBetRuleModel = new FollowBetRule();

            $bets = array();

            $followerData = $followUserModel->getAllFollowers($event->userID)->select('followerID', 'followToID', 'isFollowing', 'followBetRuleID', 'followRuleValue', 'unFollowBetRuleID', 'unFollowRuleValue')->get();

            if ($followerData->count(DB::raw('1')) != 0) {
                DB::beginTransaction();

                foreach ($followerData as $eachFollowers) {
                    $followersUserData = $userModel->checkBalanceByUserPID($eachFollowers->followerID)->select('balance', 'UUID')->get(); // getting followers user balance 

                    if ($followersUserData->count(DB::raw('1')) == 0) {
                        throw new Exception('Followers User Data not Found for userID : ' . isEmpty($eachFollowers->followerID) ? 'NULL' : $eachFollowers->followerID);
                    }

                    $followRuleData = json_decode($eachFollowers->followRuleValue); // getting user follow options 

                    if (count($followRuleData) == 0) { // check if user have some follow setting if not found any stop bellow code running
                        continue;
                    }

                    $value = $followRuleData[0]->value;
                    $betAmount = $event->betData['betAmount'];

                    $followBetRuleData = $followBetRuleModel->getFollowBetRuleData($followRuleData[0]->id)->select('rule')->get(); // getting Rule data from "follow Bet Rule" table

                    if ($followBetRuleData->count(DB::raw('1')) == 0) { // check if found same setting rule in DB
                        throw new Exception('Not record found in "follow Bet Rule" table for id : ' . isEmpty($followRuleData[0]->id) ? 'null' : $followRuleData[0]->id); // sending Job to error
                    }

                    $followerBetAmount = eval('return ' . $followBetRuleData[0]->rule . ';');

                    if ($followerBetAmount <= $followersUserData[0]->balance) {
                        $singleBet = array(
                            'gameID' =>    $event->betData['gameID'],
                            'userID' =>    $eachFollowers->followerID,
                            'betAmount' => $followerBetAmount,
                            'ruleID' => $event->betData['ruleID'],
                            'payout' =>    $event->betData['payout'],
                            'createdDate' => microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d'),
                            'createdTime' => microtimeToDateTime(getCurrentTimeStamp(), false, 'H:i:s'),
                            'UUID' => Uuid::uuid4(),
                            'source' => $event->betData['source'],
                            'parentBetID' => $event->betData['PID'],
                            'followToID' => $event->userID
                        );
                        array_push($bets, $singleBet);

                        $followersUserData[0]->decrement('balance', $followerBetAmount);
                    } else {
                        $followUserProviderRef = new FollowUserProvider(null);

                        $response = $followUserProviderRef->followUser($event->portalProviderUUID, $followersUserData[0]->UUID, $event->userUUID, 2, null, null);
                    }
                }

                if (count($bets) != 0) {
                    $bettingModel->insert($bets);
                }
                DB::commit();
            }

            return true; // exit out with successfully 
        } catch (Exception $e) {
            DB::rollback();

            $msg = 'Error : ' . $e->getMessage() . "\n";
            $msg = $msg . $e->getTraceAsString() . "\n";
            $subject = "ERROR STACK TRACE => JOB (FollowBetsListener) : " . config('app.env');
            $to = config('constants.alert_mail_id');

            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
            throw $e;
        }
    }
}
