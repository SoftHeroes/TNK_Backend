<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\PortalProvider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ResponseController as Res;
use Illuminate\Support\Facades\Route as IlluminateRoute;
use Illuminate\Support\Facades\DB;
use App\Jobs\APILogJob;

/**
 * @group Country
 * [All Country Related APIs]
 */
class CountryController extends Controller
{
    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server. (If this parameter is absent, it will show providers bet history otherwise shows users bet history) Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @response 200
     * {
     *       "code": 200,
     *       "data": [
     *           {
     *               "code": "AFG",
     *               "country": "Afghanistan"
     *           },
     *           {
     *               "code": "ALA",
     *               "country": "Åland"
     *           },
     *           {
     *               "code": "ALB",
     *               "country": "Albania"
     *           },
     *           {
     *               "code": "DZA",
     *               "country": "Algeria"
     *           },
     *           {
     *               "code": "ASM",
     *               "country": "American Samoa"
     *           },
     *           {
     *               "code": "AND",
     *               "country": "Andorra"
     *           },
     *           {
     *               "code": "AGO",
     *               "country": "Angola"
     *           },
     *           {
     *               "code": "AIA",
     *               "country": "Anguilla"
     *           }
     *       ],
     *       "status": true,
     *       "message": ["Records fetched successfully."]
     *   }
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
    public function getCountryList(Request $request)
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

        $countryModel = new Country();
        $userModel = new User();
        $providerModel = new PortalProvider();

        // compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'userUUID' => 'required|uuid',
            'version' => 'required',

        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'version.required' => 'version is required.',

        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $providerData = $providerModel->getPortalProviderByUUID($request->portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
            } else {
                $portalProviderID = $providerData[0]->PID;

                $userData = $userModel->getUserByUUID($request->userUUID)->select('PID')->get();
                if ($userData->count(DB::raw('1')) == 0) {
                    $response['res'] = Res::notFound([], 'User UUID does not exist.');
                } else {
                    $userID = $userData[0]->PID;
                    $data = $countryModel->getCountry();
                    $response['res'] = Res::success($data, 'Records fetched successfully.');
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
}
