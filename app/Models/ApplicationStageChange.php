<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStageChange extends Model
{
    protected $fillable = ['from_stage', 'to_stage'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
