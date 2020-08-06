<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{

    use SoftDeletes;    
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'notification';
    protected $primaryKey = 'PID';

    public function getNotification($userID, $portalProviderID, $createdAt, $limit, $offset)
    {
        return $this->select('notification.UUID as notificationUUID', 'u1.UUID as fromUUID', 'u2.UUID as toUUID', 'notification.type', 'notification.title', 'notification.message', 'notification.createdAt')
            ->LeftJoin('user as u1', 'notification.fromID', '=', 'u1.PID')                      // TODO: need to remove Left join
            ->LeftJoin('user as u2', 'notification.toID', '=', 'u2.PID')
            ->where('notification.toID', $userID)
            ->orWhere(function ($query) use ($portalProviderID) {
                $query->where('notification.portalProviderID', $portalProviderID)
                ->where('notification.type', 0);

            })
            ->where('notification.createdAt', '>=', $createdAt)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function getAllNotificationByPortalProvider(array $providerIDs)
    {
        return $this->join('portalProvider', 'portalProvider.PID', '=', 'notification.portalProviderID')
                    ->LeftJoin('user as u1', 'notification.fromID', '=', 'u1.PID')                      // TODO: need to remove Left join
                    ->LeftJoin('user as u2', 'notification.toID', '=', 'u2.PID')
                    ->Join('accessPolicy','accessPolicy.PID','=','portalProvider.PID')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('portalProvider.PID', $providerIDs);
                }
            )
            ->orderby('notification.PID','DESC');
    }

    public function getAllNotificationByPortalProviderWithTrashed(array $providerIDs, $limit = 500, $offset = 0)
    {
        return $this->join('portalProvider', 'portalProvider.PID', '=', 'notification.portalProviderID')
                    ->LeftJoin('user as u1', 'notification.fromID', '=', 'u1.PID')                      // TODO: need to remove Left join
                    ->LeftJoin('user as u2', 'notification.toID', '=', 'u2.PID')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('portalProvider.PID', $providerIDs);
                }
            )
            ->whereNull('portalProvider.deletedAt')
            ->whereNull('u1.deletedAt')
            ->whereNull('u2.deletedAt')
            ->withTrashed()
            ->orderby('notification.PID','DESC')
            ->limit($limit)
            ->offset($offset);
    }
}
