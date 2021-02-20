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
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . env('ACCESS_TOKEN'),
                ],
            ]);

            $json = $response->getBody()->getContents();
            $responseObj = json_decode($json);
            // $responseObj = json_decode(file_get_contents(storage_path('data/transactions.json')));
            $lmTransactions = $responseObj->transactions;

            foreach ($lmTransactions as $lmTransaction) {
                $i++;

                $transaction = Transaction::where('transactions.lm_id', $lmTransaction->id)
                    ->first();

                if (!$transaction) {
                    $transaction = Transaction::where('lm_external_id', $lmTransaction->external_id)->first();

                    if (!$transaction) {
                        $dateRangeEnd = (new DateTime($lmTransaction->date))->modify('+ 3 days');
                        $dateRangeStart = (new DateTime($lmTransaction->date))->modify('- 3 days');
                        $query = Transaction::join('accounts', 'transactions.account_id', 'accounts.id')
                            ->select('transactions.*')
                            ->whereNested(function($query) use ($lmTransaction) {
                                $query->where('amount', $lmTransaction->amount)
                                    ->orWhere('amount', $lmTransaction->amount * -1);
                            })
                            ->whereNested(function($query) use ($lmTransaction) {
                                $query->where('accounts.lm_asset_id', $lmTransaction->asset_id)
                                    ->orWhere('accounts.lm_plaid_account_id', $lmTransaction->plaid_account_id);
                            })
                            ->whereBetween('date', [$dateRangeStart->format('Y-m-d'), $dateRangeEnd->format('Y-m-d')]);

                        $transactions = $query->get();
                        $numTransactions = count($transactions);

                        if (!$numTransactions) {
                            $transaction = new Transaction($this->mapTransaction($lmTransaction));
                        } else if ($numTransactions > 1) {
                            $transactionOptions = [];

                            foreach ($transactions as $_transaction) {
                                $transactionOptions[] = '[' . $_transaction->id . '] '
                                    . 'Type: ' . ucfirst($_transaction->type)
                                    . ' | Amount: ' . $_transaction->amount
                                    . ' | Account: ' . $_transaction->account->name
                                    . ' | Date: ' . $_transaction->date_bank_processed
                                    . ' | Memo: ' . $_transaction->memo;
                            }
                            $transactionOptions['_'] = 'Skip';

                            $this->info(PHP_EOL . json_encode($lmTransaction, JSON_PRETTY_PRINT));
                            $offset = $this->choice('Which Transaction?', $transactionOptions);

                            if ($offset === '_') {
                                $this->comment('Skipping');
                                continue;
                            }
                            $transaction = $transactions[$offset] ?? null;

                            if (!$transaction) {
                                $this->comment('Skipping LM Transaction: ' . $lmTransaction->id);
                                continue;
                            }
                        } else {
                            $transaction = $transactions[0];
                        }
                    }

                }

                if ($transaction) {
                    $transaction->fill($this->mapTransaction($lmTransaction));
                    $transaction->lm_json = json_encode($lmTransaction);
                    $transaction->save();

                    $this->comment('[' . $i . '] Saved Transaction: ' . $lmTransaction->id . ' (' . $lmTransaction->date . ')');
                } else {
                    $this->error('[' . $i . '] Skipped Transaction: ' . $lmTransaction->id . ' (' . $lmTransaction->date . ')');
                }
            }

            $moreTransactions = false;
        } while ($moreTransactions);
    }

    private function mapTransaction($lmTransaction)
    {
        return  [
            'lm_amount' => $lmTransaction->amount,
            'lm_payee' => $lmTransaction->payee,
            'lm_id' => $lmTransaction->id,
            'lm_date' => $lmTransaction->date,
            'lm_category_id' => $lmTransaction->category_id,
            'lm_asset_id' => $lmTransaction->asset_id,
            'lm_plaid_account_id' => $lmTransaction->plaid_account_id,
            'lm_status' => $lmTransaction->status,
            'lm_is_group' => (int) $lmTransaction->is_group,
            'lm_parent_id' => $lmTransaction->parent_id,
            'lm_external_id' => $lmTransaction->external_id,
            'memo' => $lmTransaction->notes,
        ];
    }
}
