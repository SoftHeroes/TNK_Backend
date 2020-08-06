<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

require_once app_path() . '/Helpers/CommonUtility.php';

class Betting extends Model
{
    const UPDATED_AT = 'updatedAt';

    protected $table      = 'betting';
    protected $primaryKey = 'PID';

    //get All Bet By GameID
    public function getAllBetDetailsByGameID(
        $gameID,
        $betResult,
        $isBot = 0
    ) {
        return $this->join('rule', 'betting.ruleID', '=', 'rule.PID')
            ->join('user', 'betting.userID', '=', 'user.PID')
            ->join('portalProvider', 'user.portalProviderID', '=', 'portalProvider.PID')
            ->where('betting.isBot', $isBot)->where('betting.gameID', $gameID)
            ->whereIn('betting.betResult', $betResult);
    }

    public function getAllBetsByPortalProvider(array $providerIDs)
    {
        return $this->join('game', 'betting.gameID', '=', 'game.PID')
            ->join('user', 'betting.userID', '=', 'user.PID')
            ->join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('rule', 'betting.ruleID', '=', 'rule.PID')
            ->join('portalProvider', 'portalProvider.PID', '=', 'user.portalProviderID')
            ->where('user.isActive', 'active')->whereNull('user.deletedAt')
            ->when(
                !isEmpty($providerIDs),
                function ($query) use ($providerIDs) {
                    return $query->whereIn('portalProvider.PID', $providerIDs);
                }
            );
    }

    //user bet history
    public function getAllBets(
        $providerID,
        $userID,
        $betResult,
        $limit = 20,
        $offset = 0,
        $fromDate,
        $toDate,
        $gameUUID = null,
        $stockUUID = null,
        $showAll = false,
        $isExposed = false
    ) {
        $selectArray = array(
            'stock.stockLoop as loop',
            'betting.UUID as betUUID',
            'rule.name as ruleName',
            'betting.betAmount',
            'betting.rollingAmount',
            'betting.payout',
            DB::raw('setPrecision(game.endStockValue,stock.precision) AS gameDraw'),
            DB::raw('(CASE WHEN betting.betResult = -1 THEN "pending" WHEN betting.betResult = 0 THEN "lose" WHEN betting.betResult = 1 THEN "win" ELSE "fail" END) as betResult'),
            DB::raw('(CASE WHEN betting.parentBetID IS NULL THEN 0 ELSE 1 END) AS isFollowBet'),
            'betting.createdDate',
            'betting.createdTime',
            'game.UUID as gameUUID',
            'stock.name as stockName',
            'game.startDate as gameStartDate',
            'game.startTime as gameStartTime',
            DB::raw('(CASE WHEN gameStatus = 0 THEN "pending" WHEN gameStatus = 1 THEN "open" WHEN gameStatus = 2 THEN "close" WHEN gameStatus = 3 THEN "complete" WHEN gameStatus = 4 THEN "pending" ELSE "fail" END) as gameStatus')
        );

        if ($providerID == 1 || $showAll) {
            array_push($selectArray, 'portalProvider.UUID as portalProviderUUID');
            array_push($selectArray, 'portalProvider.name as portalProviderName');
            array_push($selectArray, 'user.balance as userBalance');
            array_push($selectArray, 'game.endDate as gameEndDate');
            array_push($selectArray, 'game.endTime as gameEndTime');
        }

        if ($isExposed) {
            array_push($selectArray, 'user.portalProviderUserID as portalProviderUserID');
        } else {
            array_push($selectArray, 'user.UUID as userUUID');
        }

        return $this->select($selectArray)
            ->join('game', 'betting.gameID', '=', 'game.PID')
            ->join('user', 'betting.userID', '=', 'user.PID')
            ->join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('rule', 'betting.ruleID', '=', 'rule.PID')
            ->join('portalProvider', 'portalProvider.PID', '=', 'user.portalProviderID')
            ->where('user.isActive', 'active')->whereNull('user.deletedAt')
            ->where('betting.isBot', 0)
            ->when(
                $providerID != 1,
                function ($query) use ($providerID) {
                    return $query->where('user.portalProviderID', $providerID);
                }
            )
            ->when(
                !isEmpty($userID),
                function ($query) use ($userID) {
                    return $query->where('betting.userID', $userID);
                }
            )
            ->when(
                !isEmpty($gameUUID),
                function ($query) use ($gameUUID) {
                    return $query->where('game.UUID', $gameUUID);
                }
            )
            ->when(
                !isEmpty($stockUUID),
                function ($query) use ($stockUUID) {
                    return $query->where('stock.UUID', $stockUUID);
                }
            )
            ->where('game.gameStatus', '!=', 5)
            ->whereIn('betting.betResult', $betResult)
            ->where('betting.createdDate', '>=', $fromDate)
            ->where('betting.createdDate', '<=', $toDate)
            ->limit($limit)
            ->offset($offset)
            ->orderBy('betting.PID', 'DESC')
            ->get();
    }

