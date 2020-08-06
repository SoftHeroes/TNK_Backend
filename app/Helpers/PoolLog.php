<?php
require_once app_path() . '/Helpers/CommonUtility.php';

use Exception as Exc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use App\Jobs\MailJob;


/**
 * This function create Pool logs for every pool operation. This log into the "poolLog' table.
 * @return void
 */
function Pool_Log($portalProviderID, $userID, $adminID, $previousBalance, $newBalance, $amount, $balanceType, $operation, $transactionId, $serviceName, $source)
{
    try {

        $rules = array(
            "portalProviderID" => "required|exists:portalProvider,PID",
            "userID" => "nullable|required_if:operation,0|required_if:operation,1|exists:user,PID",
            "adminID" => "nullable|exists:admin,PID",
            "previousBalance" => "required|numeric",
            "newBalance" => "required|numeric",
            "amount" => "required|numeric",
            "balanceType" => "required|alpha_num",
            "operation" => "required|integer",
            "transactionId" => "required|integer",
            "serviceName" => "required",
            "source" => "required|integer",
        );

        $messages = array(
            'portalProviderID.required' => 'portalProviderID is cannot be empty.',
            'portalProviderID.exists' => 'portalProviderID not present in DB.',

            'userID.required_if' => 'userID is cannot be empty.',
            'userID.exists' => 'userID not present in DB.',

            'adminID.exists' => 'adminID not present in DB.',

            'previousBalance.required' => 'previousBalance is cannot be empty.',
            'previousBalance.integer' => 'previousBalance should be an integer.',

            'newBalance.required' => 'newBalance is cannot be empty.',
            'newBalance.integer' => 'newBalance should be an integer.',

            'amount.required' => 'amount is cannot be empty.',
            'amount.integer' => 'amount should be an integer.',

            'balanceType.required' => 'balanceType is cannot be empty.',
            'balanceType.alpha_num' => 'balanceType invalid column name.',

            'operation.required' => 'operation is cannot be empty.',
            'operation.integer' => 'operation should be an integer.',

            'transactionId.required' => 'transactionId is cannot be empty.',
            'transactionId.integer' => 'transactionId should be an integer.',

            'serviceName.required' => 'serviceName is cannot be empty.',
        );

        $validator = Validator::make(
            array(
                "portalProviderID" => $portalProviderID,
                "userID" => $userID,
                "adminID" => $adminID,
                "previousBalance" => $previousBalance,
                "newBalance" => $newBalance,
                "amount" => $amount,
                "balanceType" => $balanceType,
                "operation" => $operation,
                "transactionId" => $transactionId,
                "serviceName" => $serviceName,
                "source" => $source,
            ),
            $rules,
            $messages
        );

        if ($validator->fails()) {
            throw new Exc(associativeArrayToHtmlString($validator->errors()->toArray()));
        }

        DB::beginTransaction();
        DB::table('poolLog')->insert(
            [
                'portalProviderID' => $portalProviderID,
                'userID' => $userID,
                'adminID' => isEmpty($adminID) ? 0 : $adminID,
                'previousBalance' => $previousBalance,
                'newBalance' => $newBalance,
                "amount" => $amount,
                'balanceType' => $balanceType,
                'operation' => $operation,
                'transactionId' => $transactionId,
                'serviceName' => $serviceName,
                'source' => $source,
                "UUID" => Uuid::uuid4(),
            ]
        );

        DB::commit();
    } catch (Exc $e) {
        DB::rollback();

        Log::debug("Pool Log Error : " . $e->getMessage());
        $to = config('constants.alert_mail_id');
        $msg = 'Error : ' . $e->getMessage() . "\n---------------\n";
        $msg = $msg . $e->getTraceAsString() . "\n";

        $subject = "Pool Log has caught an exception : " . config('app.env');
        MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
        throw $e;
    }
}
