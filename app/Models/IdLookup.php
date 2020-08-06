<?php

namespace App\Models;

use DB;
use Log;
use Exception;
use Illuminate\Database\Eloquent\Model;

class IdLookup extends Model
{
    protected $table = 'idLookup';
    protected $primaryKey = 'PID';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    public static function getUniqueId($tableName, $columnName)
    {
        try {
            DB::beginTransaction();
            $data = IdLookup::select('id')->where('tableName', $tableName)->where('columnName', $columnName)->lockForUpdate()->get();

            if ($data->count(DB::raw('1')) == 0) {
                return null;
            } else {
                IdLookup::where('tableName', $tableName)->where('columnName', $columnName)->increment('id', 1);

                DB::commit();
                return $data[0]->id;
            }
        } catch (Exception $e) {
            DB::rollback();
            Log::debug($e->getMessage());
            return null;
        }
    }
}
