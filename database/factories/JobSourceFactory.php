<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\JobSource> */
class JobSourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company().' Careers',
            'url' => 'https://jobs.example.com/feed-'.fake()->unique()->numberBetween(1, 99999).'.xml',
            'is_enabled' => true,
        ];
    }
}
