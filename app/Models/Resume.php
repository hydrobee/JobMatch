<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resume extends Model
{
    protected $fillable = ['job_listing_id', 'title', 'content', 'ai_notes'];

    protected function casts(): array
    {
        return ['ai_notes' => 'array'];
    }

    public function isMaster(): bool
    {
        return $this->job_listing_id === null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }
}
