<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameSetup extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'gameSetup';
    protected $primaryKey = 'PID';

    public function getGameSetupByPID(array $PID)
    {
        return $this->whereIn('PID', $PID)->where('isActive', 'active');
    }

}
