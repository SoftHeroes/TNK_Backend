<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowBetSetup extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'followBetSetup';
    protected $primaryKey = 'PID';

    protected $fillable = [
        'followBetRuleID',
        'unFollowBetRuleID',
        'minFollowBetRuleSelect',
        'maxFollowBetRuleSelect',
        'minUnFollowBetRuleSelect',
        'maxUnFollowBetRuleSelect',
        'isActive'
    ];
    

    public function updateFollowBetSetups($PID, $data)
    {
        return $this->where('PID', $PID)->update($data);
    }
}
