<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Providers\Users\UserProvider;
use App\Jobs\MailJob;

use App\Models\UserSession;

require_once app_path() . '/Helpers/CommonUtility.php';


class AutoSignOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'while:autoSignOut';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will logout all the logged-in users who have done no activity in last five minutes.';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userSessionModel = new UserSession();

        while (true) {
            try {
                $userProvider = new UserProvider(null);

                $timestamp = getCurrentTimeStamp();
                $timestamp = $timestamp - (5 * 60);
                $date = microtimeToDateTime($timestamp, false, 'Y/m/d H:i:s');

                // Getting list of inactive users for more than 5 minutes.
                $users = $userSessionModel->getInactiveUser($date);
                if ($users->count(DB::raw('1')) > 0) {
                    foreach ($users as $user) {
                        //logging out each user, and pool lag is also maintained in logoutUser
                        $userProvider->logoutUser($user['UUID'], null, 0, true);
                    }
                } else {
                    sleep(5);
                }
            } catch (Exception $e) {

                // triggering the email when an exception occurs.
                $msg = 'Error : ' . $e->getMessage() . "\n---------------\n";
                $msg = $msg . $e->getTraceAsString() . "\n";

                $subject = "ERROR STACK TRACE => JOB ($this->signature) : " . config('app.env');
                $to = config('constants.alert_mail_id');

                MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
            }
        }
    }
}
