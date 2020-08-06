<?php

namespace App\Http\Controllers\API;

use DB;
use App\Models\User;
use App\Jobs\APILogJob;
use Illuminate\Http\Request;
use App\Models\PortalProvider;
use App\Http\Controllers\Controller;
use App\Providers\Gaming\GameProvider;
use App\Providers\Gaming\BettingProvider;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ResponseController as Res;
use Illuminate\Support\Facades\Route as IlluminateRoute;

require_once app_path() . '/Helpers/CommonUtility.php';

/**
 * @group Betting
 * [All Betting Related APIs]
 */
class BettingController extends Controller
{
    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam betData.*.gameUUID UUID required Unique game ID in which user is playing. Example: bc845164-b76d-4597-8fd0-56ef8eb9f77a
     * @bodyParam betData.*.ruleID integer required Unique rule ID on which user bets. Example: 3
     * @bodyParam betData.*.betAmount integer required The amount of chips user wants to place in bet. Example: 500
     * @response 200
     * {
     *  "code": 200,
     *  "data": [
     *      {
     *          "gameUUID": "bc845164-b76d-4597-8fd0-56ef8eb9f77a",
     *          "ruleID": "3",
     *          "betAmount": "1",
     *          "betUUID": "f88cb226-80ef-4d55-86bf-84eafa49566d",
     *          "payout": 1.95,
     *          "status": true,
     *          "createdDate": "2020-03-18",
     *          "createdTime": "16:22:44",
     *          "ruleName": "FD_ODD",
     *          "message": ["Bet placed successfully"]
     *      }
     *  ],
     *  "status": true,
     *  "message": ["Bet processed!"]
     * }
     *
     * @response 400
     * {
     *  "code": 400,
     *  "data": [],
     *  "status": false,
     *  "message": ["Not enough balance."]
     * }
     */
    public function storeBet(Request $request)
    {

        $requestTime = getCurrentTimeStamp();
        $errorFound = false;
        $exceptionFound = false;
        $adminData = request()->get('adminData');
        $source = $adminData[0]->source;
        $adminID = $adminData[0]->PID;
        $userID = null;
        $portalProviderID = null;
        $version = '0.0';

        // compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'userUUID' => 'required|uuid',
            'version' => 'required',
            'betData' => 'required|array',
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'version.required' => 'version is required.',
            'betData.required' => 'betData is required.',
            'betData.array' => 'betData should be an array.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        //Compulsory parameter check
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {

            $portalProviderUUID = $request->portalProviderUUID;
            $userUUID = $request->userUUID;
            $version =  $request->version;
            $betData = $request->betData;

            $provider = new BettingProvider(null);
            $response = $provider->storeBet($portalProviderUUID, $userUUID, $betData);

            $userID = $response['userID'];
            $portalProviderID = $response['portalProviderID'];
        }

        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

        // Piyush: Establishing the Job for API Logs.
        APILogJob::dispatch(
            IlluminateRoute::getFacadeRoot()->current()->uri(),
            $request->method(),
            $response['res']['code'],
            $message,
            $errorFound,
            $source,
            $portalProviderID,
            $adminID,
            $userID,
            $version,
            $requestTime,
            json_encode($request->all()),
            json_encode($response['res']),
            $exceptionFound
        )->onQueue('low');

        return $response['res'];
    }

    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server. (If this parameter is absent, it will show providers bet history otherwise shows users bet history) Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam betResult.* integer required This consist of bet result filter, it can be pending(-1), win(1) or lose(0) and even all of them together. Example: [-1,0,1]
     * @bodyParam limit integer Specifying the number of records which we want to fetch Default : 100 Example: 10
     * @bodyParam offset integer Specifying the number of records we want to skip and fetch the data(basically for pagination) Default : 0 Example: 0
     * @bodyParam dateRangeFrom date Specify the From date from which the betting information should be displayed Example: 2020-03-22
     * @bodyParam dateRangeTo date Specify the To date till which the betting information should be displayed.(This is required only when dateRangeFrom is provided)  Example: 2020-03-22
     * @bodyParam gameUUID UUID Specify the game ID to get games. Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam stockUUID UUID Specify the stock ID to get games. Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *             "userUUID": "ab14d362-597d-468c-8d9c-3a043ba8e79c",
     *             "loop": 1,
     *             "betUUID": "d67738c8-32bf-4dfe-835b-6b346218c160",
     *             "ruleName": "FD_ODD",
     *             "betAmount": 100,
     *             "rollingAmount": null,
     *             "payout": 1.95,
     *             "gameDraw": null,
     *             "betResult": "pending",
     *             "isFollowBet": 1,
     *             "createdDate": "2020-03-03",
     *             "createdTime": "15:16:33",
     *             "gameUUID": "bc845164-b76d-4597-8fd0-56ef8eb9f77a",
     *             "stockName": "btc1",
     *             "gameStartDate": "2020-02-29",
     *             "gameStartTime": "16:54:00",
     *             "gameStatus": "open"
     *         }
     *     ],
     *     "status": true,
     *     "message": ["success"]
     * }
     *
     * @response 400
     * {
     *     "code": 400,
     *     "data": [],
     *     "status": false,
     *     "message": [
     *             "portalProviderUUID should be a valid UUID."
     *     ]
     * }
     */

    public function getAllBets(Request $request)
    {
        $requestTime = getCurrentTimeStamp();
        $errorFound = false;
        $exceptionFound = false;
        $adminData = request()->get('adminData');
        $source = $adminData[0]->source;
        $adminID = $adminData[0]->PID;
        $userID = null;
        $portalProviderID = null;
        $version = '0.0';

        // compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'gameUUID' => 'nullable|uuid',
            'stockUUID' => 'nullable|uuid',
            'betResult' => 'required|array',
            'betResult.*' => 'integer',
            'version' => 'required',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0',
            'userUUID' => 'uuid',
            'dateRangeFrom' => "nullable|date_format:Y-m-d",
            'dateRangeTo' => "nullable|required_with:dateRangeFrom|date_format:Y-m-d|after_or_equal:dateRangeFrom|before:tomorrow"
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'betResult.required' => 'betResult is required.',
            'betResult.array' => 'status should be an array.',
            'version.required' => 'version is required.',
            'limit.integer' => 'limit should be an integer.',
            'limit.min' => 'limit should be greater than 0.',
            'offset.integer' => 'offset should be an integer.',
            'offset.min' => 'offset should be greater than or equal to 0.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'dateRangeFrom.date_format' => 'dateRangeFrom should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.required_with' => 'dateRangeTo is required when dateRangeFrom is present.',
            'dateRangeTo.date_format' => 'dateRangeTo should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.after_or_equal' => 'dateRangeTo should be greater than dateRangeFrom',
            'dateRangeTo.before' => 'dateRangeTo should not be greater than current date',
            'gameUUID.uuid' => 'gameUUID should be a valid UUID.',
            'stockUUID.uuid' => 'stockUUID should be a valid UUID.',

        );

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $userUUID = $request->userUUID;
            $version =  $request->version;
            $portalProviderUUID = $request->portalProviderUUID;
            $betResult = $request->betResult;
            $limit = isEmpty($request->limit)  ? 100 : $request->limit;
            $offset = isEmpty($request->offset)  ? 0 : $request->offset;
            $gameUUID = isEmpty($request->gameUUID)  ? null : $request->gameUUID;
            $stockUUID = isEmpty($request->stockUUID)  ? null : $request->stockUUID;

            $fromDate = isset($request->dateRangeFrom) ? $request->dateRangeFrom : null;
            $toDate = isset($request->dateRangeTo) ? $request->dateRangeTo : null;

            $provider = new BettingProvider(null);

            $response = $provider->getAllBets($portalProviderUUID, $betResult, $limit, $offset, $userUUID, $fromDate, $toDate, $gameUUID, $stockUUID);
            $userID = $response['userID'];
            $portalProviderID = $response['portalProviderID'];
        }

        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

        // Piyush: Establishing the Job for API Logs.
        APILogJob::dispatch(
            IlluminateRoute::getFacadeRoot()->current()->uri(),
            $request->method(),
            $response['res']['code'],
            $message,
            $errorFound,
            $source,
            $portalProviderID,
            $adminID,
            $userID,
            $version,
            $requestTime,
            json_encode($request->all()),
            json_encode($response['res']),
            $exceptionFound
        )->onQueue('low');

        return $response['res'];
    }

    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam portalProviderUserID string Unique ID of Portal Provider's User.<br><p style="color:red">** Remarks:<br>-> PortalProviderUserID is mandatory <br>-> Unique id has to be provided by the portal provider to maintain the mapping/identification of different users.Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40<p>
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam betResult.* integer required This consist of bet result filter, it can be pending(-1), win(1) or lose(0) and even all of them together. Example: [-1,0,1]
     * @bodyParam limit integer Specifying the number of records which we want to fetch Default : 100 Example: 10
     * @bodyParam offset integer Specifying the number of records we want to skip and fetch the data(basically for pagination) Default : 0 Example: 0
     * @bodyParam dateRangeFrom date Specify the From date from which the betting information should be displayed Example: 2020-03-22
     * @bodyParam dateRangeTo date Specify the To date till which the betting information should be displayed.(This is required only when dateRangeFrom is provided)  Example: 2020-03-22
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *             "portalProviderUserID": "ab14d362-597d-468c-8d9c-3a043ba8e79c",
     *             "loop": 1,
     *             "betUUID": "d67738c8-32bf-4dfe-835b-6b346218c160",
     *             "ruleName": "FD_ODD",
     *             "betAmount": 100,
     *             "rollingAmount": null,
     *             "payout": 1.95,
     *             "gameDraw": null,
     *             "betResult": "pending",
     *             "isFollowBet": 1,
     *             "createdDate": "2020-03-03",
     *             "createdTime": "15:16:33",
     *             "gameUUID": "bc845164-b76d-4597-8fd0-56ef8eb9f77a",
     *             "stockName": "btc1",
     *             "gameStartDate": "2020-02-29",
     *             "gameStartTime": "16:54:00",
     *             "gameStatus": "open"
     *         }
     *     ],
     *     "status": true,
     *     "message": ["success"]
     * }
     *
     * @response 400
     * {
     *     "code": 400,
     *     "data": [],
     *     "status": false,
     *     "message": [
     *             "portalProviderUUID should be a valid UUID."
     *     ]
     * }
     */

    public function getAllBetsByPortalProviderUserID(Request $request)
    {
        $requestTime = getCurrentTimeStamp();
        $errorFound = false;
        $exceptionFound = false;
        $adminData = request()->get('adminData');
        $source = $adminData[0]->source;
        $adminID = $adminData[0]->PID;
        $userID = null;
        $portalProviderID = null;
        $version = '0.0';

        // compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'portalProviderUserID' => 'nullable|string|max:255',
            'betResult' => 'required|array',
            'betResult.*' => 'integer',
            'version' => 'required',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0',
            'dateRangeFrom' => "nullable|date_format:Y-m-d",
            'dateRangeTo' => "nullable|required_with:dateRangeFrom|date_format:Y-m-d|after_or_equal:dateRangeFrom|before:tomorrow"
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'betResult.required' => 'betResult is required.',
            'betResult.array' => 'status should be an array.',
            'version.required' => 'version is required.',
            'limit.integer' => 'limit should be an integer.',
            'limit.min' => 'limit should be greater than 0.',
            'offset.integer' => 'offset should be an integer.',
            'offset.min' => 'offset should be greater than or equal to 0.',
            'portalProviderUserID.required' => 'portalProviderUserID is required.',
            'dateRangeFrom.date_format' => 'dateRangeFrom should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.required_with' => 'dateRangeTo is required when dateRangeFrom is present.',
            'dateRangeTo.date_format' => 'dateRangeTo should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.after_or_equal' => 'dateRangeTo should be greater than dateRangeFrom',
            'dateRangeTo.before' => 'dateRangeTo should not be greater than current date'
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {

            $version =  $request->version;
            $betResult = $request->betResult;
            $portalProviderUUID = $request->portalProviderUUID;
            $portalProviderUserID = $request->portalProviderUserID;
            $limit = isEmpty($request->limit)  ? 100 : $request->limit;
            $offset = isEmpty($request->offset)  ? 0 : $request->offset;


            $userModelRef = new User();
            $portalProviderModelRef = new PortalProvider();

            $portalProviderData = $portalProviderModelRef->getPortalProviderByUUID($portalProviderUUID);

            if ($portalProviderData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
            } else {
                $userData = $userModelRef->userAlreadyExists($portalProviderUserID, $portalProviderData[0]->PID);

                if (!isEmpty($portalProviderUserID) && $userData->count(DB::raw('1')) == 0) {
                    $response['res'] = Res::notFound([], 'portalProviderUserID does not exist.');
                } else {
                    $userUUID = isEmpty($portalProviderUserID) ? null : $userData[0]->UUID;
                    $fromDate = isset($request->dateRangeFrom) ? $request->dateRangeFrom : null;
                    $toDate = isset($request->dateRangeTo) ? $request->dateRangeTo : null;

                    $provider = new BettingProvider(null);

                    $response = $provider->getAllBets($portalProviderUUID, $betResult, $limit, $offset, $userUUID, $fromDate, $toDate, null, null, true);
                    $userID = $response['userID'];
                    $portalProviderID = $response['portalProviderID'];
                }
            }
        }

        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

        // Piyush: Establishing the Job for API Logs.
        APILogJob::dispatch(
            IlluminateRoute::getFacadeRoot()->current()->uri(),
            $request->method(),
            $response['res']['code'],
            $message,
            $errorFound,
            $source,
            $portalProviderID,
            $adminID,
            $userID,
            $version,
            $requestTime,
            json_encode($request->all()),
            json_encode($response['res']),
            $exceptionFound
        )->onQueue('low');

        return $response['res'];
    }

    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam gameUUID UUID Specify the game ID to get games. Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam version float required App Version code is required Example: 1.0
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *             "name": "BIG",
     *             "data": [2,3,4,5],
     *             "betCounts": [6,7,8,9]
     *         },
     *         {
     *             "name": "SMALL",
     *             "data": [10,11,12,13],
     *             "betCounts": [14,15,16,17]
     *         }
     *     ],
     *     "status": true,
     *     "message": ["success"]
     * }
     *
     * @response 400
     * {
     *     "code": 400,
     *     "data": [],
     *     "status": false,
     *     "message": [
     *             "portalProviderUUID should be a valid UUID."
     *     ]
     * }
     */

    public function liveBetCount(Request $request)
    {
        $requestTime = getCurrentTimeStamp();
        $errorFound = false;
        $exceptionFound = false;
        $adminData = request()->get('adminData');
        $source = $adminData[0]->source;
        $adminID = $adminData[0]->PID;
        $userID = null;
        $portalProviderID = null;
        $version = '0.0';

        // compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'gameUUID' => 'nullable|uuid|required_if:stockUUID,==,""',
            'stockUUID' => 'nullable|required_if:gameUUID,==,""|uuid',
            'loop' => 'nullable|required_with:stockUUID',
            'version' => 'required',
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'gameUUID.uuid' => 'gameUUID should be a valid UUID.',
            'gameUUID.required_if' => 'gameUUID is required.',
            'stockUUID.uuid' => 'stockUUID should be a valid UUID.',
            'stockUUID.required_if' => 'stockUUID is required when gameUUID is empty.',
            'loop.required_with' => 'loop is required.',
            'version.required' => 'version is required.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $provider = new GameProvider(null);

            $response = $provider->betCount($request->gameUUID, $request->portalProviderUUID,$request->stockUUID,$request->loop);
            $userID = $response['userID'];
            $portalProviderID = $response['portalProviderID'];
        }

        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

        // Piyush: Establishing the Job for API Logs.
        APILogJob::dispatch(
            IlluminateRoute::getFacadeRoot()->current()->uri(),
            $request->method(),
            $response['res']['code'],
            $message,
            $errorFound,
            $source,
            $portalProviderID,
            $adminID,
            $userID,
            $version,
            $requestTime,
            json_encode($request->all()),
            json_encode($response['res']),
            $exceptionFound
        )->onQueue('low');

        return $response['res'];
    }


    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam stockUUID UUID When gameUUID is null Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam gameUUID UUID Specify the game ID to get games. Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam loop When stockUUID is null loop will be required Example: 1
     * @response 200
     * {
     *     "code": 200,
     *     "data": {
     *         "totalUsers": 2,
     *         "totalBetCount": 5,
     *         "totalAmountPlaced": 750
     *     },
     *     "status": true,
     *     "message": ["success"]
     * }
     *
     * @response 400
     * {
     *     "code": 400,
     *     "data": [],
     *     "status": false,
     *     "message": [
     *             "portalProviderUUID should be a valid UUID."
     *             "gameUUID is required.",
     *             "stockUUID is required when gameUUID is empty."
     *     ]
     * }
     */

    public function liveCountBetData(Request $request)
    {
        $requestTime = getCurrentTimeStamp();
        $errorFound = false;
        $exceptionFound = false;
        $adminData = request()->get('adminData');
        $source = $adminData[0]->source;
        $adminID = $adminData[0]->PID;
        $userID = null;
        $portalProviderID = null;
        $version = '0.0';

        // compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'gameUUID' => 'nullable|uuid|required_if:stockUUID,==,""',
            'stockUUID' => 'nullable|required_if:gameUUID,==,""|uuid',
            'loop' => 'nullable|required_with:stockUUID',
            'version' => 'required',
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'gameUUID.uuid' => 'gameUUID should be a valid UUID.',
            'gameUUID.required_if' => 'gameUUID is required.',
            'stockUUID.uuid' => 'stockUUID should be a valid UUID.',
            'stockUUID.required_if' => 'stockUUID is required when gameUUID is empty.',
            'loop.required_with' => 'loop is required.',
            'version.required' => 'version is required.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $provider = new GameProvider(null);

            $response = $provider->liveCountBetData($request->gameUUID, $request->portalProviderUUID,$request->stockUUID,$request->loop);
            $userID = $response['userID'];
            $portalProviderID = $response['portalProviderID'];
        }

        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

        // Piyush: Establishing the Job for API Logs.
        APILogJob::dispatch(
            IlluminateRoute::getFacadeRoot()->current()->uri(),
            $request->method(),
            $response['res']['code'],
            $message,
            $errorFound,
            $source,
            $portalProviderID,
            $adminID,
            $userID,
            $version,
            $requestTime,
            json_encode($request->all()),
            json_encode($response['res']),
            $exceptionFound
        )->onQueue('low');

        return $response['res'];
    }
}
