<?php

namespace App\Providers\Users;

use Illuminate\Support\ServiceProvider;
use App\Models\Notification;
use App\Models\PortalProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ResponseController as Res;
use Ramsey\Uuid\Uuid;

class NotificationProvider extends ServiceProvider
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


    public function addNotification($portalProviderUUID, $type, $fromUUID, $toUUID, $title, $message)
    {

        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        try {
            $userModel = new User();
            $providerModel = new PortalProvider();

            //Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $portalProviderID = $response['portalProviderID'] = $providerData[0]->PID;

            //check type
            if ($type == 0) {                   //Admin Notification
                $userFromID = null;
                $userToID = null;
            } else if ($type == 4 || $type = 3) {             //Balance Update
                $userFromID = null;

                //User UUID valid check
                $userToData = $userModel->getUserByUUID($toUUID)->select('PID', 'portalProviderID')->get();
                if ($userToData->count(DB::raw('1')) == 0) {
                    $response['res'] = Res::notFound([], 'toUUID does not exist.');
                    return $response;
                }
                $userToID = $userToData[0]->PID;
            } else {

                //User UUID valid check
                $userFromData = $userModel->getUserByUUID($fromUUID)->select('PID', 'portalProviderID')->get();
                if ($userFromData->count(DB::raw('1')) == 0) {
                    $response['res'] = Res::notFound([], 'fromUUID does not exist.');
                    return $response;
                }
                $userFromID = $response['userID'] = $userFromData[0]->PID;

                //User UUID valid check
                $userToData = $userModel->getUserByUUID($toUUID)->select('PID', 'portalProviderID')->get();
                if ($userToData->count(DB::raw('1')) == 0) {
                    $response['res'] = Res::notFound([], 'toUUID does not exist.');
                    return $response;
                }
                $userToID = $userToData[0]->PID;

                //fromUUID and toUUID same check
                if ($userFromID == $userToID) {
                    $response['res'] = Res::badRequest([], "fromUUID and toUUID can't be the same");
                    return $response;
                }

                // Check if both users belong to portal Provider
                if ($portalProviderID != $userFromData[0]->portalProviderID || $portalProviderID != $userToData[0]->portalProviderID) {
                    $response['res'] = Res::badRequest([], 'Invalid Request! Please contact your provider');
                    return $response;
                }
            }
            //Get Admin ID
            $adminData = request()->get('adminData');
            $adminID = $adminData[0]->PID;

            //Insert Notification
            $notification = array(
                'UUID' => Uuid::uuid4(),
                'portalProviderID' => $portalProviderID,
                'adminID' => $adminID,
                'fromID' => $userFromID,
                'toID' => $userToID,
                'type' => $type,
                'title' => $title,
                'message' => $message
            );
            Notification::insert($notification);
            $response['res'] = Res::success();
        } catch (Exception $e) {

            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }
        return $response;
    }


    public function getNotification($portalProviderUUID, $userUUID, $limit, $offset)
    {

        $response['userID'] = null;
        $response['portalProviderID'] = null;
        $response['exceptionMsg'] = null;

        try {
            $userModel = new User();
            $notificationModel = new Notification();
            $providerModel = new PortalProvider();

            //Portal provider UUID valid check
            $providerData = $providerModel->getPortalProviderByUUID($portalProviderUUID);
            if ($providerData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'portalProviderUUID does not exist.');
                return $response;
            }
            $portalProviderID = $response['portalProviderID'] = $providerData[0]->PID;

            //User UUID valid check
            $userData = $userModel->getUserByUUID($userUUID)->select('PID', 'portalProviderID', 'createdAt')->get();
            if ($userData->count(DB::raw('1')) == 0) {
                $response['res'] = Res::notFound([], 'fromUUID does not exist.');
                return $response;
            }
            $userID = $response['userID'] = $userData[0]->PID;
            $createdAt = $userData[0]->createdAt;


            // Check if both users belong to portal Provider
            if ($portalProviderID != $userData[0]->portalProviderID) {
                $response['res'] = Res::badRequest([], 'Invalid Request! Please contact your provider');
                return $response;
            }

            //get all notifications

            $result = $notificationModel->getNotification($userID, $portalProviderID, $createdAt, $limit, $offset);

            if ($result->count(DB::raw('1')) > 0) {
                $response['res'] = Res::success($result);
            } else {
                $response['res'] = Res::success([], 'No notifications found.');
            }
        } catch (Exception $e) {
            $response['exceptionMsg'] = $e->getMessage();
            $response['res'] = Res::errorException($e);
        }
        return $response;
    }
}