    //provider bet history
    public function getSumOfAllBetAmounts(
        $gameID,
        array $ruleIDs
    ) {
        return $this->select([DB::raw("SUM(betAmount) as totalAmount"), 'ruleID'])
            ->where('gameID', $gameID)
            ->whereIn('ruleID', $ruleIDs)
            ->groupBy('ruleID')->get();
    }

    //get user win and loss percentage value
    public function getUserWinLossValue($userPID)
    {
        $winLoseValue = $this->select(['userID', DB::raw("count('PID') as totalBets, ROUND((COUNT(CASE WHEN betResult = 1 THEN betResult END)/COUNT('PID'))*100,2) as winRate, ROUND((COUNT(CASE WHEN betResult = 0 THEN betResult END)/COUNT('PID'))*100,2) as lossRate, ROUND(SUM(CASE WHEN betResult = 1 THEN rollingAmount END),2) as totalProfitEarned")])
            ->where('userID', $userPID)
            ->groupBy('userID')
            ->get();

        return $winLoseValue;
    }

    //get total number of bets and
    public function getTotalBetsByGameID($gameUUID)
    {
        return $this->select(['game.PID as gameID', 'game.UUID as gameUUID', 'user.UUID as userUUID', 'game.endStockValue as endResultValue', 'stock.PID as stockID', 'stock.name as stockName', DB::raw('sum(rollingAmount) as rollingAmount'), DB::raw("count(1) as totalBets, ROUND(SUM(CASE WHEN betResult = 1 THEN rollingAmount-betAmount ELSE 0 END),2) as totalProfitEarned, SUM(betAmount) as totalBetAmount, count(distinct betting.userID) as totalUsers"), 'game.startDate as gameStartDate', 'game.startTime as gameStartTime', 'game.endDate as gameEndDate', 'game.EndTime as gameEndTime'])
            ->join('game', 'betting.gameID', '=', 'game.PID')
            ->join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('user', 'betting.userID', '=', 'user.PID')
            ->where('game.UUID', '=', $gameUUID)
            ->groupBy('gameID')
            ->get();
    }

    //get total number of bets and
    public function getBetsByGameID($gameUUID)
    {
        return $this->select(['game.UUID as gameUUID', 'user.UUID as userUUID', 'betting.UUID as bettingUUID', 'betting.betAmount as betAmount', 'betting.rollingAmount as rollingAmount', 'betting.payout as payoutAmount', 'game.endStockValue as endResultValue', 'stock.PID as stockID', 'stock.name as stockName', 'game.startDate as gameStartDate', 'game.startTime as gameStartTime', 'game.endDate as gameEndDate', 'game.EndTime as gameEndTime', DB::raw('(CASE WHEN betting.betResult = -1 THEN "pending" WHEN betting.betResult = 0 THEN "lose" WHEN betting.betResult = 1 THEN "win" ELSE "fail" END) as betResult')])
            ->join('game', 'betting.gameID', '=', 'game.PID')
            ->join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('user', 'betting.userID', '=', 'user.PID')
            ->where('game.UUID', '=', $gameUUID);
    }

    /**
     * Get user bet analysis based on user ID
     * @return QueryBuilder
     */
    public function getUserBetAnalysis(
        $userID,
        $startDate,
        $endDate
    ) {
        return $this->select(
            DB::raw('COUNT(1) totalBets'),
            DB::raw('COUNT(CASE WHEN betting.betResult = 0 THEN 1 ELSE null END ) lossCount'),
            DB::raw('COUNT(CASE WHEN betting.betResult = 1 THEN 1 ELSE null END ) winCount'),
            DB::raw('ROUND((COUNT(CASE WHEN betting.betResult = 1 THEN 1 ELSE null END )/COUNT(1) ) * 100,2) winRate'),
            'stock.name as stockName',
            'stock.category'
        )
            ->join('game', 'game.PID', '=', 'betting.gameID')
            ->join('stock', 'stock.PID', '=', 'game.stockID')
            ->where('betting.userID', $userID)
            ->where('betting.createdDate', '>=', $startDate)
            ->where('betting.createdDate', '<=', $endDate)
            ->groupBy('game.stockID')
            ->get();
    }

