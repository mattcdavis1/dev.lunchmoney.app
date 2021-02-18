<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function account()
    {
        return $this->hasOne('App/Database/Models/Account', 'account_id', 'id');
    }

    public function category()
    {
        return $this->hasOne('App/Database/Models/Category', 'category_id', 'id');
    }

    public function from()
    {
        return $this->hasOne('App/Database/Models/AccountModel', 'transfer_from_account_id', 'id');
    }

    public function source()
    {
        return $this->hasOne('App/Database/Models/IncomeSource', 'source_id', 'id');
    }

    public function splits()
    {
        return $this->hasMany('App/Database/Models/TransactionSplit', 'id', 'transaction_id');
    }

    public function to()
    {
        return $this->hasOne('App/Database/Models/AccountModel', 'transfer_to_account_id', 'id');
    }

    public function vendor()
    {
        return $this->hasOne('App/Database/Models/VendorModel', 'vendor_id', 'id');
    }
}
