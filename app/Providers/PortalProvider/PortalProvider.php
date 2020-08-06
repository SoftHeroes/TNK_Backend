<?php

namespace App\Providers\PortalProvider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ResponseController as Res;
use App\Models\PortalProvider as AppPortalProvider;
use Exception;

class PortalProvider extends ServiceProvider
{
    public static function updateProviderInfo(int $portalProviderPID, string $serverName, string $ipList, $APIKey)
    {
        try {
            $ppdModel = new AppPortalProvider();
            $ppdData = $ppdModel->findByPortalProviderID($portalProviderPID);
            if ($ppdData->count(DB::raw('1')) < 1) {
                return Res::notFound([], 'portalProviderPID does not exist');
            }

            $isServerExist = $ppdModel->findByServerName($serverName);
            if ($isServerExist->count(DB::raw('1')) > 0 && $ppdData->first()->server != $serverName) {
                return Res::badRequest($isServerExist->count(DB::raw('1')), 'Server name already in use');
            }

            $updateData = [
                'server' => $serverName,
                'ipList' => $ipList,
                'APIKey' => $APIKey
            ];
            $ppdData->update($updateData);
            return Res::success([], 'Update Success');
        } catch (Exception $ex) {
            return Res::errorException($ex->getMessage());
        }
    }
}
