<?php

namespace App\Http\Controllers\AdminPanel;

use App\Exceptions\ValidationError;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PortalProvider;
use App\Models\Admin;
use Illuminate\Http\Request;
use DB;
use Exception;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\DataTables;

class NotificationController extends Controller
{
    public function getNotification(Request $request)
    {
        try {
            $providerIDs = array();
            $checkProviderID = 1;
            $selectProviderID = '';

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
            if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
                $selectProviderID = $sessionData['portalProviderIDs'];
                $checkProviderID = 1;
            } else if ($sessionData['isAllowAll'] == 'false') {
                if (isEmpty($sessionData['portalProviderIDs'])) {
                    throw new ValidationError("You don't have access to any of Portal Provider!!");
                }
                $providerIDs = explode(',', $sessionData['portalProviderIDs']);
                $selectProviderID = explode(',', $sessionData['portalProviderIDs']);
                $checkProviderID = count($selectProviderID) > 1 ? 1 : 0;
            }

            //To get the accessibility of the admin policy tab based on the admin id
            $adminModel = new Admin();
            $adminInfo = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessNotification','isAllowAll')->get();
            $accessibility = $adminInfo[0]->accessNotification;
            $isAllowAll = $adminInfo[0]->isAllowAll;

            if ($request->ajax()) {

                $notificationModel = new Notification();

                $selectColumn = [
                    'notification.PID',
                    'notification.UUID as notificationUUID',
                    'notification.portalProviderID',
                    'notification.fromID',
                    'notification.toID',
                    DB::raw("(CASE WHEN notification.type = 0 THEN 'Admin' ELSE CASE WHEN notification.type = 1 THEN 'Follow' ELSE CASE WHEN notification.type = 2 THEN 'UnFollow' ELSE CASE WHEN notification.type = 3 THEN 'Balance Update' ELSE 'Welcome' END END END END ) as type"),
                    'notification.title',
                    'notification.message',
                    'notification.createdAt',
                    'notification.deletedAt',
                    'portalProvider.name as portalProviderName',
                    'portalProvider.UUID as portalProviderUUID',
                    'u1.UUID as notificationFromID',
                    'u2.UUID as notificationToID',
                    'accessPolicy.accessNotification',
                    'accessPolicy.isAllowAll'
                ];
                
                if ($request->cookie('includeDeleted')) {
                    $notificationData = $notificationModel->getAllNotificationByPortalProvider($providerIDs)->withTrashed()->select($selectColumn);
                } else {
                    $notificationData = $notificationModel->getAllNotificationByPortalProvider($providerIDs)->select($selectColumn);
                }

                return DataTables::of($notificationData)
                    ->addColumn('action', function ($data) {
                        if ($data->deletedAt != null) {
                            $button = '<button type="button" name="restore" id="' . $data->PID . '" class="restore btn btn-success btn-sm"><i class="fa fa-refresh"></i>Restore</button>';
                            return $button;
                        } else {
                            if ($data->isAllowAll == 'true' || (($data->isAllowAll == 'false') && (($data->accessNotification == 1) || ($data->accessNotification == 2)))) {
                                $button = '<button type="button" name="edit" id="' . $data->PID . '" class="edit btn btn-primary btn-sm"><i class="fa fa-edit"></i></button>';
                                $button .= ' <button type="button" name="edit" id="' . $data->PID . '" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                                return  $button;
                            } else {
                                return  $button = "";
                            }
                        }
                    })
                    ->addColumn('deletedAt', function ($data) {
                        if ($data->deletedAt != null) {
                            return $data->deletedAt;
                        }
                        else {
                            return "Null";
                        }
                    })
                    ->rawColumns(['action'])
                    ->setRowClass(function ($data) {
                        return $data->deletedAt != null ? 'alert-danger' : '';
                    })
                    ->make(true);
            }

            $portalProviderModel = new PortalProvider();
            $portalProviderData = $portalProviderModel->getPortalProviders($selectProviderID)->get();
            
            

            return view('adminPanel/notification', compact('portalProviderData', 'checkProviderID','accessibility','isAllowAll'));

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

    public function createNotification(Request $request)
    {
        try {
            $rules = array(
                'portalProviderID' => 'required',
                'title' => 'required|max:'.config('constants.string_max_length'),
                'message' => 'required|max:1000'
            );

            $messages = array(
                'portalProviderID.required' => 'Portal Provider ID is required.',
                'title.required' => 'Title is required.',
                'message.required' => 'Message is required.',
                'title.max' => 'Title is limited by '.config('constants.string_max_length').' characters.',
                'message.max' => 'Message is limited 1000 by characters.'
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {

                $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames');
                $portalProviderArray = $request->portalProviderID;
                foreach ($portalProviderArray as $key => $portalProviderID) {
                    $notification[] = [
                        'UUID' => Uuid::uuid4(),
                        'portalProviderID' => $portalProviderID,
                        'adminID' => $sessionData['adminPID'],
                        'type' => 0,
                        'title' => $request->title,
                        'message' => $request->message
                    ];
                }
                if (!isEmpty($notification)) {
                    $response = Notification::insert($notification);
                    if ($response) {
                        return redirect()->back()->with('message', 'Send notification Successfully');
                    }
                } else {
                    throw new Exception('Notification value not found ..!');
                }
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function deleteNotification(Request $request)
    {
        try {
            $deleteNotification = Notification::where('PID', $request->notificationID)->delete();
            if ($deleteNotification) {
                return redirect()->back()->with('message', 'Notification deleted successfully');
            }
        } catch (Exception $e) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($e->getMessage());
            }
        }
    }

    public function updateNotification(Request $request)
    {
        try {
            $rules = array(
                'portalProviderID' => 'required',
                'title' => 'required',
                'message' => 'required'
            );

            $messages = array(
                'portalProviderID.required' => 'Portal Provider ID is required.',
                'title.required' => 'Title is required.',
                'message.required' => 'Message is required.'
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {
                $data['portalProviderID'] = $request->portalProviderID;
                $data['title'] = $request->title;
                $data['message'] = $request->message;

                $updateNotification = Notification::where('PID', $request->notificationID)->update($data);

                if ($updateNotification) {
                    return redirect()->back()->with('message', 'Notification details updated successfully');
                }
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function getUpdateNotification(Request $request)
    {
        try {
            $providerIDs = array();
            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session
            if (!isEmpty($request->cookie('selectedPortalProviderIDs'))) {
                $providerIDs = explode(',', $request->cookie('selectedPortalProviderIDs'));
            } else if ($sessionData['isAllowAll'] == 'false') {
                if (isEmpty($sessionData['portalProviderIDs'])) {
                    throw new ValidationError("You don't have access to any of Portal Provider!!");
                }
                $providerIDs = explode(',', $sessionData['portalProviderIDs']);
            }

            $notificationModel = new Notification();

            return $notificationModel->getAllNotificationByPortalProvider($providerIDs)->select(
                'notification.PID',
                'notification.UUID as notificationUUID',
                'notification.portalProviderID',
                'notification.fromID',
                'notification.toID',
                DB::raw("(CASE WHEN notification.type = 0 THEN 'Admin' ELSE CASE WHEN notification.type = 1 THEN 'Follow' ELSE CASE WHEN notification.type = 2 THEN 'UnFollow' ELSE CASE WHEN notification.type = 3 THEN 'Balance Update' ELSE 'Welcome' END END END END ) as type"),
                'notification.title',
                'notification.message',
                'notification.createdAt',
                'portalProvider.name as portalProviderName',
                'portalProvider.UUID as portalProviderUUID',
                'u1.UUID as notificationFromID',
                'u2.UUID as notificationToID'
            )->where('notification.PID',$request->notificationID)->get();

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

    public function restoreNotification(Request $request)
    {
        try {
            if ($request->notificationID != '') {
                $notificationData = Notification::withTrashed()->find($request->notificationID)->restore();
                if ($notificationData) {
                    return redirect()->back()->with('message', 'Notification Restored Successfully');
                }
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }
}
