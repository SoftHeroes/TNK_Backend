<?php

namespace App\Http\Controllers;

require_once app_path() . '/Helpers/CommonUtility.php';

class ResponseController extends Controller
{
    // response Not found
    public static function notFound($data = [], $msg = 'data not found', $code = 404, $status = false)
    {
        return
            [
                'code' => $code,
                'data' => $data,
                'status' => $status,
                'message' => [$msg]
            ];
    }

    // response Bad Request
    public static function badRequest($data = [], $msg = 'invalid request', $code = 400, $status = false)
    {
        return
            [
                'code' => $code,
                'data' => $data,
                'status' => $status,
                'message' => [$msg]
            ];
    }
    // response Bad Request
    public static function validationError($data = [], $msg = 'invalid request', $code = 401, $status = false)
    {

        if ($msg != 'invalid request') {
            $error = (array) $msg;
            $msg = [];
            foreach ($error as $key => $value) {
                $msg[] = $value;
            }
            $msg = array_values($msg[0]);
            $message = [];
            foreach ($msg as $key => $value) {
                $message = array_merge($message, $value);
            }
        }else{
            $message = [$msg];
        }
        return
            [
                'code' => $code,
                'data' => $data,
                'status' => $status,
                'message' => $message
            ];
    }

    // response success
    public static function success($data = [], $msg = 'success', $singleElement = false, $code = 200, $status = true)
    {
        if ($singleElement) {
            return
                [
                    'code' => $code,
                    'data' => count($data) > 0 ? $data[0] : "",
                    'status' => $status,
                    'message' => [$msg]
                ];
        } else {
            return
                [
                    'code' => $code,
                    'data' => $data,
                    'status' => $status,
                    'message' => [$msg]
                ];
        }
    }

    // response error
    public static function error($data = [], $msg = 'internal server error', $code = 500, $status = false)
    {
        return
            [
                'code' => $code,
                'data' => $data,
                'status' => $status,
                'message' => [$msg]
            ];
    }

    // response Exist data
    public static function alreadyExist($data = [], $msg = 'request duplicated', $code = 409, $status = false)
    {
        return
            [
                'code' => $code,
                'data' => $data,
                'status' => $status,
                'message' => [$msg]
            ];
    }

    // response Exist data
    public static function notAllowed($data = [], $msg = 'method not allowed', $code = 405, $status = false)
    {
        return
            [
                'code' => $code,
                'data' => $data,
                'status' => $status,
                'message' => [$msg]
            ];
    }

    // unauthorized
    public static function unauthorized($data = [], $msg = 'unauthorized', $code = 419, $status = false)
    {
        return
            [
                'code' => $code,
                'data' => $data,
                'status' => $status,
                'message' => [$msg]
            ];
    }

    // return exception
    public static function errorException($ex, $msg = 'internal server error', $code = 500, $status = false, $isException = true)
    {
        if (IsAuthEnv()) {
            return [
                'code' => $code,
                'data' => config('constants.default_error_response'),
                'status' => $status,
                'message' => [$msg],
                'exception' => $isException
            ];
        }
        return [
            'code' => $code,
            'data' =>  $ex,
            'status' => $status,
            'message' => [$msg],
            'exception' => $isException
        ];
    }

    public static function viewNotFound($code = 404, $msg = 'not found')
    {
        return response()->view('exceptions.index', ['code' => $code, 'message' => $msg], $code);
    }
}
