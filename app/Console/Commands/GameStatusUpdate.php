<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Game;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\MailJob;
use App\Providers\Stock\StockProvider;
use App\Models\PortalProvider;
use App\Events\Socket\GetActiveGamesByCategory;
use App\Events\Backend\BetCountFromStatusUpdateEvent;
use App\Events\Backend\TotalBetCountFromStatusUpdateEvent;



require_once app_path() . '/Helpers/CommonUtility.php';

class GameStatusUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'while:GameStatusUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will runs in infinity loop and update all games status for open and for closing';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $number_of_attempts =  config('app.number_of_game_status_update_attempts');
        $attempts = 0;

        $count = 0;
        while (true) {
            try {
                DB::beginTransaction();

                $currentDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');
                $currentTime = microtimeToDateTime(getCurrentTimeStamp(), false, 'H:i:s');
                $nextDate = microtimeToDateTime(strtotime($currentDate . ' +1 day'), false, 'Y-m-d');

                $openGame = Game::where('startDate', $currentDate)
                    ->where('startTime', '<=', $currentTime)
                    ->Where(function ($query) use ($currentTime) {
                        $query->where('endTime', '>', $currentTime)
                            ->orWhere('endTime', '00:00:00');
                    })->where(function ($query) use ($currentDate, $nextDate) {
                        $query->where('endDate', $currentDate)->orWhere('endDate', $nextDate);
                    })->where('gameStatus', 0);

                $gameUUID = $openGame->select('UUID')->get()->toArray();
                if (count($gameUUID) == 0) {
                    $gamesUpdatedAsOpen = 0;
                } else {
                    $gamesUpdatedAsOpen = $openGame->update(['gameStatus' => 1]);
                }

                $currentDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d H:i:s');
                $gamesUpdatedAsClose = Game::where('betCloseTime', '<=', $currentDate)->whereIn('gameStatus', [0, 1])->update(['gameStatus' => 2]);

                $totalNumberOfRowUpdated = $gamesUpdatedAsOpen + $gamesUpdatedAsClose;

                DB::commit();

                //calling getLiveBetCount when new game is opened
                if (count($gameUUID) > 0) {
                    $gameUUID = array_column($gameUUID, 'UUID');
                    foreach ($gameUUID as $UUID) {
                        $gameModel = new Game();
                        $providerData = $gameModel->getProviderUUIDByGameUUID($UUID)->select('portalProvider.UUID as UUID', 'stock.UUID as stockUUID', 'stock.stockLoop')->get();
                        if ($providerData->count(DB::raw('1')) > 0) {
                            $providerUUID = $providerData[0]->UUID;
                            event(new BetCountFromStatusUpdateEvent($UUID, $providerUUID, $providerData[0]->stockUUID, $providerData[0]->stockLoop));
                            event(new TotalBetCountFromStatusUpdateEvent($UUID, $providerUUID, $providerData[0]->stockUUID, $providerData[0]->stockLoop));
                        } else {
                            $msg = 'Error : BetCountFromStatusUpdateEvent not called';
                            $msg = $msg . "\n Game UUID: " . $UUID;
                            $subject = "ERROR STACK TRACE => JOB ($this->signature) : " . config('app.env');
                            $to = config('constants.alert_mail_id');
                            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
                        }
                    }
                }

                // calling getActiveGamesByCategory Socket
                if ($totalNumberOfRowUpdated > 0) {

                    $PortalProviderModel = new PortalProvider();
                    $portalProviderUUIDData = $PortalProviderModel->getPortalProviders()->select('UUID')->get();

                    foreach ($portalProviderUUIDData as $portalProviderUUID) {

                        $provider = new StockProvider(null);
                        $response = $provider->getActiveGamesByCategory($portalProviderUUID->UUID);

                        if ($response['exceptionMsg'] == null && $response['res']['status']) {
                            $attempts = 0;

                            broadcast(new GetActiveGamesByCategory($response, $portalProviderUUID->UUID));
                        }
                    }
                }

                // Sleep Code : START
                if ($totalNumberOfRowUpdated == 0) {
                    if ($count <= 0) {
                        sleep(4);
                    } else if ($count <= 1) {
                        sleep(3);
                    } else if ($count <= 2) {
                        sleep(2);
                    } else if ($count <= 3) {
                        sleep(2);
                    } else if ($count >= 4) {
                        sleep(1);
                    }
                    $count += 1;
                } else {
                    $count = 0;
                }
                // Sleep Code : END

            } catch (Exception $e) {
                DB::rollback();

                if ($attempts == $number_of_attempts) {
                    $msg = 'Error : ' . $e->getMessage() . "\n";
                    $msg = $msg . $e->getTraceAsString() . "\n";
                    $subject = "ERROR STACK TRACE => JOB ($this->signature) : " . config('app.env');
                    $to = config('constants.alert_mail_id');
                    MailJob::dispatch($to, $msg, $subject)->onQueue('medium');

                    $attempts = 0;
                } else {
                    $attempts++;
                }
            }
        }
    }
}
