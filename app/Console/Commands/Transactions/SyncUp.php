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
        {--limit=10000}
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

        $query = Transaction::whereNotIn('type', ['starting-balance'])
            ->orderBy('transactions.date_bank_processed', 'ASC');

        if ($mode == 'post') {
            $query->whereNull('transactions.lm_id');
        } else {
             $query->whereNotNull('transactions.lm_id');
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

        $limit = $options['limit'];
        $numRecords = 1;

        $query->chunk(500, function($transactions) use ($mode, $limit, &$numRecords) {
            $lmTransactions = [];

            foreach ($transactions as $transaction) {
                $numRecords++;

                $lmTransaction = $transaction->toLunch($mode);

                $lmTransactions[] = $lmTransaction;
                $this->comment('[' . $numRecords . '] Adding: ' . $transaction->id . '::' . $lmTransaction['date'] . '::' . $lmTransaction['payee'] . ' (' . $lmTransaction['notes'] . ')');
            }

            $this->info('Posting / Patching Data');

            if ($mode == 'post') {
                $response = $this->post($lmTransactions);

                foreach ($response->ids as $index => $id) {
                    $transaction = $transactions[$index];
                    $transaction->lm_id = $id;
                    $transaction->save();
                }
            } else {
                foreach ($lmTransactions as $lmTransaction) {
                    $this->put($lmTransaction);
                }
            }

            if ($numRecords >= $limit) {
                return false;
            }

            sleep(3);
        });

        return 1;
    }

    protected function post($lmTransactions)
    {
        try {
            $response = $this->client->request('post', self::API_ENDPOINT, [
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

    protected function put($lmTransaction)
    {
        try {
            $response = $this->client->request('put', self::API_ENDPOINT . '/' . $lmTransaction['id'], [
                'json' => $lmTransaction,
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
