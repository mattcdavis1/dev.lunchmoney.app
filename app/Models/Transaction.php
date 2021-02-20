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

    public function toLM()
    {
        $dateString = $this->date_bank_processed < '2016-02-28' ? '2016-02-28' : $this->date_bank_processed;
        $date = new DateTime($dateString);
        $vendor = $this->vendor;
        $account = self::ACCOUNT_MAP[$this->account_id];
        $category = self::CATEGORY_MAP[$this->category_id] ?? self::LM_UNCATEGORIZED_ID;
        $amount = ((float) $this->amount) * 1000;

        $data = [
            'account_id' => $account['id'],
            'date' => $date->format('Y-m-d'),
            'amount' => round($amount, 2),
            // 'payee_id' => $payee->id,
            'payee_name' => $vendor->name ?? null,
            'category_id' => $category['id'],
            'memo' => Str::limit($this->memo, 190),
            'cleared' => 'cleared',
            'approved' => true,
            'flag_color' => 'red',
            'import_id' => '365budget-' . $this->id,
            'subtransactions' => [],
        ];

        foreach ($this->splits as $split) {
            $amount = ((float) $split->amount) * 1000;
            if ($amount != 0) {
                $subCategory = self::CATEGORY_MAP[$this->category_id] ?? null;

                $data['subtransactions'][] = [
                    'amount' => round($amount, 2),
                    // 'payee_id' => $vendor->id,
                    'payee_name' => $vendor->name ?? null,
                    'category_id' => $subCategory['id'] ?? null,
                    'memo' => '',
                ];
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
