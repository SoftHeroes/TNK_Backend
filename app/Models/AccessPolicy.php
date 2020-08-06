<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessPolicy extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $primaryKey = "PID";
    protected $table = "accessPolicy";

    protected $fillable = [
        'name',
        'isAllowAll',
        'portalProviderIDs',
        'accessAdminPolicy',
        'accessAccessPolicy',
        'accessAdminInformation',
        'accessProviderList',
        'accessProviderGameSetup',
        'accessProviderRequestList',
        'accessProviderRequestBalance',
        'accessProviderInfo',
        'accessProviderConfig',
        'accessCurrency',
        'accessBetRule',
        'accessBetSetup',
        'accessNotification',
        'accessHolidayList',
        'accessMonetaryLog',
        'accessActivityLog',
        'accessInvitationSetup',
        'isActive'
    ];

    public function findByAccessPolicyId($PID)
    { 
        return $this->where('PID', $PID);
    }

    public function updateAccessPolicy($PID, $data)
    {
        return $this->where('PID', $PID)->update($data);
    }

    public function getAllAccessPolicy()
    {
        return $this->where('isActive', 'active');
    }

}
