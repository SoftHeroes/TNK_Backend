<?php

namespace App\Providers;


use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\Backend\PostBetPlacedEvent::class => [
            \App\Listeners\Backend\FollowBetsListener::class,                   // Copy follow bets
            \App\Listeners\Backend\DynamicPayoutListener::class,                // Calculate dynamic odds values
            \App\Listeners\Socket\DynamicOddDataListener::class,                // Broadcast dynamic odds values
            \App\Listeners\Socket\LiveBetCountListener::class,                  // Broadcast live bet counts
            \App\Listeners\Socket\GetLiveCountBetDataListener::class,           // Broadcast live total bet, amount, user
            \App\Listeners\Backend\AutomaticallyUnfollowListener::class,        // Automatically Unfollow
        ],
        \App\Events\Backend\PoolLogEvent::class => [
            \App\Listeners\Backend\PoolLogListener::class
        ],
        \App\Events\Backend\BetCountFromStatusUpdateEvent::class => [           // When called from gameStatusUpdate
            \App\Listeners\Socket\LiveBetCountListener::class                   // Broadcast live bet counts
        ],
        \App\Events\Backend\TotalBetCountFromStatusUpdateEvent::class => [      // When called from gameStatusUpdate
            \App\Listeners\Socket\GetLiveCountBetDataListener::class            // Broadcast total live bet counts
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
