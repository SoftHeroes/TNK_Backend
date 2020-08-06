<?php
require_once app_path() . '/Helpers/CommonUtility.php';

use Illuminate\Support\Facades\DB;
use App\Jobs\MailJob;


//This function logs all the api calls with the request, response etc into the "apiActivityLog' table.
function API_Log($service, $method, $responseCode, $responseMessage, $errorFound, $source, $portalProviderID = null, $adminID = null, $userID = null, $version, $requestTime, $request, $response, $exceptionFound, $ipAddress = null)
{
    try {

        if (isEmpty($ipAddress)) {
            $ipAddress = \Request::getClientIp();
        }

        $responseTime = getCurrentTimeStamp();

        $timeTaken = millisecondsBetweenMicrotime($responseTime, $requestTime);

        $requestTime = microtimeToDateTime($requestTime, false, 'Y-m-d H:i:s');
        $responseTime = microtimeToDateTime($responseTime, false, 'Y-m-d H:i:s');

        DB::table('apiActivityLog')->insert(
            [
                'service' => $service,
                'method' => $method,
                'responseCode' => $responseCode,
                'responseMessage' => implode(" | ", $responseMessage),
                'errorFound' => $errorFound,
                'requestTime' => $requestTime,
                'responseTime' => $responseTime,
                'timeTaken' => $timeTaken,
                'request' => $request,
                'response' => $response,
                'portalProviderID' => $portalProviderID,
                'adminID' => $adminID,
                'userID' => $userID,
                'version' => $version,
                'source' => $source,
                'ipAddress' => $ipAddress,
                'exceptionFound' => $exceptionFound
            ]
        );
    } catch (Exception $e) {
        $to = config('constants.alert_mail_id');
        $msg = 'Error : ' . $e->getMessage() . "\n---------------\n";
        $msg = $msg . $e->getTraceAsString() . "\n";

        $subject = "API Log has caught an exception : " . config('app.env');
        MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
        throw $e;
    }
}
