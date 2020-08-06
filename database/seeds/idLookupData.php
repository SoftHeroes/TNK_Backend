<?php

use App\Models\IdLookup;
use Illuminate\Database\Seeder;

class idLookupData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $idLookup = [
            array('PID' => 1, 'tableName' => 'poolLog', 'columnName' => 'transactionId', 'id' => 1,),
        ];
        IdLookup::insert($idLookup);
    }
}
