<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class Admin extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'admin';

    protected $primaryKey = 'PID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'adminPolicyID',
        'portalProviderID', 'firstName', 'middleName', 'lastName', 'emailID',
        'username', 'password', 'profileImage', 'invalidUpdateAttemptsCount',
        'lastPasswordResetTime', 'isActive', 'accessPolicyID',
    ];

    public function intoArray($value)
    {
        return array(
            'adminPolicyID' => is_null($value->adminPolicyID) ? 1 : $value->adminPolicyID,
            'portalProviderID' => $value->portalProviderId,
            "firstName" => $value->firstName == null ? null : $value->firstName,
            "middleName" => $value->middleName == null ? null : $value->middleName,
            "lastName" => $value->lastName == null ? null : $value->lastName,
            "emailID" => $value->emailID,
            "username" => $value->username,
            "password" => Crypt::encrypt($value->password),
        );
    }

    public function fetchByUsername($userName)
    {
        return $this->select(
            'admin.PID',
            'adminPolicyID',
            'portalProviderID',
            'portalProvider.UUID as portalProviderUUID',
            'emailID',
            'username',
            'password',
            'adminPolicy.source',
            'adminPolicy.access',
            'firstName',
            'lastName',
            DB::raw("(CASE WHEN admin.profileImage != '' THEN CONCAT('" . config("constants.image_path_admin") . "',admin.profileImage) ELSE NULL END) as profileImage"),
            'server',
            'ipList',
            'APIKey',
            'currency.abbreviation',
            'accessPolicy.isAllowAll',
            'accessPolicy.portalProviderIDs',
            'accessPolicy.accessAdminPolicy',
            'accessPolicy.accessAccessPolicy',
            'accessPolicy.accessAdminInformation',
            'accessPolicy.accessProviderList',
            'accessPolicy.accessProviderGameSetup',
            'accessPolicy.accessProviderRequestList',
            'accessPolicy.accessProviderRequestBalance',
            'accessPolicy.accessProviderInfo',
            'accessPolicy.accessProviderConfig',
            'accessPolicy.accessCurrency',
            'accessPolicy.accessBetRule',
            'accessPolicy.accessBetSetup',
            'accessPolicy.accessNotification',
            'accessPolicy.accessHolidayList',
            'accessPolicy.accessMonetaryLog',
            'accessPolicy.accessActivityLog',
            'accessPolicy.accessInvitationSetup'
        )
            ->join('adminPolicy', 'admin.adminPolicyID', '=', 'adminPolicy.PID')
            ->join('portalProvider', 'admin.portalProviderID', '=', 'portalProvider.PID')
            ->join('accessPolicy', 'admin.accessPolicyID', '=', 'accessPolicy.PID')
            ->join('currency', 'portalProvider.currencyID', '=', 'currency.PID')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->where('accessPolicy.isActive', 'active')->whereNull('accessPolicy.deletedAt')
            ->where('adminPolicy.isActive', 'active')->where('admin.isActive', 'active')->whereNull('adminPolicy.deletedAt')
            ->where('admin.username', $userName)
            ->get();
    }

    //currently only used for validating admin ID, add other fields as needed.
    public function getAdminDetails($adminID)
    {
        return $this->select(
            'PID',
            'portalProviderID',
            'adminPolicyID',
            'firstName',
            'lastName',
            'emailID',
            'username',
            'lastPasswordResetTime',
            'agentType',
            'agentPaymentType',
            'mainBalance',
            'creditBalance',
            DB::raw("(CASE WHEN admin.profileImage != '' THEN CONCAT('" . config("constants.image_path_admin") . "',admin.profileImage) ELSE NULL END) as profileImage")
        )
            ->where('PID', $adminID)
            ->where('isActive', 'active')
            ->get();
    }

    //Get all the admin details of the providers
    public function getAllAdminByPortalProvider($providerIDs)
    {
        return $this->join('portalProvider', 'admin.portalProviderID', '=', 'portalProvider.PID')
            ->where('admin.isActive', 'active')->whereNull('admin.deletedAt')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('admin.portalProviderID', $providerIDs);
                }
            );
    }

    //Get all the admin details of the providers
    public function getAllAdminByPortalProviderWithTrashed($providerIDs)
    {
        return $this->join('portalProvider', 'admin.portalProviderID', '=', 'portalProvider.PID')
            ->where('admin.isActive', 'active')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('admin.portalProviderID', $providerIDs);
                }
            );
    }

    public function fetchByEmailId($emailID)
    {
        return $this->select('admin.PID', 'admin.portalProviderID', 'adminPolicy.otpValidTimeInSeconds')
            ->join('adminPolicy', 'admin.adminPolicyID', '=', 'adminPolicy.PID')
            ->join('portalProvider', 'admin.portalProviderID', '=', 'portalProvider.PID')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->where('adminPolicy.isActive', 'active')->where('admin.isActive', 'active')->whereNull('adminPolicy.deletedAt')
            ->where('emailID', '=', $emailID)
            ->get();
    }

    public function updateAdmin($PID, $data)
    {
        return $this->where('PID', $PID)->update($data);
    }

    public function getAdminDataByPID($adminPID)
    {
        return $this->join('accessPolicy', 'admin.accessPolicyID', '=', 'accessPolicy.PID')
            ->where('admin.PID', $adminPID)
            ->where('admin.isActive', 'active')->whereNull('admin.deletedAt')
            ->where('accessPolicy.isActive', 'active')->whereNull('accessPolicy.deletedAt');
    }
}
