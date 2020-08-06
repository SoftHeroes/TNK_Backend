<?php

namespace App\Console\Commands;

use DB;
use DateTime;
use Exception;
use DateTimeZone;
use App\Models\User;
use App\Models\Game;
use App\Models\Stock;
use App\Jobs\MailJob;
use App\Models\Betting;
use App\Models\IdLookup;
use App\Models\ProviderConfig;
use App\Jobs\LogoutAPICallJob;
use App\Models\PortalProvider;
use Illuminate\Console\Command;
use App\Events\Backend\PoolLogEvent;
use App\Jobs\AutomaticallyUnfollowJob;
use App\Providers\Stock\StockProvider;
use App\Events\Socket\BalanceUpdateEvent;
use App\Http\Controllers\ResponseController as Res;

require_once app_path() . '/Helpers/APICall.php';
require_once app_path() . '/Helpers/CommonUtility.php';

class CalculateGameResults extends Command
{

    protected $gameModelRef;        // game Model Reference
    protected $stockModelRef;       // stock Model Reference
    protected $bettingModelRef;     // betting Model Reference
    protected $stockProviderRef;    // stock provider Reference

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'while:calculateGameResults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will runs in infinity loop and calculate all Game Results';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->gameModelRef = new Game();
        $this->stockModelRef = new Stock();
        $this->bettingModelRef = new Betting();

        $this->stockProviderRef = new StockProvider(null);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $number_of_attempts =  config('app.number_of_game_calc_attempts');
        $attempts = 0;
        $count = 0;
        $gameID = 0;

