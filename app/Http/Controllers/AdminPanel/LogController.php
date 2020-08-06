<?php

namespace App\Http\Controllers\AdminPanel;

use App\Exceptions\ValidationError;
use App\Http\Controllers\Controller;
use App\Models\ApiActivityLog;
use App\Models\PoolLog;
use App\Models\Admin;
use App\Models\PortalProvider;
use App\Providers\Admin\AdminProvider;
use DB;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LogController extends Controller
{
    public function getPoolLog(Request $request)
    {
        try {

            $poolLogModel = new PoolLog();
            $adminModel = new Admin();
            $portalProviderModel = new PortalProvider();
            $providerIDs = array();

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');

            if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
            } else if ($sessionData['isAllowAll'] == 'false') {
                if (isEmpty($sessionData['portalProviderIDs'])) {
                    throw new ValidationError("You don't have access to any of Portal Provider!!");
                }
                $providerIDs = explode(',', $sessionData['portalProviderIDs']);
            }

            //To get the accessibility of the admin policy tab based on the admin id	
            $adminInfo = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessMonetaryLog', 'isAllowAll')->get();
            $accessibility = $adminInfo[0]->accessMonetaryLog;
            $isAllowAll = $adminInfo[0]->isAllowAll;

            $providerData = $portalProviderModel->getPortalProviderByUUID($sessionData['portalProviderUUID']);

            if ($providerData->count(DB::raw('1')) == 0) {
                return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
            } else {
                if ($request->ajax()) {
                    $poolLogSelect = [
                        'portalProvider.UUID as portalProviderID',
                        'portalProvider.name as portalProviderName',
                        'user.UUID as userID',
                        DB::raw("(CASE WHEN poolLog.adminID = 0 THEN 'SYSTEM' ELSE admin.username END) as username"),
                        'poolLog.previousBalance',
                        'poolLog.newBalance',
                        'poolLog.amount',
                        'poolLog.balanceType',
                        DB::raw("(CASE WHEN poolLog.operation = 0 THEN 'Credit' ELSE CASE WHEN poolLog.operation = 1 THEN 'Debit' ELSE CASE WHEN poolLog.operation = 2 THEN 'recharge' ELSE 'Error' END END END ) as operation"),
                        'poolLog.UUID',
                        'poolLog.serviceName',
                        'poolLog.createdAt'
                    ];

                    $resultPoolLogData = $poolLogModel->getPoolLogDetails($providerIDs)->select($poolLogSelect);
                    return DataTables::of($resultPoolLogData)->make(true);
                }
            }


            return view('adminPanel.monetaryLog');
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function getActivityLog(Request $request)
    {
        try {


            $portalProviderModel = new PortalProvider();
            $providerIDs = array();
            $adminModel = new Admin();

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');

            if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
            } else if ($sessionData['isAllowAll'] == 'false') {
                if (isEmpty($sessionData['portalProviderIDs'])) {
                    throw new ValidationError("You don't have access to any of Portal Provider!!");
                }
                $providerIDs = explode(',', $sessionData['portalProviderIDs']);
            }

            //To get the accessibility of the admin policy tab based on the admin id	
            $adminInfo = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessActivityLog', 'isAllowAll')->get();
            $accessibility = $adminInfo[0]->accessActivityLog;
            $isAllowAll = $adminInfo[0]->isAllowAll;

            $providerData = $portalProviderModel->getPortalProviderByUUID($sessionData['portalProviderUUID']);

            if ($providerData->count(DB::raw('1')) == 0) {
                return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
            } else {
                if ($request->ajax()) {
                    $selectedColumn = [
                        'portalProvider.UUID as portalProviderID',
                        'portalProvider.name as portalProviderName',
                        DB::raw("(CASE WHEN apiActivityLog.adminID = 0 THEN 'SYSTEM' ELSE admin.username END) as username"),
                        'apiActivityLog.service',
                        'apiActivityLog.method',
                        'apiActivityLog.responseCode',
                        'apiActivityLog.responseMessage',
                        'apiActivityLog.errorFound',
                        'apiActivityLog.requestTime',
                        'apiActivityLog.ipAddress',
                        'user.UUID as userID'
                    ];
                    $apiActivityLog = ApiActivityLog::getAllApiActivityLog($providerIDs)->select($selectedColumn);
                    return DataTables::of($apiActivityLog)->make(true);
                }
            }
            return view('adminPanel.activityLog');
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }
}
