<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BladePermission extends Model
{
    protected $fillable = [
        'name',
        'description',
        'route_name'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_blade_permissions');
    }
}
