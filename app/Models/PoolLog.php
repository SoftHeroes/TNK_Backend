<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoolLog extends Model
{
    protected $table = 'poolLog';
    protected $primaryKey = 'PID';

    const CREATED_AT = 'createdAt';


    public function getPoolLogDetails($portalProviderID) {
        return $this
        ->leftJoin('admin', 'admin.PID', 'poolLog.adminID')
        ->join('portalProvider', 'portalProvider.PID', 'poolLog.portalProviderID')
        ->leftjoin('user', 'user.PID', 'poolLog.userID')
        ->when(
            !isEmpty($portalProviderID),
            function ($query) use ($portalProviderID) {
                return $query->whereIn('poolLog.portalProviderID', $portalProviderID);
            }
        )
        ->orderby('poolLog.PID','DESC');
    }
    
}
