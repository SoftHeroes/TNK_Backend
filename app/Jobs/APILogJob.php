<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

require_once app_path() . '/Helpers/ApiActivityLog.php';

class APILogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $route;
    protected $method;
    protected $code;
    protected $message;
    protected $errorFound;
    protected $source;
    protected $portalProviderID;
    protected $adminID;
    protected $userID;
    protected $version;
    protected $requestTime;
    protected $content;
    protected $response;
    protected $exceptionFound;

    public function __construct(
        $route,
        $method,
        $code,
        $message,
        $errorFound,
        $source,
        $portalProviderID,
        $adminID,
        $userID,
        $version,
        $requestTime,
        $content,
        $response,
        $exceptionFound
    ) {
        $this->route            = $route;
        $this->method           = $method;
        $this->code             = $code;
        $this->message          = $message;
        $this->errorFound       = $errorFound;
        $this->source           = $source;
        $this->portalProviderID = $portalProviderID;
        $this->adminID          = $adminID;
        $this->userID           = $userID;
        $this->version          = $version;
        $this->requestTime      = $requestTime;
        $this->content          = $content;
        $this->response         = $response;
        $this->exceptionFound   = $exceptionFound;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        API_Log(
            $this->route,
            $this->method,
            $this->code,
            $this->message,
            $this->errorFound,
            $this->source,
            $this->portalProviderID,
            $this->adminID,
            $this->userID,
            $this->version,
            $this->requestTime,
            $this->content,
            $this->response,
            $this->exceptionFound
        );
    }
}
