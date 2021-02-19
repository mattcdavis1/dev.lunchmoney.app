<?php

namespace App\Console\Commands\Transactions;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Models\Transaction;
use Exception;

class SyncUp extends Command
{
    const API_ENDPOINT = 'https://api.youneedabudget.com/v1/budgets';
    protected $signature = 'transactions:sync-up';
    protected $description = 'Sync Up Categories';
    protected $client = null;

    public function handle()
    {
        $client = new Client([
            'timeout'  => 500.0,
        ]);

        $accountIds = [];
        $categoryIds = [];
        $untilDate = '';
        $vendorIds = [];

        $query = Transaction::whereNull('ynab_id')
            ->where('user_id', 1)
            ->where('id', '>=', 15862)
            ->whereIn('type', ['income', 'expense'])
            ->orderBy('transactions.date_bank_processed', 'ASC');

        if ($untilDate) {
            $query->where('date_bank_processed', '<', $untilDate);
        }

        if ($categoryIds) {
            $query->whereIn('category_id', '<', $categoryIds);
        }

        if ($vendorIds) {
            $query->whereIn('vendor_id', '<', $vendorIds);
        }

        if ($accountIds) {
            $query->whereIn('account_id', '<', $accountIds);
        }

        $endpoint = self::API_ENDPOINT . '/' . env('ACCOUNT_ID') . '/transactions';
        $numRecords = 0;
        $numRequests = 0;

        $query->chunk(1000, function($transactions) use($client, $endpoint, &$numRecords, &$numRequests) {
            $ynabTransactions = [];
            foreach ($transactions as $transaction) {
                $numRecords++;
                $amount = (float) $transaction->amount;

                if ($amount != 0) {
                    $ynabTransaction = $transaction->toYnab();
                    if (strlen($ynabTransaction['account_id']) > 5 && strlen($ynabTransaction['category_id']) > 5) {
                        $ynabTransactions[] = $ynabTransaction;
                        $this->comment('[' . $numRecords . '] Adding: ' . $transaction->id . '::' . $ynabTransaction['account_id'] . '::' . $ynabTransaction['category_id'] . ' (' . $transaction->date_bank_processed . ')');
                    }

                }
            }

            $method = 'patch';

            $response = $client->request('post', $endpoint, [
                'json' => ['transactions' => $ynabTransactions ],
                'headers' => [
                    'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
                ],
            ]);

            $numRecords = 0;

            $json = $response->getBody()->getContents();
            $responseObj = json_decode($json);

            $this->info('[' . $numRequests . '] Posted ' . $numRecords . ' Transactions');
            $numRequests++;

            foreach ($responseObj->data->transactions as $ynabTransaction) {
                $this->comment('Saving YNAB Transaction: ' . $ynabTransaction->id);

                $transaction->ynab_id = $ynabTransaction->id;

                try {
                    $transaction->ynab_json = json_encode($ynabTransaction);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }

                $transaction->save();
            }
        });

        return 1;
    }

    protected function request($endpoint, $data, $options = [])
    {
        $response = (object) [
            'data' => (object) [],
            'error' => '',
            'headers' => (object) [],
            'httpResponse' => null,
            'meta' => (object) [],
            'result' => (object) [],
        ];

        $method = $options['method'] ?? 'get';
        $query = $options['query'] ?? [];
        $json = null;

        try {
            $httpResponse = $this->client->request($method, $endpoint, [
                'json' => $data,
                'query' => $query,
                'headers' => [
                    'Authorization' => env('INVENTORY_PLANNER_AUTH'),
                    'Account' => env('INVENTORY_PLANNER_ACCOUNT'),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response->headers = $httpResponse->getHeaders();
            $response->httpResponse = $httpResponse;

            $json = $httpResponse->getBody()->getContents();
        } catch (Exception $e) {
            $response->error = $e->getMessage();

            if ($e instanceof ClientException) {
                $this->logger->error($e->getResponse()->getBody()->getContents());
            } else {
                $this->logger->error($e->getMessage());
            }
        }

        if ($json) {
            $key = $options['key'] ?? self::API_KEY_PLURAL;

            $responseObj = json_decode($json);
            $response->data = $responseObj->{$key};
            if (!empty($responseObj->meta)) {
                $response->meta = $responseObj->meta;
            }

            if (!empty($responseObj->result)) {
                $message = $responseObj->result->message ?? $responseObj->result->status ?? '';
                $response->meta = $message;
                $this->logger->comment('Result: ' . $message ?? '');
            }
        }

        return $response;
    }
}
