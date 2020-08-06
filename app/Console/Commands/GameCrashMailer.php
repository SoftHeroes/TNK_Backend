<?php
/* @class GameCrashMailer
 * @author Piyush
 * @type: Console command
 * @description: Send e-mail developer when the game status didn't updated after trying several time. */

namespace App\Console\Commands;

use App\Jobs\MailJob;
use App\Models\Game;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GameCrashMailer extends Command
{
    protected $signature = 'crawler:gameCrashMailer {--d|debug}';

    protected $description = "Send e-mail developer when the game status didn't updated after trying several time.";

    public function __construct()
    {
        parent::__construct();
    }

    // Private functions - For internal usage of the class only.
    private function boot($debug)
    {
        // Checking every single game status 4 crashed game.
        $model = new Game();
        $games = $model->select('PID', 'stockId', 'error')->where('gameStatus', 4)->get();
        $progress = $this->output->createProgressBar(count($games));
        $progress->setFormat('debug');
        $infected = $mail = array();
        $mail['message'] = 'Total Error: ' . count($games) . "\r\n";
        $this->info('Updating entries and sending mail.');

        // Starting up journey.
        $progress->start();
        foreach ($games as $game) {
            $progress->advance();
            if ($debug) {
                $this->info(print_r($game));
            }
            $infected[] = $game->PID;
            $mail['message'] .= 'Game PID: ' . $game->PID . ', Stock ID: ' . $game->stockID . "\n\r";
            $mail['message'] .= 'error' . $game->error . "\n\r";
        }
        $progress->finish('Completed!');
        $this->info(' Processing mail.');

        // Mail configuration.
        $mail['subject'] = "ERROR STACK TRACE => JOB ($this->signature) : " . config('app.env');
        $mail['to'] = config('constants.alert_mail_id');
        $mail['from'] = trim(config('constants.mail_from_address'));

        // Update database of leftover entries.
        if (MailJob::dispatch($mail['to'], $mail['message'], $mail['subject'])->onQueue('medium')) {
            DB::table('game')->whereIn('PID', $infected)->update(['gameStatus' => 2, 'error' => null]);
        }
    }

    // Public section - Script initialization begin.
    public function handle()
    {
        $debug = $this->option('debug');
        $START_TIME = microtime(true);
        try {
            $this->info('Fetching crashed games.');
            $this->boot($debug);
            if ($debug) {
                $this->info('---[ SCRIPT EXECUTION COMPLETED | TAKEN TIME: ' . (microtime(true) - $START_TIME) . ' ]---');
            }
        } catch (Exception $e) {
            $errorInfo = 'Error : ' . $e->getMessage() . "\n";
            $errorInfo = $errorInfo . $e->getTraceAsString() . "\n";

            Log::debug($errorInfo);
        }
    }
}
