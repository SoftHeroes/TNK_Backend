<?php

namespace App\Providers\Stock;

use DateTime;
use Exception;
use DateTimeZone;
use App\Models\Stock;
use App\Models\PortalProvider;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\ResponseController as Res;
use App\Providers\Admin\AdminProvider;
use Illuminate\Support\Facades\DB;
use App\Models\Game;
use App\Models\HolidayList;
use App\Models\RoadMapBackup;

require_once app_path() . '/Helpers/CommonUtility.php';
require_once app_path() . '/Helpers/APICall.php';

class StockProvider extends ServiceProvider
{

    public function __construct()
    {
        parent::__construct(null);
    }

    protected $stockModelRef;
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
     * This function return if current stock is open or not
     *
     * @return responseArray
     */
    public function checkIsStockIsOpen($dayOfWeek, $closeDays, $timeZone, $openTimeRange, $stockID)
    {
        $response = array( // default response array
            "error" => false,
            "isStockIsOpen" => false,
            "msg" => "stock is Closed!"
        );

        $tempDatetime = new DateTime('now'); // get current datetime if datetime not passed

        try {
            if (in_array($dayOfWeek, explode(",", $closeDays))) { // check if stock is closed
                return $response;
            }

            $getHolidayList = HolidayList::holidayListChecker($stockID, date('Y-m-d', getCurrentTimeStamp()));
            if ($getHolidayList->count(DB::raw('1')) > 0) {
                return $response;
            }

            if (isEmpty($openTimeRange)) { // check if stock is 24 hour open
                $response['isStockIsOpen'] = true;
                $response['msg'] = "stock is Open!";
                return $response;
            }

            $allOpenTimeInterval = explode(',', $openTimeRange); // check if stock is 24 hour open

            foreach ($allOpenTimeInterval as $singleTimeInterval) { // looping on all time interval
                $result = explode('-', $singleTimeInterval);

                if (count($result) != 2) {
                    $response['error'] = true;
                    $response['msg'] = 'Invalid open Time Range in DB';
                    return $response;
                }

                $tempDatetime->setTimezone(new DateTimeZone($timeZone)); // converting to stock timezone

                $startTimestamp = clone $tempDatetime;
                $endTimestamp = clone $tempDatetime;

                $temp = explode(':', $result[0]);
                $startTimestamp->setTime($temp[0], $temp[1], 0, 0);

                $temp = explode(':', $result[1]);
                $endTimestamp->setTime($temp[0], $temp[1], 0, 0);

                $startTimestamp->setTimezone(new DateTimeZone(config('app.timezone')));     // Converting stock timezone to App timezone
                $endTimestamp->setTimezone(new DateTimeZone(config('app.timezone')));       // Converting stock timezone to App timezone

                $currentTimestamp = new DateTime('now');

                if ($currentTimestamp <= $endTimestamp && $currentTimestamp >= $startTimestamp) { // checking if on current timezone stock is open
                    $response['isStockIsOpen'] = true;
                    $response['msg'] = "stock is Open!";
                    return $response;
                }
            }
            return $response;
        } catch (Exception $e) {
            $response["error"] = true;
            $response["msg"] = $e->getMessage();

            return $response;
        }
    }

