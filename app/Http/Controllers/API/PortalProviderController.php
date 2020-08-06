<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseController as Res;
use App\Models\PortalProvider;
use App\Models\ProviderConfig;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

require_once app_path() . '/Helpers/CommonUtility.php';
/**
 * @group PortalProvider
 * [All PortalProvider Related APIs]
 */
class PortalProviderController extends Controller
{
    /**
     * @authenticated
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *             "PID": 1,
     *             "name": "TNKMaster",
     *             "currencyID": 1,
     *             "creditBalance": -1,
     *             "mainBalance": -1,
     *             "UUID": "1e4ab5a4-64ba-4c3f-b92a-71dd7baa3014"
     *         },
     *         {
     *             "PID": 2,
     *             "name": "PortalProvider_Test_1",
     *             "currencyID": 1,
     *             "creditBalance": 500000,
     *             "mainBalance": 500000,
     *             "UUID": "83081e86-92fc-4d4a-bc9e-39a3ba83fbf5"
     *         },
     *         {
     *             "PID": 3,
     *             "name": "PortalProvider_Test_2",
     *             "currencyID": 1,
     *             "creditBalance": 10000,
     *             "mainBalance": 10000,
     *             "UUID": "3c1acd9d-4900-4d1b-8426-49ad5e5cd6ef"
     *         },
     *         {
     *             "PID": 4,
     *             "name": "Shareholder1",
     *             "currencyID": 1,
     *             "creditBalance": 1000000,
     *             "mainBalance": 1000000,
     *             "UUID": "46a31ffa-39ca-4a22-8dbf-3ef7fe2e7a28"
     *         }
     *     ],
     *     "status": true,
     *     "message": ["success"]
     * }
     *
     * @response 400
     * {
     *       "code": 404,
     *       "data": [],
     *       "status": false,
     *       "message": ["Invalid Credentials"]
     *   }
     */
    public function getPortalProviders(Request $request)
    {
        // Check ENV is on Production is TRUE Then return 404 page
        if (IsAuthEnv()) {
            return Res::viewNotFound();
        }

        try {
            $column = ['PID', 'name', 'currencyID', 'creditBalance', 'mainBalance', 'UUID'];
            $data   = PortalProvider::getPortalProviders()->select($column); // portal provider list

            return Res::success($data->get()); // out put data
        } catch (Exception $ex) {
            return Res::errorException($ex->getMessage());
        }
    }

    // Piyush: API for get config for portal provider.
    /**
     * @authenticated
     * @response 200
     * {
     *     "code": 200,
     *     "data": {
     *         "followBetSetupID": 1,
     *         "createdAt": "2020-04-06 09:23:53",
     *         "updatedAt": "2020-04-06 09:23:53"
     *     },
     *     "status": true,
     *     "message": ["success"]
     * }
     *
     * @response 400
     * {
     *       "code": 404,
     *       "data": [],
     *       "status": false,
     *       "message": ["Invalid Credentials"]
     *   }
     */
    public function getPortalProviderConfig(Request $request)
    {
        try {
            // 1. Check validation first.
            $rules = array(
                'portalProviderUUID' => 'required|uuid',
                'version'            => 'required',
            );

            $messages = array(
                'portalProviderUUID.required' => 'portalProviderUUID is required.',
                'portalProviderUUID.uuid'     => 'portalProviderUUID should be a valid UUID.',
                'version.required'            => 'version is required',
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return Res::validationError([], $validator->errors());
            }

            // 2. Get portal provider.
            $providerModel  = new PortalProvider();
            $portalProvider = $providerModel->getPortalProviderByUUID($request->portalProviderUUID);

            // 3. Find their config.
            $configModel  = new ProviderConfig();
            $column = ['followBetSetupID', 'createdAt', 'updatedAt'];
            $configData = $configModel->getProviderConfigByPID($portalProvider[0]->PID)->select($column);

            return Res::success($configData->first());
        } catch (Exception $e) {
            return Res::errorException($e->getMessage());
        }
    }
}
