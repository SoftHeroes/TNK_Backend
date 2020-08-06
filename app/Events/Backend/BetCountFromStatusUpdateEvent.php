<?php

namespace App\Events\Backend;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BetCountFromStatusUpdateEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameUUID;
    public $portalProviderUUID;
    public $stockUUID;
    public $loop;



    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($gameUUID, $portalProviderUUID,$stockUUID,$loop)
    {
        $this->gameUUID = $gameUUID;
        $this->portalProviderUUID = $portalProviderUUID;
        $this->stockUUID = $stockUUID;
        $this->loop = $loop;
    }
}
