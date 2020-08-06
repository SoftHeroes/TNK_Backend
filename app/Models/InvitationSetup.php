<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvitationSetup extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = "invitationSetup";
    protected $primaryKey = "PID";

    protected $fillable = [
        'name',
        'maximumRequestInDay',
        'requestMin',
        'maximumRequestInMin',
        'createdAt',
        'updatedAt'
    ];
}