    public function getRoadMap($portalProviderUUID, $stockUUID, $limit)
    {

        try {

            //Portal provider UUID valid check
            $portalProviderDetails = PortalProvider::select('PID')->where('UUID', $portalProviderUUID)->get();
            if ($portalProviderDetails->count(DB::raw('1')) == 0) {
                return Res::notFound([], 'portalProviderUUID does not exist.');
            }
            // --------------------------write this query in model--------------------- @tay
            // check StockUUID
            $stockDetails = Stock::select('name', 'url', 'method', 'limitTag', 'responseType', 'replaceJsonRules', 'responseStockTimeTag', 'responseStockOpenTag', 'responseStockTimeFormat', 'responseStockDataTag', 'precision', 'timeZone')->where('UUID', $stockUUID)->get();
            if ($stockDetails->count(DB::raw('1')) == 0) {
                return Res::notFound([], 'stockUUID does not exist.');
            }

            $response = [];

            foreach ($stockDetails as $key => $value) {
                $response[] = $this->getLiveStockData(
                    $value->url,
                    $value->method,
                    $value->limitTag,
                    $value->responseType,
                    $value->replaceJsonRules,
                    $value->responseStockDataTag,
                    $limit
                );
            }

            if (count($response) == 0) {
                return Res::notFound([], 'Stock Live Data not found.');
            }

            $gameEndTimestamp = json_decode($response[0]['data']);

            foreach ($gameEndTimestamp as $key => $value) {
                // Date Time format
                $validate = $stockDetails[0]->responseStockTimeTag;
                $date = date_create_from_format($stockDetails[0]->responseStockTimeFormat, $value->$validate, new DateTimeZone($stockDetails[0]->timeZone));
                $date->setTimezone(new DateTimeZone(config('app.timezone')));
                $stockDatetime = $date->format("Y-m-d H:i:s");

                // number format
                $valOpen = $stockDetails[0]->responseStockOpenTag;
                $stockEndValue = number_format($value->$valOpen, $stockDetails[0]->precision, ".", "");
                $stockEndValueAsStr = (string) $stockEndValue;

                // format string
                $data[] = [
                    'stockTimeStamp' => $stockDatetime,
                    'stockValue' =>  $stockEndValueAsStr,
                    'number1' => (int) $stockEndValueAsStr[strlen($stockEndValueAsStr) - 2],
                    'number2' => (int) $stockEndValueAsStr[strlen($stockEndValueAsStr) - 1]
                ];
            }

            return Res::success(['stockName' => $stockDetails[0]->name, 'roadMap' => $data]);
        } catch (Exception $e) {
            return Res::errorException($e->getMessage());
        }
    }

    public function getStockOnly($stockUUID, $limit)
    {

        try {

            // --------------------------write this query in model--------------------- @tay
            // check StockUUID
            $stockDetails = Stock::select('name', 'url', 'method', 'limitTag', 'responseType', 'replaceJsonRules', 'responseStockTimeTag', 'responseStockOpenTag', 'responseStockTimeFormat', 'responseStockDataTag', 'precision')->where('UUID', $stockUUID)->get();
            if ($stockDetails->count(DB::raw('1')) == 0) {
                return Res::notFound([], 'stockUUID does not exist.');
            }


            foreach ($stockDetails as $key => $value) {
                $response[] = $this->getLiveStockData(
                    $value->url,
                    $value->method,
                    $value->limitTag,
                    $value->responseType,
                    $value->replaceJsonRules,
                    $value->responseStockDataTag,
                    $limit
                );
            }

            if (count($response) == 0) {
                return Res::badRequest([], 'Stock Live Data not found.');
            }

            $gameEndTimestamp = json_decode($response[0]['data']);

            foreach ($gameEndTimestamp as $key => $value) {
                // Date Time format
                $validate = $stockDetails[0]->responseStockTimeTag;
                $date = date_create_from_format($stockDetails[0]->responseStockTimeFormat, $value->$validate);
                $stockDatetime = date_format($date, "Y-m-d H:i:s");

                // number format
                $valOpen = $stockDetails[0]->responseStockOpenTag;
                $stockEndValue = number_format($value->$valOpen, $stockDetails[0]->precision, ".", "");
                $stockEndValueAsStr = (string) $stockEndValue;

                // format string
                $data[] = [
                    'stockTimestamp' => $stockDatetime,
                    'stockValue' =>  $stockEndValueAsStr
                ];
            }

            return Res::success($data);
        } catch (Exception $e) {
            return Res::errorException($e->getMessage());
        }
    }

