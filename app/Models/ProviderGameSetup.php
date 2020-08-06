<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderGameSetup extends Model
{
    use SoftDeletes;
    
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'providerGameSetup';
    protected $primaryKey = 'PID';

    public function updateProviderPayout($PID,$data){
        return $this->where('PID', $PID)->update($data);

    }
}
