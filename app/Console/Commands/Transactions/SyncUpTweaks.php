<?php

namespace App\Console\Commands\Transactions;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Models\Transaction;
use GuzzleHttp\Exception\RequestException;
use DateTime;
use Exception;
use Illuminate\Support\Str;

class SyncUpTweaks extends Command
{
    const MODE_PATCH = 'patch';
    const MODE_POST = 'post';
    const API_ENDPOINT = 'https://dev.lunchmoney.app/v1/transactions';
    protected $signature = 'transactions:sync-up-tweaks';
    protected $description = 'Sync Up Transaction Tweaks';
    protected $client = null;

    public function handle()
    {
        die();
        $mode = self::MODE_PATCH;

        $client = new Client([
            'timeout'  => 500.0,
        ]);

        $query = Transaction::where('user_id', 1)
            ->whereIn('type', ['income', 'expense'])
            ->where('lm_date', '2016-02-28')
            ->orderBy('transactions.date_bank_processed', 'ASC')
            ->whereNotNull('transactions.lm_json')
            ->whereNotNull('transactions.lm_id');

        $endpoint = self::API_ENDPOINT . '/' . env('ACCOUNT_ID') . '/transactions';
        $numRecords = 1;
        $numRequests = 0;

        $query->chunk(1000, function($transactions) use ($client, $endpoint, $mode, &$numRecords, &$numRequests) {
            $lmTransactions = [];

            foreach ($transactions as $transaction) {
                $numRecords++;
                $lm = json_decode($transaction->lm_json);

                $date = new DateTime($transaction->date_bank_processed);

                $memo = Str::limit($lm->memo, 180);
                $memo = '[' . $date->format('Y-m-d') . '] ' . $memo;

                $lmTransaction = [
                    'id' => $lm->id,
                    'date' => $transaction->lm_date,
                    'amount' => $lm->amount,
                    'account_id' => $lm->account_id,
                    'memo' => trim($memo),
                ];

                $lmTransactions[] = $lmTransaction;
                $this->comment('[' . $numRecords . '] Adding: ' . $transaction->id . ' (' . $transaction->date_bank_processed . ')');
            }

            $this->info('Posting / Patching Data');

            try {
                $response = $client->request($mode, $endpoint, [
                    'json' => ['transactions' => $lmTransactions ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
                    ],
                ]);
            } catch (RequestException $e) {
                $this->error($e->getMessage());
            }

            $numRequests++;
            $numRecords = 0;

            $json = $response->getBody()->getContents();
            $responseObj = json_decode($json);

            $this->info('[' . $numRequests . '] Post Transactions Success');


            if ($mode == self::MODE_POST) {
                foreach ($responseObj->data->transactions as $lmTransaction) {
                    $this->comment('Saving lm Transaction: ' . $lmTransaction->id);

                    $transaction->lm_id = $lmTransaction->id;

                    try {
                        $transaction->lm_json = json_encode($lmTransaction);
                    } catch (Exception $e) {
                        $this->error($e->getMessage());
                    }

                    $transaction->save();
                }
            }

            sleep(5);
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
