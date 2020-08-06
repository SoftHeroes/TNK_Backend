<?php

namespace App\Events\Socket;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GetLiveCountBetDataEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    protected $portalProviderUUID;
    protected $stockUUID;
    protected $gameLoop;

    protected $channelName = 'LiveTotalBetData';

    public function __construct($data, $portalProviderUUID, $stockUUID, $gameLoop)
    {
        $this->data = $data;
        $this->portalProviderUUID = $portalProviderUUID;
        $this->stockUUID = $stockUUID;
        $this->gameLoop = $gameLoop;
    }

    public function broadcastOn()
    {
        return new Channel($this->channelName . '.' . $this->portalProviderUUID . '.' . $this->stockUUID . '.' . $this->gameLoop);
    }

    public function broadcastAs()
    {
        return "App\Events\\" . $this->channelName;
    }
}
