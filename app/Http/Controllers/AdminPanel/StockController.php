<?php
namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Betting;
use App\Models\Game;
use App\Models\PortalProvider;
use App\Models\Stock;
use DB;
use Exception;
use Illuminate\Http\Request;

require_once app_path() . '/Helpers/CommonUtility.php';

class StockController extends Controller
{
    public function index(Request $request)
    {
        try {
            $portalProviderModel = new PortalProvider();
            $stockModel = new Stock();
            $gameModel = new Game();
            $bettingModel = new Betting();
            $value = session(str_replace('.', '_', $request->ip()) . 'ECGames');
            $providerIDs = array();
            $gameStatus = [1, 2, 3, 4];

            if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
            } elseif ($value['isAllowAll'] == 'false') {
                if (isEmpty($value['portalProviderIDs'])) {
                    throw new ValidationError("You don't have access to any of Portal Provider!!");
                }
                $providerIDs = explode(',', $value['portalProviderIDs']);
            }

            $providerData = $portalProviderModel->getPortalProviderByUUID($value['portalProviderUUID']);

            $selectColumns = [
                'portalProvider.UUID as portalProviderUUID',
                'portalProvider.PID as portalProviderID',
                'portalProvider.name as portalProviderName',
                'stock.UUID as stockID',
                'stock.PID as stockPID',
                'stock.name as stockName',
                'stock.ReferenceURL as referenceURL',
                'stock.closeDays as closeDays',
                'stock.openTimeRange as openTimeRange',
                'stock.category as category',
                'stock.isActive as isActive',
                'stock.createdAt as createdAt'
            ];

            if ($providerData->count(DB::raw('1')) == 0) {
                return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
            } else {
                $allStockData = $stockModel->getAllStocks($providerIDs)->select($selectColumns)->get();

                $stockData = array();

                foreach ($allStockData as $eachStock) {
                    //To get the total bets, total bet amount, total rolling amount of each stock
                    $betValueBasedOnStock = $gameModel->getTotalBetValues($eachStock->portalProviderID, $eachStock->stockPID, $gameStatus);

                    //To get the total bet number of games in each stock
                    $gameCountBasedOnStock = $gameModel->getTotalGameCount($eachStock->portalProviderID, $eachStock->stockPID, $gameStatus);

                    //To get the game uuid
                    // $allGamesByStock = $gameModel->getAllGamesByStock($eachStock->portalProviderID, $eachStock->stockPID, $gameStatus);
                    $betValues = $betValueBasedOnStock->count(DB::raw('1'));
                    $gameCount = $gameCountBasedOnStock->count(DB::raw('1'));

                    $stockData[] = [
                        'stockID' => $eachStock->stockID,
                        'portalProviderUUID' => $eachStock->portalProviderUUID,
                        'portalProviderName' => $eachStock->portalProviderName,
                        'stockName' => $eachStock->stockName,
                        'referenceURL' => $eachStock->referenceURL,
                        'closeDays' => $eachStock->closeDays,
                        'openTimeRange' => $eachStock->openTimeRange,
                        'category' => $eachStock->category,
                        'isActive' => $eachStock->isActive,
                        'createdAt' => $eachStock->createdAt,
                        'totalGames' => $gameCount == 0 ? 0 : $gameCountBasedOnStock[0]->totalGames,
                        'totalBets' => $betValues == 0 ? 0 : $betValueBasedOnStock[0]->totalBets,
                        'totalBetAmount' => $betValues == 0 ? 0 : $betValueBasedOnStock[0]->totalBetAmount,
                        'totalRollingAmount' => $betValues == 0 ? 0 : $betValueBasedOnStock[0]->totalRollingAmount
                    ];
                }

                return view('adminPanel.stock', compact('stockData'));
            }
        } catch (Exception $exception) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($exception->getMessage());
            }
        }
    }
}
