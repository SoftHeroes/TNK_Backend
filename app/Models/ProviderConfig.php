<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderConfig extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table      = 'providerConfig';
    protected $primaryKey = 'PID';

    protected $fillable = [
        'portalProviderID', 'followBetSetupID', 'logoutAPICall', 'updatedAt', 'invitationSetupID'
    ];

    public function getProviderConfigByPID($portalProviderPID)
    {
        return $this->where('portalProviderID', $portalProviderPID);
    }

    public function getProviderConfigRuleByPID($portalProviderPID)
    {
        return $this->join('followBetSetup', 'providerConfig.followBetSetupID', '=', 'followBetSetup.PID')
            ->where('providerConfig.portalProviderID', $portalProviderPID)
            ->where('followBetSetup.isActive', 'active');
    }

    public function getAllProviderConfigByPortalProvider(array $providerIDs, $limit = 500, $offset = 0)
    {
        return $this->join('portalProvider', 'portalProvider.PID', '=', 'providerConfig.portalProviderID')
            ->join('invitationSetup', 'invitationSetup.PID', '=', 'providerConfig.invitationSetupID')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('portalProvider.PID', $providerIDs);
                }
            )
            ->orderby('providerConfig.PID', 'DESC')
            ->limit($limit)
            ->offset($offset);
    }

    public function getAllProviderConfigByPortalProviderWithTrashed(array $providerIDs, $limit = 500, $offset = 0)
    {
        return $this->join('portalProvider', 'portalProvider.PID', '=', 'providerConfig.portalProviderID')
            ->join('invitationSetup', 'invitationSetup.PID', '=', 'providerConfig.invitationSetupID')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('portalProvider.PID', $providerIDs);
                }
            )
            ->whereNull('portalProvider.deletedAt')
            ->whereNull('invitationSetup.deletedAt')
            ->withTrashed()
            ->orderby('providerConfig.PID', 'DESC')
            ->limit($limit)
            ->offset($offset);
    }


    public function updateProviderConfig($PID, $data)
    {
        return $this->where('PID', $PID)->update($data);
    }

    public static function getInvitationSetup($portalProviderPID)
    {
        return ProviderConfig::join('invitationSetup', 'providerConfig.invitationSetupID', 'invitationSetup.PID')->where('providerConfig.portalProviderID', $portalProviderPID);
    }
}
