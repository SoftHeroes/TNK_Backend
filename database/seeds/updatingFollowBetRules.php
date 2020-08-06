<?php

use App\Models\FollowBetRule;
use Illuminate\Database\Seeder;

class updatingFollowBetRules extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        FollowBetRule::updateOrCreate(
            ['PID' => 1, 'type' => 1, 'name' => 'byAmount'],
            ['rule' => '$value', 'min' => 100, 'max' => 10000]
        );

        FollowBetRule::updateOrCreate(
            ['PID' => 2, 'type' => 1, 'name' => 'byRate'],
            ['rule' => '($value * $betAmount)/100', 'min' => 1, 'max' => 100]
        );

        FollowBetRule::updateOrCreate(
            ['PID' => 3, 'type' => 2, 'name' => 'byTime'],
            ['rule' => '$currentTimeStamp >= $followTimeStamp->addMinute($value)', 'min' => 1440, 'max' => 14400]
        );

        FollowBetRule::updateOrCreate(
            ['PID' => 4, 'type' => 2, 'name' => 'byWin'],
            ['rule' => 'totalFollowWinAmount($followToUserID, $followerID, $fromDate, $fromTime) >= $value', 'min' => 100, 'max' => 10000]
        );

        FollowBetRule::updateOrCreate(
            ['PID' => 5, 'type' => 2, 'name' => 'byLose'],
            ['rule' => 'totalFollowLoseAmount($followToUserID, $followerID, $fromDate, $fromTime) >= $value', 'min' => 100, 'max' => 10000]
        );

        FollowBetRule::updateOrCreate(
            ['PID' => 6, 'type' => 2, 'name' => 'byBets'],
            ['rule' => 'followBetCount($followToUserID, $followerID, $fromDate, $fromTime) >= $value', 'min' => 1, 'max' => 10]
        );

        DB::commit();
    }
}
