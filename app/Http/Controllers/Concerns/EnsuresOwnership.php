<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;

trait EnsuresOwnership
{
    /**
     * Other people's records look like they do not exist.
     */
    protected function ensureOwned(Model $model): void
    {
        abort_unless((int) $model->user_id === (int) auth()->id(), 404);
    }
}
