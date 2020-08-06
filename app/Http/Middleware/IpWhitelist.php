<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ResponseController as Res;
use App\Models\IpWhitelist as AppIpWhitelist;
use Closure;

class IpWhitelist
{
    public function handle($request, Closure $next)
    {
        if (IsAuthEnv()) {
            $isWhitelistIp = AppIpWhitelist::get()->pluck('IP');
            $clientIp = $request->ip();
            if (!in_array($clientIp, $isWhitelistIp->all())) {
                return response()->json(Res::unauthorized());
            }
        }
        return $next($request);
    }
}
