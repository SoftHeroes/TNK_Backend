<?php

namespace App\Events\Backend;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PoolLogEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $portalProviderID;
    public $userID;
    public $adminID;
    public $previousBalance;
    public $newBalance;
    public $amount;
    public $balanceType;
    public $operation;
    public $transactionId;
    public $serviceName;
    public $source;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($portalProviderID, $userID, $adminID, $previousBalance, $newBalance, $amount, $balanceType, $operation, $transactionId, $serviceName, $source)
    {
        $this->portalProviderID = $portalProviderID;
        $this->userID           = $userID;
        $this->adminID          = $adminID;
        $this->previousBalance  = $previousBalance;
        $this->newBalance       = $newBalance;
        $this->amount           = $amount;
        $this->balanceType      = $balanceType;
        $this->operation        = $operation;
        $this->transactionId    = $transactionId;
        $this->serviceName      = $serviceName;
        $this->source           = $source;
    }
}