    public function getMultipleRoadMap($portalProviderUUID, array $stockUUID, int $limit)
    {
        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        $providerModel = new PortalProvider();


        $selectedColumn = ['PID', 'UUID', 'name', 'url', 'method', 'limitTag', 'responseType', 'replaceJsonRules', 'responseStockTimeTag', 'responseStockOpenTag', 'responseStockTimeFormat', 'responseStockDataTag', 'precision', "category", "closeDays", "openTimeRange", "timeZone"];
        try {

            //Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $response['portalProviderID'] = $providerData[0]->PID;


            // check StockUUID
            $stockDetails = Stock::getStockDetailsInUUID($stockUUID)->select($selectedColumn);
            // Check if stock does not exist
            if ($stockDetails->count(DB::raw('1')) == 0 || count($stockUUID) != $stockDetails->count(DB::raw('1'))) {
                $response['res'] =  Res::notFound([], 'Some stockUUID does not exist.');
                return $response;
            }

            $tempData = [];
            foreach ($stockDetails->get() as $key => $value) {
                $tempData[] = [
                    "data" => $this->getLiveStockData(
                        $value->url,
                        $value->method,
                        $value->limitTag,
                        $value->responseType,
                        $value->replaceJsonRules,
                        $value->responseStockDataTag,
                        $limit
                    ),
                    "responseStockTimeTag" => $value->responseStockTimeTag,
                    "responseStockTimeFormat" => $value->responseStockTimeFormat,
                    "precision" => $value->precision,
                    "responseStockOpenTag" => $value->responseStockOpenTag,
                    "stockName" => $value->name,
                    "category" => $value->category,
                    "closeDays" => $value->closeDays,
                    "openTimeRange" => $value->openTimeRange,
                    "timeZone" => $value->timeZone,
                    "stockUUID" => $value->UUID,
                    'stockID' => $value->PID
                ];
            }

            $data = [];
            $roadMap = [];
            $dayOfWeek = date('w', getCurrentTimeStamp());
            foreach ($tempData as $key => $value) {
                $isOldData = false;
                // Date Time format
                $validate = $value["responseStockTimeTag"];
                // number format
                $valOpen = $value["responseStockOpenTag"];

                $isOpenOrClose = $this->checkIsStockIsOpen($dayOfWeek, $value["closeDays"], $value["timeZone"], $value["openTimeRange"], $value['stockID']);

                $roadMapData = "[]";

                if (!$value["data"]["error"]) { // no error
                    $roadMapData = $value["data"]['data'];
                } else { // when error get send backup data

                }

                if ($value["data"]["error"]) {
                    $oldData = RoadMapBackup::findByStockId($value['stockID'])->select('stockId', 'roadMap')->get();

                    if ($oldData->count(DB::raw('1')) != 0) {
                        $isOldData = true;
                        $roadMapData = $oldData[0]->roadMap;
                    }
                    // continue;
                } else { // Adding code for backup
                    RoadMapBackup::updateOrCreate(['stockId' => $value['stockID']], ['roadMap' => $value["data"]["data"]]);
                }

                foreach (json_decode($roadMapData) as $roadMapData) {
                    $date = date_create_from_format($value["responseStockTimeFormat"], $roadMapData->$validate, new DateTimeZone($value['timeZone']));
                    $date->setTimezone(new DateTimeZone(config('app.timezone')));
                    $stockDatetime = $date->format("Y-m-d H:i:s");

                    $stockEndValue = number_format($roadMapData->$valOpen, $value["precision"], ".", "");
                    $stockEndValueAsStr = (string) $stockEndValue;

                    $roadMap[] = [
                        'stockTimeStamp' => $stockDatetime,
                        'stockValue' =>  $stockEndValueAsStr,
                        'number1' => (int) $stockEndValueAsStr[strlen($stockEndValueAsStr) - 2],
                        'number2' => (int) $stockEndValueAsStr[strlen($stockEndValueAsStr) - 1]

                    ];
                }

                //get active games by stock
                $gameModel = new Game();
                $gameData = $gameModel->getAllGameByProviderStockID($providerData[0]->PID, [1, 2], 100, 0, $value["stockUUID"])
                    ->select('game.UUID as gameUUID', DB::raw('(CASE WHEN game.gameStatus = 0 THEN "Pending" WHEN game.gameStatus = 1 THEN "Open" WHEN game.gameStatus = 2 THEN "Close" WHEN game.gameStatus = 3 THEN "Complete" WHEN game.gameStatus = 4 THEN "Error" ELSE "Deleted" END) as gameStatus'))
                    ->get()->toArray();

                $data[] = [
                    "stockName" => $value["stockName"],
                    "category" => $value["category"],
                    "stockStatus" => $isOpenOrClose["isStockIsOpen"] ? (count($roadMap) != 0 ? "open" : "close") : "close",
                    "roadMap" => $roadMap,
                    "gameData" => $gameData,
                    "isOldData" => $isOldData
                ];
                // clear roadMap before adding new data

                $roadMap = [];
            }

            $response['res'] = Res::success($data);
        } catch (Exception $e) {

            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }

        return $response;
    }


