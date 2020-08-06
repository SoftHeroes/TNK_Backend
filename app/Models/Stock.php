<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Game;

class Stock extends Model
{
    use SoftDeletes;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'stock';
    protected $primaryKey = 'PID';

    /**
     * Get All Stock Details
     *  - userID = user table primary key
     * @return QueryBuilder
     */
    public static function getStockDetails($stockID) //Get All End Games
    {
        return Stock::where('isActive', 'active')->where('PID', $stockID);
    }

    public static function getStockBaseOnProvider($portalProviderID,$stockUUID=null)
    {
        return Stock::join('providerGameSetup', 'providerGameSetup.stockID', 'stock.PID')
        ->join('portalProvider', 'providerGameSetup.portalProviderID', 'portalProvider.PID')
        ->where('stock.isActive', 'active')
        ->where('providerGameSetup.portalProviderID', $portalProviderID)
        ->when(
            !isEmpty($stockUUID),
            function ($query) use ($stockUUID) {
                return $query->where('stock.UUID', $stockUUID);
            }
        );
    }

    public static function getAllStocks($portalProviderID = null)
    {
        return Stock::join('providerGameSetup', 'providerGameSetup.stockID', 'stock.PID')
                    ->join('portalProvider', 'providerGameSetup.portalProviderID', 'portalProvider.PID')
                    ->where('stock.isActive', 'active')->where('portalProvider.isActive', 'active')
                    ->when(
                        !isEmpty($portalProviderID),
                        function ($query) use ($portalProviderID) {
                            return $query->whereIn('providerGameSetup.portalProviderID', $portalProviderID);
                        }
                    );
    }

    public static function getAllStockBaseOnProvider()
    {
        return Stock::leftJoin('providerGameSetup', 'providerGameSetup.stockID', 'stock.PID')->leftJoin('portalProvider', 'providerGameSetup.portalProviderID', 'portalProvider.PID')->where('stock.isActive', 'active')->where('portalProvider.isActive', 'active');
    }

    public static function getStockProviderID($portalProviderID, $stockID)
    {
        return Stock::join('providerGameSetup', 'providerGameSetup.stockID', 'stock.PID')->where('stock.isActive', 'active')->where('providerGameSetup.portalProviderID', $portalProviderID)->where('providerGameSetup.stockID', $stockID);
    }
    public static function getStockBaseOnProviderAndActiveGame($portalProviderID, $status = [1], $active = 'active')
    {
        return Game::join('stock', 'game.stockID', '=', 'stock.PID')
            ->join('providerGameSetup', 'game.providerGameSetupID', 'providerGameSetup.PID')
            ->join('portalProvider', 'providerGameSetup.portalProviderID', 'portalProvider.PID')
            ->where('portalProvider.isActive', $active)->whereNull('portalProvider.deletedAt')
            ->where('providerGameSetup.isActive', $active)->whereNull('providerGameSetup.deletedAt')
            ->where('stock.isActive', $active)->whereNull('stock.deletedAt')
            ->where('providerGameSetup.portalProviderID', $portalProviderID)
            ->whereIn('game.gameStatus', $status);
    }

    public static function getStockDetailsNotIn(array $stockID) //Get All End Games
    {
        return Stock::where('isActive', 'active')->whereNotIn('PID', $stockID);
    }

    public static function getStockDetailsInUUID(array $stockUUID) //Get All End Games
    {
        return Stock::where('isActive', 'active')->whereIn('UUID', $stockUUID);
    }

    public static function getAllStockBaseOnProviderID($portalProviderID)
    {
        return Stock::join('providerGameSetup', 'providerGameSetup.stockID', 'stock.PID')
                    ->join('portalProvider', 'providerGameSetup.portalProviderID', 'portalProvider.PID')
                    ->where('stock.isActive', 'active')
                    ->where('providerGameSetup.portalProviderID', $portalProviderID);
    }
}
