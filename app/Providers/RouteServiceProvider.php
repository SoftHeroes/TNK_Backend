<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapAppApiRoutes();

        $this->mapAdminRoutes();

        $this->mapDashboardRoutes();

        $this->mapBetHistoryRoutes();

        $this->mapStockRoutes();

        $this->mapAdminSettingsRoutes();

        $this->mapGameHistoryRoutes();

        $this->mapUserDetailsRoutes();

        $this->mapWebApiRoutes();

        $this->mapProviderRoutes();

        $this->mapChannelsRoutes();

        $this->mapLogRoutes();

        $this->mapExposeApiRoutes();

        $this->mapAdminPolicyRoutes();

        $this->mapAdminInformationRoutes();

        $this->mapAccessPolicyRoutes();

        $this->mapCurrencyRoutes();

        $this->mapFollowConfigRoutes();

        $this->mapNotificationRoutes();

        $this->mapHolidayListRoutes();
        
        $this->mapInvitationSetupRoutes();

        
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }


    /**
     * Define the "exposeApi" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapExposeApiRoutes()
    {
        Route::prefix('exposeApi')
            ->middleware('exposeApi')
            ->namespace($this->namespace)
            ->group(base_path('routes/exposeApi.php'));
    }

    /**
     * Define the "appApi" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapAppApiRoutes()
    {
        Route::prefix('appApi')
            ->middleware('appApi')
            ->namespace($this->namespace)
            ->group(base_path('routes/appApi.php'));
    }

    protected function mapAdminRoutes()
    {
        Route::prefix('admin')
            ->middleware('loginCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/admin.php'));
    }

    protected function mapDashboardRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/dashboard.php'));
    }

    protected function mapBetHistoryRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/betHistory.php'));
    }

    protected function mapStockRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/stock.php'));
    }

    protected function mapAdminSettingsRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/adminSettings.php'));
    }

    protected function mapGameHistoryRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/gameHistory.php'));
    }

    protected function mapUserDetailsRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/user.php'));
    }

    protected function mapProviderRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/provider.php'));
    }


    protected function mapWebApiRoutes()
    {
        Route::prefix('webApi')
            ->middleware('webApi')
            ->namespace($this->namespace)
            ->group(base_path('routes/webApi.php'));
    }


    protected function mapChannelsRoutes()
    {
        Route::prefix('channels')
            ->middleware('channels')
            ->namespace($this->namespace)
            ->group(base_path('routes/channels.php'));
    }

    protected function mapLogRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/log.php'));
    }

    protected function mapAdminPolicyRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/adminPolicy.php'));
    }

    protected function mapAdminInformationRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/adminInformation.php'));
    }

    protected function mapAccessPolicyRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/accessPolicy.php'));
    }
    
    protected function mapCurrencyRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/currency.php'));
    }

    protected function mapFollowConfigRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/followConfig.php'));
    }

    protected function mapNotificationRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/notification.php'));
    }

    protected function mapHolidayListRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/holidayList.php'));
    }

    protected function mapInvitationSetupRoutes()
    {
        Route::prefix('admin')
            ->middleware('dashboardCheck')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminPanel/invitationSetup.php'));
    }

    
    
}
