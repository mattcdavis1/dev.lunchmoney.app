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

    public function toLunch()
    {
        $vendor = $this->vendor;
        $account = $this->account;
        $vendor = $this->vendor;
        $category = $this->category;

        $data = [
            'account_id' => $account->asset_id ?? $account->plaid_account_id ?? $this->lm_asset_id ?? $this->plaid_account_id,
            'date' => $this->lm_date ?? $this->date_bank_processed,
            'amount' => $this->lm_amount ?? $this->amount,
            'payee' => $vendor->name ?? $this->lm_payee ?? null,
            'category_id' => $category->lm_id ?? $this->lm_category_id,
            'notes' => Str::limit($this->memo, 330),
            'status' => $this->lm_status ?? 'cleared',
            'tags' => $category->lm_tags ?? null,
        ];

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
