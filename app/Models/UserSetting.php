<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSetting extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    public static $updateColumn = ["isAllowToVisitProfile", "isAllowToFollow", "isAllowToDirectMessage", "isSound", "isAllowToLocation", 'userID'];

    protected $primaryKey = "PID";
    protected $table = "userSetting";
    protected $fillable = [
        "PID",
        "userID",
        "isAllowToVisitProfile",
        "isAllowToFollow",
        "isAllowToDirectMessage",
        "isSound",
        "isAllowToLocation",
        "createdAt",
        "updatedAt",
        "deletedAt",
    ];

    public static function findByUserID($userID)
    {
        return UserSetting::where("userID", $userID);
    }
}
