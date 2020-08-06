<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\HolidayList;
use Illuminate\Http\Request;
use App\Http\Controllers\ResponseController as Res;
use App\Models\Stock;
use App\Providers\Admin\AdminProvider;
use Exception;

class HolidayListController extends Controller
{
    public function getStockByEventColor(Request $request)
    {
        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            if ($authPolicy->access == 1 || $authPolicy->access == 3) {
                $stockModel = new Stock();
                $eventColor = ['', 'fc-event-primary', 'fc-event-warning', 'fc-event-success', 'fc-event-danger', 'fc-event-info', 'fc-event-secondary', 'fc-event-pink', 'fc-event-blue', 'fc-event-bright-purple'];
                $stock = $stockModel->where('isActive', 'active')->get();
                foreach ($stock as $key => $value) {
                    $stockData[] = [
                        'eventColor' => $eventColor[$value->PID],
                        'PID' => $value->PID,
                        'name' => $value->name,
                        'country' => $value->country,
                        'category' => $value->category
                    ];
                }

                $stockData = json_decode(json_encode($stockData));
                return view('adminPanel/holidayList', compact('stockData'));
            } else {
                throw new Exception("You don't have access to any of holiday List!!");
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function getHolidayList(Request $request)
    {
        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            if ($authPolicy->access == 1) {
                $holidayListModel = new HolidayList();
                return $holidayListModel->get();
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function createHolidayList(Request $request)
    {
        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            if ($authPolicy->access == 1) {

                $holidayListModel = new HolidayList();
                $event = $request->event;

                $data['stockID'] = (int) $event['stockID'];
                $data['className'] = $event['className'];
                $data['id'] = $event['id'];
                $data['title'] = $event['title'];
                $data['start'] = $event['start'];
                if (!isEmpty($event['end'])) {
                    $data['end'] = $event['end'];
                }
                if (!isEmpty($event['stick'])) {
                    $data['stick'] = $event['stick'];
                }

                $response = $holidayListModel->create($data);

                return Res::success($response);
            } else {
                throw new Exception("You don't have access to any of holiday List!!");
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function updateHolidayList(Request $request)
    {
        try {
            $holidayListModel = new HolidayList();
            $event = $request->event;

            if (!isEmpty((int) $event['stockID'])) {
                $data['stockID'] = (int) $event['stockID'];
            }
            if (!isEmpty($event['className'])) {
                $data['className'] = $event['className'];
            }
            if (!isEmpty($event['title'])) {
                $data['title'] = $event['title'];
            }
            if (!isEmpty($event['start'])) {
                $data['start'] = $event['start'];
            }
            if (!isEmpty($event['end'])) {
                $data['end'] = $event['end'];
            }
            if (!isEmpty($event['stick'])) {
                $data['stick'] = $event['stick'];
            }

            $response = $holidayListModel->updateHolidayList($request->id, $data);
            return Res::success($response);
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function deleteHolidayList(Request $request)
    {
        try {
            $response = HolidayList::where('id', $request->id)->delete();
            return Res::success($response);
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }
}
