<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;

require_once app_path() . '/Helpers/CommonUtility.php';

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function sendEmail($to, $message, $subject, $to_name = null, $from = null, $cc = null, $bcc = null)
    {
        if(isEmpty($from))
            $from = config('constants.mail_from_address');

        $to = explode(',', $to);
        $data = array('name' => $to_name, "body" => $message);

        $result = Mail::send('emails.mail', $data, function ($mail) use ($to, $subject, $from) {
            $mail->to($to)
                    ->subject("TNK - ".$subject)
                    //->cc()
                    //->bcc()
                    ->from($from);
        });

        return $result;
    }
}
