<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'currency';
    protected $primaryKey = 'PID';

    protected $fillable = [
        'name',
        'rate',
        'isActive',
        'symbol',
        'abbreviation',
        'createdAt',
        'updatedAt',
        'deletedAt'
    ];

    public static function findByCurrencyId($currencyId, $active = 'active')
    {
        return Currency::where('PID', $currencyId)->where('isActive', $active)->whereNull('deletedAt');
    }

    public static function findByCurrencyName($currencyName, $active = 'active')
    {
        return Currency::where('name', $currencyName)->where('isActive', $active)->whereNull('deletedAt');
    }

    public function findByCurrencyAll()
    {
        return $this->where('isActive', 'active')->whereNull('deletedAt');
    }

    public function updateCurrency($PID, $data)
    {
        return $this->where('PID', $PID)->update($data);
    }
}
