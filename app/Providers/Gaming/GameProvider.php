<?php

namespace App\Providers\Gaming;

use Exception;
use App\Models\Game;
use App\Models\PortalProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\ResponseController as Res;
use App\Models\Stock;

class GameProvider extends ServiceProvider
{

    public function getGames($portalProviderUUID, $gameStatus, $limit, $offset, $stockUUID = null)
    {
        $response['portalProviderID'] = null;
        try {
            $gameModel = new Game();
            $portalProviderModel = new portalProvider();
            $isErrorFound = false;

            //validating Provider UUID
            $providerData = $portalProviderModel->getPortalProviderByUUID($portalProviderUUID);

            if ($providerData->count(DB::raw('1')) == 0) {
                $isErrorFound = true;
                $response['res'] = Res::notFound([], 'Provider UUID does not exist.');
            }

            if (!$isErrorFound) {
                $response['portalProviderID'] = $providerData[0]->PID;


                // if TNKMaster is logged in, he should see all the game details irrespective of the portal provider
                if ($providerData[0]['PID'] == 1 && $providerData[0]['name'] == 'TNKMaster') {
                    $gameData = $gameModel->getAllGames($gameStatus, $limit, $offset);
                } else {
                    //fetching Games
                    $gameData = $gameModel->getAllGameByProviderStockID($providerData[0]->PID, $gameStatus, $limit, $offset, $stockUUID)->select('game.UUID as gameUUID', 'stock.name as stockName', 'game.startDate as gameStartDate', 'game.startTime as gameStartTime', 'game.endDate as gameEndDate', 'game.endTime as gameEndTime', 'game.endStockValue', DB::raw('(CASE WHEN game.gameStatus = 0 THEN "Pending" WHEN game.gameStatus = 1 THEN "Open" WHEN game.gameStatus = 2 THEN "Close" WHEN game.gameStatus = 3 THEN "Complete" WHEN game.gameStatus = 4 THEN "Error" ELSE "Deleted" END) as gameStatus'))->get();
                }
                if ($gameData->count(DB::raw('1')) == 0) {
                    $isErrorFound = true;
                    $response['res'] = Res::success([], 'No Games found.');
                }

                if (!$isErrorFound) {
                    $response['res'] = Res::success($gameData);
                }
            }
            return $response;
        } catch (Exception $e) {
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
            return $response;
        }
    }

    /**
     * Get live bet Count
     * @return QueryBuilder
     */
    public function betCount($gameUUID = null, $portalProviderUUID, $stockUUID = null, $loop = null, array $status = null)

