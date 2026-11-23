<?php

namespace Tests\Feature;

use App\Mail\DailyDigest;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DailyDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_digest_is_sent_when_there_is_a_new_good_match(): void
    {
        Mail::fake();
        $user = User::factory()->create(['digest_min_score' => 50]);
        JobListing::factory()->create(['user_id' => $user->id, 'match_score' => 80]);

        $this->artisan('jobmatch:digest')->assertSuccessful();

        Mail::assertSent(DailyDigest::class, fn (DailyDigest $mail) => $mail->hasTo($user->email) && $mail->jobs->count() === 1);
    }

    public function test_weak_matches_do_not_trigger_a_digest(): void
    {
        Mail::fake();
        $user = User::factory()->create(['digest_min_score' => 50]);
        JobListing::factory()->create(['user_id' => $user->id, 'match_score' => 20]);

        $this->artisan('jobmatch:digest')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_old_jobs_are_not_repeated(): void
    {
        Mail::fake();
        $user = User::factory()->create(['digest_min_score' => 0]);
        $job = JobListing::factory()->create(['user_id' => $user->id, 'match_score' => 90]);
        $job->forceFill(['created_at' => now()->subDays(3)])->save();

        $this->artisan('jobmatch:digest')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_a_due_follow_up_triggers_a_digest(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        Application::factory()->create(['user_id' => $user->id, 'follow_up_at' => now()->subDay()->toDateString()]);

        $this->artisan('jobmatch:digest')->assertSuccessful();

        Mail::assertSent(DailyDigest::class, fn (DailyDigest $mail) => $mail->followUps->count() === 1);
    }

    public function test_users_who_turned_the_digest_off_get_nothing(): void
    {
        Mail::fake();
        $user = User::factory()->create(['digest_enabled' => false]);
        JobListing::factory()->create(['user_id' => $user->id, 'match_score' => 100]);

        $this->artisan('jobmatch:digest')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_the_digest_email_renders(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create(['user_id' => $user->id, 'title' => 'Digest Job', 'match_score' => 75]);
        $application = Application::factory()->create(['user_id' => $user->id, 'follow_up_at' => now()->toDateString()]);

        $html = (new DailyDigest($user, collect([$job]), collect([$application->load('jobListing')])))->render();

        $this->assertStringContainsString('Digest Job', $html);
        $this->assertStringContainsString('75% match', $html);
        $this->assertStringContainsString('Follow-ups due', $html);
    }
}
