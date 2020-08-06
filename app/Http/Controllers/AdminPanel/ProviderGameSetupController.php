<?php

namespace App\Http\Controllers\AdminPanel;

use App\Exceptions\ValidationError;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseController as Res;
use App\Models\Admin;
use App\Models\CreditRequest;
use App\Models\Currency;
use App\Models\FollowBetSetup;
use App\Models\GameSetup;
use App\Models\InvitationSetup;
use App\Models\PortalProvider;
use App\Models\ProviderConfig;
use App\Models\ProviderGameSetup;
use App\Models\Stock;
use App\Providers\Admin\AdminProvider;
use App\Providers\Payout\GamePayouts;
use App\Providers\PortalProvider\CreditRequestProvider;
use App\Providers\PortalProvider\PortalProvider as AppPortalProvider;
use App\Providers\Users\UserProvider;
use DB;
use Exception;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderGameSetupController extends Controller
{
    public function getProviderGameSetup(Request $request)
    {
        $portalProviderModel = new PortalProvider();
        //validating Provider UUID

        $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');
        $providerData = $portalProviderModel->getPortalProviderByUUID($sessionData['portalProviderUUID']);

        if ($providerData->count(DB::raw('1')) == 0) {
            return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
        } else {
            $portalProviderID = $providerData[0]->PID;

            $selectedColumn = [
                "stock.name",
                "stock.PID",
                "providerGameSetup.payoutType"
            ];

            if ($portalProviderID == 1) {
                $portalProviderData = $portalProviderModel->getPortalProviders()->get();
                return view('adminPanel.providerGameSetup', compact('portalProviderData'));
            } else {
                $StockData = Stock::getStockBaseOnProvider($portalProviderID)->select($selectedColumn)->get();
                $portalProviderData = null;
                return view('adminPanel.providerGameSetup', compact('StockData', 'portalProviderData'));
            }
        }
    }

    public function portalProviderSelect(Request $request)
    {
        $portalProviderModel = new PortalProvider();
        //validating Provider UUID
        $providerData = $portalProviderModel->getPortalProviderByUUID($request->portalProviderUUID);

        if ($providerData->count(DB::raw('1')) == 0) {
            return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
        } else {
            $portalProviderID = $providerData[0]->PID;

            $selectedColumn = [
                "stock.name",
                "stock.PID",
                "providerGameSetup.payoutType"
            ];

            $StockData = Stock::getStockBaseOnProvider($portalProviderID)->select($selectedColumn)->get();

            return Res::success($StockData);
        }
    }

    public function selectProviderGameSetup(Request $request)
    {
        $portalProviderModel = new PortalProvider();
        $gameSetupModel = new GameSetup();
        $txtStockID = $request->txtStockID;
        //validating Provider UUID

        if ($request->portalProviderUUID) {
            $providerData = $portalProviderModel->getPortalProviderByUUID($request->portalProviderUUID);
        } else {
            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');
            $providerData = $portalProviderModel->getPortalProviderByUUID($sessionData['portalProviderUUID']);
        }

        if ($providerData->count(DB::raw('1')) == 0) {
            return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
        } else {
            $portalProviderID = $providerData[0]->PID;

            if (!isEmpty($txtStockID)) {
                $stockID = $txtStockID;
            } else {
                $stockID = null;
            }

            $providerGameSetupSelectedColumn = [
                "providerGameSetup.stockID",
                "providerGameSetup.payoutType",
                "providerGameSetup.FD_BigSmallGameID",
                "providerGameSetup.FD_EvenOddGameID",
                "providerGameSetup.FD_LowMiddleHighGameID",
                "providerGameSetup.FD_NumberGameID",
                "providerGameSetup.LD_BigSmallGameID",
                "providerGameSetup.LD_EvenOddGameID",
                "providerGameSetup.LD_LowMiddleHighGameID",
                "providerGameSetup.LD_NumberGameID",
                "providerGameSetup.TD_BigSmallTieGameID",
                "providerGameSetup.TD_EvenOddGameID",
                "providerGameSetup.TD_LowMiddleHighGameID",
                "providerGameSetup.TD_NumberGameID",
                "providerGameSetup.BD_BigSmallTieGameID",
                "providerGameSetup.BD_EvenOddGameID",
                "providerGameSetup.BD_LowMiddleHighGameID",
                "providerGameSetup.BD_NumberGameID"
            ];

            $stockData = Stock::getStockProviderID($portalProviderID, $stockID)->select($providerGameSetupSelectedColumn)->get();

            foreach ($stockData as $key => $value) {
                $ruleData = [
                    $value->FD_BigSmallGameID,
                    $value->FD_EvenOddGameID,
                    $value->FD_LowMiddleHighGameID,
                    $value->FD_NumberGameID,
                    $value->LD_BigSmallGameID,
                    $value->LD_EvenOddGameID,
                    $value->LD_LowMiddleHighGameID,
                    $value->LD_NumberGameID,
                    $value->TD_BigSmallTieGameID,
                    $value->TD_EvenOddGameID,
                    $value->TD_LowMiddleHighGameID,
                    $value->TD_NumberGameID,
                    $value->BD_BigSmallTieGameID,
                    $value->BD_EvenOddGameID,
                    $value->BD_LowMiddleHighGameID,
                    $value->BD_NumberGameID
                ];
                $payout = $value->payoutType;
            }
            // GameSetup PID
            $gameSetupData = $gameSetupModel->getGameSetupByPID($ruleData)->get();
            $resultData['rule'] = $gameSetupData;
            $resultData['payout'] = $payout;
            return Res::success($resultData);
        }
    }
    public function updateProviderPayout(Request $request)
    {
        $portalProviderModel = new PortalProvider();
        $ProviderGameSetupModel = new ProviderGameSetup();
        $gamePayOut = new GamePayouts;
        $txtStockID = $request->txtStockID;
        $txtPayoutID = $request->txtPayoutID;
        //validating Provider UUID

        // check admin or user portalProvider
        if ($request->portalProviderUUID) {
            $providerData = $portalProviderModel->getPortalProviderByUUID($request->portalProviderUUID);
        } else {
            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');
            $providerData = $portalProviderModel->getPortalProviderByUUID($sessionData['portalProviderUUID']);
        }

        if ($providerData->count(DB::raw('1')) == 0) {
            return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
        } else {
            $portalProviderID = $providerData[0]->PID;

            if (!isEmpty($txtStockID)) {
                $stockID = $txtStockID;

                if (!isEmpty($txtPayoutID)) {
                    try {
                        $data = array();
                        $providerGameSetupData = Stock::getStockProviderID($portalProviderID, $stockID)->select('providerGameSetup.PID')->get();
                        $providerGameSetupID = $providerGameSetupData[0]->PID;
                        $data['payoutType'] = $txtPayoutID;
                        $response = $ProviderGameSetupModel->updateProviderPayout($providerGameSetupID, $data);

                        $response = $gamePayOut->updateInitiallyDynamicOdd([$providerGameSetupID]);
                        return Res::success($response);
                    } catch (Exception $e) {
                        if (IsAuthEnv()) {
                            return redirect()->back()->withErrors(config('constants.default_error_response'));
                        } else {
                            return redirect()->back()->withErrors($e->getMessage());
                        }
                    }
                }
            }
        }
    }

    public function getProviderRequestBalance(Request $request)
    {

        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            $authData = AdminProvider::getAuthData($request);
            // if ($authPolicy->access == 1 || $authData->agentType == 3) {
            $portalProviderModel = new PortalProvider();
            $rate = 0;
            $selectColumn = [
                "currency.rate as currencyRate",
                "portalProvider.rate",
                "portalProvider.name"
            ];
            $rateSetup = $portalProviderModel->getPortalproviderAndCurrency($authData->portalProviderID)->select($selectColumn)->get();
            if ($authData->portalProviderID == 1 && $authData->agentType == 1) {
                $rate = $rateSetup[0]->currencyRate;
            } else {
                if (isEmpty($rateSetup[0]->rate) || $rateSetup[0]->rate < 1) {
                    $rate = $rateSetup[0]->currencyRate;
                } else {
                    $rate = $rateSetup[0]->rate;
                }
            }

            $agentAllowToSelectPortalProvider = [1, 2];
            if ($authPolicy->access == 1 || in_array($authData->agentType, $agentAllowToSelectPortalProvider)) {
                $portalProviderModel = new PortalProvider();
                $portalProviderData = $portalProviderModel->getPortalProviders()->get();
            } else {
                $portalProviderData = null;
            }

            $enableToEditRate = false;
            if($authPolicy->access == 1 || ($authData->agentType == 1 && $authData->portalProviderID == 1)){
                $enableToEditRate = true;
            }

            $currencyModel = new Currency();
            $currencyData = $currencyModel->findByCurrencyAll()->get();
            return view('adminPanel/providerRequestBalance', compact('currencyData', 'portalProviderData', 'authData', 'authPolicy', 'rate','enableToEditRate'));
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function creditRequest(Request $request)
    {
        try {
            
            $doubleMaxLength = config('constants.double_max_length');

            $rules = array(
                'amount' => "required|integer|max:$doubleMaxLength",
                'rate' => "required|max:$doubleMaxLength",
                'creditRequestDescription' => 'required',
                'currencyID' => 'required',
                'creditRequestImage' => 'required|mimes:jpeg,jpg,png|max:' . config("app.valid_image_size_in_kilo_bytes") . '|file',
                'portalProviderUUID' => 'nullable'
            );

            $messages = array(
                'amount.required' => "amount is required",
                'amount.max' => "amount is limited $doubleMaxLength max.",
                'rate.max' => "rate is limited $doubleMaxLength max.",
                'amount.integer' => 'version is required for integer type.',
                'creditRequestDescription.required' => 'creditRequestDescription is required.',
                'currencyID.required' => 'currencyID is required.',
                'creditRequestImage.required' => 'creditRequestImage is required.',
                'creditRequestImage.file' => 'version is required for file type.',
                'creditRequestImage.mimes' => 'Only jpeg, jpg, png are allowed.',
                'creditRequestImage.max' => 'Image size should not be greater than ' . config('app.valid_image_size_in_kilo_bytes') . 'KB',
                'rate.required' => 'Rate is required.',
                'portalProviderUUID.required' => "portalProviderUUID is required",
                'portalProviderUUID.uuid' => "portalProviderUUID is not valid UUID",

            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {

                $authPolicy = AdminProvider::getAuthAdminPolicy($request);
                $authData = AdminProvider::getAuthData($request);
    
                $portalProviderID = $authData->portalProviderID;
    
                $allowToSelectPortalProvider = [1, 2]; // 1= agent type self ,2 = auto
                if ($authPolicy->access !== 1 || in_array($authData->agentType, $allowToSelectPortalProvider)) {
                    $portalProviderModel = new PortalProvider();
                    $providerData = $portalProviderModel->getPortalProviderByUUID($request->portalProviderUUID);
                    if ($providerData->count(DB::raw('1')) == 0) {
                        return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
                    }
                    $portalProviderID = $providerData[0]->PID;
                }

                if ((!isEmpty($request->amount))  && $request->amount < 0) {
                    throw new ValidationError('amount not be in negative');
                }

                if ((!isEmpty($request->chipValue))  && $request->chipValue < 0) {
                    throw new ValidationError('chipValue not be in negative');
                }

                if ((!isEmpty($request->rateValue))  && $request->rateValue < 0) {
                    throw new Exception('rate not be in negative');
                }

                $requestData['amount'] = $request->amount;
                $requestData['creditRequestDescription'] = $request->creditRequestDescription;
                $requestData['currencyID'] = $request->currencyID;
                $requestData['rate'] = isEmpty($request->rateValue) ? 0 : $request->rateValue;
                $imageFile = $request->file('creditRequestImage');

                $response = CreditRequestProvider::creditRequest($imageFile, $requestData, $portalProviderID, $authData,$authPolicy);

                if ($response['code'] !== 200) {
                    return redirect()->back()->withErrors($response['message']);
                }
                return redirect()->back()->with('message', 'Request Balance Successfully');
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function creditRequestManagementUpDate(Request $request)
    {
        try {
            $authData = AdminProvider::getAuthData($request);
            $adminPolicy = AdminProvider::getAuthAdminPolicy($request); // get auth admin policy data

            $rules = array(
                'creditRequestPID' => 'required',
                'action' => 'required'
            );

            $messages = array(
                'creditRequestPID.required' => 'creditRequestPID is required.',
                'action.required' => 'action is required.'
            );

            $allowToDoActionControl = [1, 2]; // 1 = portalProviderID 1 and AgentTypeSelf ,2  = agentTypeAuto
            if ($adminPolicy->access !== 1 && !in_array($authData->agentType, $allowToDoActionControl)) {
                return redirect()->back()->withErrors("Access denied");
            }

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {
                $authData = AdminProvider::getAuthData($request); // get auth data

                $creditManagementProcess = CreditRequestProvider::creditRequestManagement($authData->PID, $request->action, $request->creditRequestPID, $authData, $adminPolicy);
                if ($creditManagementProcess['code'] == 500) {
                    return redirect()->back()->withErrors(config('constants.default_error_response'));
                }

                if ($creditManagementProcess['code'] !== 200) {
                    return redirect()->back()->withErrors($creditManagementProcess['message']);
                }
                return redirect()->back()->with('message', $creditManagementProcess['message']);
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function getProviderBalance(Request $request)
    {
        try {
            $providerIDs = array();
            $authData = AdminProvider::getAuthData($request);
            $adminPolicy = AdminProvider::getAuthAdminPolicy($request);

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
            if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
            } else if ($sessionData['isAllowAll'] == 'false') {
                if (isEmpty($sessionData['portalProviderIDs'])) {
                    throw new ValidationError("You don't have access to any of Portal Provider!!");
                }
                $providerIDs = explode(',', $sessionData['portalProviderIDs']);
            }

            $policyCheckAccess = false;

            $allowToDoActionControl = [1, 2]; // 1 = AgentTypeSelf ,2  = agentTypeAuto
            if ($adminPolicy->access == 1 || in_array($authData->agentType, $allowToDoActionControl)) {
                $policyCheckAccess = true;
            }

            $creditRequestModelRef = new CreditRequest();

            $creditRequestData = $creditRequestModelRef->getAllCreditRequestByPortalProvider($providerIDs)->select([
                'creditRequest.PID as creditRequestPID',
                'portalProvider.UUID as portalProviderUUID',
                'portalProvider.name as portalProviderName',
                'creditRequest.creditRequestImage',
                'creditRequest.amount',
                'creditRequest.creditRequestDescription',
                'creditRequest.rate',
                'creditRequest.chipValue',
                'creditRequest.createdAt',
                'creditRequest.requestStatus',
                'currency.name as currencyName'
            ])->get();

            return view('adminPanel/providerBalance', compact('creditRequestData', 'policyCheckAccess'));
        } catch (ValidationError $e) {
            return redirect()->back()->withErrors($e->getMessage());
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function updateProviderInfo(Request $request)
    {
        try {
            $authData = AdminProvider::getAuthData($request);
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);

            $stringMaxLength = config('constants.string_max_length');

            $rules = [
                'serverName' => "required|max:$stringMaxLength",
                'ipList' => "required|max:$stringMaxLength",
                'APIKey' => "required|max:$stringMaxLength"
            ];

            $messages = array(
                'serverName.required' => 'serverName is required.',
                'ipList.required' => 'ipList is required.',
                'APIKey.required' => 'API Key is required.',
                'serverName.max' => "serverName is limited by $stringMaxLength characters.",
                'ipList.max' => "ipList is limited by $stringMaxLength characters.",
                'APIKey.max' => "API Key is limited by $stringMaxLength characters."
            );

            if ($authPolicy->access == 1) {
                $rules['portalProviderPID'] = 'required';
                $messages['portalProviderPID.required'] = 'portalProviderPID is required';
            }

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            }

            if ($authPolicy->access == 1) {
                $updatePpdInfo = AppPortalProvider::updateProviderInfo($request->portalProviderPID, $request->serverName, $request->ipList, $request->APIKey);
            } else {
                $updatePpdInfo = AppPortalProvider::updateProviderInfo($authData->portalProviderID, $request->serverName, $request->ipList, $request->APIKey);
            }

            if ($updatePpdInfo['code'] != 200) {
                return redirect()->back()->withErrors($updatePpdInfo['message']);
            }
            return redirect()->back()->with('message', $updatePpdInfo['message']);
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function providerInfo(Request $request)
    {
        try {

            $authData = AdminProvider::getAuthData($request);

            $adminModel = new Admin();
            $adminData = $adminModel->fetchByUsername($authData->username);
            $serverName = $adminData[0]->server;
            $ipList = $adminData[0]->ipList;
            $APIKey = $adminData[0]->APIKey;
            $currency = $adminData[0]->abbreviation;

            $portalProviderModel = new PortalProvider();
            $portalProviderData = $portalProviderModel->getPortalProviders()->get();

            return view('adminPanel.providerInfo', compact('serverName', 'ipList', 'currency', 'APIKey', 'portalProviderData'));
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function getProviderList(Request $request)
    {
        try {

            $providerIDs = array();

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
            if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
            } else if ($sessionData['isAllowAll'] == 'false') {
                if (isEmpty($sessionData['portalProviderIDs'])) {
                    throw new ValidationError("You don't have access to any of Portal Provider!!");
                }
                $providerIDs = explode(',', $sessionData['portalProviderIDs']);
            }

            $allowAll = $sessionData['isAllowAll'];

            $portalProviderModel = new PortalProvider();
            $adminModel = new Admin();

            //To get the accessibility of the admin policy tab based on the admin id
            $adminInfo = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessProviderList', 'isAllowAll')->get();
            $accessibility = $adminInfo[0]->accessProviderList;
            $isAllowAll = $adminInfo[0]->isAllowAll;

            $providerData = $portalProviderModel->getAllPortalProvidersAndCurrencyById($providerIDs)->select(
                'portalProvider.PID',
                'portalProvider.UUID',
                'portalProvider.name',
                'portalProvider.creditBalance',
                'portalProvider.mainBalance',
                'portalProvider.server',
                'portalProvider.ipList',
                'portalProvider.isActive',
                'portalProvider.createdAt',
                'portalProvider.currencyID',
                'currency.name as currencyName',
                'currency.rate as currencyRate'
            )->get();

            $portalProviderData = array();

            foreach ($providerData as $eachProvider) {

                $totalBetsOfProvider = $portalProviderModel->getBetsByProviderID($eachProvider->PID);

                $betCount = $totalBetsOfProvider->count(DB::raw('1'));

                $userOfProvider = $portalProviderModel->getUsersInBetsByProviderID($eachProvider->PID);

                $portalProviderData[] = [
                    'portalProviderID' => $eachProvider->PID,
                    'portalProviderUUID' => $eachProvider->UUID,
                    'portalProviderName' => $eachProvider->name,
                    'creditBalance' => $eachProvider->creditBalance,
                    'mainBalance' => $eachProvider->mainBalance,
                    'server' => $eachProvider->server,
                    'ipList' => $eachProvider->ipList,
                    'isActive' => $eachProvider->isActive,
                    'createdAt' => $eachProvider->createdAt,
                    'currencyID' => $eachProvider->currencyID,
                    'currencyName' => $eachProvider->currencyName,
                    'rate' => $eachProvider->currencyRate,
                    'totalBets' => $betCount == 0 ? 0 : $totalBetsOfProvider[0]->totalBets,
                    'totalUsers' => $betCount == 0 ? 0 : $totalBetsOfProvider[0]->totalUsers,
                    'totalBetAmount' => $betCount == 0 ? 0 : $totalBetsOfProvider[0]->totalBetAmount,
                    'totalRollingAmount' => $betCount == 0 ? 0 : $totalBetsOfProvider[0]->totalRollingAmount,
                    'userDetails' => $userOfProvider
                ];
            }

            $currencyModel = new Currency();
            $currencyData = $currencyModel->findByCurrencyAll()->get();

            return view('adminPanel.providerList', compact('portalProviderData', 'currencyData', 'allowAll', 'accessibility', 'isAllowAll'));
        } catch (ValidationError $e) {
            return redirect()->back()->withErrors($e->getMessage());
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function addProviderList(Request $request)
    {
        try {
            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
            if ($sessionData['isAllowAll'] == 'false') {
                throw new ValidationError("You don't have access to any of Portal Provider!!");
            }

            $rules = [
                'name' => 'required|max:'.config('constants.string_max_length'),
                'currencyID' => 'required',
                'creditBalance' => 'required|max:'.config('constants.double_max_length'),
                'mainBalance' => 'required|max:'.config('constants.double_max_length'),
                'isActive' => 'required'
            ];

            $messages = array(
                'name.required' => 'name is required.',
                'name.max' => 'name is limited with.'.config('constants.string_max_length').' characters.',
                'currencyID.required' => 'Currency Name is required.',
                'creditBalance.required' => 'Credit Balance is required.',
                'mainBalance.required' => 'Main Balance is required.',
                'creditBalance.max' => "Credit Balance shouldn't greater than ".config('constants.double_max_length').' max',
                'mainBalance.max' => "Main Balance shouldn't greater than ".config('constants.double_max_length').' max',
                'isActive' => 'Status Active is required.'
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            }

            if ((!isEmpty($request->creditBalance)) && $request->creditBalance < 0) {
                throw new ValidationError('Credit Balance not be in negative');
            }

            if ((!isEmpty($request->mainBalance))  && $request->mainBalance < 0) {
                throw new ValidationError('Main Balance not be in negative');
            }

            $currencyData = Currency::findByCurrencyId($request->currencyID)->select('rate')->get();
            if ($currencyData->count(DB::raw('1')) == 0) {
                throw new ValidationError('currency Name does not exist.');
            }
            if (!isEmpty($request->serverName)) {
                $data['server'] = $request->serverName;
            }
            if (!isEmpty($request->ipList)) {
                $data['ipList'] = $request->ipList;
            }
            if (!isEmpty($request->APIKey)) {
                $data['APIKey'] = $request->APIKey;
            }
            if (!isEmpty($currencyData[0]->rate)) {
                $data['rate'] = $currencyData[0]->rate;
            }

            $data['name'] = $request->name;
            $data['currencyID'] = $request->currencyID;
            $data['creditBalance'] = $request->creditBalance;
            $data['mainBalance'] = $request->mainBalance;
            $data['UUID'] = Uuid::uuid4();
            $data['isActive'] = $request->isActive;
            $portalProviderModel = new PortalProvider();
            if (!isEmpty($data)) {
                $portalProviderModel->create($data);
                return redirect()->back()->with('message', 'Add New Portal Provider Successfully');
            } else {
                return redirect()->back()->withErrors('No input found..!!');
            }
        } catch (ValidationError $e) {
            return redirect()->back()->withErrors($e->getMessage());
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function updateProviderList(Request $request)
    {
        try {

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
            if ($sessionData['isAllowAll'] == 'false') {
                throw new ValidationError("You don't have access to any of Portal Provider!!");
            }


            $portalProviderModel = new PortalProvider();


            $data['name'] = $request->name;
            $data['currencyID'] = $request->currencyID;
            $data['isActive'] = $request->isActive;

            if (!isEmpty($data)) {
                $portalProviderModel->updatePortalProvider($request->providerListID, $data);
                return redirect()->back()->with('message', 'Update Portal Provider Successfully');
            } else {
                return redirect()->back()->withErrors('No input found..!!');
            }
        } catch (ValidationError $e) {
            return redirect()->back()->withErrors($e->getMessage());
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function providerLogoutAllUser(Request $request)
    {
        try {
            $userProvider = new UserProvider(null);

            $providerLogout = $userProvider->logoutAllUser($request->portalProviderPID);

            if ($providerLogout['status']) {
                return redirect()->back()->with('message', implode(" | ", $providerLogout['message']));
            } else {
                return redirect()->back()->withErrors(implode(" | ", $providerLogout['message']));
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function getProviderSelect(Request $request)
    {
        try {
            $authData = AdminProvider::getAuthData($request);
            $authPolicyData = AdminProvider::getAuthAdminPolicy($request);
            $isAjax = $request->input('isSideBarAjax');

            $totalProviders = explode(',', $authData->providerAccess);

            if ($authPolicyData->count(DB::raw('1')) != 0) {
                $portalProviderModel = new PortalProvider();
                if ($isAjax) {
                    if ($authPolicyData->access == 1) {
                        $providerList = $portalProviderModel->getPortalProviders()->get();
                    } else {
                        $providerList = $portalProviderModel->getPortalProviders()->where('PID', $authData->portalProviderID)->get();
                    }
                    return ['Provider' => $providerList, 'Access' => $totalProviders];
                }
            } else {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function getProviderConfig(Request $request)
    {
        try {
            $providerIDs = array();

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
            if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
            } else if ($sessionData['isAllowAll'] == 'false') {
                if (isEmpty($sessionData['portalProviderIDs'])) {
                    throw new ValidationError("You don't have access to any of Portal Provider!!");
                }
                $providerIDs = explode(',', $sessionData['portalProviderIDs']);
            }

            $providerConfigModel = new ProviderConfig();
            $adminModel = new Admin();

            $selectColumn = [
                'providerConfig.PID as providerConfigID',
                'portalProviderID',
                'portalProvider.name as portalProviderName',
                'providerConfig.followBetSetupID',
                'providerConfig.logoutAPICall',
                'providerConfig.createdAt',
                'providerConfig.updatedAt',
                'providerConfig.deletedAt',
                'providerConfig.invitationSetupID',
                'invitationSetup.name as invitationSetupName'
            ];

            if ($request->cookie('includeDeleted')) {
                $providerConfigData = $providerConfigModel->getAllProviderConfigByPortalProviderWithTrashed($providerIDs)->select($selectColumn)->get();
            } else {
                $providerConfigData = $providerConfigModel->getAllProviderConfigByPortalProvider($providerIDs)->select($selectColumn)->get();
            }
            //To get the accessibility of the admin policy tab based on the admin id
            $adminInfo = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessProviderConfig', 'isAllowAll')->get();
            $accessibility = $adminInfo[0]->accessProviderConfig;
            $isAllowAll = $adminInfo[0]->isAllowAll;

            $portalProviderModel = new PortalProvider();
            $portalProviderData = $portalProviderModel->getPortalProviders()->get();
            $followBetSetupData = FollowBetSetup::get();
            $invitationSetupData = InvitationSetup::get();

            return view('adminPanel/providerConfig', compact('providerConfigData', 'portalProviderData', 'followBetSetupData', 'accessibility', 'invitationSetupData', 'isAllowAll'));
        } catch (ValidationError $e) {
            return redirect()->back()->withErrors($e->getMessage());
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function addProviderConfig(Request $request)
    {
        try {
            $providerConfigModel = new ProviderConfig();
            $portalProviderModel = new PortalProvider();

            $rules = array(
                'portalProviderID' => 'required',
                'logoutAPICall' => 'nullable|max:4'
            );

            $messages = array(
                'portalProviderID.required' => 'Portal Provider is required.',
                'logoutAPICall.max'=>'logoutAPICall is limited by 4 max'
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {

                $providerData = $portalProviderModel->findByPortalProviderID($request->portalProviderID);
                if ($providerData->count(DB::raw('1')) == 0) {
                    return redirect()->back()->withErrors([], 'Provider ID does not exist.');
                }

                $data['portalProviderID'] = $request->portalProviderID;
                if (!isEmpty($request->followBetSetupID)) {
                    $data['followBetSetupID'] = $request->followBetSetupID;
                }

                if (!isEmpty($request->logoutAPICall)) {
                    $data['logoutAPICall'] = $request->logoutAPICall;
                }

                if (!isEmpty($request->invitationSetupID)) {
                    $data['invitationSetupID'] = $request->invitationSetupID;
                }

                if (!isEmpty($data)) {
                    $providerConfigModel->create($data);
                    return redirect()->back()->with('message', 'Create Provider Config Successfully');
                } else {
                    return redirect()->back()->withErrors('No input found..!!');
                }
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function updateProviderConfig(Request $request)
    {
        try {
            $providerConfigModel = new ProviderConfig();
            $portalProviderModel = new PortalProvider();

            $rules = array(
                'portalProviderID' => 'required'
            );

            $messages = array(
                'portalProviderID.required' => 'portal Provider is required.'
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {

                $providerData = $portalProviderModel->findByPortalProviderID($request->portalProviderID);
                if ($providerData->count(DB::raw('1')) == 0) {
                    return redirect()->back()->withErrors([], 'Provider ID does not exist.');
                }
                $data['portalProviderID'] = $request->portalProviderID;
                if (!isEmpty($request->followBetSetupID)) {
                    $data['followBetSetupID'] = $request->followBetSetupID;
                }

                if (!isEmpty($request->logoutAPICall)) {
                    $data['logoutAPICall'] = $request->logoutAPICall;
                }

                if (!isEmpty($request->invitationSetupID)) {
                    $data['invitationSetupID'] = $request->invitationSetupID;
                }

                if (!isEmpty($data)) {
                    $providerConfigModel->updateProviderConfig($request->providerConfigID, $data);
                    return redirect()->back()->with('message', 'Update Provider Config Successfully');
                } else {
                    return redirect()->back()->withErrors('No input found..!!');
                }
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function deleteProviderConfig(Request $request)
    {
        try {
            $providerConfig = ProviderConfig::where('PID', $request->providerConfigID)->delete();
            if ($providerConfig) {
                return redirect()->back()->with('message', 'Delete Provider Config Successfully');
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function restoreProviderConfig(Request $request)
    {
        try {
            if ($request->providerConfigID != '') {
                $providerConfigData = ProviderConfig::withTrashed()->find($request->providerConfigID)->restore();
                if ($providerConfigData) {
                    return redirect()->back()->with('message', 'Provider Config Restored Successfully');
                }
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }
}
