<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'country';
    protected $primaryKey = 'PID';

    public function getCountry(){
        return $this->select('countryCode','countryName')
        ->get();
    }

}
