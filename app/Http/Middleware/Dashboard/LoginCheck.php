<?php

namespace App\Http\Middleware\Dashboard;

require_once app_path() . '/Helpers/CommonUtility.php';

use Log;
use Closure;
use Exception;

class LoginCheck
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
        try {
            $value = session(str_replace(".", "_", $request->ip()) . 'ECGames');
            if (!isEmpty($value) && !isEmpty($value['portalProviderUUID'])) {
                return redirect()->route('vDashboard');
            } else {
                return $next($request);
            }
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
