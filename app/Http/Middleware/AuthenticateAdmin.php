<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Admin;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ResponseController as Res;
use Illuminate\Support\Facades\DB;


class AuthenticateAdmin
{
    public function handle($request, Closure $next,int $access)
    {
        $adminModel = new Admin;
        if ($request->header()) {
            $userName = app('request')->header('php-auth-user');
            $password = app('request')->header('php-auth-pw');

            if ($userName && $password) {
                $adminData = $adminModel->fetchByUsername($userName);

                if($adminData->count(DB::raw('1')) > 0) {

                    if ($password == Crypt::decrypt($adminData[0]['password'])) {

                        if ($adminData[0]['access'] == 1 || $adminData[0]['access'] == $access) { //allowing only if admin has right to access to APIs (1=both, 2=API)
                            $request->attributes->add(['adminData' => $adminData]);
                            $request->attributes->add(['authUser' => $userName]);
                            $request->attributes->add(['authPassword' => $password]);
                            return $next($request);
                        } else {
                            return response()->json(Res::badRequest([],'Not allowed to access the API'));
                        }

                    } else {
                        return response()->json(Res::notFound([],'Invalid Credentials'));
                    }
                } else {
                    return response()->json(Res::notFound([],'Invalid Credentials'));
                }
            } else {
                return response()->json(Res::notFound([],'Invalid Credentials'));
            }
        } else {
            return response()->json(Res::notFound([],'Invalid Credentials'));
        }

    }
}
