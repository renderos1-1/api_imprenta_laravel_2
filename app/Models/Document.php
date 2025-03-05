<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasUuids;

    protected $fillable = [
        'transaction_id',
        'external_transaction_id',
        'document_type',
        'original_filename',
        'original_url',
        'storage_path',
        'status',
        'error_message',
    ];

    protected $casts = [
        'id' => 'string',
        'transaction_id' => 'string',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getIsDownloadedAttribute()
    {
        return $this->status === 'downloaded';
    }

    public function markAsDownloaded($storagePath)
    {
        $this->storage_path = $storagePath;
        $this->status = 'downloaded';
        $this->save();
    }

    public function markAsError($errorMessage)
    {
        $this->status = 'error';
        $this->error_message = $errorMessage;
        $this->save();
    }
}
