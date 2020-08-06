<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowBetRule extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'followBetRule';
    protected $primaryKey = 'PID';

    protected $fillable = [
        'type',
        'name',
        'rule',
        'isActive',
        'min',
        'max'
    ];

    public function getFollowBetRuleData($followBetRuleID, $type = null)
    {
        return $this->where('PID', $followBetRuleID)
            ->when(
                !isEmpty($type),
                function ($query) use ($type) {
                    return $query->where('type', $type);
                }
            )
            ->where('isActive', 'active');
    }

    public function updateFollowBetRule($PID, $data)
    {
        return $this->where('PID', $PID)->update($data);
    }
}
