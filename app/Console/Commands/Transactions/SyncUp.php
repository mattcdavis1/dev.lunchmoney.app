<?php

namespace App\Console\Commands\Transactions;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Models\Transaction;
use GuzzleHttp\Exception\RequestException;
use Exception;

class SyncUp extends Command
{
    const API_ENDPOINT = 'https://dev.lunchmoney.app/v1/transactions';
    protected $client;
    protected $signature = 'transactions:sync-up
        {--category_ids=}
        {--vendor_ids=}
        {--id=}
        {--limit=10}
        {--mode=post}
    ';
    protected $description = 'Sync Up Transactions';

    public function handle()
    {
        $options = $this->options();
        $mode = $options['mode'];
        $this->client = new Client([
            'timeout'  => 500.0,
        ]);

        $query = Transaction::orderBy('transactions.date_bank_processed', 'ASC');

        if ($mode == 'post') {
            $query->whereNull('lm_id');
        } else {
            $query->whereNotNull('lm_id');
        }

        if ($_categoryIds = $options['category_ids']) {
            $categoryIds = explode(',', $_categoryIds);
            $query->whereIn('category_id', $categoryIds);
        }

        if ($_vendorIds = $options['category_ids']) {
            $vendorIds = explode(',', $_vendorIds);
            $query->whereIn('category_id', $vendorIds);
        }

        if ($id = $options['id']) {
            $query->where('id', $id);
        }

        if ($limit = $options['limit']) {
            $query->limit($limit);
        }

        $numRecords = 1;

        $query->chunk(1000, function($transactions) use ($mode, &$numRecords) {
            $lmTransactions = [];

            foreach ($transactions as $transaction) {
                $numRecords++;

                $lmTransaction = $transaction->toLunch();
                $lmTransactions[] = $lmTransaction;
                $this->comment('[' . $numRecords . '] Adding: ' . $transaction->id . '::' . $lmTransaction['account_id'] . '::' . $lmTransaction['category_id'] . ' (' . $transaction->date_bank_processed . ')');
            }

            $this->info('Posting / Patching Data');

            $response = $this->post($mode, $transactions);

            foreach ($response->transactions as $lmTransaction) {
                $this->comment('Saving lm Transaction: ' . $lmTransaction->id);

                $transaction->fill((array) $lmTransaction);

                try {
                    $transaction->lm_json = json_encode($lmTransaction);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }

                $transaction->save();
            }

            sleep(3);
        });

        return 1;
    }

    protected function request($mode, $lmTransactions)
    {
        try {
            $response = $this->client->request($mode, self::API_ENDPOINT, [
                'json' => ['transactions' => $lmTransactions ],
                'headers' => [
                    'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
                ],
            ]);
        } catch (RequestException $e) {
            $this->error($e->getMessage());
        }

        $json = $response->getBody()->getContents();
        $responseObj = json_decode($json);

        return $responseObj;
    }
}
