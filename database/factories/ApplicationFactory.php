<?php

namespace Database\Factories;

use App\Enums\ApplicationStage;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Application> */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            // Same owner as the application, so ownership checks line up.
            'job_listing_id' => fn (array $attributes) => JobListing::factory()
                ->create(['user_id' => $attributes['user_id']])
                ->id,
            'stage' => ApplicationStage::Saved,
        ];
    }
}
