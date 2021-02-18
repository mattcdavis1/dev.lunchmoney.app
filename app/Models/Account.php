<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';
    protected $fillable = [
        'account_type_id',
        'active',
        'global_category_id',
        'is_default',
        'name',
        'num_transactions',
        'slug',
        'sort',
        'oauth',
        'sum_transactions',
        'user_id',
    ];

    public function type()
    {
        return $this->hasOne('App/Database/Models/AccountType', 'account_type_id', 'id');
    }
}
