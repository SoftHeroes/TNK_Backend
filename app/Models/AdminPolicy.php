<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminPolicy extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'adminPolicy';
    protected $primaryKey = 'PID';

    protected $fillable = [
        'name',
        'userLockTime',
        'invalidAttemptsAllowed',
        'otpValidTimeInSeconds',
        'passwordResetTime',
        'access',
        'source',
        'isActive'
    ];

    public static function findByPolicyId(int $PID)
    { // default is 1
        return AdminPolicy::where('PID', $PID);
    }

    public function updateAdminPolicy($PID, $data)
    {
        return $this->where('PID', $PID)->update($data);
    }

    public function getAllActivePolicies()
    {
        return $this->where('adminPolicy.isActive', 'active');
    }

    public function getAdminPolicyByAdminPID($adminPID)
    {
        return $this->join('admin', 'admin.adminPolicyID', '=', 'adminPolicy.PID')
            ->where('admin.PID', $adminPID);
    }
}
