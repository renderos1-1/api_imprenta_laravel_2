<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Transaction extends Model
{
    protected $table = 'transactions';

    protected $casts = [
        'full_json' => 'array',
        'created_at' => 'datetime',
    ];
}
