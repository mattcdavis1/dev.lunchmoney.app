<?php

namespace App\Console\Commands\Transactions;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Models\Transaction;
use Exception;
use DateTime;

class SyncDown extends Command
{
    const API_ENDPOINT = 'https://dev.lunchmoney.app/v1/transactions';
    protected $signature = 'transactions:sync-down';
    protected $description = 'Sync Down Transactions';
    protected $client = null;

    public function handle()
    {
        $client = new Client([
            'timeout'  => 10.0,
        ]);

        $endpoint = self::API_ENDPOINT;
        $i = 0;
        $startDate = '2000-01-01';
        $endDate = date('Y-m-d');
        $limit = 1000;
        $offset = 0;
        $assetId = null;
        $categoryId = null;
        $plaidAccountId = null;
        $recurringId = null;
        $tagId = null;

        do {
            $this->info('Requesting Transactions');

            $response = $client->request('get', $endpoint, [
                'query' => [
                    'start_date' => $startDate,,
                    'end_date' => $endDate,,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
                ],
            ]);

            $json = $response->getBody()->getContents();
            $responseObj = json_decode($json);
            $lmTransactions = $responseObj->transactions;

            foreach ($lmTransactions as $lmTransaction) {
                $i++;

                $transactionId = str_replace('datacode-', '', $lmTransaction->import_id);
                $transaction = Transaction::find($transactionId);
                if ($transaction) {
                    $transaction->lm_amount = $lmTransaction->amount;
                    $transaction->lm_date = $lmTransaction->date;
                    $transaction->lm_id = $lmTransaction->id;
                    $transaction->lm_json = json_encode($lmTransaction);
                    $transaction->save();

                    $this->comment('[' . $i . '] Saved Transaction: ' . $transaction->id . ' (' . $transaction->date_bank_processed . ')');
                } else {
                    $this->error('[' . $i . '] Skipped Transaction: ' . $lmTransaction->id . ' (' . $lmTransaction->date . ')');
                }

                $lastSinceDate = $lmTransaction->date;
            }

            $moreTransactions = !!count($lmTransactions);
        } while ($moreTransactions);
    }
}