    {
        $gameModel = new Game();
        $providerModel = new PortalProvider();

        try {

            $response = array();
            $data = array();
            $response['gameLoop'] = null;
            $response['stockUUID'] = null;
            $response['portalProviderUUID'] = $portalProviderUUID;

            $response['userID'] = null;
            $response['portalProviderID'] = null;
            $stockID = null;

            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $response['portalProviderID'] = $providerData[0]->PID;

            if (!isEmpty($stockUUID)) {
                $stockData = Stock::getStockBaseOnProvider($response['portalProviderID'], $stockUUID)->select('stock.PID')->get();
                if ($stockData->count(DB::raw('1')) == 0) {
                    $response['res'] =  Res::notFound([], 'stockUUID does not exist.');
                    return $response;
                }
                $stockID = $stockData[0]->PID;
            }

            $gameData = $gameModel->getGameAndBetByUUID([1, 2], $stockID, $loop, $gameUUID)->select('game.PID')->get();
            if ($gameData->count(DB::raw('1')) == 0) {
                $displayMsg = isEmpty($stockUUID) ? "gameUUID" : "stockUUID";
                $response['res'] =  Res::notFound([], "$displayMsg does not exist please check your request input.");

                return $response;
            }

            $betCounts = $gameModel->getBetCounts($gameData[0]->PID);

            $providerStockLoopData = $gameModel->getStockLoop($gameData[0]->PID)->select(
                'stock.UUID as stockUUID',
                'stock.stockLoop as gameLoop'
            )->get();

            if ($providerStockLoopData->count(DB::raw('1')) != 0) {
                $response['gameLoop'] = $providerStockLoopData[0]->gameLoop;
                $response['stockUUID'] = $providerStockLoopData[0]->stockUUID;
            } else {
                $response['res'] =  Res::notFound([], 'gameID does not exist.');
                return $response;
            }

            $groups = array("BIG", "SMALL", "ODD", "EVEN", "HIGH", "MID", "LOW", "NUMBER", "TIE");

            foreach ($groups as $name) {
                $betAmountsArray = array(0, 0, 0, 0);
                $betCountsArray = array(0, 0, 0, 0);
                foreach ($betCounts as $currentValue) {
                    if (strstr($currentValue->name, $name)) {
                        if (strstr($currentValue->name, 'FD')) {
                            $betAmountsArray[0] = (int) $currentValue->betAmounts;
                            $betCountsArray[0] = (int) $currentValue->betCounts;
                        }
                        if (strstr($currentValue->name, 'LD')) {
                            $betAmountsArray[1] = (int) $currentValue->betAmounts;
                            $betCountsArray[1] = (int) $currentValue->betCounts;
                        }
                        if (strstr($currentValue->name, 'BD')) {
                            $betAmountsArray[2] = (int) $currentValue->betAmounts;
                            $betCountsArray[2] = (int) $currentValue->betCounts;
                        }
                        if (strstr($currentValue->name, 'TD')) {
                            $betAmountsArray[3] = (int) $currentValue->betAmounts;
                            $betCountsArray[3] = (int) $currentValue->betCounts;
                        }
                    }
                }
                array_push($data, array('name' => $name, 'data' => $betAmountsArray, 'betCounts' => $betCountsArray));
            }


            $response['res'] = Res::success($data);
        } catch (Exception $e) {

            DB::rollback();
            $response['res'] = Res::errorException($e->getMessage());
            $response['exceptionMsg'] = $e->getMessage();
        }
        return $response;
    }


    /**
     * Get live bet Count Date
     * @return QueryBuilder
     */
    public function liveCountBetData($gameUUID = null, $portalProviderUUID, $stockUUID = null, $loop = null)
    {
        $gameModel = new Game();
        $providerModel = new PortalProvider();

        try {
            $response = array();
            $response['userID'] = null;
            $response['portalProviderID'] = null;
            $stockID = null;

            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] =  Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }

            $response['portalProviderID'] = $providerData[0]->PID;

            if (!isEmpty($stockUUID)) {
                $stockData = Stock::getStockBaseOnProvider($response['portalProviderID'], $stockUUID)->select('stock.PID')->get();
                if ($stockData->count(DB::raw('1')) == 0) {
                    $response['res'] =  Res::notFound([], 'stockUUID does not exist.');
                    return $response;
                }
                $stockID = $stockData[0]->PID;
            }

            if (!isEmpty($gameUUID)) {
                $gameCheckModel = $gameModel->getGameByUUIDAndPortalProviderID($gameUUID, $providerData[0]->PID);

                if ($gameCheckModel->count(DB::raw('1')) == 0) {
                    $response['res'] = Res::notFound([], 'gameUUID does not exist.');
                    return $response;
                }
            }

            $gameData = $gameModel->getGameAndBetByUUID([1, 2], $stockID, $loop, $gameUUID)->select(
                'game.PID',
                DB::raw('COUNT(DISTINCT(betting.userID)) totalUsers'),
                DB::raw('COUNT(betting.PID) totalBetCount'),
                DB::raw('IFNULL(SUM(betting.betAmount),0) totalAmountPlaced')
            )->get();


            $data = array();
            $data['totalUsers'] = $gameData[0]->totalUsers;
            $data['totalBetCount'] = $gameData[0]->totalBetCount;
            $data['totalAmountPlaced'] = $gameData[0]->totalAmountPlaced;

            $response['res'] = Res::success($data);
            return $response;
        } catch (Exception $e) {


            DB::rollback();
            $response['res'] = Res::errorException($e->getMessage());
            $response['exceptionMsg'] = $e->getMessage();
        }
        return $response;
    }


    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
