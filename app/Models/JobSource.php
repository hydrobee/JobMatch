<?php

namespace App\Models;

use Database\Factories\JobSourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobSource extends Model
{
    /** @use HasFactory<JobSourceFactory> */
    use HasFactory;

    protected $fillable = ['name', 'url', 'is_enabled', 'last_fetched_at'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'last_fetched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }

    public function fetchLogs(): HasMany
    {
        return $this->hasMany(FetchLog::class);
    }

    public function latestFetchLog(): HasOne
    {
        return $this->hasOne(FetchLog::class)->latestOfMany();
    }
}
