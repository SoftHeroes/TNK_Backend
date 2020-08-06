<?php

namespace App\Providers\Gaming;

use Illuminate\Support\ServiceProvider;
use App\Models\Rule;
use App\Models\PortalProvider;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ResponseController as Res;
use Exception;



class RuleProvider extends ServiceProvider
{
    public function getAllRules($portalProviderUUID)
    {

        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        try {
            $ruleModel = new Rule();
            $providerModel = new PortalProvider();

            //Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
            } else {
                $response['portalProviderID'] = $providerData[0]->PID;

                //fetching Rules
                $data = $ruleModel->getAllRules();
                if ($data->count(DB::raw('1')) > 0) {
                    $response['res'] = Res::success($data);
                } else {
                    $response['res'] = Res::success([], 'No rules found.');
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