    /*
    This function return stock prices in json format
    */
    public function getLiveStockData($url, $method, $limitTag, $responseType, $replaceJsonRules, $responseStockDataTag, $limit = 1)
    {
        $response = array("error" => false, "data" => "NA", "msg" => "stock data retrieved successfully");

        if (!isEmpty($limitTag)) {
            $url = $url . '&' . $limitTag . '=' . $limit;
        }
        $tempResponse = APIExecute($method, $url); // Hitting API

        if (!$tempResponse["error"]) {

            if (!isEmpty($replaceJsonRules)) {
                $response["data"] = $this->nonStringToJsonString($tempResponse["data"]["response"], json_decode($replaceJsonRules)); // Converting Non-Json to json
            } elseif ($responseType == "JSON") {
                $response["data"] = $tempResponse["data"]["response"];
            } else {
                $response["data"] = $tempResponse["data"]["response"];
            }
        } else {
            $response["msg"] = $tempResponse["data"]["error"];
            $response["error"] = true;
        }

        if (!isEmpty($responseStockDataTag) && !$response["error"]) {

            $jsonStrToArray = (array) json_decode($response["data"]);

            if (strstr($responseStockDataTag, '/')) {
                $allTags = explode('/', $responseStockDataTag);
                foreach ($allTags as $currentTag) {
                    $jsonStrToArray = (array) $jsonStrToArray[$currentTag];
                }
            } else {
                $jsonStrToArray = $jsonStrToArray[$responseStockDataTag];
            }

            $response["data"] = json_encode($jsonStrToArray);
        }

        return $response;
    }

    public function nonStringToJsonString($str, $rules)
    {
        foreach ($rules as $currentRule) {
            $str = str_replace($currentRule->search, $currentRule->replace, $str); // Converting Non-Json to json based on the rules
        }

        return $str;
    }

    public static function getStockBaseOnProvider($request)
    {
        $portalProviderID = AdminProvider::getAuthData($request)->portalProviderID;

        $selectedColumn = [
            "portalProvider.UUID as portalProviderUUID",
            "portalProvider.name as portalProviderName",
            "stock.name",
            "stock.ReferenceURL",
            "stock.openTimeRange",
            "stock.category",
            "stock.isActive",
            "stock.createdAt",
            "stock.updatedAt",
            "stock.closeDays"
        ];

        if ($portalProviderID == 1) {
            $stockData =  Stock::getAllStocks()->select($selectedColumn);
        } else {
            $stockData =  Stock::getStockBaseOnProvider($portalProviderID, $stockUUID = null)->select($selectedColumn);
        }

        return $stockData;
    }

