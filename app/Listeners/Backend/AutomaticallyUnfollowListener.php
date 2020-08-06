<?php

namespace App\Listeners\Backend;

use App\Jobs\AutomaticallyUnfollowJob;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AutomaticallyUnfollowListener
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
        AutomaticallyUnfollowJob::dispatch(
            $event->userID,
            $event->portalProviderUUID,
            $event->userUUID
        )->onQueue('immediate');
    }
}
