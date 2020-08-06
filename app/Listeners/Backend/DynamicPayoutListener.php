<?php

namespace App\Listeners\Backend;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Providers\Payout\DynamicPayoutProvider;

class DynamicPayoutListener implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $dynamicPayoutProviderRef = new DynamicPayoutProvider();
        $dynamicPayoutProviderRef->calculateDynamicOddByGameID($event->gameID);
    }
}
