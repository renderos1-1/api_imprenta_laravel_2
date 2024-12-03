<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'document_number', // DUI
        'full_name',
        'person_type',
        'email',
        'phone',
        'status',
        'created_at',
        'updated_at'
    ];
}
