<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'rule';
    protected $primaryKey = 'PID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'isMatched', 'isActive'
    ];


    //add more columns in select as needed
    public function getRuleData($ruleID)
    {
        return $this->select('name')
            ->where('PID', $ruleID)
            ->where('isActive', 'active')
            ->get();
    }

    //get all rules.
    public function getAllRules()
    {
        return $this->select('PID as ruleID', 'name')
            ->where('isActive', 'active')
            ->get();
    }
}
