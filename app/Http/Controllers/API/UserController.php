<?php

namespace App\Http\Controllers\API;

use App\Events\Socket\MessageSentEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseController as Res;
use App\Jobs\APILogJob;
use App\Models\PortalProvider;
use App\Providers\Users\FollowUserProvider;
use App\Providers\Users\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as IlluminateRoute;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

require_once app_path() . '/Helpers/CommonUtility.php';
/**
 * @group User
 * [All User Related APIs]
 */
class UserController extends Controller
{
    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam dateRangeFrom Date(yyyy-mm-dd) Start Date from which the online history need to display.In response tag(activeTimeDateWise). Example: 2020-02-21
     * @bodyParam dateRangeTo Date(yyyy-mm-dd) End Date till which the online history need to display.In response tag(activeTimeDateWise). Example: 2020-02-23
     * @response 200
     * {
     *     "code": 200,
     *     "data": {
     *         "userName": "User36121520510365",
     *         "loginTime": "2020-03-12 15:51:36",
     *         "firstName": null,
     *         "lastName": null,
     *         "email": null,
     *         "profileImage": null,
     *         "balance": 10000,
     *         "isLoggedIn": "true",
     *         "isActive": "active",
     *         "userUUID": "7ad16d4d-90b4-4a8f-981f-5731d6122db1",
     *         "gender": null,
     *         "country": null,
     *         "isAllowToVisitProfile": true,
     *         "isAllowToFollow": true,
     *         "isAllowToDirectMessage": true,
     *         "isSound": true,
     *         "isAllowToLocation": true,
     *         "rollingAmount": 4900,
     *         "currentActiveTime": "0 hours, 24 minutes, 40 seconds",
     *         "totalLikes": 99
     *         "activeTimeDateWise" :
     *          [
     *              {
     *                  "activeTimeInMins": "60",
     *                  "Date": "2020-02-01"
     *              },
     *              {
     *                  "activeTimeInMins": "60",
     *                  "Date": "2020-02-02"
     *              }
     *          ]
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
     *     ]
     * }
     */

    public function getUserProfile(Request $request)
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
            'dateRangeFrom' => "nullable|date_format:Y-m-d",
            'dateRangeTo' => "nullable|required_with:dateRangeFrom|date_format:Y-m-d|after_or_equal:dateRangeFrom|before:tomorrow",
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'version.required' => 'version is required.',
            'dateRangeFrom.date_format' => 'dateRangeFrom should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.required_with' => 'dateRangeTo is required when dateRangeFrom is present.',
            'dateRangeTo.date_format' => 'dateRangeTo should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.after_or_equal' => 'dateRangeTo should be greater than dateRangeFrom',
            'dateRangeTo.before' => 'dateRangeTo should not be greater than current date',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $portalProviderUUID = $request->portalProviderUUID;
            $userUUID = $request->userUUID;

            $fromDate = ($request->dateRangeFrom) ? $request->dateRangeFrom : "";
            $toDate = ($request->dateRangeTo) ? $request->dateRangeTo : "";

            $provider = new UserProvider($request);
            $response = $provider->getUserProfile($portalProviderUUID, $userUUID, $fromDate, $toDate);

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
     * appApi/updateUserProfile
     * This api accept request as form data.So, send bellow field as form data
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam profileImage image profile image of the user Example: uploading a image file
     * @bodyParam email email Users Email ID Example: parth.ravani@gmail.com
     * @bodyParam firstName string First name of user Example: John
     * @bodyParam middleName string Middle name of user Example: Joey
     * @bodyParam avatarID integer Avatar Id which user want to save Example: 1
     * @bodyParam lastName string Last name of user Example: Clark
     * @bodyParam country string User Country code Example: IND
     * @bodyParam gender string User gender {male,female,other} Example: male
     * @bodyParam userName string Unique username of user Example: PoetParth
     * @response 200
     *   {
     *       "code": 200,
     *       "data": [],
     *       "status": true,
     *       "message": ["success"]
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
    public function updateUserProfile(Request $request)
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

        // Compulsory parameters check
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'userUUID' => 'required|uuid',
            'profileImage' => 'image|mimes:jpeg,png,jpg,svg|max:' . config("app.valid_image_size_in_kilo_bytes"),
            'avatarID' => 'integer|' . Rule::in([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            'email' => 'nullable|regex:/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+\.[a-zA-Z]{2,4}/',
            'version' => 'required',
            'gender' => Rule::in(['male', 'female', 'other']),
            'country' => 'exists:country,alphaThreeCode',
        );
        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'email.regex' => 'It should be a valid email.',
            'profileImage.image' => 'It should be a image file.',
            'profileImage.mimes' => 'Only jpeg, png, jpg and svg files are allowed.',
            'profileImage.max' => 'Image size should not be greater than ' . config("app.valid_image_size_in_kilo_bytes") . 'KB.',
            'avatarID.integer' => 'avatarID should be an integer',
            'avatarID.in' => 'avatarID should be in [1,2,3,4,5,6,7,8,9,10]',
            'version.required' => 'version is required.',
            'gender.in' => 'Gender should be either male, female or other.',
            'country.exists' => 'should be a valid country',
        );
        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            if ($request->profileImage != "") {
                $files = $request->file('profileImage');
                $extension = strtolower($files->getClientOriginalExtension());
                // Piyush: Dynamic file extension checker.
                if (!in_array($extension, explode(',', config('constants.image_file_type_accept')))) {
                    $errorFound = true;
                    $response['res'] = Res::badRequest([], 'image file does not have a valid extension');
                }
            }

            if (!$errorFound) {
                $version = $request->version;
                $provider = new UserProvider($request);
                $response = $provider->updateUserProfile($request);
                $userID = $response['userID'];
                $portalProviderID = $response['portalProviderID'];
            }
        }
        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

