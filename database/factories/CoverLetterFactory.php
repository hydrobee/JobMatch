<?php

namespace Database\Factories;

use App\Models\CoverLetter;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CoverLetter> */
class CoverLetterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_listing_id' => fn (array $attributes) => JobListing::factory()->create(['user_id' => $attributes['user_id']])->id,
            'title' => 'Cover letter - '.fake()->jobTitle(),
            'content' => "Dear Hiring Manager,\n\n".fake()->paragraph()."\n\nKind regards,\n".fake()->name(),
        ];
    }
}