        while (true) {
            try {

                $allEndsGames = $this->gameModelRef->getAllCloseGameDetails()->select(['PID', 'providerGameSetupID', 'stockID', 'endDate', 'endTime', 'UUID'])->get(); // Getting all ended games list

                $totalAllEndGames = $allEndsGames->count(DB::raw('1'));

                $stockEndValue = 0;
                $foundStockValue = false;


                foreach ($allEndsGames as $singleGame) { // Looping on each game

                    $gameEndTimestamp = $singleGame->endDate . ' ' . $singleGame->endTime;
                    $gameID = $singleGame->PID;

                    $stockDetails = $this->stockModelRef->getStockDetails($singleGame->stockID)
                        ->select(['PID', 'name', 'url', 'method', 'country', 'stockLoop', 'limitTag', 'openTimeRange', 'timeZone', 'precision', 'responseType', 'responseStockOpenTag', 'responseStockTimeTag', 'responseStockTimeZone', 'responseStockTimeFormat', 'responseStockDataTag', 'replaceJsonRules'])
                        ->get();

                    $betAllDetails = $this->bettingModelRef
                        ->getAllBetDetailsByGameID($singleGame->PID, [-1])
                        ->select([
                            'betting.PID as betID',
                            'betting.gameID',
                            'betting.betAmount',
                            'betting.rollingAmount',
                            'betting.payout',
                            'betting.betResult',
                            'rule.PID as ruleID',
                            'rule.name',
                            'rule.isMatched',
                            'user.PID as userID',
                            'user.balance',
                            'user.portalProviderUserID',
                            'user.isLoggedIn',
                            'user.UUID as userUUID',
                            'portalProvider.PID as portalProviderID',
                            'portalProvider.creditBalance',
                            'portalProvider.mainBalance',
                            'portalProvider.UUID as portalProviderUUID'
                        ])
                        ->get();

                    if ($betAllDetails->count(DB::raw('1')) == 0) { // checking if no bets on this game then
                        DB::beginTransaction();
                        Game::where('PID', $singleGame->PID)->update(["gameStatus" => 3]);
                        DB::commit();
                        $attempts = 0;
                    } elseif ($stockDetails->count(DB::raw('1')) > 0) { // if bet found, finding Stock Value at that point

                        $intervalDiff = timeDiffBetweenTwoDateTimeObjects($gameEndTimestamp);

                        $min = $intervalDiff->d * 24;
                        $min = ($min + $intervalDiff->h) * 60;
                        $min = $min + $intervalDiff->i;

                        $limit = (int) ($min / $stockDetails[0]->stockLoop) + 2; // finding limit tag value. Adding 2 history as buffer

                        $response = $this->stockProviderRef->getLiveStockData($stockDetails[0]->url, $stockDetails[0]->method, $stockDetails[0]->limitTag, $stockDetails[0]->responseType, $stockDetails[0]->replaceJsonRules, $stockDetails[0]->responseStockDataTag, $limit); // Getting live Stock prices

                        if ($response["error"]) {
                            throw new Exception($response["msg"]);
                        }

                        $APIData = json_decode($response["data"]); // Storing into Object

                        $gameEndDateTime = new DateTime($gameEndTimestamp,  new DateTimeZone(config('app.timezone')));
                        $gameEndDateTime->setTimezone(new DateTimeZone($stockDetails[0]->responseStockTimeZone));

                        foreach ($APIData as $singleObject) {
                            $singleArray = (array) $singleObject; // Object to Array

                            $stockDatetime = date_create_from_format($stockDetails[0]->responseStockTimeFormat, $singleArray[$stockDetails[0]->responseStockTimeTag]);
                            $stockDatetime->setTimezone(new DateTimeZone($stockDetails[0]->responseStockTimeZone));

                            if ($stockDatetime == $gameEndDateTime) { // Finding stock value at game Close Time
                                $stockEndValue = $singleArray[$stockDetails[0]->responseStockOpenTag];
                                $foundStockValue = true;
                                break;
                            }
                        }

                        if (!$foundStockValue) {
                            throw new Exception("Stock Value Not found for timestamp : " . $gameEndDateTime->format('Y-m-d H:i:s'));
                        }

                        // Getting Some imp variable from Stock table
                        $stockEndValue = str_replace(',', '', number_format((float) $stockEndValue, $stockDetails[0]->precision));
                        $stockEndValueAsStr = (string) $stockEndValue;
                        $number1 = (int) $stockEndValueAsStr[strlen($stockEndValueAsStr) - 2];
                        $number2 = (int) $stockEndValueAsStr[strlen($stockEndValueAsStr) - 1];

                        DB::beginTransaction();
                        foreach ($betAllDetails as $singleBetAllDetail) { // Looping on each bet of game
                            $ruleName = strtoupper($singleBetAllDetail->name);
                            $matchedArray = explode(',', $singleBetAllDetail->isMatched);

                            $isWin = false;
                            $rollingAmount = 0;
                            // win or loss check block : Start
                            if (strstr($ruleName, 'FD') && in_array($number1, $matchedArray)) {
                                $isWin = true;
                            } elseif (strstr($ruleName, 'LD') && in_array($number2, $matchedArray)) {
                                $isWin = true;
                            } elseif (strstr($ruleName, 'TD') && in_array($number1 * 10 + $number2, $matchedArray)) {
                                $isWin = true;
                            } elseif (strstr($ruleName, 'BD') && in_array($number1 + $number2, $matchedArray)) {
                                $isWin = true;
                            }
                            // win or loss check block : Start
                            if ($isWin) {
                                $rollingAmount = $singleBetAllDetail->payout * $singleBetAllDetail->betAmount; // Calculating rolling Amount
                            }

                            $userModel = User::where('PID', $singleBetAllDetail->userID);
                            if ($singleBetAllDetail->isLoggedIn == 'true' && $isWin) { // if user is Still login
                                $userModel->increment('balance', $rollingAmount);

                                //call balanceUpdateEvent
                                if (!isEmpty($singleBetAllDetail->userUUID) && !isEmpty($singleBetAllDetail->balance)) {
                                    $data['userUUID'] = $userModel->get()[0]->UUID;
                                    $data['userBalance'] = $userModel->get()[0]->balance;
                                    $socketData = Res::success($data);
                                    broadcast(new BalanceUpdateEvent($socketData));
                                }
                            } elseif ($isWin) { // if user get logout
                                PortalProvider::where('PID', $singleBetAllDetail->portalProviderID)->increment('creditBalance', $rollingAmount);
                                PortalProvider::where('PID', $singleBetAllDetail->portalProviderID)->increment('mainBalance', $rollingAmount);

                                $tranID = IdLookup::getUniqueId('poolLog', 'transactionId');

                                // pool Log code : START
                                event(new PoolLogEvent(
                                    $singleBetAllDetail->portalProviderID,
                                    $singleBetAllDetail->userID,
                                    null, // system
                                    $singleBetAllDetail->mainBalance,
                                    $singleBetAllDetail->mainBalance + $rollingAmount,
                                    $rollingAmount,
                                    'mainBalance',
                                    0, // credit
                                    $tranID,
                                    $this->signature,
                                    0 // system
                                ));

                                event(new PoolLogEvent(
                                    $singleBetAllDetail->portalProviderID,
                                    $singleBetAllDetail->userID,
                                    null, // system
                                    $singleBetAllDetail->creditBalance,
                                    $singleBetAllDetail->creditBalance + $rollingAmount,
                                    $rollingAmount,
                                    'creditBalance',
                                    0, // credit
                                    $tranID,
                                    $this->signature,
                                    0 // system
                                ));
                                // pool Log code : END

                                $configModel  = new ProviderConfig();
                                $column = ['logoutAPICall'];
                                $configData = $configModel->getProviderConfigByPID($singleBetAllDetail->portalProviderID)->select($column)->get();

                                if (IsAuthEnv() && $configData->count(DB::raw('1')) != 0 && $configData[0]->logoutAPICall == 1) {
                                    // logout call to provider
                                    LogoutAPICallJob::dispatch(
                                        $this->signature,
                                        $singleBetAllDetail->portalProviderID,
                                        0, // system
                                        0, // system
                                        $singleBetAllDetail->userID,
                                        $singleBetAllDetail->portalProviderUserID,
                                        $rollingAmount,
                                        0 // system
                                    )->onQueue('logout');
                                }
                            }

                            // Updating Game status
                            Betting::where('PID', $singleBetAllDetail->betID)
                                ->update(["betResult" => $isWin, "rollingAmount" => $rollingAmount]);


                            AutomaticallyUnfollowJob::dispatch(
                                $singleBetAllDetail->userID,
                                $singleBetAllDetail->portalProviderUUID,
                                $singleBetAllDetail->userUUID
                            )->onQueue('immediate')->delay(now()->addSeconds(10));
                        }
                        Game::where('PID', $singleGame->PID)->update(["gameStatus" => 3, 'endStockValue' => (float) $stockEndValue]);

                        DB::commit();

                        $attempts = 0;
                    } else {
                        throw new Exception("Stock not found");
                    }
                }

                // Sleep Code : START
                if ($totalAllEndGames == 0) {
                    if ($count <= 0) {
                        sleep(4);
                    } else if ($count <= 1) {
                        sleep(3);
                    } else if ($count <= 2) {
                        sleep(2);
                    } else if ($count <= 3) {
                        sleep(2);
                    } else if ($count >= 4) {
                        sleep(1);
                    }
                    $count += 1;
                } else {
                    $count = 0;
                }
                // Sleep Code : END

            } catch (Exception $e) {

                DB::rollback();

                if ($attempts == $number_of_attempts) {

                    $msg = 'Error : ' . $e->getMessage() . "\n";
                    $msg = $msg . $e->getTraceAsString() . "\n";

                    $this->gameModelRef->getAllCloseGameDetails()
                        ->where('PID', $gameID)
                        ->update(["error" => $msg, "gameStatus" => 4,]);

                    $subject = "ERROR STACK TRACE => JOB ($this->signature) : " . config('app.env');
                    $to = config('constants.alert_mail_id');

                    MailJob::dispatch($to, $msg, $subject)->onQueue('medium');

                    $attempts = 0;
                } else {
                    $attempts++;
                    usleep(config('app.number_of_game_calc_sleep'));
                }
            }
        }
    }

    public function nonStringToJsonString($str, $rules)
    {
        foreach ($rules as $currentRule) {
            $str = str_replace($currentRule->search, $currentRule->replace, $str);
        }

        return $str;
    }
}
