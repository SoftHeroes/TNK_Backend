<?php

namespace App\Console\Commands;

use DateTime;
use Exception;
use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Jobs\MailJob;
use App\Providers\Stock\StockProvider;
use Illuminate\Support\Facades\Log;

class StockBackup extends Command
{
    protected $signature = 'crawler:stockBackup {--loop=}';

    protected $description = 'Stock Backup Data Crawler API';

    protected $selectedColumn = [
        'PID',
        'UUID',
    ];

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
        try {
            $stockLoop = $this->option('loop');
            $limit = 2000;

            $stock = new Stock();
            $allStockData = $stock->select($this->selectedColumn)->where('stockLoop', $stockLoop)->get();

            $createdDate = microtimeToDateTime(getCurrentTimeStamp(), false, 'Y-m-d');
            $createdTime = microtimeToDateTime(getCurrentTimeStamp(), false, 'H:i:s');

            $serviceProvider = new StockProvider;

            foreach ($allStockData as $key => $value) { // ppd = portal provider data
                $stock = $serviceProvider->getStockOnly($value->UUID, $limit);

                if (!$stock['status']) {
                    throw new Exception($stock["message"]);
                }
                // check select yesterday
                $yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));

                foreach ($stock['data'] as $key => $stockValue) {
                    $date = new DateTime($stockValue['stockTimestamp']);
                    // check select data yesterday
                    if ($yesterday == $date->format('Y-m-d')) {
                        $response[] = [
                            "stockID" => $value->PID,
                            "stockValue" => $stockValue['stockValue'],
                            "stockTimestamp" => $stockValue['stockTimestamp'],
                            "createdDate" => $createdDate,
                            "createdTime" => $createdTime,
                        ];
                    }
                }
            }

            $response = array_map("unserialize", array_unique(array_map("serialize", $response)));

            // insert Data backup Stock History
            StockHistory::insert($response);

        } catch (Exception $e) {
            $msg = 'Error : ' . $e->getMessage() . "\n";
            $msg = $msg . $e->getTraceAsString() . "\n";

            $subject = "ERROR STACK TRACE => JOB ($this->signature) : " . config('app.env');
            $to = config('constants.alert_mail_id');

            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
        }
    }
}
