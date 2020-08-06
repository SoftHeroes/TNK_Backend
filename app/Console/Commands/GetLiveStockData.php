<?php

namespace App\Console\Commands;

use Log;
use Exception;
use DateTimeZone;
use App\Models\Stock;
use Illuminate\Console\Command;
use App\Providers\Stock\StockProvider;
use App\Jobs\MailJob;
use App\Http\Controllers\ResponseController as Res;
use App\Events\Socket\BroadcastLiveStockData;

require_once app_path() . '/Helpers/APICall.php';
require_once app_path() . '/Helpers/CommonUtility.php';

class GetLiveStockData extends Command
{

    protected $stockProviderRef;    // Stock Provider Reference

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'while:GetLiveStockData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will runs in infinity loop and Call event for get live stock data.
    ';

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

                $canBroadcastData = (microtimeToDateTime(getCurrentTimeStamp(), false, 's') % config('constants.socket_live_stock_data_time_sec') == 0);

                if ($canBroadcastData) { // if second is dividable by config variable then go and get stock Data broadcast them on socket
                    $dayOfWeek = date('w', getCurrentTimeStamp());

                    $getAllStocks = Stock::select([
                        'PID',
                        'name',
                        'url',
                        'method',
                        'country',
                        'stockLoop',
                        'closeDays',
                        'limitTag',
                        'openTimeRange',
                        'timeZone',
                        'precision',
                        'liveStockUrl',
                        'liveStockResponseType',
                        'liveStockOpenTag',
                        'liveStockTimeTag',
                        'splitString',
                        'openValueIndex',
                        'dateValueIndex',
                        'timeValueIndex',
                        'liveStockDataTag',
                        'liveStockReplaceJsonRules',
                        'responseStockTimeZone',
                        'responseStockTimeFormat'
                    ])->get();

                    foreach ($getAllStocks as $currentStock) {
                        $response = array();
                        $roadMap = array();

                        $isStockIsOpenResponse = $this->stockProviderRef->checkIsStockIsOpen($dayOfWeek, $currentStock->closeDays, $currentStock->timeZone, $currentStock->openTimeRange, $currentStock->PID);

                        if ($isStockIsOpenResponse['error']) {
                            throw new Exception($isStockIsOpenResponse["msg"]);
                        }
                        if ($isStockIsOpenResponse['isStockIsOpen']) {
                            $stockData = $this->stockProviderRef->getLiveStockData($currentStock->liveStockUrl, $currentStock->method, null, $currentStock->liveStockResponseType, $currentStock->liveStockReplaceJsonRules, $currentStock->liveStockDataTag);

                            if ($stockData['error']) {
                                throw new Exception($stockData['msg']);
                            }

                            $response['stockName'] = $currentStock->name;

                            $stockTimestamp = null;
                            $stockValue = null;

                            if ($currentStock->liveStockResponseType == 'JSON') { // response type is JSON

                                $APIData = json_decode($stockData["data"]);

                                $singleArray = (array) $APIData[0]; // Object to Array

                                $ts = (string) $singleArray[$currentStock->liveStockTimeTag];

                                $ts = (int) substr($ts, 0, strlen($ts) - 3);

                                $stockTimestamp = date_create_from_format($currentStock->responseStockTimeFormat, $ts, new DateTimeZone($currentStock->timeZone));
                                $stockValue = $singleArray[$currentStock->liveStockOpenTag];
                            }
                            if ($currentStock->liveStockResponseType == 'stringSplit') { // response type is string split
                                $singleArray = explode($currentStock->splitString, $stockData["data"]);

                                $stockTimestamp = date_create_from_format($currentStock->responseStockTimeFormat, $singleArray[$currentStock->dateValueIndex] . " " . $singleArray[$currentStock->timeValueIndex], new DateTimeZone($currentStock->timeZone));
                                $stockValue = $singleArray[$currentStock->openValueIndex];
                            }

                            $stockTimestamp->setTimezone(new DateTimeZone(config('app.timezone')));
                            $stockValue = number_format($stockValue, $currentStock->precision);
                            $stockValueAsStr = (string) $stockValue;
                            $number1 = (int) $stockValueAsStr[strlen($stockValueAsStr) - 2];
                            $number2 = (int) $stockValueAsStr[strlen($stockValueAsStr) - 1];

                            array_push($roadMap, array('stockValue' => $stockValue, 'stockTimestamp' => $stockTimestamp->format('Y-m-d H:i:s'), 'number1' => $number1, 'number2' => $number2));

                            $response['roadMap'] = $roadMap;
                            $response = Res::success($response);
                            broadcast(new BroadcastLiveStockData($response));
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
