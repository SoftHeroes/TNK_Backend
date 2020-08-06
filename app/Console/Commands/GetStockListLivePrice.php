<?php

namespace App\Console\Commands;

use App\Events\Socket\GetStockList;
use App\Http\Controllers\ResponseController as Res;
use App\Models\PortalProvider;
use App\Models\Stock;
use App\Jobs\MailJob;
use App\Providers\Stock\StockProvider;
use Exception;
use Illuminate\Console\Command;

class GetStockListLivePrice extends Command
{
    protected $stockProviderRef; // Stock Provider Reference

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'while:getStockListLivePrice';

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
        while (true) {
            try {
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
                    'portalProvider.UUID as portalProviderUUID',
                ];

                //Running every 10 sec time interval(getting time from config - constants)
                $canBroadcastData = (microtimeToDateTime(getCurrentTimeStamp(), false, 's') % config('constants.socket_live_game_time_sec') == 0);

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
                                    $stockStatus = "";

                                    // To check whether each stock under the particular portal provider is open or closed
                                    $isStockIsOpenResponse = $this->stockProviderRef->checkIsStockIsOpen($dayOfWeek, $eachStock->closeDays, $eachStock->timeZone, $eachStock->openTimeRange,$eachStock->stockID);
                                    if ($isStockIsOpenResponse['isStockIsOpen']) {

                                        $stockStatus = 'Open';

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
                                        $stockStatus = "Closed";
                                    }


                                    array_push($stockList, array(
                                        'stockUUID' => $eachStock['stockUUID'], 'stockName' => $eachStock['stockName'], 'stockStatus' => $stockStatus, 'stockPrice' => $stockValue, "stockTimestamp" => microtimeToDateTime(getCurrentTimeStamp(), false),'stockReference' => $eachStock['ReferenceURL']
                                    ));

                                    $response['portalProviderUUID'] = $eachProvider['portalProviderUUID'];
                                    $response['stockData'] = $stockList;
                                }

                                $response = Res::success($response);
                                broadcast(new GetStockList($response));
                            }
                        }
                    }
                }

                // Sleep Code : START
                if ($canBroadcastData == 0) {
                    if ($count <= 0) {
                        sleep(3);
                    } else if ($count <= 1) {
                        sleep(2);
                    } else if ($count <= 2) {
                        sleep(1);
                    }
                    $count += 1;
                } else {
                    $count = 0;
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
