<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Stock;
use App\Models\Game;
use App\Models\PortalProvider;
use Illuminate\Console\Command;
use App\Providers\Stock\StockProvider;
use App\Jobs\MailJob;
use App\Events\Socket\LiveCountDownEvent;
use App\Http\Controllers\ResponseController as Res;

require_once app_path() . '/Helpers/APICall.php';
require_once app_path() . '/Helpers/CommonUtility.php';

class LiveCountDownTimer extends Command
{

    protected $signature = 'while:countDownTimer';

    protected $description = 'Run Count Down Timer Every Second';

    public function __construct()
    {
        parent::__construct();
        $this->stockProviderRef = new StockProvider();
    }

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
                    'stock.closeDays',
                    'stock.openTimeRange',
                    'stock.timeZone',
                    'portalProvider.PID as portalProviderPID',
                    'portalProvider.UUID as portalProviderUUID'
                ];

                //Running every second
                $currentSec = microtimeToDateTime(getCurrentTimeStamp(), false, 's');
                $canBroadcastData = ($currentSec != $lastCalledSec);

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

                                    $stockOpenOrClose = "";
                                    $betCloseTimeCountDown = "";
                                    $gameEndTimeCountDown = "";

                                    // To check whether each stock under the particular portal provider is open or closed
                                    $isStockIsOpenResponse = $this->stockProviderRef->checkIsStockIsOpen($dayOfWeek, $eachStock->closeDays, $eachStock->timeZone, $eachStock->openTimeRange,$eachStock->stockID);
                                    if ($isStockIsOpenResponse['isStockIsOpen']) {
                                        $stockOpenOrClose = 'Open';

                                        //To find the game related to that particular stock and provider id
                                        $gameStatus = [1, 2]; // open/closed games should be listed
                                        $currentTimeStamp = microtimeToDateTime(getCurrentTimeStamp());
                                        $portalProviderPID = $eachStock['portalProviderPID'];
                                        $gameData = $gameModel->getAllProviderGamesByStock($portalProviderPID, $eachStock['stockUUID'], $gameStatus, $currentTimeStamp);

                                        if (count($gameData) > 0) {
                                            $betCloseTimeCountDown = $gameData[0]['betCloseTimeInSec'];
                                            $gameEndTimeCountDown = $gameData[0]['gameCloseTimeInSec'];
                                        }
                                    } else {
                                        $stockOpenOrClose = 'Closed';
                                    }

                                    array_push(
                                        $stockList,
                                        array(
                                            'stockName' => $eachStock['stockName'],
                                            'stockUUID' => $eachStock['stockUUID'],
                                            'stockStatus' => $stockOpenOrClose,
                                            'betCloseTimeCountDownInSec' => $betCloseTimeCountDown,
                                            'gameEndTimeCountDownInSec' => $gameEndTimeCountDown,
                                            "stockTimestamp" => microtimeToDateTime(getCurrentTimeStamp(), false)

                                        )
                                    );
                                }
                                $response['portalProviderUUID'] = $eachStock['portalProviderUUID'];
                                $response['timeData'] = $stockList;
                                $response = Res::success($response);

                                $lastCalledSec = $currentSec;
                                broadcast(new LiveCountDownEvent($response));
                            }
                        }
                    }
                }

                // Sleep Code : START
                if ($canBroadcastData == 0) {
                    if ($count <= 0) {
                        usleep(500000);
                    } else if ($count <= 1) {
                        usleep(250000);
                    } else if ($count <= 2) {
                        usleep(100000);
                    }
                    $count += 1;
                } else {
                    $count = 0;
                    usleep(10000);
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
