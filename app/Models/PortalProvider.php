<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PortalProvider extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'portalProvider';
    protected $primaryKey = 'PID';

    protected $fillable = [
        "name",
        "currencyID",
        "creditBalance",
        "mainBalance",
        "UUID",
        "server",
        "ipList",
        "rate",
        "APIKey",
        "isActive"
    ];

    public function getPortalProviderByUUID($portalProviderUUID)
    {
        return $this->select('PID', 'name', 'currencyID', 'creditBalance', 'mainBalance', 'server', 'ipList', 'APIKey')
            ->where('UUID', $portalProviderUUID)
            ->where('isActive', 'active')
            ->get();
    }

    public static function getPortalProviders($portalProviderID = null)
    {
        return PortalProvider::where('isActive', 'active')
            ->when(
                !isEmpty($portalProviderID),
                function ($query) use ($portalProviderID) {
                    return $query->whereIn('PID', $portalProviderID);
                }
            );
    }

    public static function getAllPortalProvidersById(array $providerIDs)
    {
        return PortalProvider::where('isActive', 'active')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('portalProvider.PID', $providerIDs);
                }
            );
    }

    //Get total betting values based on provider id
    public function getBetsByProviderID($portalProviderID)
    {
        return $this->select(DB::raw("count(1) as totalBets, ROUND(SUM(CASE WHEN betResult = 1 THEN rollingAmount-betAmount END),2) as totalProfitEarned, SUM(betAmount) as totalBetAmount, count(distinct betting.userID) as totalUsers, SUM(rollingAmount) as totalRollingAmount"), 'user.portalProviderID')
            ->join('user', 'user.portalProviderID', '=', 'portalProvider.PID')
            ->join('betting', 'betting.userID', '=', 'user.PID')
            ->where('portalProvider.PID', '=', $portalProviderID)
            ->get();
    }

    public function getUsersInBetsByProviderID($portalProviderID)
    {
        return $this->select('user.UUID as userUUID', 'user.firstName', 'user.lastName', 'user.balance','user.userName','user.isLoggedIn')
            ->join('user', 'user.portalProviderID', '=', 'portalProvider.PID')
            ->join('betting', 'betting.userID', '=', 'user.PID')
            ->where('portalProvider.PID', '=', $portalProviderID)->distinct('user.UUID')
            ->get();
    }

    public static function getAllPortalProvidersAndCurrencyById(array $providerIDs)
    {
        return PortalProvider::join('currency', 'currency.PID', 'portalProvider.currencyID')
            ->where('portalProvider.isActive', 'active')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('portalProvider.PID', $providerIDs);
                }
            );
    }

    public function findByPortalProviderID($portalProvidersID)
    {
        return $this->where('PID', $portalProvidersID)->where('isActive', 'active');
    }

    public function findByServerName($serverName)
    {
        return $this->where('server', $serverName)->where('isActive', 'active');
    }

    public function updatePortalProvider($PID, $data)
    {
        return $this->where('PID', $PID)->update($data);
    }

    public static function getPortalproviderAndCurrency($portalProviderID,$status = "active"){
        return PortalProvider::join('currency','portalProvider.currencyID','currency.PID')
        ->where('portalProvider.PID', $portalProviderID)->whereNull('currency.deletedAt')->where('portalProvider.isActive',$status);
    }
}
