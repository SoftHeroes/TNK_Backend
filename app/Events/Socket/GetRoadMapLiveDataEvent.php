<?php

namespace App\Events\Socket;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GetRoadMapLiveDataEvent implements ShouldBroadcastNow
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    protected $channelName = 'roadMap';

    public function __construct($data)
    {
        $this->data = $data;
    }


    public function broadcastOn()
    {
        return new Channel($this->channelName . '.' . $this->data["data"]['channelName']);
    }

    public function broadcastAs()
    {
        return "App\Events\\" . $this->channelName;
    }
}
