<?php

namespace App\Http\Controllers\API;

use App\Events\Socket\MessageSentEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ResponseController as Res;
use App\Providers\Chat\ChatProvider;

require_once app_path() . '/Helpers/CommonUtility.php';


/**
 * @group Chat
 * [All Chat Related APIs]
 */
class ChatController extends Controller
{
    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam gameUUID UUID Unique game ID in which user is playing.(This is required only when the chatType is 1 ) Example: bc845164-b76d-4597-8fd0-56ef8eb9f77a
     * @bodyParam chatType integer required Chat type can be Game-based(1) or global(2) Example: 1

     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *             "userName": "user001",
     *             "userUUID": "b86a42e4-d18c-4fdd-8de2-cfd7c18bee54",
     *             "message": "message 56465 hjikghjb",
     *             "date": "2020-03-04 11:24:42"
     *         },
     *         {
     *             "userName": "user001",
     *             "userUUID": "b86a42e4-d18c-4fdd-8de2-cfd7c18bee54",
     *             "message": "messag",
     *             "date": "2020-03-04 11:28:37"
     *         },
     *         {
     *             "userName": "user001",
     *             "userUUID": "b86a42e4-d18c-4fdd-8de2-cfd7c18bee54",
     *             "message": "fh  sdfh  sfh",
     *             "date": "2020-03-04 11:28:43"
     *         }
     *     ],
     *     "status": true,
     *     "message": ["success"],
     *     "author": "83081e86-92fc-4d4a-bc9e-39a3ba83fbf5.bc845164-b76d-4597-8fd0-56ef8eb9f77a"
     * }
     *
     * @response 400
     * {
     *     "code": 400,
     *     "data": [],
     *     "status": false,
     *     "message": ["portalProviderUUID does not exist."],
     *     "author": "e530f08a-87e6-485f-b595-c1bb461afdf1.953acb26-5045-452f-9f88-d3b6845e4232"
     * }
     */
    public function index(Request $request)
    {

        // compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'userUUID' => 'required|uuid',
            'chatType' => 'required',
            'gameUUID' => 'nullable|uuid|required_if:chatType,1',
            'version' => 'required'
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'chatType.required' => 'chatType is required.',
            'gameUUID.uuid' => 'gameUUID should be a valid UUID.',
            'gameUUID.required_if' => 'gameUUID is required.',
            'version.required' => 'version is required'
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            return Res::validationError([], $validator->errors());
        }

        $serviceProviderRef = new ChatProvider(null);

        $portalProviderUUID = $request->portalProviderUUID;
        $gameUUID = isEmpty($request->gameUUID)  ? '' : $request->gameUUID;
        $chatType = $request->chatType;
        $message = $request->message;

        $response = $serviceProviderRef->getMessage($portalProviderUUID, $gameUUID, $chatType);
        // check game or global
        if ($chatType == 1) {
            $response['author'] = $portalProviderUUID . '.' . $gameUUID;
        } else {
            $response['author'] = $portalProviderUUID . '.global';
        }
        broadcast(new MessageSentEvent($response));
        return $response;
    }

    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam gameUUID UUID required Unique game ID in which user is playing.(This is required only when chatType is 1) Example: bc845164-b76d-4597-8fd0-56ef8eb9f77a
     * @bodyParam chatType integer required Chat type can be Game-based(1) or global(2) Example: 1
     * @bodyParam message string required Message Text which users is sending. Example: Hi, That was a great game!

     * @response 200
     * {
     *     "code": 200,
     *     "data": {
     *         "userName": "User57211720360433",
     *         "userUUID": "94da65de-5857-4a16-b828-4066c7b9c0d1",
     *         "message": "Hi, That was a great game!",
     *         "date": "2020-04-22 10:52:17"
     *     },
     *     "status": true,
     *     "message": [
     *         "success"
     *     ],
     *     "author": "5e5558c5-6909-4592-a606-b2d3715712a9.global"
     * }
     *
     * @response 400
     * {
     *     "code": 400,
     *     "data": [],
     *     "status": false,
     *     "message": ["portalProviderUUID does not exist."],
     *     "author": "e530f08a-87e6-485f-b595-c1bb461afdf1.953acb26-5045-452f-9f88-d3b6845e4232"
     * }
     */
    public function store(Request $request)
    {
        // compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'userUUID' => 'required|uuid',
            'chatType' => 'required',
            'gameUUID' => 'uuid|required_if:chatType,1',
            'version' => 'required',
            'message' => 'required'
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'gameUUID.uuid' => 'gameUUID should be a valid UUID.',
            'gameUUID.required_if' => 'gameUUID is required.',
            'chatType.required' => 'chatType is required.',
            'version.required' => 'version is required.',
            'message.required' => 'message is required.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            return Res::validationError([], $validator->errors());
        }

        $serviceProviderRef = new ChatProvider(null);

        $portalProviderUUID = $request->portalProviderUUID;
        $userUUID = $request->userUUID;
        $gameUUID = isEmpty($request->gameUUID)  ? '' : $request->gameUUID;

        $chatType = $request->chatType;
        $message = $request->message;

        $response = $serviceProviderRef->sendMessage($portalProviderUUID, $userUUID, $gameUUID, $chatType, $message);
        // check game or global
        if ($chatType == 1) {
            $response['author'] = $portalProviderUUID . '.' . $gameUUID;
        } else {
            $response['author'] = $portalProviderUUID . '.global';
        }
        broadcast(new MessageSentEvent($response));

        return $response;
    }
}
