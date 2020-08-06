<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

require_once app_path() . '/Helpers/CommonUtility.php';


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }


    public function handleException(Exception $exception)
    {
        // custom error message
        if ($exception instanceof \ErrorException) {
            return response()->view(
                'exceptions.index',
                [
                    'code' => 500,
                    'message' => config('constants.default_error_response')
                ],
                500
            );
        }

        if ($this->isHttpException($exception)) {
            return response()->view(
                'exceptions.index',
                [
                    'code' => $exception->getStatusCode(),
                    'message' => config('constants.default_error_response')
                ],
                $exception->getStatusCode()
            );
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */

    public function render($request, Exception $exception)
    {
        if (IsAuthEnv()) return $this->handleException($exception);
        return parent::render($request, $exception);
    }
}
