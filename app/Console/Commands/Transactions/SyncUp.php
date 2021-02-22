<?php

namespace App\Console\Commands\Transactions;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Models\Transaction;
use GuzzleHttp\Exception\RequestException;
use Exception;
use Illuminate\Support\Arr;

class SyncUp extends Command
{
    const API_ENDPOINT = 'https://dev.lunchmoney.app/v1/transactions';
    protected $client;
    protected $signature = 'transactions:sync-up
        {--category_ids=}
        {--vendor_ids=}
        {--account_ids=2}
        {--plaid_account_ids=}
        {--id=}
        {--limit=10000}
        {--mode=put}
    ';
    protected $description = 'Sync Up Transactions';

    public function handle()
    {
        die();
        $options = $this->options();
        $mode = $options['mode'];
        $this->client = new Client([
            'timeout'  => 500.0,
        ]);

        $query = Transaction::whereNotIn('type', ['starting-balance'])
            ->orderBy('transactions.date', 'ASC')
            ->select('transactions.*')
            ->where('type', 'expense')
            ->where('memo', 'LIKE', '%check%');

        if ($mode == 'post') {
            $query->whereNull('transactions.lm_id');
        } else {
             $query->whereNotNull('transactions.lm_id');
        }

        if ($_plaidAccountIds = $options['plaid_account_ids']) {
            $plaidAccountIds = explode(',', $_plaidAccountIds);
            $query->whereIn('lm_plaid_account_id', $plaidAccountIds);
        }

        if ($_accountIds = $options['account_ids']) {
            $accountIds = explode(',', $_accountIds);
            $query->whereIn('account_id', $accountIds);
        }

        if ($_categoryIds = $options['category_ids']) {
            $categoryIds = explode(',', $_categoryIds);
            $query->whereIn('category_id', $categoryIds);
        }

        if ($_vendorIds = $options['vendor_ids']) {
            $vendorIds = explode(',', $_vendorIds);
            $query->whereIn('vendor_id', $vendorIds);
        }

        if ($id = $options['id']) {
            $query->where('id', $id);
        }

        $limit = (int) $options['limit'];
        $numRecords = 1;

        $query->chunk(500, function($transactions) use ($mode, $limit, &$numRecords) {
            $lmTransactions = [];

            foreach ($transactions as $transaction) {
                $numRecords++;

                // $splits = $transaction->splits;
                // if (!count($splits)) {
                //     continue;
                // }

                $lmTransaction = $transaction->toLunch($mode);

                $lmTransactions[] = $lmTransaction;
                $this->comment('[' . $numRecords . '] Adding: ' . $transaction->id . '::' . ($lmTransaction['date'] ?? $transaction->date) . '::' . $lmTransaction['payee'] . ' (' . $lmTransaction['notes'] . ')');
            }

            if ($mode == 'post') {
                $this->info('POST-ing');
                $response = $this->post($lmTransactions);

                foreach ($response->ids as $index => $id) {
                    $transaction = $transactions[$index];
                    $transaction->lm_id = $id;
                    $transaction->save();
                }
            } else {
                foreach ($lmTransactions as $lmTransaction) {
                    $this->comment('PUT-ting: ' . $lmTransaction['id'] . ':' . ($lmTransaction['date'] ?? 'No Date for Plaid'));
                    $response = $this->put($lmTransaction);
                    $responseSplits = $response->split ?? [];

                    if ($responseSplits) {
                        $transaction = Transaction::where('lm_id', $lmTransaction['id'])->first();
                        $splits = $transaction->splits;

                        foreach ($splits as $index => $split) {
                            $split->lm_id = $responseSplits[$index] ?? null;
                            $split->save();
                        }
                    }
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
                'json' => [ 'transactions' => $lmTransactions ],
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

    protected function put($_lmTransaction)
    {
        $lmTransaction = Arr::except($_lmTransaction, ['id']);

        try {
            $response = $this->client->request('put', self::API_ENDPOINT . '/' . $_lmTransaction['id'], [
                'json' => ['transaction' => $lmTransaction ],
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