    public static function getBetByGameID(
        $gameID,
        $betResult
    ) {
        return Betting::where('gameID', $gameID)->whereIn('betResult', $betResult);
    }

    public function getFollowBetsByUser(
        $followToUserID,
        $followerID,
        $fromDate = null,
        $fromTime = null,
        array $betResult = null
    ) {
        return $this->where('followToID', $followToUserID)
            ->where('userID', $followerID)
            ->when(
                !isEmpty($betResult),
                function ($query) use ($betResult) {
                    return $query->whereIn('betResult', $betResult);
                }
            )
            ->when(
                !isEmpty($fromDate),
                function ($query) use ($fromDate) {
                    return $query->where('createdDate', '>=', $fromDate);
                }
            )
            ->when(
                !isEmpty($fromTime),
                function ($query) use ($fromTime) {
                    return $query->where('createdTime', '>=', $fromTime);
                }
            );
    }
    public function getAllBetAdmin(
        $providerID,
        $userID,
        $betResult,
        $gameUUID = null,
        $stockUUID = null,
        $showAll = false,
        $isExposed = false
    ) {
        $selectArray = [
            'stock.stockLoop as loop',
            'betting.UUID as betUUID',
            'rule.name as ruleName',
            'betting.betAmount',
            'betting.rollingAmount',
            'betting.payout',
            DB::raw('setPrecision(game.endStockValue, stock.precision) as gameDraw'),
            DB::raw('(CASE WHEN betting.betResult = -1 THEN "pending" WHEN betting.betResult = 0 THEN "lose" WHEN betting.betResult = 1 THEN "win" ELSE "fail" END) as betResult'),
            DB::raw('(CASE WHEN betting.parentBetID IS NULL THEN 0 ELSE 1 END) AS isFollowBet'),
            'betting.createdDate',
            'betting.createdTime',
            'game.UUID as gameUUID',
            'stock.name as stockName',
            DB::raw("CAST(CONCAT(game.startDate, ' ',game.startTime) AS DATETIME) as gameStartDateTime"),
            DB::raw('(CASE WHEN gameStatus = 0 THEN "pending" WHEN gameStatus = 1 THEN "open" WHEN gameStatus = 2 THEN "close" WHEN gameStatus = 3 THEN "complete" WHEN gameStatus = 4 THEN "pending" ELSE "fail" END) as gameStatus')
        ];

        if ($providerID == 1 || $showAll) {
            array_push($selectArray, 'portalProvider.UUID as portalProviderUUID');
            array_push($selectArray, 'portalProvider.name as portalProviderName');
            array_push($selectArray, 'user.balance as userBalance');
            array_push($selectArray, DB::raw("CAST(CONCAT(game.endDate, ' ',game.endTime) AS DATETIME) as gameEndDateTime"));
        }

        if ($isExposed) {
            array_push($selectArray, 'user.portalProviderUserID as portalProviderUserID');
        } else {
            array_push($selectArray, 'user.UUID as userUUID');
        }

        return $this->select($selectArray)
            ->join('game', 'betting.gameID', '=', 'game.PID')
            ->join('user', 'betting.userID', '=', 'user.PID')
            ->join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('rule', 'betting.ruleID', '=', 'rule.PID')
            ->join('portalProvider', 'portalProvider.PID', '=', 'user.portalProviderID')
            ->where('user.isActive', 'active')->whereNull('user.deletedAt')
            ->where('betting.isBot', 0)
            ->when(
                $providerID != 1,
                function ($query) use ($providerID) {
                    return $query->where('user.portalProviderID', $providerID);
                }
            )
            ->when(
                !isEmpty($userID),
                function ($query) use ($userID) {
                    return $query->where('betting.userID', $userID);
                }
            )
            ->when(
                !isEmpty($gameUUID),
                function ($query) use ($gameUUID) {
                    return $query->where('game.UUID', $gameUUID);
                }
            )
            ->when(
                !isEmpty($stockUUID),
                function ($query) use ($stockUUID) {
                    return $query->where('stock.UUID', $stockUUID);
                }
            )
            ->where('game.gameStatus', '!=', 5)
            ->whereIn('betting.betResult', $betResult)
            ->orderBy('betting.PID', 'DESC');
    }

    public function getTotalBetOnRuleByUserID($gameID, $ruleID, $usedID)
    {
        return $this->select(DB::raw("SUM(betAmount) as betTotal"))
            ->where('gameID', $gameID)
            ->where('ruleID', $ruleID)
            ->where('userID', $usedID)
            ->get();
    }
}
