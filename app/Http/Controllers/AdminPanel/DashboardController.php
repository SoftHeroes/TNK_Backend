<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;


require_once app_path() . '/Helpers/CommonUtility.php';


class DashboardController extends Controller
{
    public function loadDashboard(Request $request)
    {

        try {

            return view('adminPanel.dashboard');

        } catch (Exception $e) {

            return redirect()->back()->withErrors(['Something went wrong, ']);
            //log error message $e->getMessage();

        }
    }
}
