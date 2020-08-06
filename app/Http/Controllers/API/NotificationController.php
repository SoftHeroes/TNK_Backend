<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ResponseController as Res;
use App\Providers\Users\NotificationProvider;
use Illuminate\Validation\Rule;
use App\Jobs\APILogJob;
use Illuminate\Support\Facades\Route as IlluminateRoute;


/**
 * @group Notification
 * [All Notification Related APIs]
 */
class NotificationController extends Controller
{

    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam title string required This is Title of the Notification. Example: This is the title.
     * @bodyParam message string required This is the body/message of the Notification. Example: This is the example body
     * @response 200
     * {
     *      "code": 200,
     *      "data": [],
     *      "status": true,
     *      "message": [
     *              "success"
     *      ]
     * }
     *
     * @response 400
     * {
     *      "code": 400,
     *      "data": [],
     *      "status": false,
     *      "message": [
     *              "portalProviderUUID should be a valid UUID."
     *       ]
     *  }
     */

    public function addNotification(Request $request)
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

        //compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'type' => 'nullable|' . Rule::in([0, 1, 2, 3, 4]),                                 //'0 = admin , 1 = follow , 2 = unFollow, 3 = balanceUpdate, 4 = welcome'
            'fromUUID' => 'uuid|nullable',
            'toUUID' => 'uuid|nullable',
            'title' => 'required|max:255',
            'message' => 'required|max:1000',
            'version' => 'required',

        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'type.in' => 'Notification type should be in (0 = admin , 1 = follow , 2 = unFollow, 3 = balanceUpdate)',
            'fromUUID.uuid' => 'fromUUID should be a valid UUID.',
            'toUUID.uuid' => 'toUUID should be a valid UUID.',
            'title.required' => 'title is required.',
            'title.max' => 'title should not be longer than 255 characters.',
            'message.required' => 'message is required.',
            'message.max' => 'message should not be longer than 1000 characters.',
            'version.required' => 'version is required.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $portalProviderUUID = $request->portalProviderUUID;
            // only admin will be able to trigger the notification using this api (following will3 parameters will be hard coded)
            $type = isEmpty($request->type)  ? 0 : $request->type;
            $fromUUID =  isEmpty($request->fromUUID)  ? null : $request->fromUUID;
            $toUUID =  isEmpty($request->toUUID)  ? null : $request->toUUID;

            $title = $request->title;
            $message = $request->message;

            $notificationProvider = new NotificationProvider(null);
            $response = $notificationProvider->addNotification($portalProviderUUID, $type, $fromUUID, $toUUID, $title, $message);

            $userID = $response['userID'];
            $portalProviderID = $response['portalProviderID'];
        }

        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

        //API Log
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
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server. Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam limit integer Specifying the number of records which we want to fetch Default : 50 Example: 10
     * @bodyParam offset integer Specifying the number of records we want to skip and fetch the data(basically for pagination) Default : 0 Example: 0
     * @response 200
     *{
     *      "code": 200,
     *      "data": [
     *          {
     *              "notificationUUID": "e5a08fd8-b4a9-4a59-a82f-4906d73d08d6",
     *              "fromUUID": null,
     *              "toUUID": null,
     *              "type": 0,
     *              "title": "Admin Notification",
     *              "message": "Notification is sent from from Admin.",
     *              "createdAt": "2020-04-20 11:23:14"
     *          },
     *          {
     *              "notificationUUID": "56c45de3-2b8e-4a51-bf5d-f9fe1a621464",
     *              "fromUUID": "68e0b79f-60d5-48d4-af0c-ec0490e5b671",
     *              "toUUID": "0c246bf8-221b-4fac-bec2-f6f9b365b25d",
     *              "type": 1,
     *              "title": "You have got a new follower!",
     *              "message": "PoetParth has started following you !!",
     *              "createdAt": "2020-04-20 13:58:15"
     *          }
     *
     *      ],
     *      "status": true,
     *      "message": [
     *          "success"
     *      ]
     *  }
     *
     * @response 400
     * {
     *      "code": 400,
     *      "data": [],
     *      "status": false,
     *      "message": [
     *              "portalProviderUUID should be a valid UUID."
     *       ]
     *  }
     */

    public function getNotification(Request $request)
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

        //compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'userUUID' => 'required|uuid',
            'version' => 'required',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0',
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'version.required' => 'version is required.',
            'limit.integer' => 'limit should be an integer.',
            'limit.min' => 'limit should be greater than 0.',
            'offset.integer' => 'offset should be an integer.',
            'offset.min' => 'offset should be greater than or equal to 0.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $portalProviderUUID = $request->portalProviderUUID;
            $userUUID = $request->userUUID;
            $limit = isEmpty($request->limit)  ? 50 : $request->limit;
            $offset = isEmpty($request->offset)  ? 0 : $request->offset;

            $notificationProvider = new NotificationProvider(null);
            $response = $notificationProvider->getNotification($portalProviderUUID, $userUUID, $limit, $offset);

            $userID = $response['userID'];
            $portalProviderID = $response['portalProviderID'];
        }

        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

        //API Log
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
