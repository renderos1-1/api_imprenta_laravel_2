<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    use HasUuids;

    // Disable Laravel's timestamps since we're managing our own
    public $timestamps = false;

    // Primary key settings
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'external_id',
        'proceso_id',
        'document_type',
        'person_type',
        'document_number',
        'full_name',
        'email',
        'phone',
        // Added location fields
        'state_code',
        'state_name',
        'city_code',
        'city_name',
        'full_json',
        'status',
        'sync_status',
        'created_at',
        'start_date',
        'end_date',
        'last_modified_at',
        'last_sync_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'external_id' => 'integer',
        'proceso_id' => 'integer',
        'full_json' => 'array',
        'created_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_modified_at' => 'datetime',
        'last_sync_at' => 'datetime',
        // Added casting for ENUM fields and location fields
        'document_type' => 'string',
        'person_type' => 'string',
        'status' => 'string',
        'state_code' => 'string',
        'state_name' => 'string',
        'city_code' => 'string',
        'city_name' => 'string'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'internal_id' // This is auto-incrementing, so we don't want it mass-assignable
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }
}
