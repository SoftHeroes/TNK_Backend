<?php

namespace App\Events\Socket;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BalanceUpdateEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'high'; //Queue Name

    public function queue(QueueManager $handler, $method, $arguments)
    {
        $handler->push($method, $arguments, $this->queue);
    }

    public $data;

    protected $channelName = 'balanceUpdate';

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
        return new Channel($this->channelName . '.' . $this->data['data']['userUUID']);
    }


    public function broadcastAs()
    {
        return "App\Events\\" . $this->channelName;
    }
}
