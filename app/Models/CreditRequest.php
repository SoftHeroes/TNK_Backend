<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditRequest extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $primaryKey = 'PID';

    protected $table = 'creditRequest';

    protected $fillable = [
        'amount',
        'requestStatus',
        'creditRequestDescription',
        'creditRequestImage',
        'portalProviderID',
        'currencyID',
        'adminID',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'chipValue',
        'rate'
    ];

    protected $hidden = ['PID'];

    public static function findByPID($PID)
    {
        return CreditRequest::where('PID', $PID)->whereNull('deletedAt');
    }

    public function findByPortalProviderIDAndAccessID($portalProviderID, $policyAccessID)
    {
        return $this->join('portalProvider', 'portalProvider.PID', 'creditRequest.portalProviderID')
            ->when(
                $portalProviderID != 1 && $policyAccessID != 1,
                function ($query) use ($portalProviderID) {
                    return $query->where('creditRequest.portalProviderID', $portalProviderID);
                }
            )
            ->whereNull('creditRequest.deletedAt')->orderBy('creditRequest.createdAt', 'desc');
    }

    public function getAllCreditRequestByPortalProvider(array $providerIDs, $limit = 500, $offset = 0)
    {
        return $this->join('portalProvider', 'portalProvider.PID', '=', 'creditRequest.portalProviderID')
                    ->join('currency', 'currency.PID', 'creditRequest.currencyID')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('portalProvider.PID', $providerIDs);
                }
            )
            ->orderby('creditRequest.PID','DESC')
            ->limit($limit)
            ->offset($offset);
    }
}
