<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Stock;
use App\Models\Game;
use App\Models\PortalProvider;
use Illuminate\Console\Command;
use App\Providers\Stock\StockProvider;
use App\Jobs\MailJob;
use App\Events\Socket\BroadcastStockList;
use App\Http\Controllers\ResponseController as Res;

require_once app_path() . '/Helpers/APICall.php';
require_once app_path() . '/Helpers/CommonUtility.php';

class GetStockList extends Command
{

    protected $stockProviderRef;    // Stock Provider Reference

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'while:getStockList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will give stock list and game status of each games based on the stock';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->stockProviderRef = new StockProvider();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $count = 0;
        $lastCalledSec = -1;
        $currentSec = -1;
        while (true) {
            try {

                $gameModel = new Game();
                $stockModel = new Stock();
                $portalProvider = new PortalProvider();


                $selectedColumn = [
                    'stock.PID as stockID',
                    'stock.UUID as stockUUID',
                    'stock.name as stockName',
                    'stock.liveStockUrl',
                    'stock.ReferenceURL',
                    'stock.method',
                    'stock.closeDays',
                    'stock.liveStockResponseType',
                    'stock.liveStockReplaceJsonRules',
                    'stock.liveStockDataTag',
                    'stock.closeDays',
                    'stock.limitTag',
                    'stock.openTimeRange',
                    'stock.timeZone',
                    'stock.liveStockTimeTag',
                    'stock.liveStockOpenTag',
                    'stock.splitString',
                    'stock.dateValueIndex',
                    'stock.responseStockTimeFormat',
                    'stock.timeValueIndex',
                    'stock.responseStockTimeZone',
                    'stock.precision',
                    'stock.openValueIndex',
                    'portalProvider.PID as portalProviderPID',
                    'portalProvider.UUID as portalProviderUUID'
                ];

                //Running every second
                $currentSec = microtimeToDateTime(getCurrentTimeStamp(), false, 's');
                $canBroadcastData = ($currentSec != $lastCalledSec);
                // //Running every 10 sec time interval(getting time from config - constants)
                // $canBroadcastData = (microtimeToDateTime(getCurrentTimeStamp(), false, 's') % config('constants.socket_live_game_time_sec') == 0);

                if ($canBroadcastData) {
                    $dayOfWeek = date('w', getCurrentTimeStamp());

                    //getting active portal providers
                    $activePortalProviders = $portalProvider->getPortalProviders()->select('PID as portalProviderPID', 'UUID as portalProviderUUID')->get();

                    if (count($activePortalProviders) > 0) {

                        foreach ($activePortalProviders as $eachProvider) {

                            $response = array();
                            $stockList = array();

                            $portalProviderID = $eachProvider['portalProviderPID'];

                            $getAllStocks = $stockModel->getAllStockBaseOnProviderID($portalProviderID)->select($selectedColumn)->get();

                            if (count($getAllStocks) > 0) {
                                foreach ($getAllStocks as $eachStock) {

                                    $stockValue = "";
                                    $stockOpenOrClose = "";

                                    // To check whether each stock under the particular portal provider is open or closed
                                    $isStockIsOpenResponse = $this->stockProviderRef->checkIsStockIsOpen($dayOfWeek, $eachStock->closeDays, $eachStock->timeZone, $eachStock->openTimeRange, $eachStock->stockID);
                                    if ($isStockIsOpenResponse['isStockIsOpen']) {

                                        $stockOpenOrClose = 'Open';

                                        // To get the price of the each stock under the particular portal provider
                                        $stockData = $this->stockProviderRef->getLiveStockData($eachStock->liveStockUrl, $eachStock->method, null, $eachStock->liveStockResponseType, $eachStock->liveStockReplaceJsonRules, $eachStock->liveStockDataTag);

                                        if ($stockData['error']) {
                                            throw new Exception($stockData['msg']);
                                        }

                                        if ($eachStock->liveStockResponseType == 'JSON') { // response type is JSON

                                            $APIData = json_decode($stockData["data"]);

                                            $singleArray = (array) $APIData[0]; // Object to Array

                                            $ts = (string) $singleArray[$eachStock->liveStockTimeTag];

                                            $ts = (int) substr($ts, 0, strlen($ts) - 3);

                                            $stockValue = $singleArray[$eachStock->liveStockOpenTag];
                                        }

                                        if ($eachStock->liveStockResponseType == 'stringSplit') { // response type is string split
                                            $singleArray = explode($eachStock->splitString, $stockData["data"]);

                                            $stockValue = $singleArray[$eachStock->openValueIndex];
                                        }

                                        $stockValue = number_format($stockValue, $eachStock->precision);
                                    } else {
                                        $stockOpenOrClose = "Closed!";
                                    }

                                    //To find the game related to that particular stock and provider id
                                    $gameStatus = [1, 2]; // open/closed games should be listed
                                    $currentTimeStamp = microtimeToDateTime(getCurrentTimeStamp());
                                    $portalProviderPID = $eachStock['portalProviderPID'];
                                    $gameData = $gameModel->getAllProviderGamesByStock($portalProviderPID, $eachStock['stockUUID'], $gameStatus, $currentTimeStamp);

                                    $gameUUID = "";
                                    $betCloseTimeCountDown = "";
                                    $gameEndTimeCountDown = "";

                                    if (count($gameData) > 0) {
                                        $betCloseTimeCountDown = $gameData[0]['betCloseTimeInSec'];
                                        $gameEndTimeCountDown = $gameData[0]['gameCloseTimeInSec'];
                                        $gameUUID = $gameData[0]['gameUUID'];
                                    }

                                    array_push($stockList, array(
                                        'stockUUID' => $eachStock['stockUUID'], 'stockName' => $eachStock['stockName'], 'referenceUrl' => $eachStock['ReferenceURL'], 'stockOpenOrClosed' => $stockOpenOrClose, 'stockPrice' => $stockValue, 'gameUUID' => $gameUUID, 'betCloseTimeCountDownInSec' => $betCloseTimeCountDown, 'gameEndTimeCountDownInSec' => $gameEndTimeCountDown, "stockTimestamp" => microtimeToDateTime(getCurrentTimeStamp(), false)
                                    ));

                                    $response['portalProviderUUID'] = $eachProvider['portalProviderUUID'];
                                    $response['stockData'] = $stockList;
                                }

                                $response = Res::success($response);

                                $lastCalledSec = $currentSec;
                                broadcast(new BroadcastStockList($response));
                            }
                        }
                    }
                }

                // Sleep Code : START
                if ($canBroadcastData == 0) {
                    if ($count <= 0) {
                        usleep(150000);
                    } else if ($count <= 1) {
                        usleep(100000);
                    } else if ($count <= 2) {
                        usleep(50000);
                    }
                    $count += 1;
                } else {
                    $count = 0;
                    usleep(5000);
                }
                // Sleep Code : END

            } catch (Exception $e) {
                $msg = 'Error : ' . $e->getMessage() . "\n";
                $msg = $msg . $e->getTraceAsString() . "\n";

                $subject = "ERROR STACK TRACE => JOB ($this->signature) : " . config('app.env');
                $to = config('constants.alert_mail_id');

                MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
            }
        }
    }
}
