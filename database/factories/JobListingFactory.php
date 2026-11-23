<?php

namespace Database\Factories;

use App\Models\User;
use App\Services\Jobs\JobListingService;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\JobListing> */
class JobListingFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->jobTitle();
        $company = fake()->unique()->company();
        $location = 'Kuching';

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'company' => $company,
            'location' => $location,
            'description' => fake()->paragraph(),
            'url' => fake()->url(),
            'posted_at' => now()->subDays(fake()->numberBetween(0, 20)),
            'fingerprint' => JobListingService::fingerprint($title, $company, $location),
            'skills' => [],
            'match_score' => null,
            'is_expired' => false,
        ];
    }
}
