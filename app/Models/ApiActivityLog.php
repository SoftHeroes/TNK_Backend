<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiActivityLog extends Model
{
    protected $table = 'apiActivityLog';

    public static function getAllApiActivityLog($portalProviderID)
    {
        return ApiActivityLog::join('portalProvider', 'portalProvider.PID', 'apiActivityLog.portalProviderID')
            ->join('admin', 'admin.PID', 'apiActivityLog.adminID')
            ->leftJoin('user', 'user.PID', 'apiActivityLog.userID')
            ->when(
                !isEmpty($portalProviderID),
                function ($query) use ($portalProviderID) {
                    return $query->whereIn('apiActivityLog.portalProviderID', $portalProviderID);
                }
            )
            ->orderby('apiActivityLog.PID', 'DESC');
    }
}
