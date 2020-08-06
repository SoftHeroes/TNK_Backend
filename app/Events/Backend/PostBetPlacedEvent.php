<?php

namespace App\Events\Backend;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostBetPlacedEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameID;
    public $userID;
    public $betData;
    public $gameUUID;
    public $portalProviderUUID;
    public $userUUID;
    public $stockUUID;
    public $loop;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($gameID, $userID, $betData, $gameUUID, $portalProviderUUID, $userUUID,$stockUUID,$loop)
    {
        $this->gameID = $gameID;
        $this->userID = $userID;
        $this->betData = $betData;
        $this->gameUUID = $gameUUID;
        $this->portalProviderUUID = $portalProviderUUID;
        $this->userUUID = $userUUID;
        $this->stockUUID = $stockUUID;
        $this->loop = $loop;
    }
}
