<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayList extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';
    protected $table = 'holidayList';
    protected $primaryKey = 'PID';

    protected $fillable = [
        'stockID',
        'className',
        'id',
        'title',
        'start',
        'end',
        'stick'
    ];

    public static function holidayListChecker($stockID, $date)
    { // $date must be only date ex: month-day (2020-05-06)
        return HolidayList::where('stockID', $stockID)
            ->whereDate('start', '<=', $date)
            ->whereDate('end', '>=', $date);
    }


    public function updateHolidayList($PID, $data)
    {
        return $this->where('id', $PID)->update($data);
    }
}
