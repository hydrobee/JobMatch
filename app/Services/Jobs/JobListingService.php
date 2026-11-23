<?php

namespace App\Services\Jobs;

use App\Models\JobListing;
use App\Models\JobSource;
use App\Models\User;
use App\Services\Skills\SkillExtractor;
use App\Services\Skills\SkillMatcher;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JobListingService
{
    public function __construct(
        private SkillExtractor $extractor,
        private SkillMatcher $matcher,
    ) {}

    /**
     * The same job posted on two sites ends up with the same fingerprint.
     */
    public static function fingerprint(string $title, string $company, ?string $location): string
    {
        $normalise = fn (?string $value) => Str::of($value ?? '')
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->toString();

        return sha1($normalise($title).'|'.$normalise($company).'|'.$normalise($location));
    }

    /**
     * Save a job for a user unless they already have it.
     *
     * @param  array<string, mixed>  $data  title, company, location, description, url, salary, posted_at, closes_at
     * @return array{0: JobListing, 1: bool} the listing, and whether it was newly created
     */
    public function store(User $user, array $data, ?JobSource $source = null): array
    {
        $fingerprint = self::fingerprint(
            $data['title'],
            $data['company'],
            $data['location'] ?? null,
        );

        $existing = $user->jobListings()->where('fingerprint', $fingerprint)->first();

        if ($existing) {
            return [$existing, false];
        }

        $skills = $this->extractor->extract($data['title']."\n".($data['description'] ?? ''));
        $score = $this->matcher->match($skills, $this->userSkillNames($user))['score'];

        $listing = $user->jobListings()->create([
            'job_source_id' => $source?->id,
            'title' => $data['title'],
            'company' => $data['company'],
            'location' => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'url' => $data['url'] ?? null,
            'salary' => $data['salary'] ?? null,
            'posted_at' => $data['posted_at'] ?? null,
            'closes_at' => $data['closes_at'] ?? null,
            'fingerprint' => $fingerprint,
            'skills' => $skills,
            'match_score' => $score,
        ]);

        return [$listing, true];
    }

    /**
     * Update a saved job and re-detect its skills and match score.
     *
     * @param  array<string, mixed>  $data  title, company, location, description, url, salary, posted_at, closes_at
     *
     * @throws ValidationException when the edit would duplicate another saved job
     */
    public function update(JobListing $listing, array $data): void
    {
        $fingerprint = self::fingerprint(
            $data['title'],
            $data['company'],
            $data['location'] ?? null,
        );

        $isDuplicate = JobListing::query()
            ->where('user_id', $listing->user_id)
            ->where('fingerprint', $fingerprint)
            ->whereKeyNot($listing->getKey())
            ->exists();

        if ($isDuplicate) {
            throw ValidationException::withMessages([
                'title' => 'You already have a saved job with this title, company and location.',
            ]);
        }

        $skills = $this->extractor->extract($data['title']."\n".($data['description'] ?? ''));

        $listing->update([
            'title' => $data['title'],
            'company' => $data['company'],
            'location' => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'url' => $data['url'] ?? null,
            'salary' => $data['salary'] ?? null,
            'posted_at' => $data['posted_at'] ?? null,
            'closes_at' => $data['closes_at'] ?? null,
            'fingerprint' => $fingerprint,
            'skills' => $skills,
            'match_score' => $this->matcher->match($skills, $this->userSkillNames($listing->user))['score'],
        ]);
    }

    /**
     * Recalculate every match score, e.g. after the user edits their skills.
     */
    public function rescoreAll(User $user): void
    {
        $userSkills = $this->userSkillNames($user);

        $user->jobListings()->chunkById(200, function ($listings) use ($userSkills) {
            foreach ($listings as $listing) {
                $listing->update([
                    'match_score' => $this->matcher->match($listing->skills ?? [], $userSkills)['score'],
                ]);
            }
        });
    }

    /**
     * @return array<int, string>
     */
    private function userSkillNames(User $user): array
    {
        return $user->skills()->pluck('name')->all();
    }
}
