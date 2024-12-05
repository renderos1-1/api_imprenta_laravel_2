<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $fillable = [
        'dui',
        'action',
        'ip_address',
        'full_name',
    ];

    protected $dates = ['created_at'];

    public $timestamps = false;

    public function user()
    {
        // Asumiendo que en la tabla users tienes una columna 'name' y 'dui'
        return $this->belongsTo(User::class, 'dui', 'dui');
    }
}
