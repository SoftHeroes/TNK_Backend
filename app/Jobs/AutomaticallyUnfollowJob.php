<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Models\User;
use App\Jobs\MailJob;
use App\Models\FollowUser;
use App\Models\FollowBetRule;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Providers\Users\UnfollowHelper;
use Illuminate\Queue\InteractsWithQueue;
use App\Providers\Users\FollowUserProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

require_once app_path() . '/Helpers/CommonUtility.php';
require_once app_path() . '/Helpers/UnfollowHelper.php';

class AutomaticallyUnfollowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $followToUserID;
    public $portalProviderUUID;
    public $userUUID;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($followToUserID, $portalProviderUUID, $userUUID)
    {
        $this->followToUserID = $followToUserID;
        $this->portalProviderUUID = $portalProviderUUID;
        $this->userUUID = $userUUID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $followUserRef = new FollowUser();
            $followBetRuleRef = new FollowBetRule();

            $followerData = $followUserRef->getAllFollowers($this->followToUserID)->select('followerID', 'followToID', 'isFollowing', 'followBetRuleID', 'followRuleValue', 'unFollowBetRuleID', 'unFollowRuleValue', 'updatedAt')->get();

            DB::beginTransaction();
            foreach ($followerData as $eachFollowers) { // looping on each follower
                $unfollowRuleData = json_decode($eachFollowers->unFollowRuleValue);

                if (count($unfollowRuleData) == 0) { // check if user have some un-follow setting if not found any stop bellow code running
                    continue;
                }

                $followerID = $eachFollowers->followerID;
                $fromDate = $eachFollowers->updatedAt->format('Y-m-d');
                $fromTime = $eachFollowers->updatedAt->format('H:i:s');
                $followTimeStamp = $eachFollowers->updatedAt;
                $currentTimeStamp = now();
                $followToUserID = $this->followToUserID;

                foreach ($unfollowRuleData as $eachUnfollowRuleData) { // looping on each Un-following Rule
                    $value = $eachUnfollowRuleData->value;
                    $ruleData = $followBetRuleRef->getFollowBetRuleData($eachUnfollowRuleData->id)->select('PID', 'name', 'rule')->get();

                    if ($ruleData->count(DB::raw('1')) == 0) {
                        throw new Exception('No record found in table "followBetRule" for ID : ' . isEmpty($eachUnfollowRuleData->id) ? 'NULL' : $eachUnfollowRuleData->id);
                    }

                    $canUnfollow = eval('return ' . $ruleData[0]->rule . ';');

                    if ($canUnfollow) {
                        $UserModelRef = new User();
                        $followUserProviderRef = new FollowUserProvider(null);

                        $followerData = $UserModelRef->getUserByUserID($followerID)->select('UUID')->get();
                        if ($followerData->count(DB::raw('1')) == 0) {
                            throw new Exception('Follower data not found during Un-following for ID : ' . isEmpty($followerID) ? 'NULL' : $followerID);
                        }

                        $response = $followUserProviderRef->followUser($this->portalProviderUUID, $followerData[0]->UUID, $this->userUUID, 2, null, null);

                        if ($response['res']['status']) { // check if Un-follow successfully then break
                            break;
                        }
                    }
                }
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();

            $msg = 'Error : ' . $e->getMessage() . "\n";
            $msg = $msg . $e->getTraceAsString() . "\n";
            $subject = "ERROR STACK TRACE => JOB (AutomaticallyUnfollow) : " . config('app.env');
            $to = config('constants.alert_mail_id');

            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
            throw $e;
        }
    }
}