    public static function getActiveStockBaseOnProvider($portalProviderID, $portalProviderUUID)
    {
        $selectedColumn = [
            "stock.name",
            "stock.UUID",
            "stock.ReferenceURL",
            "stock.category",
            "stock.stockLoop",
            "game.UUID as gameUUID",
            "stock.PID as stockPID"
        ];
        try {
            $stockData = Stock::getStockBaseOnProviderAndActiveGame($portalProviderID)->select($selectedColumn)->get();
            $resData = array();
            $notInQuery = array();
            if ($stockData->count() > 0) {
                foreach ($stockData as $value) {
                    $resData[] = StockProvider::storeActiveStockArray($value, $portalProviderUUID);
                    $notInQuery[] = $value->stockPID;
                }
            }
            $closedStock = Stock::getStockDetailsNotIn($notInQuery);
            if ($closedStock->count() > 0) {
                foreach ($closedStock->get() as $value) {
                    $resData[] = StockProvider::storeActiveStockArray($value, $portalProviderUUID);
                }
            }

            return Res::success($resData);
        } catch (Exception $e) {
            return Res::errorException($e->getMessage());
        }
    }

    public static function storeActiveStockArray($value, $portalProviderUUID)
    {
        return [
            "stockName" => $value->name,
            "stockUUID" => $value->UUID,
            "referenceURL" => $value->ReferenceURL,
            "type" => $value->category,
            "loop" => $value->stockLoop,
            "gameUUID" => $value->gameUUID == null ? null : $value->gameUUID,
            "crawlData" => array()
        ];
    }

