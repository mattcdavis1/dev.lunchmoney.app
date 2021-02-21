<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionSplit extends Model
{
    use HasFactory;

    protected $table = 'transaction_splits';
    protected $fillable = [
        'id',
        'user_id',
        'transaction_id',
        'sub_transaction_id',
        'amount',
        'category_id',
        'created_at',
        'updated_at',
    ];

    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }

    public function transaction()
    {
        return $this->hasOne('App\Models\Transaction', 'id', 'transaction_id');
    }

    public function toLunch($mode = 'put')
    {
        $transaction = $this->transaction;

        $amount = (float) $this->amount;
        $vendor = $transaction->vendor;
        $category = $this->category;
        $account = $transaction->account;
        $payee = $vendor->name ?? $transaction->lm_payee ?? null;

        $data = [
            'id' => $this->lm_id,
            'date' => $this->lm_date ?? $this->date_bank_processed,
            'amount' => $amount,
            'payee' => $payee ?? 'N/A',
            'category_id' => $category->lm_id ?? $this->lm_category_id,
            'notes' => Str::limit($this->memo, 330),
            'status' => $this->lm_status ?? 'cleared',
            'tags' => $category->lm_tags ?? null,
            'external_id' => $this->lm_external_id,
            'debit_as_negative' => true,
        ];

        if ($mode == 'post') {
            $data['asset_id'] = $account->lm_asset_id;
        }

        return $data;
    }
}
