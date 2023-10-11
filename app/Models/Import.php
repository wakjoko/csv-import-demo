<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Import extends Model
{
    protected $fillable = [
        'batch_id',
        'file_name',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(JobBatch::class, 'batch_id');
    }
}
