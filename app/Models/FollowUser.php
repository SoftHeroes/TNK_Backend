<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUser extends Model
{

    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'followUser';
    protected $primaryKey = 'PID';

    protected $fillable = [
        "followerID",
        "followToID",
        "isFollowing",
        "followBetRuleID",
        "followRuleValue",
        "unFollowBetRuleID",
        "unFollowRuleValue",
        "createdAt",
        "updatedAt",
        "deletedAt"
    ];

    function getAllFollowers($followUserID)
    {
        return $this->where('followToID', '=', $followUserID)->where('isFollowing', true);
    }

    public static function getFollowerAndFollowTo($followerID, $followToID)
    {
        return FollowUser::where([['followerID', $followerID], ['followToID', $followToID]]);
    }

    public function getUserFollowersOrFollowing($userID, $followersType, $limit, $offset)
    {
        return $this->join('user', 'user.PID', $followersType == 1 ? 'followUser.followerID' : 'followUser.followToID')
            ->join('userSetting', 'userSetting.userID', $followersType == 1 ? 'followUser.followerID' : 'followUser.followToID')
            ->when(                                                             // getting user followers
                $followersType == 1,
                function ($query) use ($userID) {
                    return $query->where('followUser.followToID', $userID);
                }
            )
            ->when(                                                             // getting list of users to whom user is following
                $followersType == 2,
                function ($query) use ($userID) {
                    return $query->where('followUser.followerID', $userID);
                }
            )
            ->where('followUser.isFollowing', true)
            ->limit($limit)
            ->offset($offset);
    }
}
