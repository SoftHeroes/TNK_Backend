<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingSessionUpdate extends Model
{
    const CREATED_AT = 'createdAt';
    protected $table = 'pendingSessionUpdate';
    protected $primaryKey = 'PID';

    public function getPendingDetailsByIp($ip)
    {
        return $this->where('ip', $ip);
    }
}
