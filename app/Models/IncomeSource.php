<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeSource extends Model
{
    use HasFactory;

    protected $table = 'income_sources';
    protected $fillable = [
        'active',
        'created_at',
        'deleted',
        'group',
        'id',
        'name',
        'slug',
        'sort',
        'updated_at',
        'user_id',
    ];
}
