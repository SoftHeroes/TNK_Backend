<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ResponseController as Res;
use Closure;

require_once app_path() . '/Helpers/CommonUtility.php';

class IpServerChecker
{
    public function handle($request, Closure $next)
    {
        if (IsAuthEnv()) {
            $clientIp = $request->ip(); // get client IP
            $clientServerName = parse_url(request()->headers->get('referer'), PHP_URL_HOST); // get client server name

            $adminData = $request->get('adminData')[0]; // get auth Data
            $ppdIp = explode(',', $adminData->ipList); // get authenticated admin ip list
            $ppdServerName = $adminData->server; // get authenticated server name

            // if server name or ip is valid
            if (!in_array($clientIp, $ppdIp) && (isEmpty($ppdServerName) || strtoupper($ppdServerName) != strtoupper($clientServerName))) {
                return response()->json(Res::unauthorized());
            }
        }
        return $next($request);
    }
}
