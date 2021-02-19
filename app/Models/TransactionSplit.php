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

    public function transaction()
    {
        return $this->hasOne('App\Models\Transaction', 'id', 'transaction_id');
    }
}
