<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Likes extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table      = 'likes';
    protected $primaryKey = 'PID';
    protected $fillable   = ['userFrom', 'userTo', 'status'];

    public function isLiked(
        $userFrom,
        $userTo
    ) {
        return $this->where([['userFrom', '=', $userFrom], ['userTo', '=', $userTo]])->first();
    }

    public function getAllLiker($userId)
    {
        return $this->where('userTo', '=', $userId)->where('status', true);
    }
}
