<?php

namespace App\Providers\PortalProvider;

use App\Http\Controllers\ResponseController as Res;
use App\Models\CreditRequest;
use App\Models\Currency;
use App\Models\PortalProvider;
use App\Models\IdLookup;
use App\Events\Backend\PoolLogEvent;
use App\Models\Admin;
use App\Providers\File\FileProvider;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class CreditRequestProvider extends ServiceProvider
{
    public static function creditRequest($file, $requestData, $portalProviderID, $authData,$authPolicy)
    {
        $savePath = config('constants.portal_provider_admin_image_path') . 'creditRequest/';
        try {
            $isCurrency = Currency::findByCurrencyId($requestData['currencyID']);
            if ($isCurrency->count(DB::raw('1')) < 1) {
                return Res::notFound([], 'CurrencyID does not exist');
            }

            $enableToEditRate = false;
            if ($authPolicy->access == 1 || ($authData->agentType == 1 && $authData->portalProviderID == 1)) {
                $enableToEditRate = true;
            }
            
            if(!$enableToEditRate){
                // Check if rate is not match with data in record in database when agenttype is not self and ppd is not super
                if ((int) $requestData['rate'] != $isCurrency->first()->rate) {
                    return Res::badRequest([], 'please correct rate input, DO NOT modify rate');
                }
            }

            $requestData['chipValue'] = $requestData['amount'] * $requestData['rate'];
            $requestData['portalProviderID'] = $portalProviderID;

            $isFileCreated = FileProvider::saveImgFormFile($file, $savePath);
            if ($isFileCreated['code'] == 200) {
                $requestData['creditRequestImage'] = $isFileCreated['data'];
                $create = CreditRequest::create($requestData);
                return Res::success($create);
            }
            return $isFileCreated;
        } catch (Exception $ex) {
            return Res::errorException($ex->getMessage());
        }
    }

    public static function creditRequestManagement($adminID, $action, $creditRequestID, $authData, $authPolicy)
    {
        try {
            if ($action != 'accept' && $action != 'cancel') {
                return Res::badRequest([], "action should be 'accept' or 'cancel'");
            }

            if ($action == 'accept') { // accepted
                $requestStatus = 1;
                $action = 'accepted';
            } else { // canceled
                $requestStatus = 2;
                $action = 'canceled';
            }

            $creditRequest = CreditRequest::findByPID($creditRequestID);
            if ($creditRequest->count(DB::raw('1')) < 1) {
                return Res::notFound([], 'creditRequestID does not exist');
            }

            $updateData = [
                'requestStatus' => $requestStatus,
                'adminID' => $adminID,
            ];

            $creditRequestBuilder = CreditRequest::findByPID($creditRequestID)->select('amount', 'portalProviderID');
            $creditRequestData = $creditRequestBuilder->get();
            if ($creditRequestData->count(DB::raw('1')) != 0) {

                $portalProvider = PortalProvider::getPortalproviderAndCurrency($creditRequestData[0]->portalProviderID);
                if ($portalProvider->count(DB::raw('1')) < 1) {
                    return Res::notFound([], "PortalProviderID or CurrencyID is deactivated or deleted");
                }

                $providerData = $portalProvider->select('portalProvider.PID', 'portalProvider.name', 'portalProvider.currencyID', 'portalProvider.creditBalance', 'portalProvider.mainBalance', 'portalProvider.server', 'portalProvider.ipList', 'portalProvider.rate', 'currency.rate as currencyRate')->get();

                $transactionId = IdLookup::getUniqueId('poolLog', 'transactionId');

                // get agent data
                $adminModel = new Admin();
                $adminData = $adminModel->getAdminDetails($adminID);
                DB::beginTransaction();

                // deduct from admin main balance base on prepaid and credit
                $deductFromAgentAllow = [1, 2]; // 1 = agentTypeSelf ,2 = agentTypeAuto
                if (in_array($authData->agentType, $deductFromAgentAllow)) {
                    // agentPaymentType  : 1 = prepaid ,2 , credit
                    $columnDeduct = "creditBalance";
                    if ($authData->agentPaymentType == 1) $columnDeduct = "mainBalance";

                    // if balance bigger than mainBalance or creditBalance then throw error
                    if ($creditRequestData[0]->amount > $adminData[0]->$columnDeduct) {
                        return Res::badRequest([], "Please recharge $columnDeduct to continue");
                    }

                    $mainBalance = $adminData[0]->$columnDeduct;
                    $adminData[0]->decrement($columnDeduct, $creditRequestData[0]->amount);

                    event(new PoolLogEvent(
                        $authData->portalProviderID, // agent's portalProvider
                        null,
                        $adminID, // agentID
                        $mainBalance, // current Balance of agent
                        $adminData[0]->$columnDeduct,
                        $creditRequestData[0]->amount,
                        $columnDeduct,
                        1, // debit
                        $transactionId,
                        'Agent Request List',
                        0 // system
                    ));
                }

                $portalProvider->increment('creditBalance', $creditRequestData[0]->amount);
                $portalProvider->increment('mainBalance', $creditRequestData[0]->amount);

                if (isEmpty(($providerData[0]->rate)) || $providerData[0]->rate < 1) {
                    $rate = $providerData[0]->currencyRate;
                    $chipAmount = $providerData[0]->currencyRate * $creditRequestData[0]->amount;
                } else {
                    $rate = $providerData[0]->rate;
                    $chipAmount = $providerData[0]->rate * $creditRequestData[0]->amount;
                }

                $creditRequestBuilder->increment('chipValue', $chipAmount);
                $creditRequestBuilder->update(['rate' => $rate]);

                DB::commit();



                // recharging the balance updates both the credit balance and main balance. So there is two separate pool log entries for updating both the balances.
                event(new PoolLogEvent(
                    $creditRequestData[0]->portalProviderID,
                    null,
                    $adminID,
                    $providerData[0]->creditBalance,
                    $providerData[0]->creditBalance + $creditRequestData[0]->amount,
                    $creditRequestData[0]->amount,
                    'creditBalance',
                    2, // recharge
                    $transactionId,
                    'Admin Panel',
                    0 // system
                ));

                event(new PoolLogEvent(
                    $creditRequestData[0]->portalProviderID,
                    null,
                    $adminID,
                    $providerData[0]->mainBalance,
                    $providerData[0]->mainBalance + $creditRequestData[0]->amount,
                    $creditRequestData[0]->amount,
                    'mainBalance',
                    2, // recharge
                    $transactionId,
                    'Admin Panel',
                    0 // system
                ));
            }
            $isUpdated = $creditRequest->update($updateData);

            if ($isUpdated) {
                return Res::success($creditRequest->first(), "Request Was $action");
            }
            return Res::badRequest([], "Request Failed");
        } catch (Exception $ex) {
            DB::rollback();
            return Res::errorException($ex->getMessage());
        }
    }
}
