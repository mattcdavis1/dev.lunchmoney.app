<?php

namespace App\Console\Commands\Transactions;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Models\Transaction;

class SyncUp extends Command
{
    const API_ENDPOINT = 'https://api.youneedabudget.com/v1/budgets';
    protected $signature = 'transactions:sync-up';
    protected $description = 'Sync Up Categories';
    protected $client = null;

    public function handle()
    {
        $client = new Client([
            'timeout'  => 25.0,
        ]);

        $accountIds = [];
        $categoryIds = [];
        $untilDate = '';
        $vendorIds = [];

        $query = Transaction::whereNull('ynab_id')
            ->whereIn('type', ['income', 'expense']);

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

        $transactions = $query->get();
        $endpoint = self::API_ENDPOINT . '/' . env('ACCOUNT_ID') . '/transactions';

        foreach ($transactions as $transaction) {
            $data = $transaction->toYnab();
            $response = $client->request(self::API_ENDPOINT, $data, [
                'json' => $data,
                'method' => 'post',
                'headers' => [
                    'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
                ],
            ]);
        }

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
