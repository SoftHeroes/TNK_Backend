<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSession extends Model
{
    use SoftDeletes;
    protected $table = 'userSession';
    protected $primaryKey = 'PID';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';


    //find by user's ID
    public function checkUserSession ($userID) {
        return $this->select('PID')
                    ->where('userID', '=', $userID)
                    ->whereNull('deletedAt')
                    ->get();
    }

    public function findByUserId ($userID) {
        return $this->select('userID','userIpAddress','balance','loginTime','logoutTime')
                    ->where('userID', '=', $userID)
                    ->get();
    }

    public function getUserActiveTime($fromDate, $toDate, $currentTime, $userPID) {
        return $this->withTrashed()->selectRaw("SUM(TIMESTAMPDIFF(MINUTE,loginTime,IFNULL(logoutTime,?))) as activeTimeInMins,DATE(loginTime) as Date",[$currentTime])
        ->whereDate('createdAt', '>=', $fromDate)
        ->whereDate('createdAt', '<=', $toDate)
        ->where('userID', '=', $userPID)
        ->groupBy('Date')
        ->get();
    }

    public function getUserTotalActiveTime($userPID, $currentTime) {
        return $this->withTrashed()->selectRaw("SUM(TIMESTAMPDIFF(MINUTE,loginTime,IFNULL(logoutTime,?))) as activeTimeInMins,DATE(loginTime) as Date",[$currentTime])
        ->where('userID', '=', $userPID)
        ->get();
    }

    //Get inactive users for more than 5 minutes
    public function getInactiveUser($date) {
        return $this->selectRaw('user.UUID,userSession.userID,userSession.loginTime as loginTime ,MAX(apiActivityLog.responseTime) as maxResponseTime,MAX(chat.createdAt) as maxCreatedAt')
                    ->leftJoin('apiActivityLog', 'userSession.userID', '=', 'apiActivityLog.userID')
                    ->leftJoin('chat', 'userSession.userID', '=', 'chat.userID')
                    ->leftJoin('user', 'userSession.userID', '=', 'user.PID')
                    ->whereNull('userSession.logoutTime')
                    ->groupByRaw('userSession.userID')
                    ->having('maxResponseTime', '<',$date)
                    ->orHaving('loginTime', '<',$date)
                    ->orHaving('maxCreatedAt', '<',$date)
                    ->get();

    }
}
