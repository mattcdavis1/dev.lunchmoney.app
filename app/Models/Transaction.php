<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $fillable = [
        'account_id',
        'amount',
        'bank_reference_id',
        'category_id',
        'check_number',
        'commission',
        'cusip',
        'date_bank_processed',
        'date',
        'gross',
        'has_been_reimbursed',
        'is_refund',
        'is_reimbursable',
        'is_self_employment_income',
        'is_self_employment',
        'is_tax_deductible',
        'memo',
        'notes',
        'source_id',
        'symbol',
        'tips_taxed',
        'transfer_from_account_id',
        'transfer_id',
        'transfer_to_account_id',
        'type',
        'unit_price',
        'units',
        'use_tax',
        'import_id',
        'vendor_id',
        'lm_amount',
        'lm_asset_id',
        'lm_category_id',
        'lm_date',
        'lm_external_id',
        'lm_id',
        'lm_is_group',
        'lm_parent_id',
        'lm_payee',
        'lm_plaid_account_id',
        'lm_status',
    ];

    public function account()
    {
        return $this->hasOne('App\Models\Account', 'id', 'account_id');
    }

    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }

    public function from()
    {
        return $this->hasOne('App\Models\Account', 'id', 'transfer_from_account_id');
    }

    public function source()
    {
        return $this->hasOne('App\Models\IncomeSource', 'id', 'source_id');
    }

    public function splits()
    {
        return $this->hasMany('App\Models\TransactionSplit', 'transaction_id', 'id');
    }

    public function to()
    {
        return $this->hasOne('App\Models\Account', 'id', 'transfer_to_account_id');
    }

    public function toLunch($mode = 'put')
    {
        $amount = (float) ($this->lm_amount ?? $this->amount);
        $vendor = $this->vendor;
        $category = $this->category;

        if ($this->type == 'transfer') {
            $fromAccount = $this->from;
            $toAccount = $this->to;
            if ($amount < 0) {
                $account = $fromAccount;
                $payee = $toAccount->name;
            } else {
                $account = $toAccount;
                $payee = $fromAccount->name;
            }
        } else {
            $account = $this->account;
            $payee = $vendor->name ?? $this->lm_payee ?? null;
        }

        $date = $this->date ?? $this->lm_date;

        $data = [
            'id' => $this->lm_id,
            'payee' => $payee ?? 'N/A',
            'category_id' => $category->lm_id ?? $this->lm_category_id,
            'notes' => Str::limit($this->memo, 330),
            'status' => $this->lm_status ?? 'cleared',
            'debit_as_negative' => false,
        ];

        if (!$this->lm_plaid_account_id) {
            $data['amount'] = $account->invert_amount ? $amount * -1 : $amount;
            $data['date'] = $date;

            if ($this->lm_external_id) {
                $data['external_id'] = $this->lm_external_id;
            }
        }

        $tags = $category->lm_tag_ids ?? null;
        if ($tags) {
            $tagArrString = explode(',', $tags);
            $tagArrInt = [];
            foreach ($tagArrString as $tagId) {
                $tagArrInt[] = (int) $tagId;
            }
            $data['tags'] = $tagArrInt;
        }

        if ($mode == 'post') {
            $data['asset_id'] = $account->lm_asset_id;
        } else {
            $splits = $this->splits;

            if (count($splits)) {
                $data['split'] = [];

                foreach ($splits as $split) {
                    $splitAmount = (float) $split->amount;
                    $splitCategory = $split->category;
                    $splitAmountAdj = $account->invert_amount ? $splitAmount :  $splitAmount * -1;

                    $data['split'][] = [
                        'date' => $date,
                        'category_id' => $splitCategory->lm_id,
                        'amount' => $splitAmountAdj,
                    ];
                }
            }
        }

        return $data;
    }

    public function vendor()
    {
        return $this->hasOne('App\Models\Vendor', 'id', 'vendor_id');
    }

    protected function LMAccount()
    {
        $id = null;
        $name = null;

        return (object) [
            'id' => $id,
            'name' => $name,
        ];
    }
}
