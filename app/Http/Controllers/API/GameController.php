<?php

namespace App\Http\Controllers\API;

use App\Providers\Gaming\GameProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ResponseController as Res;
use Illuminate\Support\Facades\Route as IlluminateRoute;
use App\Jobs\APILogJob;

/**
 * @group Game
 * [All Game Related APIs]
 */
class GameController extends Controller
{

    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam status.* integer required This consist of game status filter, it can be open(1), close-bet closed and waiting for result(2) or complete-game finished(3) and even all of them together. Example: 1
     * @bodyParam limit integer Specifying the number of records which we want to fetch Default : 10 Example: 10
     * @bodyParam offset integer Specifying the number of records we want to skip and fetch the data(basically for pagination) Default : 0 Example: 0
     * @bodyParam stockUUID UUID The unique id of the stock. Example: 9b1d2aef-7197-49cb-a983-a6e29f77e790
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *             "gameUUID": "bc845164-b76d-4597-8fd0-56ef8eb9f77a",
     *             "stockName": "btc1",
     *             "gameStartDate": "2020-02-29",
     *             "gameStartTime": "16:54:00",
     *             "gameEndDate": "2020-02-29",
     *             "gameEndTime": "16:55:00",
     *             "endStockValue": null,
     *             "gameStatus": "Open"
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
    public function getGames(Request $request)
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
            'status' => 'required|array',
            'status.*' => 'integer',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0',
            'version' => 'required',
            'stockUUID' => 'uuid'
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'status.required' => 'status is required.',
            'status.array' => 'status should be an array.',
            'limit.required' => 'limit is required.',
            'limit.integer' => 'limit should be an integer.',
            'limit.min' => 'limit should be greater than 0.',
            'offset.required' => 'offset is required.',
            'offset.integer' => 'offset should be an integer.',
            'offset.min' => 'offset should be greater than or equal to 0.',
            'version.required' => 'version is required.',
            'stockUUID.uuid' => 'stockUUID should be a valid UUID.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $portalProviderUUID = $request->portalProviderUUID;
            $gameStatus = $request->status;
            $limit = isEmpty($request->limit)  ? 10 : $request->limit;
            $offset = isEmpty($request->offset)  ? 0 : $request->offset;
            $stockUUID = isset($request->stockUUID) ? $request->stockUUID : null;

            $provider = new GameProvider($request);
            $response = $provider->getGames($portalProviderUUID, $gameStatus, $limit, $offset, $stockUUID);
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
