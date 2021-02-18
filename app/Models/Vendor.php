<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $table = 'vendors';
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'active',
        'created_at',
        'updated_at',
        'sort',
        'slug',
        'deleted',
    ];
}
