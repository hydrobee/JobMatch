<?php

namespace App\Models;

use Database\Factories\JobListingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobListing extends Model
{
    /** @use HasFactory<JobListingFactory> */
    use HasFactory;

    protected $fillable = [
        'job_source_id',
        'title',
        'company',
        'location',
        'description',
        'url',
        'salary',
        'posted_at',
        'closes_at',
        'fingerprint',
        'skills',
        'match_score',
        'is_expired',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'posted_at' => 'datetime',
            'closes_at' => 'date',
            'match_score' => 'integer',
            'is_expired' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(JobSource::class, 'job_source_id');
    }

    public function application(): HasOne
    {
        return $this->hasOne(Application::class);
    }

    public function tailoredResume(): HasOne
    {
        return $this->hasOne(Resume::class);
    }

    public function coverLetter(): HasOne
    {
        return $this->hasOne(CoverLetter::class);
    }
}
