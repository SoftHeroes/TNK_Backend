<?php

use Illuminate\Database\Seeder;
use App\Models\FollowBetRule;
use App\Models\FollowBetSetup;
use App\Models\ProviderConfig;

class configFollow extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $followBetRules = [
            array('PID' => 1, 'type' => 1, 'name' => 'byAmount', 'rule' => '$value', 'min' => 1, 'max' => 100000),
            array('PID' => 2, 'type' => 1, 'name' => 'byRate', 'rule' => '($value * $betAmount)/100', 'min' => 1, 'max' => 100),
            array('PID' => 3, 'type' => 2, 'name' => 'byTime', 'rule' => null, 'min' => 10, 'max' => 14400), //in minutes (min 10 min and max 10 days)
            array('PID' => 4, 'type' => 2, 'name' => 'byWin', 'rule' => null, 'min' => 1, 'max' => 100000),
            array('PID' => 5, 'type' => 2, 'name' => 'byLose', 'rule' => null, 'min' => 1, 'max' => 100000),
            array('PID' => 6, 'type' => 2, 'name' => 'byBets', 'rule' => null, 'min' => 1, 'max' => 100)
        ];
        FollowBetRule::insert($followBetRules);


        $followBetSetup = [
            array('PID' => 1, 'followBetRuleID' => null, 'unFollowBetRuleID' => null, 'minFollowBetRuleSelect' => null, 'maxFollowBetRuleSelect' => null, 'minUnFollowBetRuleSelect' => null, 'maxUnFollowBetRuleSelect' => null),  // Normal Follow
            array('PID' => 2, 'followBetRuleID' => '1,2', 'unFollowBetRuleID' => null, 'minFollowBetRuleSelect' => 1, 'maxFollowBetRuleSelect' => 1, 'minUnFollowBetRuleSelect' => null, 'maxUnFollowBetRuleSelect' => null),       // only Copy bet with Follow
            array('PID' => 3, 'followBetRuleID' => '1,2', 'unFollowBetRuleID' => '3,4,5,6', 'minFollowBetRuleSelect' => 1, 'maxFollowBetRuleSelect' => 1, 'minUnFollowBetRuleSelect' => 1, 'maxUnFollowBetRuleSelect' => 1),        // With Single Copy Bets and Unfollow bets
            array('PID' => 4, 'followBetRuleID' => '1,2', 'unFollowBetRuleID' => '3,4,5,6', 'minFollowBetRuleSelect' => 1, 'maxFollowBetRuleSelect' => 1, 'minUnFollowBetRuleSelect' => 1, 'maxUnFollowBetRuleSelect' => 4)         // With Single Copy Bets and Multiple Unfollow bets
        ];
        FollowBetSetup::insert($followBetSetup);


        $config = [
            array('PID' => 1, 'portalProviderID' => 1, 'followBetSetupID' => null),
            array('PID' => 2, 'portalProviderID' => 2, 'followBetSetupID' => 3),
            array('PID' => 3, 'portalProviderID' => 3, 'followBetSetupID' => 4),
            array('PID' => 4, 'portalProviderID' => 4, 'followBetSetupID' => 3)
        ];
        ProviderConfig::insert($config);
    }
}
