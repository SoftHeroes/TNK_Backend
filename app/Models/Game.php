<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Game extends Model
{
    const UPDATED_AT = 'updatedAt';

    protected $table = 'game';
    protected $primaryKey = 'PID';

    public function getActiveGameDetails($gameUUID) //add more fields in select() when needed.
    {
        return $this->select('game.stockID', 'game.PID', 'providerGameSetup.portalProviderID', 'stock.UUID as stockUUID', 'stock.stockLoop')
            ->join('providerGameSetup', 'game.providerGameSetupID', '=', 'providerGameSetup.PID')
            ->join('stock', 'stock.PID', '=', 'game.stockID')
            ->where('game.UUID', $gameUUID)
            ->where('game.gameStatus', 1)
            ->where('providerGameSetup.isActive', 'active')->whereNull('providerGameSetup.deletedAt')
            ->get();
    }

    public function getAllGameByProviderStockID($portalProviderID, $status, $limit = 100, $offset = 0, $stockUUID)
    {
        return $this->join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('providerGameSetup', 'game.providerGameSetupID', 'providerGameSetup.PID')
            ->join('portalProvider', 'providerGameSetup.portalProviderID', 'portalProvider.PID')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->where('providerGameSetup.portalProviderID', $portalProviderID)
            ->when(
                !isEmpty($stockUUID),
                function ($query) use ($stockUUID) {
                    return $query->where('stock.UUID', $stockUUID);
                }
            )
            ->whereIn('game.gameStatus', $status)
            ->groupBy('stock.name', 'stock.stockLoop', 'game.gameStatus')
            ->limit($limit)
            ->offset($offset);
    }

    public function getTotalBetValues($providerID, $stockID, $status)
    {
        return $this->select(DB::raw('count(betting.PID) as totalBets, count(distinct gameID) as totalGames, SUM(betting.betAmount) as totalBetAmount, SUM(betting.rollingAmount) as totalRollingAmount'), 'stock.PID', 'stock.UUID', 'game.PID as gamePID')
            ->join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('portalProvider', 'game.portalProviderID', 'portalProvider.PID')
            ->join('betting', 'betting.gameID', 'game.PID')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->where('game.portalProviderID', $providerID)
            ->where('game.stockID', $stockID)
            ->whereIn('game.gameStatus', $status)
            ->groupBy('stock.PID')
            ->get();
    }

    public function getTotalGameCount($providerID, $stockID, $status)
    {
        return $this->select(DB::raw('count(game.PID) as totalGames'))
            ->join('stock', 'game.stockID', 'stock.PID')
            ->where('game.portalProviderID', $providerID)
            ->where('game.stockID', $stockID)
            ->whereIn('game.gameStatus', $status)
            ->groupBy('stock.PID')
            ->get();
    }

    public function getAllGamesByStock($providerID, $stockID, $status)
    {
        return $this->select('game.UUID')
            ->join('stock', 'game.stockID', 'stock.PID')
            ->join('portalProvider', 'game.portalProviderID', 'portalProvider.PID')
            // ->join('betting', 'betting.gameID', 'game.PID')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->where('game.portalProviderID', $providerID)
            ->where('game.stockID', $stockID)
            ->whereIn('game.gameStatus', $status)
            // ->groupBy('stock.PID')
            ->get();
    }

    public function getAllGames($status, $limit = 100, $offset = 0)
    {
        return $this->select('game.UUID as gameUUID', 'portalProvider.UUID as portalProviderUUID', 'portalProvider.name as portalProviderName', 'stock.name as stockName', 'game.startDate as gameStartDate', 'game.startTime as gameStartTime', 'game.endDate as gameEndDate', 'game.endTime as gameEndTime', 'game.endStockValue', DB::raw('(CASE WHEN game.gameStatus = 0 THEN "Pending" WHEN game.gameStatus = 1 THEN "Open" WHEN game.gameStatus = 2 THEN "Close" WHEN game.gameStatus = 3 THEN "Complete" WHEN game.gameStatus = 4 THEN "Error" ELSE "Deleted" END) as gameStatus'))
            ->join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('providerGameSetup', 'game.providerGameSetupID', 'providerGameSetup.PID')
            ->join('portalProvider', 'providerGameSetup.portalProviderID', 'portalProvider.PID')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->whereIn('game.gameStatus', $status)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function getAllGamesByPortalProviderID($portalProviderID, $status)
    {
        return $this->select('game.UUID as gameUUID', 'portalProvider.UUID as portalProviderUUID', 'portalProvider.name as portalProviderName', 'stock.UUID as stockUUID','stock.name as stockName', 'game.startDate as gameStartDate', 'game.startTime as gameStartTime', 'game.endDate as gameEndDate', 'game.endTime as gameEndTime', 'game.endStockValue', DB::raw('(CASE WHEN game.gameStatus = 0 THEN "Pending" WHEN game.gameStatus = 1 THEN "Open" WHEN game.gameStatus = 2 THEN "Close" WHEN game.gameStatus = 3 THEN "Complete" WHEN game.gameStatus = 4 THEN "Error" ELSE "Deleted" END) as gameStatus,IFNULL(count(betting.PID),0) as totalBets,IFNULL(SUM(betting.betAmount),0) as totalBetAmount,IFNULL(SUM(betting.rollingAmount),0) as totalRollingAmount,IFNULL(count(distinct betting.userID),0) as totalUsers,IFNULL(ROUND(SUM(CASE WHEN betting.betResult = 1 THEN betting.rollingAmount-betting.betAmount ELSE 0 END),2),0) as totalProfitEarned'))
            ->join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('providerGameSetup', 'game.providerGameSetupID', 'providerGameSetup.PID')
            ->join('portalProvider', 'providerGameSetup.portalProviderID', 'portalProvider.PID')
            ->leftJoin('betting', 'betting.gameID', '=', 'game.PID')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->whereIn('game.gameStatus', $status)
            ->when(
                !isEmpty($portalProviderID),
                function ($query) use ($portalProviderID) {
                    return $query->whereIn('providerGameSetup.portalProviderID', $portalProviderID);
                }
            )
            ->orderby('game.PID', 'DESC')
            ->groupBy('game.PID');
    }

    public function getAllProviderGamesByStock($portalProviderID, $stockID, $status, $currentTime, $limit = 100, $offset = 0)
    {
        return $this->select('game.UUID as gameUUID', DB::raw('(CASE WHEN TIMESTAMPDIFF(SECOND,"' . $currentTime . '",game.betCloseTime) > 0 THEN TIMESTAMPDIFF(SECOND,"' . $currentTime . '",game.betCloseTime) ELSE 0 END) as betCloseTimeInSec'), DB::raw('(CASE WHEN TIMESTAMPDIFF(SECOND,"' . $currentTime . '",CONCAT(DATE_FORMAT(game.endDate, "%Y-%m-%d"), " ", game.endTime)) > 0 THEN TIMESTAMPDIFF(SECOND,"' . $currentTime . '",CONCAT(DATE_FORMAT(game.endDate, "%Y-%m-%d"), " ", game.endTime)) ELSE 0 END) as gameCloseTimeInSec'))
            ->join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('providerGameSetup', 'game.providerGameSetupID', 'providerGameSetup.PID')
            ->join('portalProvider', 'providerGameSetup.portalProviderID', 'portalProvider.PID')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->where('portalProvider.PID', $portalProviderID)
            ->where('stock.UUID', $stockID)
            ->whereIn('game.gameStatus', $status)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function getGameByPortalProviderID($portalProviderID, $gameUUID)
    {
        return $this->join('providerGameSetup', 'game.providerGameSetupID', 'providerGameSetup.PID')
            ->join('portalProvider', 'providerGameSetup.portalProviderID', 'portalProvider.PID')
            ->where('portalProvider.UUID', $portalProviderID)
            ->where('game.UUID', $gameUUID)
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt');
    }

    /**
     * Get All End Games
     *  - userID = user table primary key
     * @return QueryBuilder
     */
    public function getAllCloseGameDetails() //Get All End Games
    {
        $currentDate = microtimeToDateTime(getCurrentTimeStamp(), false, "Y-m-d");
        $currentTime = microtimeToDateTime(getCurrentTimeStamp(), false, 'H:i:s');

        return $this->where('gameStatus', 2)
            ->where(function ($query) use ($currentDate, $currentTime) {
                $query->where("endDate", "<", $currentDate)
                    ->orWhere(function ($query) use ($currentDate, $currentTime) {
                        $query->where('endDate', $currentDate)
                            ->Where('endTime', '<=', $currentTime);
                    });
            });
    }

    /**
     * Get All Provider Game Setup and Game
     *  - gameIDs = All gameId as Array
     * @return QueryBuilder
     */
    public function getProviderGameSetupByGameIDs(array $gameIDs)
    {
        return $this->join('providerGameSetup', 'game.providerGameSetupID', '=', 'providerGameSetup.PID')
            ->whereIn('game.PID', $gameIDs)->where('isActive', 'active');
    }

    /**
     * Get Game Setup, Provider Game Setup, Game Setup and Game
     *  - gameIDs = All gameId as Array
     * @return QueryBuilder
     */
    public function getGameSetupProviderGameSetupByGameIDs(array $gameIDs)
    {
        return $this->join('providerGameSetup', 'game.providerGameSetupID', '=', 'providerGameSetup.PID')
            ->join('providerGameSetup', 'game.providerGameSetupID', '=', 'providerGameSetup.PID')
            ->whereIn('PID', $gameIDs)->where('isActive', 'active');
    }

    public function getGameByUUIDAndPortalProviderID($gameUUID, $portalProviderID)
    {
        return $this->join('providerGameSetup', 'providerGameSetup.PID', '=', 'game.providerGameSetupID')
            ->join('portalProvider', 'providerGameSetup.portalProviderID', '=', 'portalProvider.PID')
            ->where('game.gameStatus', '!=', 5)->where('game.UUID', $gameUUID)
            ->where('providerGameSetup.isActive', 'active')->whereNull('providerGameSetup.deletedAt')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')->where('portalProvider.PID', $portalProviderID);
    }

    public function getGameByUUID($gameUUID, array $status)
    {
        return $this->whereIn('gameStatus', $status)->where('UUID', $gameUUID);
    }

    public function getGameAndBetByUUID(array $status, $stockID, $loop, $gameUUID = null)
    {
        return $this->leftJoin('betting', 'betting.gameID', 'game.PID')
            ->leftJoin('stock', 'game.stockID', 'stock.PID')
            ->when(
                !isEmpty($gameUUID),
                function ($query) use ($gameUUID) {
                    return $query->where('game.UUID', $gameUUID);
                }
            )
            ->when(
                !isEmpty($stockID),
                function ($query) use ($stockID, $loop) {
                    return $query->where('stock.PID', $stockID)
                        ->where('stock.stockLoop', $loop);
                }
            )
            ->whereIn('game.gameStatus', $status);
    }

    public function getGameByGameID($gameID)
    {
        return $this->where('PID', $gameID);
    }

    public function getBetCounts($gameID)
    {
        return DB::select("call USP_BetCount(?)", [$gameID]);
    }

    public function getActiveGamesByProviderStock($portalProviderID, $limit = 100, $offset = 0, $stockID = null)
    {
        return DB::select("call USP_ActiveGames(?,?,?,?)", [$portalProviderID, $limit, $offset, $stockID]);
    }

    public function getStockLoop($gameID)
    {
        return $this->join('stock', 'game.stockID', '=', 'stock.PID')
            ->where('game.PID', $gameID);
    }

    public function getProviderUUIDByGameUUID($gameUUID)
    {
        return $this->join('portalProvider', 'game.portalProviderID', 'portalProvider.PID')
            ->leftJoin('stock', 'game.stockID', 'stock.PID')
            ->where('portalProvider.isActive', 'active')->whereNull('portalProvider.deletedAt')
            ->where('game.UUID', $gameUUID);
    }
}
