<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Ixudra\Curl\Facades\Curl;
use App\Models\PortalProvider;
use App\Http\Controllers\ResponseController as Res;
use Illuminate\Support\Facades\DB;

require_once app_path() . '/Helpers/LogoutCallLog.php';

class LogoutAPICallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $service;
    protected $portalProviderID;
    protected $adminID;
    protected $source;
    protected $userID;
    protected $portalProviderUserID;
    protected $userBalance;
    protected $ipAddress;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $service,
        $portalProviderID,
        $adminID = null,
        $source,
        $userID,
        $portalProviderUserID,
        $userBalance,
        $ipAddress
    ) {
        $this->service = $service;
        $this->portalProviderID = $portalProviderID;
        $this->adminID = $adminID;
        $this->source = $source;
        $this->userID = $userID;
        $this->portalProviderUserID = $portalProviderUserID;
        $this->userBalance = $userBalance;
        $this->ipAddress = $ipAddress;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $errorFound = false;
        $exceptionFound = false;
        $version = '1.0';
        $responseCode = null;
        $URL = null;

        try {

            $request = [
                'service' => $this->service,
                'portalProviderID' => $this->portalProviderID,
                'adminID' => $this->adminID,
                'source' => $this->source,
                'userID' => $this->userID,
                'portalProviderUserID' => $this->portalProviderUserID,
                'userBalance' => $this->userBalance,
                'ipAddress' => $this->ipAddress
            ];

            // check for User UUID and fetch domain apiKey and userBalance
            $providerModel = new PortalProvider();
            $providerData = $providerModel->findByPortalProviderID($this->portalProviderID)->select('server', 'APIKey')->first();

            if ($providerData->count(DB::raw('1')) == 0) {
                $errorFound = true;
                $responseMessage = 'providerID does not exist.';
                $response =  Res::notFound([], $responseMessage);
                return $response;
            }
            if (isEmpty($providerData->APIKey) || isEmpty($providerData->server)) {
                $errorFound = true;
                $responseMessage = 'API Key or Domain is not available';
                $response =  Res::badRequest([], $responseMessage);
                return $response;
            }



            if ($this->userBalance >= 0) {
                //call curl

                //--------- this is dummy url, we need to uncomment below code and try when we want to actually try the provider URL. -----------//

                //$URL = 'https://jsonplaceholder.typicode.com/posts';
                $URL = 'https://' . $providerData->server . '/ecgaming/updateUserBalance';

                $result = Curl::to($URL)
                    ->withData(['APIKey' => $providerData->APIKey, 'portalProviderUserID' => $this->portalProviderUserID, 'balance' => $this->userBalance])
                    //->withData(['title' => $providerData->APIKey, 'body' => $this->portalProviderUserID, 'userId' => $this->userBalance])
                    ->asJson()
                    ->withContentType('application/json')
                    ->returnResponseObject()
                    ->post();

                if (!empty($result->status)) {

                    $responseCode = $result->status;
                    //logging error if response code is not 200 or 201
                    if (!($responseCode == 200 || $responseCode == 201)) {
                        $responseMessage = "API response has error";
                        $response =  Res::badRequest([], $responseMessage);
                        $errorFound = true;
                    } else {
                        $responseMessage = json_encode((array) $result->content);
                    }
                    // $result = (array) json_decode($result);
                    $response = json_encode((array) $result);
                } else {
                    $responseMessage = 'No result';
                    $response =  Res::badRequest([], $responseMessage);
                    $errorFound = true;
                }
            } else {
                //balance error
                $responseMessage = 'Balance cannot be negative.';
                $response =  Res::badRequest([], $responseMessage);
                $errorFound = true;
            }
        } catch (Exception $e) {
            //log exception
            $exceptionFound = true;
            $to = config('constants.alert_mail_id');
            $msg = 'Error : ' . $e->getMessage() . "\n---------------\n";
            $msg = $msg . $e->getTraceAsString() . "\n";

            $subject = "logoutCallToProvider has caught an exception : " . config('app.env');
            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
            $response = Res::errorException($e->getMessage());
            throw $e;
        } finally {

            $request = json_encode($request);
            $response = json_encode($response);
            //create log no matter what
            logoutCallLog(
                $this->service,
                $this->portalProviderID,
                $this->adminID,
                $this->userID,
                $responseCode,
                $errorFound,
                $exceptionFound,
                $responseMessage,
                $request,
                $response,
                $this->source,
                $this->ipAddress,
                $version,
                $URL
            );
        }
    }
}
