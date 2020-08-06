<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PortalProvider;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ResponseController as Res;
use Illuminate\Support\Facades\DB;
use Exception;



class AuthController extends Controller
{

    /**
     * @OA\Info(
     *  title="API's Documentation",
     *  description="This is a sample API Documentation",
     *  version="1.0.0"
     * )
     */

    /**
     *  @SWG\SecurityScheme(
     *   securityDefinition="basicAuth",
     *   type="basic",
     *  )
     */

    /**
     * @OA\Post(
     *  path="/api/registerAdmin",
     *  description="Admin registration API",
     *  security = {{"basicAuth": {}}},
     *  @OA\RequestBody(
     *   @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Schema(
     *     @OA\Property(
     *      property="username",
     *      type="string"
     *     ),
     *     @OA\Property(
     *      property="emailID",
     *      type="string"
     *     ),
     *     @OA\Property(
     *      property="password",
     *      type="string"
     *     ),
     *     @OA\Property(
     *      property="password_confirmation",
     *      type="string"
     *     ),
     *     @OA\Property(
     *      property="portalProviderUUID",
     *      type="string"
     *     ),
     *     @OA\Property(
     *      property="firstName",
     *      type="string"
     *     ),
     *     @OA\Property(
     *      property="middleName",
     *      type="string"
     *     ),
     *     @OA\Property(
     *      property="lastName",
     *      type="string"
     *     ),
     *     example={
     *	    "username": "man",
     *	    "emailID": "mani@gmail.com",
     *	    "password": "test",
     *	    "password_confirmation": "test",
     *	    "portalProviderUUID": "78ecbfee-3284-11ea-9d69-e0d55ecac457",
     *	    "firstName": "ManiKanDan",
     *	    "middleName": "",
     *	    "lastName": "G"
     *     }
     *    )
     *   )
     *  ),

     *  @OA\Response(
     *   response="200",
     *   description="ok",
     *   content={
     *    @OA\MediaType(
     *     mediaType="application/json",
     *
     *     @OA\Schema(
     *      @OA\Property(
     *       property="code",
     *       type="integer",
     *       description="The HTTPS response code"
     *      ),
     *      @OA\Property(
     *       property="message",
     *       type="string",
     *       description="The response message"
     *      ),
     *      @OA\Property(
     *       property="status",
     *       type="boolean",
     *       description="The response message"
     *      ),
     *      @OA\Property(
     *       property="dataGroupExample",
     *       type="object",
     *       description="The response message",
     *       @OA\Property(
     *        property="inner abc",
     *        type="number",
     *        default=1,
     *        example=123
     *       )
     *      ),
     *      @OA\Property(
     *       property="dataArrayExample",
     *       type="array",
     *       description="The response data",
     *       @OA\Items(
     *        @OA\Property(
     *          property="adminPolicyID",
     *          type="string",
     *          description="adminPolicy PID"
     *        ),
     *        @OA\Property(
     *          property="portalProviderID",
     *          type="string",
     *          description="portalProviderID PID"
     *        ),
     *        @OA\Property(
     *          property="firstName",
     *          type="string",
     *          description="firstName"
     *        ),
     *       )
     *      )
     *     )
     *    )
     *   }
     *  )
     * )
     */

    public function registerAdmin(Request $request)
    {

        $adminModel = new Admin;
        $portalProviderModel = new PortalProvider;
        $isErrorFound = false;
        // $requestTime = getRequestTime();

        $rules = array(
            'username' => 'required|unique:admin|max:100',
            'emailID' => 'email|required|unique:admin',
            'password' => 'required|confirmed',
            // 'adminPolicyID' => 'required',
            'portalProviderUUID' => 'required'
        );

        $validator = Validator::make($request->toArray(), $rules);

        if ($validator->fails()) {
            return $validator->errors();
        }

        try {
            //to get the portal provider id from the portal providers UUID

            $portalProviderDetails = $portalProviderModel->getPortalProviderByUUID($request->portalProviderUUID);
        } catch (Exception $ex) {
            $exception = $ex->getMessage();
            $isErrorFound = true;
            $response = Res::errorException($ex);
        }

        if ($portalProviderDetails->count(DB::raw('1')) > 0) {
            $request['portalProviderId'] = $portalProviderDetails[0]['PID'];

            $userData = $adminModel->intoArray($request);

            try {
                $adminCreate = Admin::create($userData);
                $response = Res::success($adminCreate);
            } catch (Exception $ex) {
                $exception = $ex->getMessage();
                $isErrorFound = true;
                $response = Res::errorException($ex);
            }
        } else {
            $isErrorFound = true;
            $response = Res::notFound([], 'The portal provider details not found');
        }

        return $response;
    }
}
