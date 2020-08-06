<?php

namespace App\Events\Socket;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class LiveBetCountEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    protected $portalProviderUUID;
    protected $stockUUID;
    protected $gameLoop;

    protected $channelName = 'liveBetCounts';
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data, $portalProviderUUID, $stockUUID, $gameLoop)
    {
        $this->data = $data;
        $this->portalProviderUUID = $portalProviderUUID;
        $this->stockUUID = $stockUUID;
        $this->gameLoop = $gameLoop;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->channelName . '.' . $this->portalProviderUUID . '.' . $this->stockUUID . '.' . $this->gameLoop);
    }

    public function broadcastAs()
    {
        return "App\Events\\" . $this->channelName;
    }
}
