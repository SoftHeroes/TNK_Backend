<?php

namespace App\Listeners\Socket;

use App\Models\DynamicOdd;
use App\Events\Socket\DynamicOddData;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\ResponseController as Res;

class DynamicOddDataListener implements ShouldQueue
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
        $dynamicOddModel = new DynamicOdd();

        $data = $dynamicOddModel->dynamicOddByGame($event->gameID);
        $data = Res::success($data);
        broadcast(new DynamicOddData($data));

    }
}
