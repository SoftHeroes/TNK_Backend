<?php

namespace App\Listeners\Backend;

use Illuminate\Contracts\Queue\ShouldQueue;

require_once app_path() . '/Helpers/PoolLog.php';

class PoolLogListener implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Pool_Log(
            $event->portalProviderID,
            $event->userID,
            $event->adminID,
            $event->previousBalance,
            $event->newBalance,
            $event->amount,
            $event->balanceType,
            $event->operation,
            $event->transactionId,
            $event->serviceName,
            $event->source
        );
    }
}
