<?php

namespace App\Providers\Payout;

use DB;
use Exception;
use App\Models\Rule;
use App\Models\Game;
use App\Models\Betting;
use App\Models\GameSetup;
use App\Models\DynamicOdd;
use App\Providers\Payout\GamePayouts;
use App\Jobs\MailJob;
use Illuminate\Support\ServiceProvider;

class DynamicPayoutProvider extends ServiceProvider
{
    protected $ruleModelRef;
    protected $gameModelRef;
    protected $bettingModelRef;
    protected $gamePayoutProviderRef;


    public function __construct()
    {
        parent::__construct(null);

        $this->ruleModelRef = new Rule();
        $this->gameModelRef = new Game();
        $this->bettingModelRef = new Betting();
        $this->gamePayoutProviderRef = new GamePayouts();
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

    /**
     * Call Calculate Dynamic Odds by Game Setup Array
     * @return bool
     * @throws ExceptionClass
     */
    public function callCalculateDynamicOddByGameSetupIDs($gameID, array $gameSetupIDs)
    {
        foreach ($gameSetupIDs as $currentGameSetupIDs) {
            $gameSetupRecord =  GameSetup::select('rulesID', 'initialOdd', 'commission')->where('PID', $currentGameSetupIDs)->get();

            if ($gameSetupRecord->count(DB::raw('1')) != 0) {
                $tempResponse = $this->calculateDynamicOdd($gameID, $gameSetupRecord[0]->commission, $gameSetupRecord[0]->initialOdd, explode(',', $gameSetupRecord[0]->rulesID));
                if ($tempResponse['error']) {
                    throw new Exception($tempResponse['msg']);
                }
            } else {
                throw new Exception($tempResponse['Game Setup not found!.']);
            }
        }
        return true;
    }


    /**
     *  Calculate Dynamic Odds by Game ID And rule ID
     * @return array
     */
    public function calculateDynamicOddByGameID($gameID)
    {

        try {

            $providerGameSetup = $this->gameModelRef->getProviderGameSetupByGameIDs([$gameID])->select([
                'providerGameSetup.payoutType',
                'providerGameSetup.FD_BigSmallGameID', 'providerGameSetup.FD_EvenOddGameID', 'providerGameSetup.FD_LowMiddleHighGameID', 'providerGameSetup.FD_NumberGameID',
                'providerGameSetup.LD_BigSmallGameID', 'providerGameSetup.LD_EvenOddGameID', 'providerGameSetup.LD_LowMiddleHighGameID', 'providerGameSetup.LD_NumberGameID',
                'providerGameSetup.TD_BigSmallTieGameID', 'providerGameSetup.TD_EvenOddGameID', 'providerGameSetup.TD_LowMiddleHighGameID', 'providerGameSetup.TD_NumberGameID',
                'providerGameSetup.BD_BigSmallTieGameID', 'providerGameSetup.BD_EvenOddGameID', 'providerGameSetup.BD_LowMiddleHighGameID', 'providerGameSetup.BD_NumberGameID'
            ])->get();

            if ($providerGameSetup->count(DB::raw('1')) == 0) {
                throw new Exception('rule name not found!');
            }

            if ($providerGameSetup[0]->payoutType == 1) { // standard = 1
                $response['msg'] = 'Payout Type : standard';
                return $response;
            }

            if ($providerGameSetup[0]->payoutType == 2) { // dynamic = 2
                $response['msg'] = 'Payout Type : dynamic';
                $this->callCalculateDynamicOddByGameSetupIDs(
                    $gameID,
                    [
                        $providerGameSetup[0]->FD_BigSmallGameID,
                        $providerGameSetup[0]->FD_EvenOddGameID,
                        $providerGameSetup[0]->FD_LowMiddleHighGameID,
                        $providerGameSetup[0]->FD_NumberGameID,
                        $providerGameSetup[0]->LD_BigSmallGameID,
                        $providerGameSetup[0]->LD_EvenOddGameID,
                        $providerGameSetup[0]->LD_LowMiddleHighGameID,
                        $providerGameSetup[0]->LD_NumberGameID,
                        $providerGameSetup[0]->TD_BigSmallTieGameID,
                        $providerGameSetup[0]->TD_EvenOddGameID,
                        $providerGameSetup[0]->TD_LowMiddleHighGameID,
                        $providerGameSetup[0]->TD_NumberGameID,
                        $providerGameSetup[0]->BD_BigSmallTieGameID,
                        $providerGameSetup[0]->BD_EvenOddGameID,
                        $providerGameSetup[0]->BD_LowMiddleHighGameID,
                        $providerGameSetup[0]->BD_NumberGameID
                    ]
                );
            }

            return $response;
        } catch (Exception $e) {

            $to = config('constants.alert_mail_id');
            $msg = $e->getMessage();
            $subject = "Calculation of Dynamic Odd has caught an exception : " . config('app.env');

            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');

            throw $e;
        }
    }

    /**
     *  Calculate Dynamic Odds
     *  Note : Only send Payout Type = dynamic
     *  - gameIDs = All gameId as Array
     * @return array
     */
    public function calculateDynamicOdd($gameID, $commission, $maxOdds, array $ruleIDs)
    {
        $response = array("error" => false, "msg" => "Dynamic Odds Calculated Successfully.");
        $dynamicOdd = array();
        $isAllBetRuleAmountEqual = true;

        try {
            $sumOfAllBetAmountsByRules = $this->bettingModelRef->getSumOfAllBetAmounts($gameID, $ruleIDs);

            $sumOfAllBetAmountsByRulesCount = $sumOfAllBetAmountsByRules->count(DB::raw('1'));
            if ($sumOfAllBetAmountsByRulesCount == 0) {
                $response['msg'] = "No bet found!.";
                return $response;
            }

            // checking if all bet have equal amounts : START

            $firstRuleAmount = $sumOfAllBetAmountsByRules[0]->totalAmount;

            $isAllBetRuleAmountEqual = (count($ruleIDs) == $sumOfAllBetAmountsByRulesCount);

            if ($isAllBetRuleAmountEqual) {
                foreach ($sumOfAllBetAmountsByRules as $currentSumOfAllBetAmountsByRules) { // check if All Bet Rule Amount are Equal or not,
                    if ($firstRuleAmount != $currentSumOfAllBetAmountsByRules->totalAmount) {
                        $isAllBetRuleAmountEqual = false;
                        break;
                    }
                }
            }

            // checking if all bet have equal amounts : START

            $total = 0;
            foreach ($sumOfAllBetAmountsByRules as $currentSumOfAllBetAmountsByRules) { // get total on games like : bigSmall,EvenOdd etc.,
                $total += $currentSumOfAllBetAmountsByRules->totalAmount;
            }

            $commissionAmount = $total * ($commission / 100);
            $winningAmount = $total - $commissionAmount;

            // getting initial odds : START
            $payouts = array();

            if ($isAllBetRuleAmountEqual) { // only Calculate initial odds when All bet Amount are same
                $providerGameSetups = $this->gameModelRef->getProviderGameSetupByGameIDs([$gameID])->select([
                    'providerGameSetup.PID', 'providerGameSetup.portalProviderID', 'providerGameSetup.stockID', 'providerGameSetup.payoutType',
                    'providerGameSetup.FD_BigSmallGameID', 'providerGameSetup.FD_EvenOddGameID', 'providerGameSetup.FD_LowMiddleHighGameID', 'providerGameSetup.FD_NumberGameID',
                    'providerGameSetup.LD_BigSmallGameID', 'providerGameSetup.LD_EvenOddGameID', 'providerGameSetup.LD_LowMiddleHighGameID', 'providerGameSetup.LD_NumberGameID',
                    'providerGameSetup.TD_BigSmallTieGameID', 'providerGameSetup.TD_EvenOddGameID', 'providerGameSetup.TD_LowMiddleHighGameID', 'providerGameSetup.TD_NumberGameID',
                    'providerGameSetup.BD_BigSmallTieGameID', 'providerGameSetup.BD_EvenOddGameID', 'providerGameSetup.BD_LowMiddleHighGameID', 'providerGameSetup.BD_NumberGameID'
                ])->get();

                if ($providerGameSetups->count(DB::raw('1')) == 0) {
                    throw new Exception('Provider Game Setup not found!');
                }

                $dynamicPayoutsResponse = $this->gamePayoutProviderRef->getDynamicPayouts($providerGameSetups[0]);          // getting value from initial odds table

                if (!$dynamicPayoutsResponse['error']) {
                    $payouts = $dynamicPayoutsResponse['data'];
                } else {
                    $standardPayoutsResponse = $this->gamePayoutProviderRef->getStandardPayouts($providerGameSetups[0]);    // getting value from Game Setup table
                    if (!$standardPayoutsResponse['error']) {
                        $payouts = $standardPayoutsResponse['data'];
                    } else {
                        throw new Exception('Game Commission not found!');
                    }
                }
                // getting initial odds : END
            }

            foreach ($sumOfAllBetAmountsByRules as $currentSumOfAllBetAmountsByRules) { // creating dynamic Odds update array

                $ruleName = "";
                // getting rule name : START
                $ruleResult = $this->ruleModelRef->getRuleData($currentSumOfAllBetAmountsByRules->ruleID);
                if ($ruleResult->count(DB::raw('1')) == 0) {
                    throw new Exception('rule name not found!');
                } else {
                    $ruleName = $ruleResult[0]->name;
                }
                // getting rule name : END

                if ($isAllBetRuleAmountEqual) { // if all rule bet amount are equal se initial odds
                    $dynamicOdd[$ruleName] = round($payouts[$ruleName], 2);
                } elseif ($currentSumOfAllBetAmountsByRules->totalAmount != 0) {
                    $odds = $winningAmount / $currentSumOfAllBetAmountsByRules->totalAmount;

                    if ($odds > $maxOdds) {
                        $odds = $maxOdds - ($commission / 100);
                    } elseif ($odds < 1.01) {
                        $odds = 1.01;
                    }

                    $dynamicOdd[$ruleName] = round($odds, 2);
                }
                // if bet amount is Zero(0) on change

            }

            // Update dynamic odds
            DB::beginTransaction();
            DynamicOdd::where("gameID", $gameID)->update($dynamicOdd);
            DB::commit();

            return $response;
        } catch (Exception $e) {
            DB::rollback();
            $response['error'] = true;
            $response['msg'] = $e->getMessage();

            return $response;
        }
    }
}
