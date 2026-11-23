<?php

namespace Tests\Feature;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobListingTest extends TestCase
{
    use RefreshDatabase;

    private function jobData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Backend Developer',
            'company' => 'Acme Sdn Bhd',
            'location' => 'Kuching',
            'description' => 'We use PHP and Docker every day.',
        ], $overrides);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('jobs.index'))->assertRedirect(route('login'));
    }

    public function test_a_job_can_be_added_by_hand_and_skills_are_detected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('jobs.store'), $this->jobData(['description' => 'PHP and Laravel']))
            ->assertRedirect();

        $job = JobListing::firstOrFail();
        $this->assertSame($user->id, $job->user_id);
        $this->assertSame(['PHP', 'Laravel'], $job->skills);
    }

    public function test_a_job_can_be_added_with_an_optional_closing_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('jobs.store'), $this->jobData(['posted_at' => '2026-11-01', 'closes_at' => '2026-12-15']))
            ->assertRedirect();

        $job = JobListing::firstOrFail();
        $this->assertSame('2026-12-15', $job->closes_at->toDateString());

        $this->get(route('jobs.show', $job))->assertSee('15 Dec 2026');
    }

    public function test_closing_date_cannot_be_before_the_posted_date(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('jobs.store'), $this->jobData(['posted_at' => '2026-11-10', 'closes_at' => '2026-11-01']))
            ->assertSessionHasErrors('closes_at');

        $this->assertSame(0, JobListing::count());
    }

    public function test_a_saved_job_can_be_edited_and_is_rescored(): void
    {
        $user = User::factory()->create();
        $user->skills()->create(['name' => 'PHP', 'level' => 3]);
        $job = JobListing::factory()->create(['user_id' => $user->id, 'skills' => [], 'match_score' => 0]);

        $this->actingAs($user)->get(route('jobs.edit', $job))->assertOk()->assertSee($job->title);

        $this->put(route('jobs.update', $job), $this->jobData(['closes_at' => '2026-12-31']))
            ->assertRedirect(route('jobs.show', $job));

        $job->refresh();
        $this->assertSame('Backend Developer', $job->title);
        $this->assertSame('2026-12-31', $job->closes_at->toDateString());
        $this->assertSame(['PHP', 'Docker'], $job->skills);
        $this->assertSame(50, $job->match_score);
    }

    public function test_a_job_cannot_be_edited_into_a_duplicate_of_another_saved_job(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('jobs.store'), $this->jobData());
        $other = JobListing::factory()->create(['user_id' => $user->id, 'title' => 'Designer']);

        $this->put(route('jobs.update', $other), $this->jobData())->assertSessionHasErrors('title');

        $this->assertSame('Designer', $other->fresh()->title);
    }

    public function test_title_and_company_are_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('jobs.store'), ['title' => '', 'company' => ''])
            ->assertSessionHasErrors(['title', 'company']);
    }

    public function test_the_same_job_is_not_saved_twice(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('jobs.store'), $this->jobData());
        $this->actingAs($user)->post(route('jobs.store'), $this->jobData(['title' => ' backend  developer ']));

        $this->assertSame(1, JobListing::count());
    }

    public function test_two_users_can_each_save_the_same_job(): void
    {
        $this->actingAs(User::factory()->create())->post(route('jobs.store'), $this->jobData());
        $this->actingAs(User::factory()->create())->post(route('jobs.store'), $this->jobData());

        $this->assertSame(2, JobListing::count());
    }

    public function test_match_score_uses_the_users_skills(): void
    {
        $user = User::factory()->create();
        $user->skills()->create(['name' => 'PHP', 'level' => 3]);

        $this->actingAs($user)->post(route('jobs.store'), $this->jobData());

        $this->assertSame(50, JobListing::firstOrFail()->match_score);
    }

    public function test_adding_a_skill_rescores_existing_jobs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('jobs.store'), $this->jobData());
        $this->assertSame(0, JobListing::firstOrFail()->match_score);

        $this->post(route('skills.store'), ['name' => 'php', 'level' => 4]);

        $this->assertSame(50, JobListing::firstOrFail()->match_score);
        $this->assertSame('PHP', $user->skills()->firstOrFail()->name);
    }

    public function test_removing_a_skill_rescores_existing_jobs(): void
    {
        $user = User::factory()->create();
        $skill = $user->skills()->create(['name' => 'PHP', 'level' => 3]);
        $this->actingAs($user)->post(route('jobs.store'), $this->jobData());
        $this->assertSame(50, JobListing::firstOrFail()->match_score);

        $this->delete(route('skills.destroy', $skill));

        $this->assertSame(0, JobListing::firstOrFail()->match_score);
    }

    public function test_other_users_jobs_cannot_be_viewed_changed_or_deleted(): void
    {
        $owner = User::factory()->create();
        $job = JobListing::factory()->create(['user_id' => $owner->id]);
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('jobs.show', $job))->assertNotFound();
        $this->patch(route('jobs.expired', $job))->assertNotFound();
        $this->get(route('jobs.edit', $job))->assertNotFound();
        $this->put(route('jobs.update', $job), $this->jobData())->assertNotFound();
        $this->delete(route('jobs.destroy', $job))->assertNotFound();

        $this->assertDatabaseHas('job_listings', ['id' => $job->id, 'is_expired' => false]);
    }

    public function test_search_filters_by_keyword_in_title_company_or_description(): void
    {
        $user = User::factory()->create();
        JobListing::factory()->create(['user_id' => $user->id, 'title' => 'Laravel Developer', 'company' => 'Alpha']);
        JobListing::factory()->create(['user_id' => $user->id, 'title' => 'Nurse', 'company' => 'Beta', 'description' => 'Ward duty']);

        $this->actingAs($user)
            ->get(route('jobs.index', ['q' => 'LARAVEL']))
            ->assertOk()
            ->assertSee('Laravel Developer')
            ->assertDontSee('Nurse');
    }

    public function test_minimum_score_filter_and_best_match_sort(): void
    {
        $user = User::factory()->create();
        JobListing::factory()->create(['user_id' => $user->id, 'title' => 'High Match Job', 'match_score' => 90]);
        JobListing::factory()->create(['user_id' => $user->id, 'title' => 'Low Match Job', 'match_score' => 10]);

        $this->actingAs($user)
            ->get(route('jobs.index', ['min_score' => 50]))
            ->assertSee('High Match Job')
            ->assertDontSee('Low Match Job');

        $this->get(route('jobs.index', ['sort' => 'match']))
            ->assertSeeInOrder(['High Match Job', 'Low Match Job']);
    }

    public function test_expired_jobs_are_hidden_unless_requested(): void
    {
        $user = User::factory()->create();
        JobListing::factory()->create(['user_id' => $user->id, 'title' => 'Old Posting', 'is_expired' => true]);

        $this->actingAs($user)->get(route('jobs.index'))->assertDontSee('Old Posting');
        $this->get(route('jobs.index', ['show_expired' => 1]))->assertSee('Old Posting');
    }

    public function test_a_job_can_be_marked_expired_and_active_again(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->patch(route('jobs.expired', $job));
        $this->assertTrue($job->fresh()->is_expired);

        $this->patch(route('jobs.expired', $job));
        $this->assertFalse($job->fresh()->is_expired);
    }
}
