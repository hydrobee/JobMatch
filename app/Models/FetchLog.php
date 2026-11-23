<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FetchLog extends Model
{
    protected $fillable = ['status', 'jobs_found', 'jobs_created', 'error'];

    public function jobSource(): BelongsTo
    {
        return $this->belongsTo(JobSource::class);
    }
}
