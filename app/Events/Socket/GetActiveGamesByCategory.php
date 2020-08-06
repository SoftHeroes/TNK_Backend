<?php

namespace App\Events\Socket;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class GetActiveGamesByCategory implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    protected $channelName = 'getActiveGamesByCategory';
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data,$portalProviderUUID)
    {
        $this->portalProviderUUID = $portalProviderUUID;
        $this->data = $data;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->channelName . '.' . $this->portalProviderUUID);
    }


    public function broadcastAs()
    {

        return "App\Events\\".$this->channelName;
    }
}
