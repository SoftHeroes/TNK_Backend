<?php
namespace App\Http\Controllers\AdminPanel;

use App\Exceptions\ValidationError;
use App\Http\Controllers\Controller;
use App\Models\Betting;
use DB;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BetHistoryController extends Controller
{
    public function betHistory(Request $request)
    {
        try {
            if ($request->ajax()) {
                $bettingModel = new Betting();
                $providerIDs = array();
                $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
                if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                    $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
                } elseif ($sessionData['isAllowAll'] == 'false') {
                    if (isEmpty($sessionData['portalProviderIDs'])) {
                        throw new ValidationError("You don't have access to any of Portal Provider!!");
                    }
                    $providerIDs = explode(',', $sessionData['portalProviderIDs']);
                }
                $betData = $bettingModel->getAllBetsByPortalProvider($providerIDs)->select([
                    'user.UUID as userUUID',
                    'betting.UUID as betUUID',
                    'portalProvider.UUID as portalProviderUUID',
                    'portalProvider.name as portalProviderName',
                    'rule.name as ruleName',
                    'betting.betAmount as betAmount',
                    'betting.rollingAmount as rollingAmount',
                    'betting.payout as payout',
                    DB::raw('(CASE WHEN betting.betResult = -1 THEN "pending" WHEN betting.betResult = 0 THEN "lose" WHEN betting.betResult = 1 THEN "win" ELSE "fail" END) as betResult'),
                    DB::raw("CAST(CONCAT(betting.createdDate, ' ',betting.createdTime) AS DATETIME) as betTimeStamp"),
                    'game.UUID as gameUUID'
                ]);

                return DataTables::of($betData)->make(true);
            }

            return view('adminPanel/betHistory');
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

    public function getSingleGameData($gameUUID)
    {
        $betting = new Betting();

        return $betting->getTotalBetsByGameID($gameUUID)->first();
    }
}
