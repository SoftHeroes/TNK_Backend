<?php

namespace App\Listeners\Socket;

use App\Models\Game;
use App\Providers\Gaming\GameProvider;
use App\Events\Socket\GetLiveCountBetDataEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class GetLiveCountBetDataListener implements ShouldQueue
{

    public function __construct()
    {
    }

    public function handle($event)
    {
        $gameProviderRef = new GameProvider(null);
        $response = $gameProviderRef->liveCountBetData($event->gameUUID, $event->portalProviderUUID,$event->stockUUID,$event->loop);
        broadcast(new GetLiveCountBetDataEvent($response['res'], $event->portalProviderUUID,$event->stockUUID,$event->loop));
    }
}
