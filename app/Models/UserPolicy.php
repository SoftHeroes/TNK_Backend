<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPolicy extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'userPolicy';
    protected $primaryKey = 'PID';

    public function validatePolicyID($userPolicyID)
    {
        return $this->select('PID')->where('PID', '=', $userPolicyID)->where('isActive', '=', 'active')->get();
    }


}
