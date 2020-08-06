<?php

namespace App\Listeners\Socket;

use App\Providers\Gaming\GameProvider;
use App\Events\Socket\LiveBetCountEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\MailJob;


class LiveBetCountListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $gameProvider = new GameProvider(null);
        $data = $gameProvider->betCount($event->gameUUID, $event->portalProviderUUID, $event->stockUUID, $event->loop, [0, 1, 2]);

        if ($data['res']['code'] == 200) {
            broadcast(new LiveBetCountEvent($data['res'], $data['portalProviderUUID'], $data['stockUUID'], $data['gameLoop']));
        } else {

            $msg = 'Error : LiveBetCountEvent not called';
            $msg = $msg . "\n Game UUID: " . $event->gameUUID;;
            $msg = $msg . "\n Provider UUID: " . $event->portalProviderUUID;
            $subject = "ERROR STACK TRACE => JOB (LiveBetCountEvent) : " . config('app.env');
            $to = config('constants.alert_mail_id');
            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
        }
    }
}
