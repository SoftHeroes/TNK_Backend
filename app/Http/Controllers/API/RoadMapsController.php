<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\Stock\StockProvider;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ResponseController as Res;
use Illuminate\Support\Facades\Route as IlluminateRoute;
use App\Jobs\APILogJob;

require_once app_path() . '/Helpers/CommonUtility.php';

/**
 * @group Roadmap
 * [All Roadmap Related APIs]
 */
class RoadMapsController extends Controller
{

    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam stockUUID.* UUID required List of StockUUID which save in EC gaming server Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam limit integer The limit for get data with define record.Example: 2
     * @bodyParam version float required App Version code is required Example: 1.0
     *
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *             "stockName": "btc5",
     *             "category": "crypto",
     *             "stockStatus": "open",
     *             "roadMap": [
     *                 {
     *                     "stockTimeStamp": "2020-05-29 02:35:00",
     *                     "stockValue": "9529.39",
     *                     "number1": 3,
     *                     "number2": 9
     *                 }
     *             ],
     *             "gameData": [
     *                 {
     *                     "gameUUID": "08df0211-405f-4e26-b9b1-052cd380f4ef",
     *                     "gameStatus": "Open"
     *                 }
     *             ],
     *             "isOldData": false
     *         }
     *     ],
     *     "status": true,
     *     "message": [
     *         "success"
     *     ]
     * }
     *
     * @response 400
     * {
     *     "code": 400,
     *     "data": [],
     *     "status": false,
     *     "message": ["Invalid stockUUID!!"]
     * }
     */
    public function getRoadMap(Request $request)
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
            'stockUUID' => 'required|array',
            'stockUUID.*' => 'uuid',
            'limit' => 'nullable|integer|min:1|max:2000',
            'version' => 'required'
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'stockUUID.required' => 'stockUUID is required.',
            'stockUUID.array' => 'stockUUID should be a valid array.',
            'limit.integer' => 'limit should be an integer.',
            'limit.min' => 'limit should be greater than 0.',
            'limit.max' => 'limit should be less than 2000.',
            'version.required' => 'version is required.',

        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        //Compulsory parameter check
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;

            $serviceProviderRef = new StockProvider(null);

            $limit = isEmpty($request->limit) ? 300 : $request->limit;

            $response = $serviceProviderRef->getMultipleRoadMap($request->portalProviderUUID, $request->stockUUID, $limit);

            //$userID = $response['userID'];
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
