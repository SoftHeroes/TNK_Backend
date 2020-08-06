<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\ResponseController as Res;
use Log;
use Illuminate\Support\Facades\DB;

require_once app_path() . '/Helpers/CommonUtility.php';

class AuthenticateAdminSource
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $adminData = request()->get('adminData');

        if($adminData->count(DB::raw('1')) > 0 ){

            $source = strtoupper($request->header('User-Agent'));

            if( !isEmpty($source) ){

                if( $adminData[0]->source == 1 ){ // All
                    return $next($request);
                }
                elseif( !IsAuthEnv() && strstr($source,'POSTMAN') ){ // Dev
                    return $next($request);
                }
                elseif( $adminData[0]->source == 2 && ( strstr($source,'CHROME') || strstr($source,'MOZILLA') || strstr($source,'OPERA') ) ){ // Web
                    return $next($request);
                }
                elseif( $adminData[0]->source == 3 && ( strstr($source,'IPHONE') || strstr($source,'IPAD') ) ){ // IOS
                    return $next($request);
                }
                elseif( $adminData[0]->source == 4 && strstr($source,'ANDROID') ){ // Android
                    return $next($request);
                }
                else{
                    Log::debug('access denied for user : '.$adminData[0]->username.' ,User-Agent : '.$source);
                    return response()->json(Res::unauthorized([],"Unauthorized access denied"));
                }
            }
            else{
                return response()->json(Res::notFound([],"invalid user agent."));
            }

        }
    }
}
