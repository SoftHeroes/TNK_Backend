<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'user';
    protected $primaryKey = 'PID';

    protected $fillable = [
        'portalProviderUserID',
        'portalProviderID', 'userPolicyID', 'userName', 'country', 'gender', 'middleName', 'lastName', 'firstName',
        'email', 'profileImage', 'avatar', 'password', 'balance', 'isLoggedIn', 'isActive', 'lastCalledTime',
        'lastIP', 'loginTime', 'logoutTime', 'activeMinutes',
        'totalInvitationSent',
        'totalInvitationSentInDay',
        'totalInvitationSentInMin',
        'lastInvitationSend',
        'lastInvitationMin',
    ];

    //to check user balance can be merged with get user details in future.
    public function getUserByUUID($userUUID)
    {
        return $this->where('UUID', $userUUID)
            ->where('isActive', 'active');
    }

    public function checkUserName($userName)
    {
        return $this->where('userName', $userName)
            ->where('isActive', 'active');
    }

    /**
     * Get user by UUID and Portal Provider ID
     * this also verify if both belong to each other
     * @return QueryBuilder
     */
    public function getUserByUUIDAndPortalProviderID($userUUID, $portalProviderID)
    {
        return $this->join('portalProvider', 'user.portalProviderID', '=', 'portalProvider.PID')
            ->where('user.isActive', 'active')->where('user.UUID', $userUUID)
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')->where('portalProvider.PID', $portalProviderID);
    }
    public function userAlreadyExists($portalProviderUserID, $portalProviderID)
    {
        return $this->select('PID', 'balance', 'UUID')
            ->where('portalProviderUserID', '=', $portalProviderUserID)
            ->where('portalProviderID', '=', $portalProviderID)
            ->get();
    }

    /**
     * Get user and portal provider using userID
     *  - userID = user table primary key
     * @return QueryBuilder
     */
    public static function getPortalProviderByUserID($userID)
    {
        return DB::table('portalProvider')
            ->join('user', 'user.portalProviderID', '=', 'portalProvider.PID')->where('user.PID', $userID)
            ->where('portalProvider.isActive', 'active')->where('user.isActive', 'active')->whereNull('portalProvider.deletedAt')->whereNull('user.deletedAt');
    }

    public function checkBalanceByUserPID($userPID)
    {
        return $this->where('PID', $userPID);
    }

    public function updateUser($userID, $data)
    {
        return $this->where('PID', $userID)->update($data);
    }

    public function getAllUsersByPortalProviderID($portalProviderID)
    {
        return $this->select('user.PID', 'user.portalProviderUserID', 'user.UUID', 'user.firstName', 'user.lastName', 'user.email', 'user.balance', 'user.gender', 'user.country', DB::raw('(CASE WHEN user.isLoggedIn = true THEN "Logged In" ELSE "Logged out" END) as isLoggedIn'), 'user.lastCalledTime', 'user.lastIP', 'user.activeMinutes', 'portalProvider.UUID as portalProviderUUID', 'portalProvider.name as PortalProviderName')
            ->join('portalProvider', 'portalProvider.PID', '=', 'user.portalProviderID')
            ->where('user.portalProviderID', '=', $portalProviderID)
            ->where('user.isActive', 'active')
            ->whereNull('portalProvider.deletedAt')->whereNull('user.deletedAt');
    }

    public function getAllUsers()
    {
        return $this->select('user.PID', 'user.portalProviderUserID', 'user.UUID', 'user.firstName', 'user.lastName', 'user.email', 'user.balance', 'user.gender', 'user.country', DB::raw('(CASE WHEN user.isLoggedIn = true THEN "Logged In" ELSE "Logged out" END) as isLoggedIn'), 'user.lastCalledTime', 'user.lastIP', 'user.activeMinutes', 'portalProvider.UUID as portalProviderUUID', 'portalProvider.name as PortalProviderName')
            ->join('portalProvider', 'portalProvider.PID', '=', 'user.portalProviderID')
            ->where('user.isActive', 'active')
            ->whereNull('portalProvider.deletedAt')->whereNull('user.deletedAt')
            ->get();
    }

    public static function getUserProfile($userUUID)
    {
        return User::join('userSetting', 'user.PID', 'userSetting.userID')
            ->leftJoin('betting', 'betting.userID', 'user.PID')
            ->where('user.UUID', $userUUID)
            ->where('user.isActive', 'active');
    }

    public static function getTopLeaderBoardUsers($portalProviderID, $userID, $startDate, $endDate, $limit)
    {
        return DB::select("call USP_LeaderBoard(?,?,?,?,?,?,?)", [$portalProviderID, $userID, $startDate, $endDate, $limit, config("constants.image_path_avatar"), config("constants.image_path_user")]);
    }

    public function getUserByUserID($userPID)
    {
        return $this->where('PID', $userPID)->where('isActive', 'active');
    }

    public static function getUserDetails($portalProviderID, $userID, $startDate, $endDate)
    {
        return DB::select("call USP_GetUserDetails(?,?,?,?,?,?)", [$portalProviderID, $userID, $startDate, $endDate, "" . config("constants.image_path_avatar") . "", "" . config("constants.image_path_user") . ""]);
    }

    public function getAllUserByPortalProvider(array $providerIDs)
    {
        return $this->join('portalProvider', 'portalProvider.PID', '=', 'user.portalProviderID')
            // ->join('userSession', 'userSession.userID', '=', 'user.PID')
            ->leftJoin('country', 'country.alphaThreeCode', '=', 'user.country')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('portalProvider.PID', $providerIDs);
                }
            )
            ->orderby('user.PID','DESC');
    }
}
