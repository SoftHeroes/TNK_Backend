<?php

namespace App\Http\Controllers\API;

use App\Providers\Gaming\RuleProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ResponseController as Res;
use Illuminate\Support\Facades\Route as IlluminateRoute;
use App\Jobs\APILogJob;

/**
 * @group Rules
 * [All Rules Related APIs]
 */
class RuleController extends Controller
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
     *             "ruleID": 1,
     *             "name": "FD_BIG"
     *         },
     *         {
     *             "ruleID": 2,
     *             "name": "FD_SMALL"
     *         },
     *         {
     *             "ruleID": 3,
     *             "name": "FD_ODD"
     *         },
     *         {
     *             "ruleID": 4,
     *             "name": "FD_EVEN"
     *         },
     *         {
     *             "ruleID": 5,
     *             "name": "FD_HIGH"
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
    public function getAllRules(Request $request)
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
            $provider = new RuleProvider(null);
            $response = $provider->getAllRules($request->portalProviderUUID);

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
