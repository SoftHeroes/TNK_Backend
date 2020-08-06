<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\DB;
use App\Models\PortalProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ResponseController as Res;
use App\Providers\Stock\StockProvider;
use Exception;
use Illuminate\Support\Facades\Route as IlluminateRoute;
use App\Jobs\APILogJob;


/**
 * @group Stock
 * [All Stock Related APIs]
 */
class StockController extends Controller
{
    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam version float required App Version code is required Example: 1.0
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *              "stockName": "StockName",
     *              "stockUUID": "cac17677-3302-44e6-8045-dbf6c417fc4e",
     *              "referenceURL": "http://example.com/",
     *              "type": "china",
     *              "loop": 5,
     *              "gameUUID": null,
     *              "crawlData": []
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
    public function Stock(Request $request)
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

        $portalProviderModel = new PortalProvider();

        // compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'version' => 'required',

        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'version.required' => 'version is required.',

        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            //validating Provider UUID
            $providerData = $portalProviderModel->getPortalProviderByUUID($request->portalProviderUUID);

            if ($providerData->count(DB::raw('1')) == 0) {
                $errorFound = true;
                $response['res'] = Res::notFound([], 'Provider UUID does not exist.');
            } else {
                $portalProviderID = $providerData[0]->PID;
                $stockData = StockProvider::getActiveStockBaseOnProvider($portalProviderID, $request->portalProviderUUID);
                $response['res'] = Res::success($stockData['data']);
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
     * @bodyParam version float required App Version code is required Example: 1.0
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *          {
     *              "stockTimeStamp": "15:00",
     *              "stockValue": "3034.330",
     *              "stockName": "sh000001",
     *              "referenceURL": "http://example.com/"
     *          }
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
    public function getAllStock(Request $request)
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

        $stockProvider = new StockProvider();

        // compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'version' => 'required',
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'version.required' => 'version is required.',
        );

        try {
            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                $errorFound = true;
                $response['res'] = Res::validationError([], $validator->errors());
            } else {
                $version = $request->version;
                $response = $stockProvider->getAllStock($request->portalProviderUUID);
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
        } catch (Exception $ex) {
            return Res::errorException($ex->getMessage());
        }
    }


    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam version float required App Version code is required Example: 1.0
     * @response 200
     * {
     *    "code": 200,
     *    "data": [
     *        {
     *            "type": "china",
     *            "stocks": [
     *                {
     *                    "stockName": "sh000001",
     *                    "loops": [
     *                        {
     *                            "loopName": 5,
     *                            "gameID": "f8665eed-9090-4389-9cdc-ca48edcaf825",
     *                            "gameStatus": "Open"
     *                        }
     *                    ]
     *                },
     *                {
     *                    "stockName": "sh000300",
     *                    "loops": [
     *                        {
     *                            "loopName": 5,
     *                            "gameID": "64b320ff-bf4f-41a5-b33c-b7634a2dd319",
     *                            "gameStatus": "Open"
     *                        }
     *                    ]
     *                },
     *                {
     *                    "stockName": "sz399415",
     *                    "loops": [
     *                        {
     *                            "loopName": 5,
     *                            "gameID": "ce139b65-d924-441f-9411-6d8aa58411ee",
     *                            "gameStatus": "Open"
     *                        }                    ]
     *                },
     *                {
     *                    "stockName": "sz399001",
     *                    "loops": [
     *                        {
     *                            "loopName": 5,
     *                            "gameID": "0f8f00ba-2e7b-4881-81b3-471e7e0b07ee",
     *                            "gameStatus": "Open"
     *                        }
     *                    ]
     *                }
     *            ]
     *        },
     *        {
     *            "type": "crypto",
     *            "stocks": [
     *                {
     *                    "stockName": "btc",
     *                    "loops": [
     *                        {
     *                            "loopName": 5,
     *                            "gameID": "a51b271e-87a0-4839-b9c8-4124f56e2d20",
     *                            "gameStatus": "Open"
     *                        },
     *                        {
     *                            "loopName": 1,
     *                            "gameID": "4d568fdb-86bd-445a-bc59-f8532ec5255f",
     *                            "gameStatus": "Open"
     *                        }
     *                    ]
     *                }
     *            ]
     *        }
     *    ],
     *    "status": true,
     *    "message": ["success"]
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
    public function getActiveGamesByCategory(Request $request)
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

        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'version' => 'required',
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'version.required' => 'version is required.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $portalProviderUUID = $request->portalProviderUUID;
            $provider = new StockProvider(null);
            $response = $provider->getActiveGamesByCategory($portalProviderUUID);
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
