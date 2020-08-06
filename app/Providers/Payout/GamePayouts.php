<?php

namespace App\Providers\Payout;

use DB;
use Exception;
use App\Models\Game;
use App\Models\GameSetup;
use App\Models\InitialOdd;
use App\Models\DynamicOdd;
use App\Models\ProviderGameSetup;
use Illuminate\Support\ServiceProvider;

class GamePayouts extends ServiceProvider
{
    protected $providerGameSetupModelRef;
    protected $initialOddModelRef;
    protected $gameModelRef;
    protected $gameSetupModelRef;


    public function __construct()
    {
        parent::__construct(null);

        $this->gameModelRef = new Game();
        $this->initialOddModelRef = new InitialOdd();
        $this->gameSetupModelRef = new GameSetup();
        $this->providerGameSetupModelRef = new ProviderGameSetup();
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

    /*
    * This function call will create Game Dynamic Odds Initially of all available pending games for given
    * throw : Exception if unable to Play
    */
    public function populateDynamicOddInitially()
    {
        // default response
        $response = array("error" => false, "msg" => "All Starting Payout Setup Successfully.");
        $dynamicOdd = array();

        $foundInitialOdd = false;

        try {

            // getting all unmatched records
            $providerGameSetupIDs = $this->gameModelRef->select('game.providerGameSetupID')->distinct()
                ->leftJoin('dynamicOdd', 'dynamicOdd.gameID', '!=', 'game.PID')
                ->where('game.gameStatus', 0)->pluck('game.providerGameSetupID')->toArray();

            $providerGameSetups = $this->providerGameSetupModelRef // getting All provider Game Setup
                ->select([
                    'PID', 'portalProviderID', 'stockID', 'payoutType',
                    'FD_BigSmallGameID', 'FD_EvenOddGameID', 'FD_LowMiddleHighGameID', 'FD_NumberGameID',
                    'LD_BigSmallGameID', 'LD_EvenOddGameID', 'LD_LowMiddleHighGameID', 'LD_NumberGameID',
                    'TD_BigSmallTieGameID', 'TD_EvenOddGameID', 'TD_LowMiddleHighGameID', 'TD_NumberGameID',
                    'BD_BigSmallTieGameID', 'BD_EvenOddGameID', 'BD_LowMiddleHighGameID', 'BD_NumberGameID'
                ])
                ->whereIn('PID', $providerGameSetupIDs)->where('isActive', 'active')->get();

            foreach ($providerGameSetups as $currentProviderGameSetup) {

                $allPendingGames = $this->gameModelRef->select('PID')->where('providerGameSetupID', $currentProviderGameSetup->PID)->where('gameStatus', 0)->get();

                // code for dynamic order setup : START
                if ($currentProviderGameSetup->payoutType == 2) { // dynamic = 2 : Getting all details from initial odds table by stock ID

                    $tempResponse = $this->getDynamicPayouts($currentProviderGameSetup);

                    if (!$tempResponse['error']) {
                        $foundInitialOdd = true;
                        foreach ($allPendingGames as $currentGame) {
                            $singleDynamicOdd = $tempResponse['data'];
                            $singleDynamicOdd['gameID'] = $currentGame->PID;

                            array_push($dynamicOdd, $singleDynamicOdd);
                        }
                    }
                }
                // code for dynamic order setup : END

                // code for standard order setup : START
                if ($currentProviderGameSetup->payoutType == 1 || !$foundInitialOdd) { // standard = 1 : Getting all details from game Setup table by game ID

                    $tempResponse = $this->getStandardPayouts($currentProviderGameSetup);

                    if ($tempResponse['error']) {
                        throw new Exception($tempResponse["msg"]);
                    }

                    foreach ($allPendingGames as $currentGame) {
                        $singleDynamicOdd = $tempResponse['data'];
                        $singleDynamicOdd['gameID'] = $currentGame->PID;
                        array_push($dynamicOdd, $singleDynamicOdd);
                    }
                }
                // code for standard order setup : END
            }

            DB::beginTransaction();

            foreach (array_chunk($dynamicOdd, config('constants.create_game_odds_chunk')) as $dynamicOddChunk) {
                DynamicOdd::insert($dynamicOddChunk);
            }

            DB::commit();

            return $response;
        } catch (Exception $ex) {
            $response['error'] = true;
            $response['msg'] = $ex->getMessage();

            return $response;
        }
    }

    public function updateInitiallyDynamicOdd(array $providerGameSetupIDs)
    {
        $response = array("error" => false, "msg" => "All Starting Payout Setup Successfully.");

        $foundInitialOdd = false;

        try {

            $providerGameSetups = $this->providerGameSetupModelRef // getting All provider Game Setup
                ->select([
                    'PID', 'portalProviderID', 'stockID', 'payoutType',
                    'FD_BigSmallGameID', 'FD_EvenOddGameID', 'FD_LowMiddleHighGameID', 'FD_NumberGameID',
                    'LD_BigSmallGameID', 'LD_EvenOddGameID', 'LD_LowMiddleHighGameID', 'LD_NumberGameID',
                    'TD_BigSmallTieGameID', 'TD_EvenOddGameID', 'TD_LowMiddleHighGameID', 'TD_NumberGameID',
                    'BD_BigSmallTieGameID', 'BD_EvenOddGameID', 'BD_LowMiddleHighGameID', 'BD_NumberGameID'
                ])
                ->whereIn('PID', $providerGameSetupIDs)->where('isActive', 'active')->get();

            foreach ($providerGameSetups as $currentProviderGameSetup) {
                $allPendingGames = $this->gameModelRef->select('PID')
                    ->where('providerGameSetupID', $currentProviderGameSetup->PID)
                    ->where('gameStatus', 0)
                    ->pluck('PID')->toArray();

                // code for dynamic order setup : START
                if ($currentProviderGameSetup->payoutType == 2) { // dynamic = 2 : Getting all details from initial odds table by stock ID
                    $tempResponse = $this->getDynamicPayouts($currentProviderGameSetup);

                    if ($tempResponse['error']) {
                        break;
                    }

                    $foundInitialOdd = true;

                    $dynamicOdd = $tempResponse['data'];
                }
                if ($currentProviderGameSetup->payoutType == 1 || !$foundInitialOdd) { // standard = 1 : Getting all details from game Setup table by game ID
                    $tempResponse = $this->getStandardPayouts($currentProviderGameSetup);

                    if ($tempResponse['error']) {
                        throw new Exception($tempResponse["msg"]);
                    }

                    $dynamicOdd = $tempResponse['data'];
                }
                // code for dynamic order setup : END

                DynamicOdd::whereIn('PID', $allPendingGames)
                    ->update([
                        'FD_BIG' => $dynamicOdd['FD_BIG'], 'FD_SMALL' => $dynamicOdd['FD_SMALL'], 'FD_ODD' => $dynamicOdd['FD_ODD'], 'FD_EVEN' => $dynamicOdd['FD_EVEN'], 'FD_HIGH' => $dynamicOdd['FD_HIGH'], 'FD_MIDDLE' => $dynamicOdd['FD_MIDDLE'], 'FD_LOW' => $dynamicOdd['FD_LOW'], 'FD_0' => $dynamicOdd['FD_0'], 'FD_1' => $dynamicOdd['FD_1'], 'FD_2' => $dynamicOdd['FD_2'], 'FD_3' => $dynamicOdd['FD_3'], 'FD_4' => $dynamicOdd['FD_4'], 'FD_5' => $dynamicOdd['FD_5'], 'FD_6' => $dynamicOdd['FD_6'], 'FD_7' => $dynamicOdd['FD_7'], 'FD_8' => $dynamicOdd['FD_8'], 'FD_9' => $dynamicOdd['FD_9'],
                        'LD_BIG' => $dynamicOdd['LD_BIG'], 'LD_SMALL' => $dynamicOdd['LD_SMALL'], 'LD_ODD' => $dynamicOdd['LD_ODD'], 'LD_EVEN' => $dynamicOdd['LD_EVEN'], 'LD_HIGH' => $dynamicOdd['LD_HIGH'], 'LD_MIDDLE' => $dynamicOdd['LD_MIDDLE'], 'LD_LOW' => $dynamicOdd['LD_LOW'], 'LD_0' => $dynamicOdd['LD_0'], 'LD_1' => $dynamicOdd['LD_1'], 'LD_2' => $dynamicOdd['LD_2'], 'LD_3' => $dynamicOdd['LD_3'], 'LD_4' => $dynamicOdd['LD_4'], 'LD_5' => $dynamicOdd['LD_5'], 'LD_6' => $dynamicOdd['LD_6'], 'LD_7' => $dynamicOdd['LD_7'], 'LD_8' => $dynamicOdd['LD_8'], 'LD_9' => $dynamicOdd['LD_9'],
                        'TD_BIG' => $dynamicOdd['TD_BIG'], 'TD_SMALL' => $dynamicOdd['TD_SMALL'], 'TD_ODD' => $dynamicOdd['TD_ODD'], 'TD_EVEN' => $dynamicOdd['TD_EVEN'], 'TD_HIGH' => $dynamicOdd['TD_HIGH'], 'TD_MIDDLE' => $dynamicOdd['TD_MIDDLE'], 'TD_LOW' => $dynamicOdd['TD_LOW'], 'TD_0' => $dynamicOdd['TD_0'], 'TD_1' => $dynamicOdd['TD_1'], 'TD_2' => $dynamicOdd['TD_2'], 'TD_3' => $dynamicOdd['TD_3'], 'TD_4' => $dynamicOdd['TD_4'], 'TD_5' => $dynamicOdd['TD_5'], 'TD_6' => $dynamicOdd['TD_6'], 'TD_7' => $dynamicOdd['TD_7'], 'TD_8' => $dynamicOdd['TD_8'], 'TD_9' => $dynamicOdd['TD_9'], 'TD_10' => $dynamicOdd['TD_10'], 'TD_11' => $dynamicOdd['TD_11'], 'TD_12' => $dynamicOdd['TD_12'], 'TD_13' => $dynamicOdd['TD_13'], 'TD_14' => $dynamicOdd['TD_14'], 'TD_15' => $dynamicOdd['TD_15'], 'TD_16' => $dynamicOdd['TD_16'], 'TD_17' => $dynamicOdd['TD_17'], 'TD_18' => $dynamicOdd['TD_18'], 'TD_19' => $dynamicOdd['TD_19'], 'TD_20' => $dynamicOdd['TD_20'], 'TD_21' => $dynamicOdd['TD_21'], 'TD_22' => $dynamicOdd['TD_22'], 'TD_23' => $dynamicOdd['TD_23'], 'TD_24' => $dynamicOdd['TD_24'], 'TD_25' => $dynamicOdd['TD_25'], 'TD_26' => $dynamicOdd['TD_26'], 'TD_27' => $dynamicOdd['TD_27'], 'TD_28' => $dynamicOdd['TD_28'], 'TD_29' => $dynamicOdd['TD_29'], 'TD_30' => $dynamicOdd['TD_30'], 'TD_31' => $dynamicOdd['TD_31'], 'TD_32' => $dynamicOdd['TD_32'], 'TD_33' => $dynamicOdd['TD_33'], 'TD_34' => $dynamicOdd['TD_34'], 'TD_35' => $dynamicOdd['TD_35'], 'TD_36' => $dynamicOdd['TD_36'], 'TD_37' => $dynamicOdd['TD_37'], 'TD_38' => $dynamicOdd['TD_38'], 'TD_39' => $dynamicOdd['TD_39'], 'TD_40' => $dynamicOdd['TD_40'], 'TD_41' => $dynamicOdd['TD_41'], 'TD_42' => $dynamicOdd['TD_42'], 'TD_43' => $dynamicOdd['TD_43'], 'TD_44' => $dynamicOdd['TD_44'], 'TD_45' => $dynamicOdd['TD_45'], 'TD_46' => $dynamicOdd['TD_46'], 'TD_47' => $dynamicOdd['TD_47'], 'TD_48' => $dynamicOdd['TD_48'], 'TD_49' => $dynamicOdd['TD_49'], 'TD_50' => $dynamicOdd['TD_50'], 'TD_51' => $dynamicOdd['TD_51'], 'TD_52' => $dynamicOdd['TD_52'], 'TD_53' => $dynamicOdd['TD_53'], 'TD_54' => $dynamicOdd['TD_54'], 'TD_55' => $dynamicOdd['TD_55'], 'TD_56' => $dynamicOdd['TD_56'], 'TD_57' => $dynamicOdd['TD_57'], 'TD_58' => $dynamicOdd['TD_58'], 'TD_59' => $dynamicOdd['TD_59'], 'TD_60' => $dynamicOdd['TD_60'], 'TD_61' => $dynamicOdd['TD_61'], 'TD_62' => $dynamicOdd['TD_62'], 'TD_63' => $dynamicOdd['TD_63'], 'TD_64' => $dynamicOdd['TD_64'], 'TD_65' => $dynamicOdd['TD_65'], 'TD_66' => $dynamicOdd['TD_66'], 'TD_67' => $dynamicOdd['TD_67'], 'TD_68' => $dynamicOdd['TD_68'], 'TD_69' => $dynamicOdd['TD_69'], 'TD_70' => $dynamicOdd['TD_70'], 'TD_71' => $dynamicOdd['TD_71'], 'TD_72' => $dynamicOdd['TD_72'], 'TD_73' => $dynamicOdd['TD_73'], 'TD_74' => $dynamicOdd['TD_74'], 'TD_75' => $dynamicOdd['TD_75'], 'TD_76' => $dynamicOdd['TD_76'], 'TD_77' => $dynamicOdd['TD_77'], 'TD_78' => $dynamicOdd['TD_78'], 'TD_79' => $dynamicOdd['TD_79'], 'TD_80' => $dynamicOdd['TD_80'], 'TD_81' => $dynamicOdd['TD_81'], 'TD_82' => $dynamicOdd['TD_82'], 'TD_83' => $dynamicOdd['TD_83'], 'TD_84' => $dynamicOdd['TD_84'], 'TD_85' => $dynamicOdd['TD_85'], 'TD_86' => $dynamicOdd['TD_86'], 'TD_87' => $dynamicOdd['TD_87'], 'TD_88' => $dynamicOdd['TD_88'], 'TD_89' => $dynamicOdd['TD_89'], 'TD_90' => $dynamicOdd['TD_90'], 'TD_91' => $dynamicOdd['TD_91'], 'TD_92' => $dynamicOdd['TD_92'], 'TD_93' => $dynamicOdd['TD_93'], 'TD_94' => $dynamicOdd['TD_94'], 'TD_95' => $dynamicOdd['TD_95'], 'TD_96' => $dynamicOdd['TD_96'], 'TD_97' => $dynamicOdd['TD_97'], 'TD_98' => $dynamicOdd['TD_98'], 'TD_99' => $dynamicOdd['TD_99'], 'TD_TIE' => $dynamicOdd['TD_TIE'],
                        'BD_BIG' => $dynamicOdd['BD_BIG'], 'BD_SMALL' => $dynamicOdd['BD_SMALL'], 'BD_ODD' => $dynamicOdd['BD_ODD'], 'BD_EVEN' => $dynamicOdd['BD_EVEN'], 'BD_HIGH' => $dynamicOdd['BD_HIGH'], 'BD_MIDDLE' => $dynamicOdd['BD_MIDDLE'], 'BD_LOW' => $dynamicOdd['BD_LOW'], 'BD_0' => $dynamicOdd['BD_0'], 'BD_1' => $dynamicOdd['BD_1'], 'BD_2' => $dynamicOdd['BD_2'], 'BD_3' => $dynamicOdd['BD_3'], 'BD_4' => $dynamicOdd['BD_4'], 'BD_5' => $dynamicOdd['BD_5'], 'BD_6' => $dynamicOdd['BD_6'], 'BD_7' => $dynamicOdd['BD_7'], 'BD_8' => $dynamicOdd['BD_8'], 'BD_9' => $dynamicOdd['BD_9'], 'BD_10' => $dynamicOdd['BD_10'], 'BD_11' => $dynamicOdd['BD_11'], 'BD_12' => $dynamicOdd['BD_12'], 'BD_13' => $dynamicOdd['BD_13'], 'BD_14' => $dynamicOdd['BD_14'], 'BD_15' => $dynamicOdd['BD_15'], 'BD_16' => $dynamicOdd['BD_16'], 'BD_17' => $dynamicOdd['BD_17'], 'BD_18' => $dynamicOdd['BD_18'], 'BD_TIE'  => $dynamicOdd['BD_TIE']
                    ]);
            }

            return $response;
        } catch (Exception $ex) {
            $response['error'] = true;
            $response['msg'] = $ex->getMessage();

            return $response;
        }
    }

    public function getDynamicPayouts($ProviderGameSetup)
    {
        $response = array("error" => false, "msg" => "All Starting Payout Setup Successfully.", 'data' => 'NA');

        try {
            $initialOdd = $this->initialOddModelRef->select([ // getting initial order by stock
                'stockID', 'FD_BIG', 'FD_SMALL', 'FD_ODD', 'FD_EVEN', 'FD_HIGH', 'FD_MIDDLE', 'FD_LOW', 'FD_0', 'FD_1', 'FD_2', 'FD_3', 'FD_4', 'FD_5', 'FD_6', 'FD_7', 'FD_8', 'FD_9',
                'LD_BIG', 'LD_SMALL', 'LD_ODD', 'LD_EVEN', 'LD_HIGH', 'LD_MIDDLE', 'LD_LOW', 'LD_0', 'LD_1', 'LD_2', 'LD_3', 'LD_4', 'LD_5', 'LD_6', 'LD_7', 'LD_8', 'LD_9',
                'TD_BIG', 'TD_SMALL', 'TD_ODD', 'TD_EVEN', 'TD_HIGH', 'TD_MIDDLE', 'TD_LOW', 'TD_0', 'TD_1', 'TD_2', 'TD_3', 'TD_4', 'TD_5', 'TD_6', 'TD_7', 'TD_8', 'TD_9', 'TD_10', 'TD_11', 'TD_12', 'TD_13', 'TD_14', 'TD_15', 'TD_16', 'TD_17', 'TD_18', 'TD_19', 'TD_20', 'TD_21', 'TD_22', 'TD_23', 'TD_24', 'TD_25', 'TD_26', 'TD_27', 'TD_28', 'TD_29', 'TD_30', 'TD_31', 'TD_32', 'TD_33', 'TD_34', 'TD_35', 'TD_36', 'TD_37', 'TD_38', 'TD_39', 'TD_40', 'TD_41', 'TD_42', 'TD_43', 'TD_44', 'TD_45', 'TD_46', 'TD_47', 'TD_48', 'TD_49', 'TD_50', 'TD_51', 'TD_52', 'TD_53', 'TD_54', 'TD_55', 'TD_56', 'TD_57', 'TD_58', 'TD_59', 'TD_60', 'TD_61', 'TD_62', 'TD_63', 'TD_64', 'TD_65', 'TD_66', 'TD_67', 'TD_68', 'TD_69', 'TD_70', 'TD_71', 'TD_72', 'TD_73', 'TD_74', 'TD_75', 'TD_76', 'TD_77', 'TD_78', 'TD_79', 'TD_80', 'TD_81', 'TD_82', 'TD_83', 'TD_84', 'TD_85', 'TD_86', 'TD_87', 'TD_88', 'TD_89', 'TD_90', 'TD_91', 'TD_92', 'TD_93', 'TD_94', 'TD_95', 'TD_96', 'TD_97', 'TD_98', 'TD_99', 'TD_TIE',
                'BD_BIG', 'BD_SMALL', 'BD_ODD', 'BD_EVEN', 'BD_HIGH', 'BD_MIDDLE', 'BD_LOW', 'BD_0', 'BD_1', 'BD_2', 'BD_3', 'BD_4', 'BD_5', 'BD_6', 'BD_7', 'BD_8', 'BD_9', 'BD_10', 'BD_11', 'BD_12', 'BD_13', 'BD_14', 'BD_15', 'BD_16', 'BD_17', 'BD_18', 'BD_TIE'
            ])->where('isActive', 'active')->where('stockID', $ProviderGameSetup->stockID)->get();

            if ($initialOdd->count(DB::raw('1')) == 0) {
                $response['error'] = true;
                $response['msg'] = 'Initial Odds not found!';
                $response['data'] = 'NA';

                return $response;
            }

            $dynamicOdd = array(
                'stockID' => $ProviderGameSetup->stockID, 'FD_BIG' => $initialOdd[0]->FD_BIG, 'FD_SMALL' => $initialOdd[0]->FD_SMALL, 'FD_ODD' => $initialOdd[0]->FD_ODD, 'FD_EVEN' => $initialOdd[0]->FD_EVEN, 'FD_HIGH' => $initialOdd[0]->FD_HIGH, 'FD_MIDDLE' => $initialOdd[0]->FD_MIDDLE, 'FD_LOW' => $initialOdd[0]->FD_LOW, 'FD_0' => $initialOdd[0]->FD_0, 'FD_1' => $initialOdd[0]->FD_1, 'FD_2' => $initialOdd[0]->FD_2, 'FD_3' => $initialOdd[0]->FD_3, 'FD_4' => $initialOdd[0]->FD_4, 'FD_5' => $initialOdd[0]->FD_5, 'FD_6' => $initialOdd[0]->FD_6, 'FD_7' => $initialOdd[0]->FD_7, 'FD_8' => $initialOdd[0]->FD_8, 'FD_9' => $initialOdd[0]->FD_9,
                'LD_BIG' => $initialOdd[0]->LD_BIG, 'LD_SMALL' => $initialOdd[0]->LD_SMALL, 'LD_ODD' => $initialOdd[0]->LD_ODD, 'LD_EVEN' => $initialOdd[0]->LD_EVEN, 'LD_HIGH' => $initialOdd[0]->LD_HIGH, 'LD_MIDDLE' => $initialOdd[0]->LD_MIDDLE, 'LD_LOW' => $initialOdd[0]->LD_LOW, 'LD_0' => $initialOdd[0]->LD_0, 'LD_1' => $initialOdd[0]->LD_1, 'LD_2' => $initialOdd[0]->LD_2, 'LD_3' => $initialOdd[0]->LD_3, 'LD_4' => $initialOdd[0]->LD_4, 'LD_5' => $initialOdd[0]->LD_5, 'LD_6' => $initialOdd[0]->LD_6, 'LD_7' => $initialOdd[0]->LD_7, 'LD_8' => $initialOdd[0]->LD_8, 'LD_9' => $initialOdd[0]->LD_9,
                'TD_BIG' => $initialOdd[0]->TD_BIG, 'TD_SMALL' => $initialOdd[0]->TD_SMALL, 'TD_TIE' => $initialOdd[0]->TD_TIE, 'TD_ODD' => $initialOdd[0]->TD_ODD, 'TD_EVEN' => $initialOdd[0]->TD_EVEN, 'TD_HIGH' => $initialOdd[0]->TD_HIGH, 'TD_MIDDLE' => $initialOdd[0]->TD_MIDDLE, 'TD_LOW' => $initialOdd[0]->TD_LOW, 'TD_0' => $initialOdd[0]->TD_0, 'TD_1' => $initialOdd[0]->TD_1, 'TD_2' => $initialOdd[0]->TD_2, 'TD_3' => $initialOdd[0]->TD_3, 'TD_4' => $initialOdd[0]->TD_4, 'TD_5' => $initialOdd[0]->TD_5, 'TD_6' => $initialOdd[0]->TD_6, 'TD_7' => $initialOdd[0]->TD_7, 'TD_8' => $initialOdd[0]->TD_8, 'TD_9' => $initialOdd[0]->TD_9, 'TD_10' => $initialOdd[0]->TD_10, 'TD_11' => $initialOdd[0]->TD_11, 'TD_12' => $initialOdd[0]->TD_12, 'TD_13' => $initialOdd[0]->TD_13, 'TD_14' => $initialOdd[0]->TD_14, 'TD_15' => $initialOdd[0]->TD_15, 'TD_16' => $initialOdd[0]->TD_16, 'TD_17' => $initialOdd[0]->TD_17, 'TD_18' => $initialOdd[0]->TD_18, 'TD_19' => $initialOdd[0]->TD_19, 'TD_20' => $initialOdd[0]->TD_20, 'TD_21' => $initialOdd[0]->TD_21, 'TD_22' => $initialOdd[0]->TD_22, 'TD_23' => $initialOdd[0]->TD_23, 'TD_24' => $initialOdd[0]->TD_24, 'TD_25' => $initialOdd[0]->TD_25, 'TD_26' => $initialOdd[0]->TD_26, 'TD_27' => $initialOdd[0]->TD_27, 'TD_28' => $initialOdd[0]->TD_28, 'TD_29' => $initialOdd[0]->TD_29, 'TD_30' => $initialOdd[0]->TD_30, 'TD_31' => $initialOdd[0]->TD_31, 'TD_32' => $initialOdd[0]->TD_32, 'TD_33' => $initialOdd[0]->TD_33, 'TD_34' => $initialOdd[0]->TD_34, 'TD_35' => $initialOdd[0]->TD_35, 'TD_36' => $initialOdd[0]->TD_36, 'TD_37' => $initialOdd[0]->TD_37, 'TD_38' => $initialOdd[0]->TD_38, 'TD_39' => $initialOdd[0]->TD_39, 'TD_40' => $initialOdd[0]->TD_40, 'TD_41' => $initialOdd[0]->TD_41, 'TD_42' => $initialOdd[0]->TD_42, 'TD_43' => $initialOdd[0]->TD_43, 'TD_44' => $initialOdd[0]->TD_44, 'TD_45' => $initialOdd[0]->TD_45, 'TD_46' => $initialOdd[0]->TD_46, 'TD_47' => $initialOdd[0]->TD_47, 'TD_48' => $initialOdd[0]->TD_48, 'TD_49' => $initialOdd[0]->TD_49, 'TD_50' => $initialOdd[0]->TD_50, 'TD_51' => $initialOdd[0]->TD_51, 'TD_52' => $initialOdd[0]->TD_52, 'TD_53' => $initialOdd[0]->TD_53, 'TD_54' => $initialOdd[0]->TD_54, 'TD_55' => $initialOdd[0]->TD_55, 'TD_56' => $initialOdd[0]->TD_56, 'TD_57' => $initialOdd[0]->TD_57, 'TD_58' => $initialOdd[0]->TD_58, 'TD_59' => $initialOdd[0]->TD_59, 'TD_60' => $initialOdd[0]->TD_60, 'TD_61' => $initialOdd[0]->TD_61, 'TD_62' => $initialOdd[0]->TD_62, 'TD_63' => $initialOdd[0]->TD_63, 'TD_64' => $initialOdd[0]->TD_64, 'TD_65' => $initialOdd[0]->TD_65, 'TD_66' => $initialOdd[0]->TD_66, 'TD_67' => $initialOdd[0]->TD_67, 'TD_68' => $initialOdd[0]->TD_68, 'TD_69' => $initialOdd[0]->TD_69, 'TD_70' => $initialOdd[0]->TD_70, 'TD_71' => $initialOdd[0]->TD_71, 'TD_72' => $initialOdd[0]->TD_72, 'TD_73' => $initialOdd[0]->TD_73, 'TD_74' => $initialOdd[0]->TD_74, 'TD_75' => $initialOdd[0]->TD_75, 'TD_76' => $initialOdd[0]->TD_76, 'TD_77' => $initialOdd[0]->TD_77, 'TD_78' => $initialOdd[0]->TD_78, 'TD_79' => $initialOdd[0]->TD_79, 'TD_80' => $initialOdd[0]->TD_80, 'TD_81' => $initialOdd[0]->TD_81, 'TD_82' => $initialOdd[0]->TD_82, 'TD_83' => $initialOdd[0]->TD_83, 'TD_84' => $initialOdd[0]->TD_84, 'TD_85' => $initialOdd[0]->TD_85, 'TD_86' => $initialOdd[0]->TD_86, 'TD_87' => $initialOdd[0]->TD_87, 'TD_88' => $initialOdd[0]->TD_88, 'TD_89' => $initialOdd[0]->TD_89, 'TD_90' => $initialOdd[0]->TD_90, 'TD_91' => $initialOdd[0]->TD_91, 'TD_92' => $initialOdd[0]->TD_92, 'TD_93' => $initialOdd[0]->TD_93, 'TD_94' => $initialOdd[0]->TD_94, 'TD_95' => $initialOdd[0]->TD_95, 'TD_96' => $initialOdd[0]->TD_96, 'TD_97' => $initialOdd[0]->TD_97, 'TD_98' => $initialOdd[0]->TD_98, 'TD_99' => $initialOdd[0]->TD_99,
                'BD_BIG' => $initialOdd[0]->BD_BIG, 'BD_SMALL' => $initialOdd[0]->BD_SMALL, 'BD_TIE' => $initialOdd[0]->BD_TIE, 'BD_ODD' => $initialOdd[0]->BD_ODD, 'BD_EVEN' => $initialOdd[0]->BD_EVEN, 'BD_HIGH' => $initialOdd[0]->BD_HIGH, 'BD_MIDDLE' => $initialOdd[0]->BD_MIDDLE, 'BD_LOW' => $initialOdd[0]->BD_LOW, 'BD_0' => $initialOdd[0]->BD_0, 'BD_1' => $initialOdd[0]->BD_1, 'BD_2' => $initialOdd[0]->BD_2, 'BD_3' => $initialOdd[0]->BD_3, 'BD_4' => $initialOdd[0]->BD_4, 'BD_5' => $initialOdd[0]->BD_5, 'BD_6' => $initialOdd[0]->BD_6, 'BD_7' => $initialOdd[0]->BD_7, 'BD_8' => $initialOdd[0]->BD_8, 'BD_9' => $initialOdd[0]->BD_9, 'BD_10' => $initialOdd[0]->BD_10, 'BD_11' => $initialOdd[0]->BD_11, 'BD_12' => $initialOdd[0]->BD_12, 'BD_13' => $initialOdd[0]->BD_13, 'BD_14' => $initialOdd[0]->BD_14, 'BD_15' => $initialOdd[0]->BD_15, 'BD_16' => $initialOdd[0]->BD_16, 'BD_17' => $initialOdd[0]->BD_17, 'BD_18' => $initialOdd[0]->BD_18
            );

            $response['data'] = $dynamicOdd;
            return $response;
        } catch (Exception $ex) {
            $response['error'] = true;
            $response['msg'] = $ex->getMessage();

            return $response;
        }
    }

    public function getStandardPayouts($ProviderGameSetup)
    {
        $response = array("error" => false, "msg" => "All Starting Payout Setup Successfully.", 'data' => 'NA');

        try {
            $gameCommission = array( // creating Array with all commission details
                'FD_BigSmall' => 0, 'FD_OddEven' => 0, 'FD_HighMiddleLow' => 0, 'FD_Number' => 0,
                'LD_BigSmall' => 0, 'LD_OddEven' => 0, 'LD_HighMiddleLow' => 0, 'LD_Number' => 0,
                'TD_BigSmallTie' => 0, 'TD_OddEven' => 0, 'TD_HighMiddleLow' => 0, 'TD_Number' => 0,
                'BD_BigSmallTie' => 0, 'BD_OddEven' => 0, 'BD_HighMiddleLow' => 0, 'BD_Number' => 0,
            );

            $gameSetup = $this->gameSetupModelRef->select(['gameName', 'initialOdd', 'commission'])->whereIn('PID', [
                $ProviderGameSetup->FD_BigSmallGameID, $ProviderGameSetup->FD_EvenOddGameID, $ProviderGameSetup->FD_LowMiddleHighGameID, $ProviderGameSetup->FD_NumberGameID,
                $ProviderGameSetup->LD_BigSmallGameID, $ProviderGameSetup->LD_EvenOddGameID, $ProviderGameSetup->LD_LowMiddleHighGameID, $ProviderGameSetup->LD_NumberGameID,
                $ProviderGameSetup->TD_BigSmallTieGameID, $ProviderGameSetup->TD_EvenOddGameID, $ProviderGameSetup->TD_LowMiddleHighGameID, $ProviderGameSetup->TD_NumberGameID,
                $ProviderGameSetup->BD_BigSmallTieGameID, $ProviderGameSetup->BD_EvenOddGameID, $ProviderGameSetup->BD_LowMiddleHighGameID, $ProviderGameSetup->BD_NumberGameID
            ])->get();

            if ($gameSetup->count(DB::raw('1')) == 0) {
                $response['error'] = true;
                $response['msg'] = "Game setup not found.";
                return $response;
            }

            foreach ($gameSetup as $currentGameSetup) { // populating value in commission array
                $gameCommission[$currentGameSetup->gameName] = $currentGameSetup->initialOdd - ($currentGameSetup->commission / 100);
            }

            $standardOdds = array(
                'stockID' => $ProviderGameSetup->stockID, 'FD_BIG' => $gameCommission['FD_BigSmall'], 'FD_SMALL' => $gameCommission['FD_BigSmall'], 'FD_ODD' => $gameCommission['FD_OddEven'], 'FD_EVEN' => $gameCommission['FD_OddEven'], 'FD_HIGH' => $gameCommission['FD_HighMiddleLow'], 'FD_MIDDLE' => $gameCommission['FD_HighMiddleLow'], 'FD_LOW' => $gameCommission['FD_HighMiddleLow'], 'FD_0' => $gameCommission['FD_Number'], 'FD_1' => $gameCommission['FD_Number'], 'FD_2' => $gameCommission['FD_Number'], 'FD_3' => $gameCommission['FD_Number'], 'FD_4' => $gameCommission['FD_Number'], 'FD_5' => $gameCommission['FD_Number'], 'FD_6' => $gameCommission['FD_Number'], 'FD_7' => $gameCommission['FD_Number'], 'FD_8' => $gameCommission['FD_Number'], 'FD_9' => $gameCommission['FD_Number'],
                'LD_BIG' => $gameCommission['LD_BigSmall'], 'LD_SMALL' => $gameCommission['LD_BigSmall'], 'LD_ODD' => $gameCommission['LD_OddEven'], 'LD_EVEN' => $gameCommission['LD_OddEven'], 'LD_HIGH' => $gameCommission['LD_HighMiddleLow'], 'LD_MIDDLE' => $gameCommission['LD_HighMiddleLow'], 'LD_LOW' => $gameCommission['LD_HighMiddleLow'], 'LD_0' => $gameCommission['LD_Number'], 'LD_1' => $gameCommission['LD_Number'], 'LD_2' => $gameCommission['LD_Number'], 'LD_3' => $gameCommission['LD_Number'], 'LD_4' => $gameCommission['LD_Number'], 'LD_5' => $gameCommission['LD_Number'], 'LD_6' => $gameCommission['LD_Number'], 'LD_7' => $gameCommission['LD_Number'], 'LD_8' => $gameCommission['LD_Number'], 'LD_9' => $gameCommission['LD_Number'],
                'TD_BIG' => $gameCommission['TD_BigSmallTie'], 'TD_SMALL' => $gameCommission['TD_BigSmallTie'], 'TD_TIE' => $gameCommission['TD_BigSmallTie'], 'TD_ODD' => $gameCommission['TD_OddEven'], 'TD_EVEN' => $gameCommission['TD_OddEven'], 'TD_HIGH' => $gameCommission['TD_HighMiddleLow'], 'TD_MIDDLE' => $gameCommission['TD_HighMiddleLow'], 'TD_LOW' => $gameCommission['TD_HighMiddleLow'], 'TD_0' => $gameCommission['TD_Number'], 'TD_1' => $gameCommission['TD_Number'], 'TD_2' => $gameCommission['TD_Number'], 'TD_3' => $gameCommission['TD_Number'], 'TD_4' => $gameCommission['TD_Number'], 'TD_5' => $gameCommission['TD_Number'], 'TD_6' => $gameCommission['TD_Number'], 'TD_7' => $gameCommission['TD_Number'], 'TD_8' => $gameCommission['TD_Number'], 'TD_9' => $gameCommission['TD_Number'], 'TD_10' => $gameCommission['TD_Number'], 'TD_11' => $gameCommission['TD_Number'], 'TD_12' => $gameCommission['TD_Number'], 'TD_13' => $gameCommission['TD_Number'], 'TD_14' => $gameCommission['TD_Number'], 'TD_15' => $gameCommission['TD_Number'], 'TD_16' => $gameCommission['TD_Number'], 'TD_17' => $gameCommission['TD_Number'], 'TD_18' => $gameCommission['TD_Number'], 'TD_19' => $gameCommission['TD_Number'], 'TD_20' => $gameCommission['TD_Number'], 'TD_21' => $gameCommission['TD_Number'], 'TD_22' => $gameCommission['TD_Number'], 'TD_23' => $gameCommission['TD_Number'], 'TD_24' => $gameCommission['TD_Number'], 'TD_25' => $gameCommission['TD_Number'], 'TD_26' => $gameCommission['TD_Number'], 'TD_27' => $gameCommission['TD_Number'], 'TD_28' => $gameCommission['TD_Number'], 'TD_29' => $gameCommission['TD_Number'], 'TD_30' => $gameCommission['TD_Number'], 'TD_31' => $gameCommission['TD_Number'], 'TD_32' => $gameCommission['TD_Number'], 'TD_33' => $gameCommission['TD_Number'], 'TD_34' => $gameCommission['TD_Number'], 'TD_35' => $gameCommission['TD_Number'], 'TD_36' => $gameCommission['TD_Number'], 'TD_37' => $gameCommission['TD_Number'], 'TD_38' => $gameCommission['TD_Number'], 'TD_39' => $gameCommission['TD_Number'], 'TD_40' => $gameCommission['TD_Number'], 'TD_41' => $gameCommission['TD_Number'], 'TD_42' => $gameCommission['TD_Number'], 'TD_43' => $gameCommission['TD_Number'], 'TD_44' => $gameCommission['TD_Number'], 'TD_45' => $gameCommission['TD_Number'], 'TD_46' => $gameCommission['TD_Number'], 'TD_47' => $gameCommission['TD_Number'], 'TD_48' => $gameCommission['TD_Number'], 'TD_49' => $gameCommission['TD_Number'], 'TD_50' => $gameCommission['TD_Number'], 'TD_51' => $gameCommission['TD_Number'], 'TD_52' => $gameCommission['TD_Number'], 'TD_53' => $gameCommission['TD_Number'], 'TD_54' => $gameCommission['TD_Number'], 'TD_55' => $gameCommission['TD_Number'], 'TD_56' => $gameCommission['TD_Number'], 'TD_57' => $gameCommission['TD_Number'], 'TD_58' => $gameCommission['TD_Number'], 'TD_59' => $gameCommission['TD_Number'], 'TD_60' => $gameCommission['TD_Number'], 'TD_61' => $gameCommission['TD_Number'], 'TD_62' => $gameCommission['TD_Number'], 'TD_63' => $gameCommission['TD_Number'], 'TD_64' => $gameCommission['TD_Number'], 'TD_65' => $gameCommission['TD_Number'], 'TD_66' => $gameCommission['TD_Number'], 'TD_67' => $gameCommission['TD_Number'], 'TD_68' => $gameCommission['TD_Number'], 'TD_69' => $gameCommission['TD_Number'], 'TD_70' => $gameCommission['TD_Number'], 'TD_71' => $gameCommission['TD_Number'], 'TD_72' => $gameCommission['TD_Number'], 'TD_73' => $gameCommission['TD_Number'], 'TD_74' => $gameCommission['TD_Number'], 'TD_75' => $gameCommission['TD_Number'], 'TD_76' => $gameCommission['TD_Number'], 'TD_77' => $gameCommission['TD_Number'], 'TD_78' => $gameCommission['TD_Number'], 'TD_79' => $gameCommission['TD_Number'], 'TD_80' => $gameCommission['TD_Number'], 'TD_81' => $gameCommission['TD_Number'], 'TD_82' => $gameCommission['TD_Number'], 'TD_83' => $gameCommission['TD_Number'], 'TD_84' => $gameCommission['TD_Number'], 'TD_85' => $gameCommission['TD_Number'], 'TD_86' => $gameCommission['TD_Number'], 'TD_87' => $gameCommission['TD_Number'], 'TD_88' => $gameCommission['TD_Number'], 'TD_89' => $gameCommission['TD_Number'], 'TD_90' => $gameCommission['TD_Number'], 'TD_91' => $gameCommission['TD_Number'], 'TD_92' => $gameCommission['TD_Number'], 'TD_93' => $gameCommission['TD_Number'], 'TD_94' => $gameCommission['TD_Number'], 'TD_95' => $gameCommission['TD_Number'], 'TD_96' => $gameCommission['TD_Number'], 'TD_97' => $gameCommission['TD_Number'], 'TD_98' => $gameCommission['TD_Number'], 'TD_99' => $gameCommission['TD_Number'],
                'BD_BIG' => $gameCommission['BD_BigSmallTie'], 'BD_SMALL' => $gameCommission['BD_BigSmallTie'], 'BD_TIE' => $gameCommission['BD_BigSmallTie'], 'BD_ODD' => $gameCommission['BD_OddEven'], 'BD_EVEN' => $gameCommission['BD_OddEven'], 'BD_HIGH' => $gameCommission['BD_HighMiddleLow'], 'BD_MIDDLE' => $gameCommission['BD_HighMiddleLow'], 'BD_LOW' => $gameCommission['BD_HighMiddleLow'], 'BD_0' => $gameCommission['BD_Number'], 'BD_1' => $gameCommission['BD_Number'], 'BD_2' => $gameCommission['BD_Number'], 'BD_3' => $gameCommission['BD_Number'], 'BD_4' => $gameCommission['BD_Number'], 'BD_5' => $gameCommission['BD_Number'], 'BD_6' => $gameCommission['BD_Number'], 'BD_7' => $gameCommission['BD_Number'], 'BD_8' => $gameCommission['BD_Number'], 'BD_9' => $gameCommission['BD_Number'], 'BD_10' => $gameCommission['BD_Number'], 'BD_11' => $gameCommission['BD_Number'], 'BD_12' => $gameCommission['BD_Number'], 'BD_13' => $gameCommission['BD_Number'], 'BD_14' => $gameCommission['BD_Number'], 'BD_15' => $gameCommission['BD_Number'], 'BD_16' => $gameCommission['BD_Number'], 'BD_17' => $gameCommission['BD_Number'], 'BD_18' => $gameCommission['BD_Number'],
            );

            $response['data'] = $standardOdds;

            return $response;
        } catch (Exception $ex) {
            $response['error'] = true;
            $response['msg'] = $ex->getMessage();

            return $response;
        }
    }
}
