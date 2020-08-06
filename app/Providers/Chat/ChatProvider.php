<?php

namespace App\Providers\Chat;

use DB;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\ResponseController as Res;
use App\Models\Chat;
use App\Models\Game;
use App\Models\PortalProvider;
use App\Models\User;


class ChatProvider extends ServiceProvider
{
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

    public function getMessage($portalProviderUUID, $gameUUID = '', $chatType)

    {
        $userModel = new User();
        $chatModel = new Chat();
        $providerModel = new PortalProvider();

        //Portal provider UUID valid check
        $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
        if ($providerData->count(DB::raw('1')) == 0) {
            $response =  Res::notFound([], 'portalProviderUUID does not exist.');
            return $response;
        }

        if ($chatType == 1) {

            // check gameUUID
            $gameDetails = Game::select('PID')->where('UUID', $gameUUID)->get();
            if ($gameDetails->count(DB::raw('1')) == 0) {
                return Res::notFound([], 'gameUUID does not exist.');
            }

            $response = $chatModel->getChat($providerData[0]->PID, $gameDetails[0]->PID, $chatType);
        } else {
            $response = $chatModel->getChat($providerData[0]->PID, $gameDetails = null, $chatType);
        }

        if ($response->count(DB::raw('1')) == 0) {
            return Res::notFound([], 'No message found.');
        }

        foreach ($response as $key => $value) {

            //User UUID valid check and get username
            $userData = $userModel->select('userName', 'PID', 'UUID')->where('PID', $value->userID)->get();
            if ($userData->count(DB::raw('1')) == 0) {
                $response = Res::notFound([], 'UserUUID does not exist.');
                return $response;
            }

            $messageData[] =
                [
                    'userName' => $userData[0]->userName,
                    'userUUID' => $userData[0]->UUID,
                    'message' => $value->message,
                    'date' => date_format($value->createdAt, "Y-m-d H:i:s")
                ];
        }

        return Res::success($messageData);
    }

    public function sendMessage($portalProviderUUID, $userUUID, $gameUUID = '', $chatType, $message)
    {
        $userModel = new User();
        $providerModel = new PortalProvider();


        //Portal provider UUID valid check
        $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
        if ($providerData->count(DB::raw('1')) == 0) {
            $response =  Res::notFound([], 'portalProviderUUID does not exist.');
            return $response;
        }

        //User UUID valid check and get username
        $userData = $userModel->getUserByUUID($userUUID)->select('userName', 'PID')->get();
        if ($userData->count(DB::raw('1')) == 0) {
            $response = Res::notFound([], 'UserUUID does not exist.');
            return $response;
        }

        $data = array();

        if ($chatType == 1) {

            // check gameUUID
            $gameDetails = Game::select('PID')->where('UUID', $gameUUID)->get();
            if ($gameDetails->count(DB::raw('1')) == 0) {
                return Res::notFound([], 'gameUUID does not exist.');
            }

            $data['portalProviderID'] = $providerData[0]->PID;
            $data['userID'] = $userData[0]->PID;
            $data['gameID'] = $gameDetails[0]->PID;
            $data['chatType'] = $chatType;
            $data['message'] = $message;
        } else {
            $data['portalProviderID'] = $providerData[0]->PID;
            $data['userID'] = $userData[0]->PID;
            $data['gameID'] = null;
            $data['chatType'] = $chatType;
            $data['message'] = $message;
        }


        $response = Chat::create($data);

        if (!isEmpty($response)) {

            $messageData[] =
                [
                    'userName' => $userData[0]->userName,
                    'userUUID' => $userUUID,
                    'message' => $response->message,
                    'date' => date_format($response->createdAt, "Y-m-d H:i:s")
                ];

            return Res::success($messageData, 'success', true);
        }
    }
}
