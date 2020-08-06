<?php

namespace App\Console\Commands;

use DateTime;
use Exception;
use DateTimeZone;
use DateInterval;
use App\Models\Game;
use App\Models\Stock;
use Ramsey\Uuid\Uuid;
use Illuminate\Console\Command;
use App\Models\ProviderGameSetup;
use Illuminate\Support\Facades\DB;
use App\Providers\Payout\GamePayouts;
use App\Jobs\MailJob;
use App\Models\HolidayList;
use App\Models\DynamicOdd;

require_once app_path() . '/Helpers/CommonUtility.php';

class CreateGames extends Command
{

    protected $providerGameSetupModelRef;           // game Model Reference
    protected $gamePayoutsProviderRef;              // game payouts Provider Reference
    protected $maxGamePID;
    protected $maxDynamicOddPID;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timer:createGames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command will get all open stock and create game based on them.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->providerGameSetupModelRef = new ProviderGameSetup();
        $this->gamePayoutsProviderRef    = new GamePayouts();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        try {

            $providerGames = $this->providerGameSetupModelRef->select(['PID', 'portalProviderID', 'stockID'])->where('isActive', 'active')->get();     // getting all provider setup

            $games = array();

            $createdDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');
            $createdTime = microtimeToDateTime(getCurrentTimeStamp(), false, 'H:i:s');

            for ($providerGameIndex = 0; $providerGameIndex < $providerGames->count(DB::raw('1')); $providerGameIndex++) {

                // Verifying is stock is open for next date
                $stockSet = Stock::getStockDetails($providerGames[$providerGameIndex]->stockID)->select(['PID', 'timeZone', 'closeDays', 'openTimeRange', 'stockLoop', 'betCloseSec'])->get();

                // setting time zone of stock
                if ($stockSet->count(DB::raw('1')) > 0) {

                    $getNextDayDateTimeBasedOnStockTimeZone = new DateTime('now +1 day', new DateTimeZone($stockSet[0]->timeZone));
                    $dayOfWeek = date('w', $getNextDayDateTimeBasedOnStockTimeZone->getTimestamp()); 

                    $getHolidayList = HolidayList::holidayListChecker($providerGames[$providerGameIndex]->stockID,$getNextDayDateTimeBasedOnStockTimeZone->format('Y-m-d'));
                    
                    // check if tomorrow is a holiday or not
                    if($getHolidayList->count(DB::raw('1')) < 1){

                    if (!in_array($dayOfWeek, explode(",", $stockSet[0]->closeDays))) {

                        // code for create Games : START
                        if (!isEmpty($stockSet[0]->openTimeRange)) { // creating games for specified time interval

                            $allOpenTimeInterval = explode(',', $stockSet[0]->openTimeRange);

                            foreach ($allOpenTimeInterval as $singleTimeInterval) {
                                $result = explode('-', $singleTimeInterval);

                                if (count($result) != 2) {
                                    throw new ValidationError('Invalid open Time Range in DB');
                                }

                                $startTimestamp = clone $getNextDayDateTimeBasedOnStockTimeZone;
                                $endTimestamp = clone $getNextDayDateTimeBasedOnStockTimeZone;

                                $temp = explode(':', $result[0]);
                                $startTimestamp->setTime($temp[0], $temp[1], 0, 0);

                                $temp = explode(':', $result[1]);
                                $endTimestamp->setTime($temp[0], $temp[1], 0, 0);

                                $intervalDiff = $startTimestamp->diff($endTimestamp);
                                $intervalInMin = $intervalDiff->h * 60;
                                $intervalInMin += $intervalDiff->i;

                                $endTimestamp = clone $startTimestamp;

                                $gameTimeInterval = (int) $stockSet[0]->stockLoop;
                                $betCloseSec = (int) $stockSet[0]->betCloseSec;

                                $startTimestamp->setTimezone(new DateTimeZone(config('app.timezone')));     // Converting stock timezone to App timezone
                                $endTimestamp->setTimezone(new DateTimeZone(config('app.timezone')));       // Converting stock timezone to App timezone

                                for ($i = 0; $i < $intervalInMin / $gameTimeInterval; $i++) {

                                    $endTimestamp->add(new DateInterval('PT' . $gameTimeInterval . 'M'));   // calculating game end time

                                    $betCloseTime = clone $endTimestamp;
                                    $betCloseTime->sub(new DateInterval('PT' . $betCloseSec . 'S'));        // calculating bet close time

                                    // creating Game : Start
                                    $singleGame = array(
                                        'portalProviderID' => $providerGames[$providerGameIndex]->portalProviderID,
                                        'providerGameSetupID' => $providerGames[$providerGameIndex]->PID,
                                        'stockID' => $providerGames[$providerGameIndex]->stockID,
                                        'startDate' => $startTimestamp->format('Y-m-d'),
                                        'startTime' => $startTimestamp->format('H:i:s'),
                                        'endDate' => $endTimestamp->format('Y-m-d'),
                                        'endTime' => $endTimestamp->format('H:i:s'),
                                        'betCloseTime' => $betCloseTime->format('Y-m-d H:i:s'),
                                        'createdDate' => $createdDate,
                                        'createdTime' => $createdTime,
                                        'UUID' => Uuid::uuid4(),
                                    );
                                    // creating Game : end

                                    array_push($games, $singleGame); // Adding Game into Array

                                    $startTimestamp->add(new DateInterval('PT' . $gameTimeInterval . 'M'));
                                }
                            }
                        } else { // creating games for all day

                            $gameTimeInterval = (int) $stockSet[0]->stockLoop;
                            $betCloseSec = (int) $stockSet[0]->betCloseSec;

                            $startDateTime = clone $getNextDayDateTimeBasedOnStockTimeZone;
                            $endDateTime = clone $getNextDayDateTimeBasedOnStockTimeZone;

                            $startDateTime->setTime(0, 0, 0, 0);
                            $endDateTime->setTime(0, 0, 0, 0);

                            $startDateTime->setTimezone(new DateTimeZone(config('app.timezone')));      // Converting stock timezone to App timezone
                            $endDateTime->setTimezone(new DateTimeZone(config('app.timezone')));        // Converting stock timezone to App timezone

                            for ($i = 0; $i < 1440 / $gameTimeInterval; $i++) {

                                $endDateTime->add(new DateInterval('PT' . $gameTimeInterval . 'M'));    // calculating game end time

                                $betCloseTime = clone $endDateTime;
                                $betCloseTime->sub(new DateInterval('PT' . $betCloseSec . 'S'));        // calculating bet close time

                                // creating Game : Start
                                $singleGame = array(
                                    'portalProviderID' => $providerGames[$providerGameIndex]->portalProviderID,
                                    'providerGameSetupID' => $providerGames[$providerGameIndex]->PID,
                                    'stockID' => $providerGames[$providerGameIndex]->stockID,
                                    'startDate' => $startDateTime->format('Y-m-d'),
                                    'startTime' => $startDateTime->format('H:i:s'),
                                    'endDate' => $endDateTime->format('Y-m-d'),
                                    'endTime' => $endDateTime->format('H:i:s'),
                                    'betCloseTime' => $betCloseTime->format('Y-m-d H:i:s'),
                                    'createdDate' => $createdDate,
                                    'createdTime' => $createdTime,
                                    'UUID' => Uuid::uuid4(),
                                );
                                // creating Game : end

                                array_push($games, $singleGame); // Adding Game into Array

                                $startDateTime->add(new DateInterval('PT' . $gameTimeInterval . 'M'));
                            }
                        }
                        // code for create Games : END
                    }
                }
                }
            }

            if ($providerGames->count(DB::raw('1')) > 0) {

                $maxPID = Game::max('PID'); // get maxPID
                $this->maxGamePID = $maxPID == null ? 0 : $maxPID; // if maxPID is null will throw error need to set with 0 

                DB::beginTransaction();

                foreach (array_chunk($games, config('constants.create_game_chunk')) as $gamesChunk) {
                    Game::insert($gamesChunk);
                }

                DB::commit();

                $maxDynamicOddPID = DynamicOdd::max('PID'); // get maxPID
                $this->maxDynamicOddPID = $maxDynamicOddPID == null ? 0 : $maxDynamicOddPID; // if maxDynamicOddPID is null will throw error need to set with 0 
                
                $tempResponse = $this->gamePayoutsProviderRef->populateDynamicOddInitially();
                
                if ($tempResponse['error']) {
                    throw new Exception($tempResponse["msg"]);
                }

                dd('games created successfully.');
            } else {
                dd('provider game Setup not found.');
            }
        } catch (Exception $e) {
            
            DB::rollback();
            
            $msg = 'Error : ' . $e->getMessage() . "\n";
            $msg = $msg . $e->getTraceAsString() . "\n";
            
            $subject = "ERROR STACK TRACE => JOB ($this->signature) : " . config('app.env');
            $to = config('constants.alert_mail_id');
            
            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
            
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Game::where('PID','>',$this->maxGamePID)->delete();
            DynamicOdd::where('PID','>',$this->maxDynamicOddPID)->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
