<?php

namespace Tests\Feature;

use App\Enums\ApplicationStage;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_job_can_be_saved_to_the_tracker_with_history(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('applications.store'), ['job_listing_id' => $job->id])
            ->assertRedirect();

        $application = Application::firstOrFail();
        $this->assertSame(ApplicationStage::Saved, $application->stage);
        $this->assertSame(1, $application->stageChanges()->count());
    }

    public function test_saving_the_same_job_twice_keeps_one_application(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('applications.store'), ['job_listing_id' => $job->id]);
        $this->post(route('applications.store'), ['job_listing_id' => $job->id]);

        $this->assertSame(1, Application::count());
    }

    public function test_another_users_job_cannot_be_saved(): void
    {
        $job = JobListing::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('applications.store'), ['job_listing_id' => $job->id])
            ->assertNotFound();

        $this->assertSame(0, Application::count());
    }

    public function test_moving_to_applied_records_history_and_sets_the_applied_date(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch(route('applications.stage', $application), ['stage' => 'applied'])
            ->assertRedirect();

        $application->refresh();
        $this->assertSame(ApplicationStage::Applied, $application->stage);
        $this->assertTrue($application->applied_at->isToday());
        $this->assertDatabaseHas('application_stage_changes', [
            'application_id' => $application->id,
            'from_stage' => 'saved',
            'to_stage' => 'applied',
        ]);
    }

    public function test_moving_to_the_same_stage_adds_no_history(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->patch(route('applications.stage', $application), ['stage' => 'saved']);

        $this->assertSame(0, $application->stageChanges()->count());
    }

    public function test_an_invalid_stage_is_rejected(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patch(route('applications.stage', $application), ['stage' => 'hired-by-aliens'])
            ->assertSessionHasErrors('stage');
    }

    public function test_details_can_be_updated(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->patch(route('applications.update', $application), [
            'stage' => 'interview',
            'contact_person' => 'Aisha',
            'resume_version' => 'backend-v2',
            'notes' => 'Phone screen went well.',
            'follow_up_at' => '2026-12-01',
        ])->assertRedirect();

        $application->refresh();
        $this->assertSame(ApplicationStage::Interview, $application->stage);
        $this->assertSame('Aisha', $application->contact_person);
        $this->assertSame('backend-v2', $application->resume_version);
        $this->assertSame('2026-12-01', $application->follow_up_at->toDateString());
    }

    public function test_other_users_applications_are_off_limits(): void
    {
        $application = Application::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('applications.show', $application))->assertNotFound();
        $this->patch(route('applications.stage', $application), ['stage' => 'offer'])->assertNotFound();
        $this->delete(route('applications.destroy', $application))->assertNotFound();

        $this->assertSame(ApplicationStage::Saved, $application->fresh()->stage);
    }

    public function test_the_board_and_dashboard_render(): void
    {
        $user = User::factory()->create();
        Application::factory()->create(['user_id' => $user->id, 'follow_up_at' => now()->toDateString()]);

        $this->actingAs($user)->get(route('applications.index'))->assertOk();
        $this->get(route('dashboard'))->assertOk()->assertSee('Follow-ups due soon');
    }

    public function test_removing_from_the_tracker_keeps_the_job(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->delete(route('applications.destroy', $application));

        $this->assertSame(0, Application::count());
        $this->assertSame(1, JobListing::count());
    }
}