        // Piyush: Establishing the Job for API Logs, Adding custom logs for the FORM request.
        $requestLog = array();
        $requestLog = $request->post();
        $requestLog['profileImage'] = isEmpty($request->file('profileImage')) ? null : $request->file('profileImage')->getClientOriginalName();
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
            json_encode($requestLog),
            json_encode($response['res']),
            $exceptionFound
        )->onQueue('low');

        return $response['res'];
    }

    /**
     * @authenticated
     * @bodyParam portalProviderUUID UUID required The unique id of portal provider.<br><p style="color:red">This is provided at the time of registration.</p> Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam isAllowToVisitProfile Boolean To set profile privacy send <strong>false</strong> or <strong>true</strong> for public. Example: true
     * @bodyParam isAllowToFollow Boolean For allowing follow option send <strong>true</strong> or send <strong>false</strong> Example: true
     * @bodyParam isAllowToDirectMessage Boolean For allowing direct message send <strong>true</strong> or <strong>false</strong> Example: true
     * @bodyParam isSound Boolean For enabling sound <strong>true</strong> or <strong>false</strong> Example: true
     * @bodyParam isAllowToLocation Boolean For allowing location show send <strong>true</strong> or <strong>false</strong> Example: true
     * @response 200
     *  {
     *     "code": 200,
     *     "data": {
     *         "isAllowToVisitProfile": true,
     *         "isAllowToFollow": false,
     *         "isAllowToDirectMessage": false,
     *         "isSound": false,
     *         "isAllowToLocation": true
     *     },
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
     *     "message": [
     *             "portalProviderUUID should be a valid UUID."
     *     ]
     * }
     */
    public function updateUserSetting(Request $request)
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
            'userUUID' => 'required|uuid',
            'version' => 'required',
            'isAllowToVisitProfile' => ['nullable', 'boolean', Rule::in([true, false]), 'checkTrueFalse'],
            'isAllowToFollow' => ['nullable', 'boolean', Rule::in([true, false]), 'checkTrueFalse'],
            'isAllowToDirectMessage' => ['nullable', 'boolean', Rule::in([true, false]), 'checkTrueFalse'],
            'isSound' => ['nullable', 'boolean', Rule::in([true, false]), 'checkTrueFalse'],
            'isAllowToLocation' => ['nullable', 'boolean', Rule::in([true, false]), 'checkTrueFalse'],
        );

        // Piyush: Return these messages when requested values are null.
        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'version.required' => 'version is required.',
            'isAllowToVisitProfile' => 'isAllowToVisitProfile can not be null.',
            'isAllowToFollow' => 'isAllowToFollow can not be null.',
            'isAllowToDirectMessage' => 'isAllowToDirectMessage can not be null.',
            'isSound' => 'isSound can not be null.',
            'isAllowToLocation' => 'isAllowToLocation can not be null.',
        );

        // Piyush: Check the validation to make sure requested value is either true(Boolean) or either false(Boolean) only.
        Validator::extend('checkTrueFalse', function ($attribute, $value) {
            return $value === true || $value === false ? true : false;
        }, ':attribute is must be true or false.');

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $response = UserProvider::updateUserSetting($request);
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
     * @bodyParam portalProviderUserID string required Unique ID of Portal Provider's User.<br><p style="color:red">** Remarks:<br>-> PortalProviderUserID is mandatory <br>-> Unique id has to be provided by the portal provider to maintain the mapping/identification of different users<p>.Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam balance integer required The amount of chips with which users will login into EC gaming.<br><p style="color:red">** Remarks:<br>-> Balance is mandatory <br>-> This amount will be deducted from the portal provider's main balance..Example: 1000<p>
     *
     * @response 200
     * {
     *   "code": 200,
     *   "data": {
     *     "userUUID": "c1d9312e-979a-449f-bf19-d6e3a324f6ab"
     *   },
     *   "status": true,
     *   "message": [
     *     "user login Successfully"
     *   ]
     * }
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
    public function loginAppUsers(Request $request)
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

        // Json tag validation block : Start
        $rules = array(
            "balance" => "bail|required|integer|min:1",
            "portalProviderUserID" => "bail|required",
            "portalProviderUUID" => "bail|required|uuid",
            'version' => 'required',
        );

        $messages = [
            "balance.required" => "balance field is required.",
            "balance.integer" => "balance should be integer type.",
            "balance.min" => "balance should be greater then zero(0).",
            "portalProviderUserID.required" => "PortalProviderUserID field is required.",
            "portalProviderUUID.required" => "PortalProviderUUID field is required.",
            "portalProviderUUID.uuid" => "invalid UUID.",
            'version.required' => 'version is required.',

        ];

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;

            // Json to variables block
            $balance = (float) trim($request->input('balance'));
            $balance = round($balance, 2);
            $portalProviderUserID = trim($request->input('portalProviderUserID'));
            $portalProviderUUID = trim($request->input('portalProviderUUID'));

            $userProvider = new UserProvider(null); // creating login provider object

            $response = $userProvider->createOrLoginUser($portalProviderUUID, $portalProviderUserID, $balance, $adminID, $source);
            $portalProviderID = $response['portalProviderID'];
            $userID = $response['userID'];
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
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     *
     * @response 200
     * {
     *     "code": 200,
     *     "data": [],
     *     "status": true,
     *     "message": ["User logged out successfully"]
     * }
     *
     * @response 400
     * {
     *     "code": 400,
     *     "data": [],
     *     "status": false,
     *     "message": ["Invalid User!!"]
     * }
     */
    public function logoutAppUsers(Request $request)
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
            'userUUID' => 'bail|required|uuid',
            'version' => 'required',
        );

        $messages = array(
            'userUUID.required' => 'userUUID field is required',
            "userUUID.uuid" => "invalid UUID.",
            'version' => 'version field is required.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        //Compulsory parameter check
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $userProvider = new UserProvider(null);
            $response = $userProvider->logoutUser($request->userUUID, $adminData[0]->PID, $adminData[0]->source);

            $userID = $response['userID'];
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
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam followToUUID UUID required The user unique id which is saved in the EC gaming server.  which is being followed Example: 390b3622-66a3-4ebc-b802-f485cb6e132a
     * @bodyParam method integer required 1 to follow a user , 2 to un-follow a user Example: 1
     * @bodyParam followBetRule.*.id integer The follow type (Amount = 1, Rate = 2) by which the user is going to be followed. Example: 1
     * @bodyParam followBetRule.*.value integer The value of selected follow type. Example: 100
     * @bodyParam unFollowBetRule.*.id integer The un-follow type (By time = 3, By win = 4, By lose = 5, By bets = 6) by which the user will un-follow. Example: 3
     * @bodyParam unFollowBetRule.*.value integer The value of selected follow type. Example: 100
     * @bodyParam version float required App Version code is required Example: 1.0
     *
     * @response 200
     * {
     *     "code": 200,
     *     "data": [],
     *     "status": true,
     *     "message": ["User followed successfully."]
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
    public function FollowUser(Request $request)
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
            'userUUID' => 'bail|required|uuid',
            'portalProviderUUID' => 'bail|required|uuid',
            'followToUUID' => 'bail|required|uuid',
            'method' => 'required|' . Rule::in([1, 2]),
            'followBetRule' => 'array',
            'followBetRule.*.id' => 'integer|exists:followBetRule,PID',
            'followBetRule.*.value' => 'integer',
            'unFollowBetRule' => 'array',
            'unFollowBetRule.*.id' => 'integer|exists:followBetRule,PID',
            'unFollowBetRule.*.value' => 'integer',
            'version' => 'required'

        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'followToUUID.required' => 'followToUUID is required.',
            'followToUUID.uuid' => 'followToUUID should be a valid UUID.',
            'followBetRuleID.array' => 'followBetRuleID should an array of follow rules',
            'method.required' => 'method is required.',
            'method.in' => 'method should be either 1 follow or 2 un-follow',
            'followAmount.required' => 'followAmount is required.',
            'version' => 'version field is required.',
            'followBetRule.*.id.integer' => 'follow bet rule id should be an integer',
            'followBetRule.*.id.exists' => 'Invalid follow bet rule id.',
            'unFollowBetRule.*.id.integer' => 'follow bet rule id should be an integer',
            'unFollowBetRule.*.id.exists' => 'Invalid un-follow bet rule id.',

        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        //Compulsory parameter check
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $followUserProvider = new FollowUserProvider(null);
            $response = $followUserProvider->followUser($request->portalProviderUUID, $request->userUUID, $request->followToUUID, $request->method, $request->followBetRule, $request->unFollowBetRule);
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
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam followersType integer required followersType should be either 1 - (Followers) or 2 - (following) Example: 1,
     * @bodyParam limit integer Specifying the number of records which we want to fetch Default : 100 Example: 10
     * @bodyParam offset integer Specifying the number of records we want to skip and fetch the data(basically for pagination) Default : 0 Example: 0
     *
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *             "UUID": "b73cedc2-4573-4f06-b62c-8f8854bc1c37",
     *             "userName": "User54141720090411",
     *             "fullName": "fName 1 LName 1",
     *             "profileImage": null,
     *             "isFollowing": 1,
     *             "isAllowToVisitProfile": 0,
     *             "followRuleValue": [
     *                 {
     *                     "id": 1,
     *                     "value": 100,
     *                     "name": "byAmount"
     *                 }
     *             ],
     *             "unFollowRuleValue": [
     *                 {
     *                     "id": 3,
     *                     "value": 10,
     *                     "name": "byTime"
     *                 }
     *             ]
     *         },
     *         {
     *             "UUID": "9dd65f98-df47-4709-aaa6-0d71c7aadbf4",
     *             "userName": "User05141720100453",
     *             "fullName": "fName 3 LName 3",
     *             "profileImage": null,
     *             "isFollowing": 1,
     *             "isAllowToVisitProfile": 1,
     *             "followRuleValue": [
     *                 {
     *                     "id": 1,
     *                     "value": 100,
     *                     "name": "byAmount"
     *                 }
     *             ],
     *             "unFollowRuleValue": [
     *                 {
     *                     "id": 3,
     *                     "value": 10,
     *                     "name": "byTime"
     *                 }
     *             ]
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

    public function FollowUserList(Request $request)
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
            'userUUID' => 'required|uuid',
            'portalProviderUUID' => 'required|uuid',
            'followersType' => 'required|' . Rule::in([1, 2]),
            'version' => 'required',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0',
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'followersType.required' => 'followersType is required.',
            'followersType.in' => 'followersType should be either 1(followUser) or 2(userFollow)',
            'version' => 'version field is required.',
            'limit.integer' => 'limit should be an integer.',
            'limit.min' => 'limit should be greater than 0.',
            'offset.integer' => 'offset should be an integer.',
            'offset.min' => 'offset should be greater than or equal to 0.',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        //Compulsory parameter check
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $userUUID = $request->userUUID;
            $portalProviderUUID = $request->portalProviderUUID;
            $followersType = $request->followersType;
            $limit = isEmpty($request->limit)  ? 100 : $request->limit;
            $offset = isEmpty($request->offset)  ? 0 : $request->offset;

            $followUserProvider = new FollowUserProvider(null);
            $response = $followUserProvider->followUserList($userUUID, $portalProviderUUID, $followersType, $limit, $offset);
            $userID = $response['userID'];
            $portalProviderID = $response['portalProviderID'];
        }
        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

        //Establishing the Job for API Logs.
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
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam limit integer Number for top player you want to return Default : 10 Example: 10
     * @bodyParam dateRangeFrom Date(yyyy-mm-dd) The Start date from which leader board needs to start calculating  Default : 7 day from now Example: 1970-01-31
     * @bodyParam dateRangeTo Date(yyyy-mm-dd) The End date till which leader board will be calculated. Not greater than current data Default : Current date Example: 1970-03-31
     *
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *             "userUUID": "ba125aa1-12e3-4842-8a72-db222a0fcf82",
     *             "username": "User130612205903",
     *             "userImage": null,
     *             "totalBetAmount": 8583,
     *             "totalWinAmount": 10334.2,
     *             "totalBets": 1347,
     *             "totalWinBets": 1299,
     *             "winRate": "96.44",
     *             "isFollowing": 0,
     *             "isAllowToLocation": 0,
     *             "country": null,
     *             "rank": 1
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

    public function getLeaderBoard(Request $request)
    {
        $requestTime = getCurrentTimeStamp();
        $errorFound = true;
        $adminData = request()->get('adminData');
        $source = $adminData[0]->source;
        $adminID = $adminData[0]->PID;
        $userID = null;
        $exceptionFound = 0;
        $portalProviderID = null;
        $version = '0.0';

        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'userUUID' => 'required|uuid',
            'version' => 'required',
            'dateRangeFrom' => "nullable|date_format:Y-m-d",
            'dateRangeTo' => "nullable|required_with:dateRangeFrom|after_or_equal:dateRangeFrom|before:tomorrow",
            'limit' => 'nullable|integer|min:1',
        );

        $messages = [
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'version.required' => 'version is required.',
            'dateRangeFrom.date_format' => 'dateRangeFrom should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.required_with' => 'dateRangeTo is required when dateRangeFrom is present.',
            'dateRangeTo.date_format' => 'dateRangeTo should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.after_or_equal' => 'dateRangeTo should be greater than dateRangeFrom',
            'dateRangeTo.before' => 'dateRangeTo should not be greater than current date',
        ];

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $userProvider = new UserProvider(null);
            $response = $userProvider->getTopUsers($request->portalProviderUUID, $request->userUUID, $request->dateRangeFrom, $request->dateRangeTo, $request->limit);
            $portalProviderID = $response['portalProviderID'];
            $userID = $response['userID'];
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
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam dateRangeFrom DATE(Y-m-d) The Start date from which leader board needs to start calculating  Default : 31 day from now Example: 1970-01-31
     * @bodyParam dateRangeTo DATE(Y-m-d) The End date till which leader board will be calculated. Not greater than current data Default : Current date Example: 1970-03-31
     *
     * @response 200
     *  {
     *      "code": 200,
     *      "data": [
     *          {
     *              "totalBets": 3,
     *              "lossCount": 3,
     *              "winCount": 0,
     *              "winRate": "0.00",
     *              "stockName": "btc1",
     *              "category": "crypto"
     *          }
     *      ],
     *      "status": true,
     *      "message": [
     *          "success"
     *      ]
     *  }
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
    public function getUserBetAnalysis(Request $request)
    {
        $requestTime = getCurrentTimeStamp();
        $errorFound = true;
        $adminData = request()->get('adminData');
        $source = $adminData[0]->source;
        $adminID = $adminData[0]->PID;
        $userID = null;
        $exceptionFound = 0;
        $portalProviderID = null;
        $version = '0.0';

        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'userUUID' => 'required|uuid',
            'version' => 'required',
            'dateRangeFrom' => "nullable|date_format:Y-m-d",
            'dateRangeTo' => "nullable|required_with:dateRangeFrom|after_or_equal:dateRangeFrom|before:tomorrow",
        );

        $messages = [
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'version.required' => 'version is required.',
            'dateRangeFrom.date_format' => 'dateRangeFrom should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.required_with' => 'dateRangeTo is required when dateRangeFrom is present.',
            'dateRangeTo.date_format' => 'dateRangeTo should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.after_or_equal' => 'dateRangeTo should be greater than dateRangeFrom',
            'dateRangeTo.before' => 'dateRangeTo should not be greater than current date',
        ];

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $userProvider = new UserProvider(null);
            $response = $userProvider->getUserBetAnalysis($request->portalProviderUUID, $request->userUUID, $request->dateRangeFrom, $request->dateRangeTo);
            $portalProviderID = $response['portalProviderID'];
            $userID = $response['userID'];
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
     * @bodyParam portalProviderUserID string required Unique ID of Portal Provider's User.<br><p style="color:red">** Remarks:<br>-> PortalProviderUserID is mandatory <br>-> Unique id has to be provided by the portal provider to maintain the mapping/identification of different users.Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40<p>
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam ip string required IP from where the login request came. Example: 90.156.116.103
     * @bodyParam domain string required App Version code is required Example: domain.com
     * @bodyParam balance integer required The amount of chips with which users will login into EC gaming.<br><p style="color:red">** Remarks:<br>-> Balance is mandatory <br>-> This amount will be deducted from the portal provider's main balance..Example: 1000<p>
     * @response 200
     * {
     *     "code": 200,
     *     "data": [
     *         {
     *             "userUUID": "ab14d362-597d-468c-8d9c-3a043ba8e79c"
     *         }
     *     ],
     *     "status": true,
     *     "message": ["user login Successfully"]
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

    public function loginWebUser(Request $request)
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

        // Json tag validation block
        $rules = array(
            "balance" => "bail|required|integer|min:1",
            "portalProviderUserID" => "bail|required",
            "portalProviderUUID" => "bail|required|uuid",
            "domain" => "required",
            "ip" => "required",
            "version" => "required",
        );

        $messages = [
            "balance.required" => "balance field is required.",
            "balance.integer" => "balance should be integer type.",
            "balance.min" => "balance should be greater then zero(0).",
            "portalProviderUserID.required" => "PortalProviderUserID field is required.",
            "portalProviderUUID.required" => "PortalProviderUUID field is required.",
            "portalProviderUUID.uuid" => "invalid UUID.",
            "domain.required" => "domain is required.",
            "ip.required" => "ip is required.",
            "version.required" => "version is required.",
        ];

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $portalProviderModelRef = new PortalProvider();

            $clientIp = $request->input('ip'); // get client IP
            $clientServerName = $request->input('domain'); // get client server name

            $portalProviderData = $portalProviderModelRef->getPortalProviderByUUID(trim($request->input('portalProviderUUID')));

            if (count($portalProviderData) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
            } else {
                $ppdIp = explode(',', $portalProviderData[0]->ipList); // get authenticated admin ip list
                $ppdServerName = $portalProviderData[0]->server; // get authenticated server name

                // if server name or ip is valid
                if (IsAuthEnv() && !in_array($clientIp, $ppdIp) && strtoupper($ppdServerName) != strtoupper($clientServerName)) {
                    $response['res'] = Res::unauthorized();
                } else {
                    $balance = (float) trim($request->input('balance'));
                    $balance = round($balance, 2);
                    $portalProviderUserID = trim($request->input('portalProviderUserID'));
                    $portalProviderUUID = trim($request->input('portalProviderUUID'));
                    $version = $request->version;

                    $userProvider = new UserProvider(null); // creating login provider object

                    $response = $userProvider->createOrLoginUser($portalProviderUUID, $portalProviderUserID, $balance, $adminID, $source);
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
     * @bodyParam portalProviderUserID string required Unique ID of Portal Provider's User.<br><p style="color:red">** Remarks:<br>-> PortalProviderUserID is mandatory <br>-> Unique id has to be provided by the portal provider to maintain the mapping/identification of different users.Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40<p>
     * @bodyParam version float required App Version code is required Example: 1.0
     * @response 200
     * {
     *     "code": 200,
     *     "data": {
     *         "balance": 1000
     *     },
     *     "status": true,
     *     "message": ["success"]
     * }
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

    public function getUserBalance(Request $request)
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

        // Json tag validation block : Start
        $rules = array(
            "portalProviderUserID" => "bail|required",
            "portalProviderUUID" => "bail|required|uuid",
            'version' => 'required',
        );

        $messages = [
            "portalProviderUserID.required" => "portalProviderUserID field is required.",
            "portalProviderUUID.required" => "PortalProviderUUID field is required.",
            "portalProviderUUID.uuid" => "invalid UUID.",
            'version.required' => 'version is required.',
        ];

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $userProviderRef = new UserProvider(null);

            $response = $userProviderRef->getUserBalance(trim($request->input('portalProviderUUID')), trim($request->input('portalProviderUserID')));

            $portalProviderID = $response['portalProviderID'];
            $userID = $response['userID'];
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
     * @bodyParam portalProviderUserID string required Unique ID of Portal Provider's User.<br><p style="color:red">** Remarks:<br>-> PortalProviderUserID is mandatory <br>-> Unique id has to be provided by the portal provider to maintain the mapping/identification of different users.Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40<p>
     * @bodyParam balance integer required The amount of chips with which users will login into EC gaming.<br><p style="color:red">** Remarks:<br>-> Balance is mandatory <br>-> This amount will be deducted from the portal provider's main balance..Example: 1000<p>
     * @bodyParam version float required App Version code is required Example: 1.0
     * @response 200
     * {
     *     "code": 200,
     *     "data": [],
     *     "status": true,
     *     "message": ["success"]
     * }
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
    public function updateUserBalance(Request $request)
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

        // Json tag validation block : Start
        $rules = array(
            "portalProviderUserID" => "bail|required",
            "portalProviderUUID" => "bail|required|uuid",
            'version' => 'required',
            "balance" => "bail|required|integer|min:1",
        );

        $messages = [
            "portalProviderUserID.required" => "PortalProviderUserID field is required.",
            "portalProviderUUID.required" => "PortalProviderUUID field is required.",
            "portalProviderUUID.uuid" => "invalid UUID.",
            'version.required' => 'version is required.',
            "balance.required" => "balance field is required.",
            "balance.integer" => "balance should be integer type.",
            "balance.min" => "balance should be greater then zero(0).",
        ];

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $userProviderRef = new UserProvider(null);

            $response = $userProviderRef->updateUserBalance(trim($request->input('portalProviderUUID')), trim($request->input('portalProviderUserID')), trim($request->input('balance')));

            $portalProviderID = $response['portalProviderID'];
            $userID = $response['userID'];
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
     * @bodyParam portalProviderUserID string required Unique ID of Portal Provider's User.<br><p style="color:red">** Remarks:<br>-> PortalProviderUserID is mandatory <br>-> Unique id has to be provided by the portal provider to maintain the mapping/identification of different users.Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40<p>
     * @bodyParam version float required App Version code is required Example: 1.0
     * @response 200
     * {
     *     "code": 200,
     *     "data": [],
     *     "status": true,
     *     "message": ["User logged out successfully"]
     * }
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
    public function logoutAndClearPool(Request $request)
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

        // Json tag validation block
        $rules = array(
            "portalProviderUserID" => "bail|required",
            "portalProviderUUID" => "bail|required|uuid",
            'version' => 'required',
        );

        $messages = [
            "portalProviderUserID.required" => "PortalProviderUserID field is required.",
            "portalProviderUUID.required" => "PortalProviderUUID field is required.",
            "portalProviderUUID.uuid" => "invalid UUID.",
            'version.required' => 'version is required.',
        ];

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $userProviderRef = new UserProvider(null);

            $response = $userProviderRef->logoutAndClearPool(trim($request->input('portalProviderUUID')), trim($request->input('portalProviderUserID')), $adminID, $source);

            $portalProviderID = $response['portalProviderID'];
            $userID = $response['userID'];
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
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam gameUUID UUID Specify the game ID to get games. Example: 9b1d2aef-7197-49cb-a983-a6e29f77e793
     * @bodyParam category.* integer required This consist category like <strong>1 = totalWinBets ,2 = followerCount ,3 = rank </strong>. Example: 1
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam dateRangeFrom DATE(Y-m-d) The Start date from which leader board needs to start calculating  Default : 31 day from now Example: 1970-01-31
     * @bodyParam dateRangeTo DATE(Y-m-d) The End date till which leader board will be calculated. Not greater than current data Default : Current date Example: 1970-03-31
     * @response 200
     * {
     *       "code": 200,
     *       "data": {
     *           "userName": "User57130920340568",
     *           "loginTime": "2020-05-13 09:36:31",
     *           "firstName": "Parth1",
     *           "lastName": "Ravani1",
     *           "email": "parth.ravani@gmail.com",
     *           "profileImage": "images/user/profile/5ebb4fac71fe420200513093852.png",
     *           "balance": 100000,
     *           "isLoggedIn": "true",
     *           "isActive": "active",
     *           "userUUID": "cd92a858-9430-46a2-98df-0f00bcfadf09",
     *           "gender": "male",
     *           "country": null,
     *           "isAllowToVisitProfile": true,
     *           "isAllowToFollow": true,
     *           "isAllowToDirectMessage": true,
     *           "isSound": true,
     *           "isAllowToLocation": true,
     *           "rollingAmount": 0,
     *           "currentActiveTime": "14 days, 4 hours, 43 minutes, 43 seconds",
     *           "activeTimeDateWise": [],
     *           "totalLikes": 0
     *       },
     *       "status": true,
     *       "message": [
     *           "success"
     *       ]
     *   }
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
    public function sendInvitation(Request $request)
    {
        $requestTime = getCurrentTimeStamp();
        $errorFound = true;
        $adminData = request()->get('adminData');
        $source = $adminData[0]->source;
        $adminID = $adminData[0]->PID;
        $userID = null;
        $exceptionFound = 0;
        $portalProviderID = null;
        $version = '0.0';

        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'userUUID' => 'required|uuid',
            'gameUUID' => 'nullable|uuid',
            'category' => 'required|array',
            'category.*' => 'integer',
            'version' => 'required',
            'dateRangeFrom' => "nullable|date_format:Y-m-d",
            'dateRangeTo' => "nullable|required_with:dateRangeFrom|after_or_equal:dateRangeFrom|before:tomorrow",
        );

        $messages = [
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'gameUUID.uuid' => 'gameUUID should be a valid UUID.',
            'category.required' => 'category is required.',
            'category.array' => 'category should be an array.',
            'version.required' => 'version is required.',
            'dateRangeFrom.date_format' => 'dateRangeFrom should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.required_with' => 'dateRangeTo is required when dateRangeFrom is present.',
            'dateRangeTo.date_format' => 'dateRangeTo should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.after_or_equal' => 'dateRangeTo should be greater than dateRangeFrom',
            'dateRangeTo.before' => 'dateRangeTo should not be greater than current date',
        ];

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $userProvider = new UserProvider(null);
            $response = $userProvider->sendInvitation($request->portalProviderUUID, $request->userUUID, $request->gameUUID, $request->dateRangeFrom, $request->dateRangeTo);
            $portalProviderID = $response['portalProviderID'];
            $userID = $response['userID'];

            if ($response['res']['status']) {
                $response['res']['data']->{'category'} = $request->category;
                broadcast(new MessageSentEvent($response['res']));
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
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam dateRangeFrom DATE(Y-m-d) The Start date from which leader board needs to start calculating  Default : 31 day from now Example: 1970-01-31
     * @bodyParam dateRangeTo DATE(Y-m-d) The End date till which leader board will be calculated. Not greater than current data Default : Current date Example: 1970-03-31
     * @response 200
     * {
     *     "code": 200,
     *     "data": {
     *         "userUUID": "36a1b057-f3ef-4b0f-9808-d9906d4543b4",
     *         "rank": 1,
     *         "followerCount": 3,
     *         "winRate": "22.22"
     *     },
     *     "status": true,
     *     "message": [
     *         "success"
     *     ]
     * }
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
    public function getUserInvitationDetail(Request $request)
    {
        $requestTime = getCurrentTimeStamp();
        $errorFound = true;
        $adminData = request()->get('adminData');
        $source = $adminData[0]->source;
        $adminID = $adminData[0]->PID;
        $userID = null;
        $exceptionFound = 0;
        $portalProviderID = null;
        $version = '0.0';

        // Json tag validation block
        $rules = array(
            'portalProviderUUID' => 'required|uuid',
            'userUUID' => 'required|uuid',
            'version' => 'required',
            'dateRangeFrom' => "nullable|date_format:Y-m-d",
            'dateRangeTo' => "nullable|required_with:dateRangeFrom|after_or_equal:dateRangeFrom|before:tomorrow",
        );

        $messages = [
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'version.required' => 'version is required.',
            'dateRangeFrom.date_format' => 'dateRangeFrom should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.required_with' => 'dateRangeTo is required when dateRangeFrom is present.',
            'dateRangeTo.date_format' => 'dateRangeTo should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.after_or_equal' => 'dateRangeTo should be greater than dateRangeFrom',
            'dateRangeTo.before' => 'dateRangeTo should not be greater than current date',
        ];

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {

            $version = $request->version;
            $userProvider = new UserProvider(null);
            $response = $userProvider->userInvitationDetail($request->portalProviderUUID, $request->userUUID, $request->dateRangeFrom, $request->dateRangeTo);
            $portalProviderID = $response['portalProviderID'];
            $userID = $response['userID'];
        }

        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

        // API Logs.
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
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam visitingUserUUID UUID required The user unique id which is saved in the EC gaming server & you want to see profile.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam version float required App Version code is required Example: 1.0
     * @bodyParam dateRangeFrom Date(yyyy-mm-dd) Start Date from which calculation need to start.Default = Now . Example: 2020-02-21
     * @bodyParam dateRangeTo Date(yyyy-mm-dd) End Date till which calculation need to do.Default = 6 Months . Example: 2020-02-23
     * @response 200
     * {
     *     "code": 200,
     *     "data": {
     *         "userUUID": "5e167890-9c42-46e1-b6c4-324fddbc9630",
     *         "username": "User43071020000393",
     *         "userImage": "images/user/profile/5e63080b6b3e420200307103347.",
     *         "totalBetAmount": 0,
     *         "totalWinAmount": 0,
     *         "totalBets": 0,
     *         "totalWinBets": 0,
     *         "winRate": "0.00",
     *         "followerCount": 0,
     *         "isAllowToVisitProfile": 1,
     *         "isAllowToLocation": 1,
     *         "rank": 12,
     *         "firstName": null,
     *         "middleName": null,
     *         "lastName": null,
     *         "gender": "male",
     *         "country": "CAN",
     *         "isFollowing": 0,
     *         "activeTimeDateWise": [
     *             {
     *                 "activeTimeInMins": "60",
     *                 "Date": "2020-02-01"
     *             },
     *             {
     *                 "activeTimeInMins": "60",
     *                 "Date": "2020-02-02"
     *             }
     *         ],
     *         "currentActiveTime": "offline"
     *     },
     *     "status": true,
     *     "message": ["success"]
     * }
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
    public function visitUserProfile(Request $request)
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
            'visitingUserUUID' => 'required|uuid',
            'version' => 'required',
            'dateRangeFrom' => "nullable|date_format:Y-m-d",
            'dateRangeTo' => "nullable|required_with:dateRangeFrom|date_format:Y-m-d|after_or_equal:dateRangeFrom|before:tomorrow",
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'visitingUserUUID.required' => 'visitingUserUUID is required.',
            'visitingUserUUID.uuid' => 'visitingUserUUID should be a valid UUID.',
            'version.required' => 'version is required.',
            'dateRangeFrom.date_format' => 'dateRangeFrom should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.required_with' => 'dateRangeTo is required when dateRangeFrom is present.',
            'dateRangeTo.date_format' => 'dateRangeTo should be a valid date format (eg: yyyy-mm-dd)',
            'dateRangeTo.after_or_equal' => 'dateRangeTo should be greater than dateRangeFrom',
            'dateRangeTo.before' => 'dateRangeTo should not be greater than current date',
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);
        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $portalProviderUUID = $request->portalProviderUUID;
            $userUUID = $request->userUUID;
            $visitingUserUUID = $request->visitingUserUUID;

            $fromDate = ($request->dateRangeFrom) ? $request->dateRangeFrom : "";
            $toDate = ($request->dateRangeTo) ? $request->dateRangeTo : "";

            $provider = new UserProvider($request);
            $response = $provider->visitUserProfile($portalProviderUUID, $userUUID, $visitingUserUUID, $fromDate, $toDate);

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
     * @bodyParam userUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam userToUUID UUID required The user unique id which is saved in the EC gaming server.  Example: db2d4063-c5bf-48b0-8524-9ffe82c80a40
     * @bodyParam like Boolean required Set parameter as required when send like set like parameter true, when remove like set parameter false. Example: true
     * @bodyParam version float required App Version code is required Example: 1.0
     * @response 200
     * {
     *  "code": 200,
     *  "data": [],
     *  "status": true,
     *  "message": [
     *      "You Liked User43071020000393"
     *  ]
     * }
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
    // Piyush: Like feature for user.
    public function sendLike(Request $request)
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
            'portalProviderUUID' => 'bail|required|uuid',
            'userUUID' => 'bail|required|uuid',
            'userToUUID' => 'bail|required|uuid',
            'like' => 'required|' . Rule::in([true, false]),
            'version' => 'required'
        );

        $messages = array(
            'portalProviderUUID.required' => 'portalProviderUUID is required.',
            'portalProviderUUID.uuid' => 'portalProviderUUID should be a valid UUID.',
            'userUUID.required' => 'userUUID is required.',
            'userUUID.uuid' => 'userUUID should be a valid UUID.',
            'userToUUID.required' => 'userToUUID is required.',
            'userToUUID.uuid' => 'userToUUID should be a valid UUID.',
            'like.in' => 'like parameter should be either true(Like) or false(Remove like)',
            'version' => 'version field is required.'
        );

        $validator = Validator::make($request->toArray(), $rules, $messages);

        if ($validator->fails()) {
            $errorFound = true;
            $response['res'] = Res::validationError([], $validator->errors());
        } else {
            $version = $request->version;
            $provider = new UserProvider($request);
            $response = $provider->sendLike($request->portalProviderUUID, $request->userUUID, $request->userToUUID, $request->like);
            $userID = $response['userID'];
            $portalProviderID = $response['portalProviderID'];
        }

        $message = $response['res']['message'];
        if (isset($response['res']['exception']) && $response['res']['exception']) {
            $exceptionFound = true;
            $message = [$response['exceptionMsg']];
        }
        $errorFound = !$response['res']['status'];

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
