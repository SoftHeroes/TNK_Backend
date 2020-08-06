<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Admin;
use App\Providers\Admin\AdminProvider;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    public function getCurrency(Request $request)
    {
        try {
            $authPolicy = AdminProvider::getAuthAdminPolicy($request);
            $adminModel = new Admin();

            $sessionData = session(str_replace(".", "_", $request->ip()) . 'ECGames'); //getting PortalProviderUUID from session

            //To get the accessibility of the admin policy tab based on the admin id
            $adminInfo = $adminModel->getAdminDataByPID($sessionData['adminPID'])->select('accessCurrency','isAllowAll')->get();
            $accessibility = $adminInfo[0]->accessCurrency;
            $isAllowAll = $adminInfo[0]->isAllowAll;

            if ($authPolicy->access == 1 || $authPolicy->access == 3) {
                if ($request->cookie('includeDeleted')) {
                    $currencyData = Currency::withTrashed()->get();
                } else {
                    $currencyData = Currency::get();
                }
                return view('adminPanel/currency', compact('currencyData', 'accessibility', 'isAllowAll'));
            }

        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function createCurrency(Request $request)
    {
        try {
            $currencyModel = new Currency();

            $rules = array(
                'name' => 'required|max:'.config('constants.string_max_length'),
                'rate' => 'required|max:10',
                'isActive' => 'required',
                'symbol' => 'required|max:5',
                'abbreviation' => 'required|max:20',
            );

            $messages = array(
                'name' => 'Name is required.',
                'name.max' => "Name shouldn't greater than ".config('constants.string_max_length').' characters.',
                'rate' => 'Rate is required.',
                'rate.max' => 'Rate is limited by 10 max.',
                'isActive' => 'Status Active is required.',
                'symbol' => 'Symbol is required.',
                'symbol.max' => "Symbol shouldn't greater than 5 characters.",
                'abbreviation' => 'Abbreviation is required.',
                'abbreviation.max' => "Abbreviation shouldn't greater than 20 characters.",
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {

                $data['name'] = $request->name;
                $data['rate'] = $request->rate;
                $data['isActive'] = $request->isActive;
                $data['symbol'] = $request->symbol;
                $data['abbreviation'] = $request->abbreviation;

                if (!isEmpty($data)) {
                    $currencyModel->create($data);
                    return redirect()->back()->with('message', 'Create Provider Config Successfully');
                } else {
                    return redirect()->back()->withErrors('No input found..!!');
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

    public function updateCurrency(Request $request)
    {
        try {
            $currencyModel = new Currency();

            $rules = array(
                'name' => 'required',
                'rate' => 'required',
                'isActive' => 'required',
                'symbol' => 'required',
                'abbreviation' => 'required',
            );

            $messages = array(
                'name.required' => 'Name is required.',
                'rate.required' => 'Rate is required.',
                'isActive.required' => 'Status Active is required.',
                'symbol.required' => 'Symbol is required.',
                'abbreviation.required' => 'Abbreviation is required.',
            );

            $validator = Validator::make($request->toArray(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            } else {

                $data['name'] = $request->name;
                $data['rate'] = $request->rate;
                $data['isActive'] = $request->isActive;
                $data['symbol'] = $request->symbol;
                $data['abbreviation'] = $request->abbreviation;

                if (!isEmpty($data)) {
                    $currencyModel->updateCurrency($request->currencyID, $data);
                    return redirect()->back()->with('message', 'Create Provider Config Successfully');
                } else {
                    return redirect()->back()->withErrors('No input found..!!');
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

    public function deleteCurrency(Request $request)
    {
        $currencyModel = new Currency();

        try {
            $currency = $currencyModel->where('PID', $request->currencyID)->delete();
            if ($currency) {
                return redirect()->back()->with('message', 'Delete Provider Config Successfully');
            }
        } catch (Exception $ex) {
            if (IsAuthEnv()) {
                return redirect()->back()->withErrors(config('constants.default_error_response'));
            } else {
                return redirect()->back()->withErrors($ex->getMessage());
            }
        }
    }

    public function restoreCurrency(Request $request)
    {
        try {
            if ($request->currencyID != '') {
                $currencyData = Currency::withTrashed()->find($request->currencyID)->restore();
                if ($currencyData) {
                    return redirect()->back()->with('message', 'Currency Restored Successfully');
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