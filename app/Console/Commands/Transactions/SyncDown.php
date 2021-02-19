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
    const API_ENDPOINT = 'https://api.youneedabudget.com/v1/budgets';
    protected $signature = 'transactions:sync-down';
    protected $description = 'Sync Up Categories';
    protected $client = null;

    public function handle()
    {
        $client = new Client([
            'timeout'  => 500.0,
        ]);
        $endpoint = self::API_ENDPOINT . '/' . env('ACCOUNT_ID') . '/transactions';
        $i = 0;
        $lastSinceDate = '2000-01-01';

        do {
            $sinceDate = (new DateTime($lastSinceDate))->modify('+ 1 day');

            $this->info('Requesting Transactions');

            $response = $client->request('get', $endpoint, [
                'query' => ['since_date' => $sinceDate->format('Y-m-d')],
                'headers' => [
                    'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
                ],
            ]);
            $json = $response->getBody()->getContents();
            $responseObj = json_decode($json);
            $ynabTransactions = $responseObj->data->transactions;

            foreach ($ynabTransactions as $ynabTransaction) {
                $i++;
                $transactionId = str_replace('365budget-', '', $ynabTransaction->import_id);
                $transaction = Transaction::find($transactionId);
                if ($transaction) {
                    $transaction->ynab_id = $ynabTransaction->id;
                    $transaction->ynab_json = json_encode($ynabTransaction);
                    $transaction->save();

                    $this->comment('[' . $i . '] Saved Transaction: ' . $transaction->id . ' (' . $transaction->date_bank_processed . ')');
                } else {
                    $this->error('[' . $i . '] Skipped Transaction: ' . $ynabTransaction->id . ' (' . $ynabTransaction->date . ')');
                }

                $lastSinceDate = $ynabTransaction->date;
            }

            $moreTransactions = !!count($ynabTransactions);
        } while ($moreTransactions);
    }
}
