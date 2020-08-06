<?php

namespace App\Jobs;

use App\Models\MailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;
use Illuminate\Support\Facades\Log;

use App\Providers\EmailServiceProvider;

class MailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    protected $to;
    protected $message;
    protected $subject;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $message, $subject)
    {
        $this->to      = $to;
        $this->message = $message;
        $this->subject = $subject;

    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function handle()
    {
        try {

            $emailProvider = new EmailServiceProvider(null);
            $emailProvider->sendEmail($this->to, $this->message, $this->subject);

            $status = 'success';
            $mailLogEntry = array(
                'to' => $this->to,
                'subject' => $this->subject,
                'message' => $this->message,
                'status' => $status
            );

            //Success mail log entry
            MailLog::insert($mailLogEntry);

        } catch (Exception $e) {

            $status = 'fail';
            $mailLogEntry = array(
                'to' => $this->to,
                'subject' => $this->subject,
                'message' => $this->message,
                'status' => $status
            );
            //Fail mail log entry
            MailLog::insert($mailLogEntry);

            Log::debug("Error in sending Mail : " . $e->getMessage());
            Log::debug("Actual error : " . $this->message);

            throw $e;
        }
    }
}
