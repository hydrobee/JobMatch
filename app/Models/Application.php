<?php

namespace App\Models;

use App\Enums\ApplicationChannel;
use App\Enums\ApplicationStage;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'job_listing_id',
        'stage',
        'applied_at',
        'applied_via',
        'contact_person',
        'resume_version',
        'notes',
        'follow_up_at',
    ];

    protected function casts(): array
    {
        return [
            'stage' => ApplicationStage::class,
            'applied_at' => 'date',
            'applied_via' => ApplicationChannel::class,
            'follow_up_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function stageChanges(): HasMany
    {
        return $this->hasMany(ApplicationStageChange::class)->latest('id');
    }

    /**
     * Move to another stage and record it in the history.
     */
    public function moveTo(ApplicationStage $stage): void
    {
        $from = $this->stage;

        if ($from === $stage) {
            return;
        }

        $this->stage = $stage;

        if ($stage === ApplicationStage::Applied && $this->applied_at === null) {
            $this->applied_at = now()->toDateString();
        }

        $this->save();

        $this->stageChanges()->create([
            'from_stage' => $from?->value,
            'to_stage' => $stage->value,
        ]);
    }
}
