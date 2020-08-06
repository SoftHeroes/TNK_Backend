<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadMapBackup extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stockId', 'roadMap'
    ];

    protected $table = 'roadMapBackup';
    protected $primaryKey = 'PID';

    
    public static function findByStockId($stockId)
    {
        return RoadMapBackup::where('stockID', $stockId);
    }
}
