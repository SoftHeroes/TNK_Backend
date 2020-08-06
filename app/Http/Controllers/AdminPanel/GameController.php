<?php
namespace App\Http\Controllers\AdminPanel;

use App\Exceptions\ValidationError;
use App\Http\Controllers\Controller;
use App\Models\Betting;
use App\Models\Game;
use App\Models\PortalProvider;
use DB;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class GameController extends Controller
{
    public function getAllGames(Request $request)
    {
        try {
            if ($request->ajax()) {
                $portalProviderModel = new PortalProvider();
                $gameModel = new Game();

                $gameStatus = [1, 2, 3, 4];
                $providerIDs = array();
                $value = session(str_replace('.', '_', $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session

                if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                    $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
                } elseif ($value['isAllowAll'] == 'false') {
                    if (isEmpty($value['portalProviderIDs'])) {
                        throw new ValidationError("You don't have access to any of Portal Provider!!");
                    }
                    $providerIDs = explode(',', $value['portalProviderIDs']);
                }

                $portalProviderUUID = $value['portalProviderUUID'];
                $providerData = $portalProviderModel->getPortalProviderByUUID($portalProviderUUID);
                if ($providerData->count(DB::raw('1')) == 0) {
                    return redirect()->back()->withErrors([], 'Provider UUID does not exist.');
                } else {
                    //To get all the games if super user or else get the games based on the provider ids stored in access policy table
                    $gameData = $gameModel->getAllGamesByPortalProviderID($providerIDs, $gameStatus);

                    return DataTables::of($gameData)->make(true);
                }
            }

            return view('adminPanel/gameHistory');
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

    // Get game details by using game UUID.
    public function getGameDetail($gameUUID)
    {
        $bettingModel = new Betting();
        $data = $bettingModel->getBetsByGameID($gameUUID);

        return DataTables::of($data)->make(true);
    }
}
