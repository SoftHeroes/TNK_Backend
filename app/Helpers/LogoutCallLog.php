<?php
require_once app_path() . '/Helpers/CommonUtility.php';

use Illuminate\Support\Facades\DB;
use App\Jobs\MailJob;


//This function generally logs all the Logout calls to provider with the request, response etc into the "logoutCallLog' table.
function logoutCallLog($service, $portalProviderID = null, $adminID = null, $userID = null, $responseCode,  $errorFound, $exceptionFound, $responseMessage,  $request, $response, $source, $ipAddress = null, $version, $URL)
{
    try {
        //Insert log
        DB::table('logoutCallLog')->insert(
            [
                'service' => $service,
                'portalProviderID' => $portalProviderID,
                'adminID' => $adminID,
                'userID' => $userID,
                'responseCode' => $responseCode,
                'errorFound' => $errorFound,
                'exceptionFound' => $exceptionFound,
                'responseMessage' => $responseMessage,
                'request' => $request,
                'response' => $response,
                'source' => $source,
                'ipAddress' => $ipAddress,
                'version' => $version,
                'URL'=> $URL
            ]
        );

        // triggering email even if there is any error.
        if ($errorFound == true) {

            $to = config('constants.alert_mail_id');
            $msg = $response;
            $subject = "LogoutCallLog has caught an error : " . config('app.env');
            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
        }
    } catch (Exception $e) {

        $to = config('constants.alert_mail_id');
        $msg = 'Error : ' . $e->getMessage() . "\n---------------\n";
        $msg = $msg . $e->getTraceAsString() . "\n";

        $subject = "LogoutCallLog has caught an exception : " . config('app.env');
        MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
    }
}