    public function getActiveGamesByCategory($portalProviderUUID)
    {


        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        $gameModel = new Game();
        $portalProviderModel = new PortalProvider();

        try {

            $providerData = $portalProviderModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'Provider UUID does not exist.');
            } else {
                $response['portalProviderID'] = $providerData[0]->PID;
                $gameData = $gameModel->getActiveGamesByProviderStock($providerData[0]->PID, 100, 0, null);

                if (empty($gameData)) {

                    $response['res'] = Res::success([], 'No Games found.');
                } else {

                    $data = [];
                    //$response['portalProviderUUID'] = $portalProviderUUID;
                    $category = array_unique(array_column($gameData, 'stockType'));
                    $i = 0;
                    foreach ($category as $singleCategory) {

                        $data[$i]['type'] = $singleCategory;
                        $data[$i]['stocks'] = [];
                        $j = 0;

                        foreach ($gameData as $game) {
                            $game = get_object_vars($game); //stdClass obj to array

                            if ($game['stockName'] == 'btc1' || $game['stockName'] == 'btc5') {
                                $game['stockName'] = 'btc';
                            }
                            $k = 0;
                            if ($data[$i]['type'] == $game['stockType']) {

                                if ($j > 0) {

                                    $a = $j - 1;
                                    while ($a >= 0) {

                                        if ($data[$i]['stocks'][$a]['stockName'] == $game['stockName']) {

                                            $data[$i]['stocks'][$a]['loops'][$k + 1]['loopName'] = $game['stockLoop'];
                                            $data[$i]['stocks'][$a]['loops'][$k + 1]['gameID'] = $game['gameID'];
                                            $data[$i]['stocks'][$a]['loops'][$k + 1]['gameStatus'] = $game['gameStatus'];
                                            $k++;
                                        } else {

                                            $data[$i]['stocks'][$j]['stockName'] = $game['stockName'];
                                            $data[$i]['stocks'][$j]['loops'][$k]['loopName'] = $game['stockLoop'];
                                            $data[$i]['stocks'][$j]['loops'][$k]['gameID'] = $game['gameID'];
                                            $data[$i]['stocks'][$j]['loops'][$k]['gameStatus'] = $game['gameStatus'];
                                            $k++;
                                        }
                                        $a--;
                                    }
                                } else {

                                    $data[$i]['stocks'][$j]['stockName'] = $game['stockName'];
                                    $data[$i]['stocks'][$j]['loops'][$k]['loopName'] = $game['stockLoop'];
                                    $data[$i]['stocks'][$j]['loops'][$k]['gameID'] = $game['gameID'];
                                    $data[$i]['stocks'][$j]['loops'][$k]['gameStatus'] = $game['gameStatus'];
                                    $k++;
                                }
                            } else {
                                continue;
                            }
                            $j++;
                        }
                        $i++;
                    }
                    $response['res'] = Res::success($data);
                }
            }
        } catch (Exception $e) {
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }

        return $response;
    }


    public function getAllStock($portalProviderUUID)
    {
        $response['res'] = Res::success();
        $response['portalProviderID'] = null;

        $selectedColumn = [
            "stock.url",
            "stock.method",
            "stock.limitTag",
            "stock.responseType",
            "stock.replaceJsonRules",
            "stock.responseStockDataTag",
            "stock.responseStockTimeTag",
            "stock.responseStockTimeFormat",
            "stock.responseStockOpenTag",
            "stock.precision",
            "stock.name",
            "stock.ReferenceURL",
            "stock.timeZone"
        ];

        try {
            $portalProviderModel = new PortalProvider();
            $portalProvider = $portalProviderModel->getPortalProviderByUUID($portalProviderUUID);

            if ($portalProvider->count(DB::raw('1')) < 1) {
                $response['res'] = Res::notFound([], 'Portal Provider UUID does not exist');
                return $response;
            }

            $portalProviderData = $portalProvider->first();
            $response['portalProviderID'] = $portalProviderData->PID;
            $stockData = Stock::getAllStockBaseOnProviderID($portalProviderData->PID)->select($selectedColumn);

            if ($stockData->count(DB::raw('1')) < 1) {
                $response['res'] = Res::notFound([], 'There is no stock data belong to this portal Provider');
                return $response;
            }


            $responseArray = [];
            foreach ($stockData->get() as $key => $value) {
                $responseArray[] = [
                    "data" => $this->getLiveStockData(
                        $value->url,
                        $value->method,
                        $value->limitTag,
                        $value->responseType,
                        $value->replaceJsonRules,
                        $value->responseStockDataTag,
                        1
                    ),
                    "responseStockTimeTag" => $value->responseStockTimeTag,
                    "responseStockTimeFormat" => $value->responseStockTimeFormat,
                    "precision" => $value->precision,
                    "responseStockOpenTag" => $value->responseStockOpenTag,
                    "stockName" => $value->name,
                    "ReferenceURL" => $value->ReferenceURL,
                    "timeZone" => $value->timeZone
                ];
            }

            $data = [];
            foreach ($responseArray as $key => $value) {

                // Date Time format
                $findError = json_decode($value['data']['error']);

                if (!$findError) {
                    $responseData = json_decode($value["data"]['data'])[0];
                    $validate = $value["responseStockTimeTag"];
                    $date = date_create_from_format($value["responseStockTimeFormat"], $responseData->$validate, new DateTimeZone($value['timeZone']));
                    $date->setTimezone(new DateTimeZone(config('app.timezone')));
                    $stockDatetime = date_format($date, "H:i");

                    // number format
                    $valOpen = $value["responseStockOpenTag"];
                    $stockEndValue = number_format($responseData->$valOpen, $value["precision"], ".", "");
                    $stockEndValueAsStr = (string) $stockEndValue;
                    // format string
                    $data[] = [
                        'stockTimeStamp' => $stockDatetime,
                        'stockValue' =>  $stockEndValueAsStr,
                        "stockName" => $value["stockName"],
                        "referenceURL" => $value["ReferenceURL"]
                    ];
                } else {
                    $data[] = [
                        'stockTimeStamp' => null,
                        'stockValue' =>  "Can not get Data",
                        "stockName" => $value["stockName"],
                        "referenceURL" => $value["ReferenceURL"]
                    ];
                }
            }
            $response['res'] = Res::success($data);
        } catch (Exception $e) {
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }
        return $response;
    }
}
