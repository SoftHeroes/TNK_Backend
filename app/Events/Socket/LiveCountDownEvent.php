<?php

namespace App\Events\Socket;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class LiveCountDownEvent implements ShouldBroadcastNow
{
    public $data;

    protected $channelName = 'countdown';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->channelName . '.' . $this->data['data']['portalProviderUUID']);
    }

    public function broadcastAs()
    {
        return "App\Events\\".$this->channelName;
    }
}
