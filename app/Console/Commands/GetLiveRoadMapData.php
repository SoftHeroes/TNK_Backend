<?php

namespace App\Console\Commands;

use App\Events\Socket\GetRoadMapLiveDataEvent;
use App\Http\Controllers\ResponseController as Res;
use App\Models\Stock;
use App\Providers\Stock\StockProvider;
use Exception;
use Illuminate\Console\Command;
use App\Jobs\MailJob;
use Illuminate\Support\Facades\Log;

class GetLiveRoadMapData extends Command
{
    protected $signature = 'socket:roadmap {--loop=} {--limit=300}';
    protected $description = 'Store Road Map Data Into Socket';

    protected $selectedColumn = [
        'stock.PID',
        'stock.name',
        'stock.url',
        'stock.method',
        'stock.country',
        'stock.stockLoop',
        'stock.closeDays',
        'stock.limitTag',
        'stock.openTimeRange',
        'stock.timeZone',
        'stock.precision',
        'stock.liveStockUrl',
        'stock.liveStockResponseType',
        'stock.liveStockOpenTag',
        'stock.liveStockTimeTag',
        'stock.splitString',
        'stock.openValueIndex',
        'stock.dateValueIndex',
        'stock.timeValueIndex',
        'stock.liveStockDataTag',
        'stock.liveStockReplaceJsonRules',
        'stock.responseStockTimeZone',
        'stock.responseStockTimeFormat',
        'stock.UUID as stockUUID',
        'portalProvider.UUID as portalProviderUUID'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $stockLoop = $this->option('loop');
        $limit = $this->option('limit');

        $stockProvider = new StockProvider;
        $allStockData = Stock::getAllStockBaseOnProvider()->select($this->selectedColumn)->where('stock.stockLoop', $stockLoop)->get();

        try {
            foreach ($allStockData  as $key => $value) { // ppd = portal provider data
                $roadMap = $stockProvider->getRoadMap($value->portalProviderUUID, $value->stockUUID, $limit);
                
                if (!$roadMap['status']) {
                    throw new Exception($roadMap["message"]);
                }

                $response = [
                    "channelName" => $value->stockUUID . '.' . $value->portalProviderUUID,
                    "stockName" => $value->name,
                    "roadMap" => $roadMap['data']['roadMap']
                ];
                $response = Res::success($response);
                broadcast(new GetRoadMapLiveDataEvent($response));
            }
        } catch (Exception $e) {
            $msg = 'Error : ' . $e->getMessage() . "\n";
            $msg = $msg . $e->getTraceAsString() . "\n";

            $subject = "ERROR STACK TRACE => JOB ($this->signature) : " . config('app.env');
            $to = config('constants.alert_mail_id');

            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
        }
    }
}
