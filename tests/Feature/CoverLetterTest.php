<?php

namespace Tests\Feature;

use App\Models\CoverLetter;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoverLetterTest extends TestCase
{
    use RefreshDatabase;

    private function userWithMaster(string $content = "Summary\nI build PHP apps."): User
    {
        $user = User::factory()->create(['name' => 'Aina Rahman']);
        $user->resumes()->create(['title' => 'Master resume', 'content' => $content]);

        return $user;
    }

    private function fakeAiReply(array $payload): void
    {
        config(['jobmatch.gemini.api_key' => 'test-key']);

        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode($payload)]]]]],
        ])]);
    }

    public function test_without_an_api_key_a_template_letter_is_saved(): void
    {
        config(['jobmatch.gemini.api_key' => null]);
        Http::fake();
        $user = User::factory()->create(['name' => 'Aina Rahman']);
        $user->skills()->create(['name' => 'PHP', 'level' => 3]);
        $job = JobListing::factory()->create([
            'user_id' => $user->id,
            'title' => 'Backend Developer',
            'company' => 'Acme',
            'skills' => ['PHP', 'Docker'],
        ]);

        $this->actingAs($user)->post(route('jobs.cover-letter', $job))->assertRedirect();

        $letter = CoverLetter::where('job_listing_id', $job->id)->firstOrFail();
        $this->assertStringContainsString('Backend Developer position at Acme', $letter->content);
        $this->assertStringContainsString('My experience with PHP', $letter->content);
        $this->assertStringContainsString('Aina Rahman', $letter->content);
        Http::assertNothingSent();
    }

    public function test_ai_writes_the_letter_from_the_master_resume_and_the_posting(): void
    {
        $this->fakeAiReply(['letter' => "Dear Hiring Manager,\n\nAI written letter."]);
        $user = $this->userWithMaster('Summary\nMy unique resume line.');
        $job = JobListing::factory()->create(['user_id' => $user->id, 'title' => 'Unique Job Title']);

        $this->actingAs($user)->post(route('jobs.cover-letter', $job))->assertRedirect();

        $letter = CoverLetter::where('job_listing_id', $job->id)->firstOrFail();
        $this->assertSame("Dear Hiring Manager,\n\nAI written letter.", $letter->content);

        Http::assertSent(function ($request) {
            $body = json_encode($request->data());

            return str_contains($body, 'Unique Job Title') && str_contains($body, 'My unique resume line.');
        });

        $this->get(route('cover-letters.show', $letter))->assertOk()->assertSee('AI written letter.');
    }

    public function test_ai_writing_needs_a_master_resume(): void
    {
        $this->fakeAiReply(['letter' => 'Unused']);
        $user = User::factory()->create();
        $job = JobListing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('jobs.cover-letter', $job))
            ->assertRedirect(route('resume.master'))
            ->assertSessionHas('error');

        $this->assertSame(0, CoverLetter::count());
        Http::assertNothingSent();
    }

    public function test_a_failed_ai_reply_falls_back_to_the_template(): void
    {
        $this->fakeAiReply(['not_a_letter' => true]);
        $user = $this->userWithMaster();
        $job = JobListing::factory()->create(['user_id' => $user->id, 'company' => 'Acme']);

        $this->actingAs($user)->post(route('jobs.cover-letter', $job))->assertSessionHas('error');

        $this->assertStringContainsString('at Acme', CoverLetter::firstOrFail()->content);
    }

    public function test_an_existing_letter_is_not_overwritten_unless_regenerated(): void
    {
        config(['jobmatch.gemini.api_key' => null]);
        $user = User::factory()->create();
        $letter = CoverLetter::factory()->create(['user_id' => $user->id, 'content' => 'My hand edits']);

        $this->actingAs($user)
            ->post(route('jobs.cover-letter', $letter->jobListing))
            ->assertRedirect(route('cover-letters.show', $letter));
        $this->assertSame('My hand edits', $letter->fresh()->content);

        $this->post(route('jobs.cover-letter', $letter->jobListing), ['regenerate' => 1]);
        $this->assertStringContainsString('Dear Hiring Manager', $letter->fresh()->content);
        $this->assertSame(1, CoverLetter::count());
    }

    public function test_a_letter_can_be_edited_downloaded_and_deleted(): void
    {
        $user = User::factory()->create();
        $letter = CoverLetter::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->put(route('cover-letters.update', $letter), ['content' => 'Edited letter']);
        $this->assertSame('Edited letter', $letter->fresh()->content);

        $this->put(route('cover-letters.update', $letter), ['content' => ''])->assertSessionHasErrors('content');

        $this->get(route('cover-letters.download', $letter))->assertOk()->assertStreamedContent('Edited letter');
        $this->get(route('cover-letters.print', $letter))->assertOk()->assertSee('Edited letter');

        $this->delete(route('cover-letters.destroy', $letter))->assertRedirect(route('jobs.show', $letter->job_listing_id));
        $this->assertSame(0, CoverLetter::count());
    }

    public function test_other_users_letters_and_jobs_are_off_limits(): void
    {
        $letter = CoverLetter::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->post(route('jobs.cover-letter', $letter->jobListing))->assertNotFound();
        $this->get(route('cover-letters.show', $letter))->assertNotFound();
        $this->put(route('cover-letters.update', $letter), ['content' => 'Hacked'])->assertNotFound();
        $this->get(route('cover-letters.download', $letter))->assertNotFound();
        $this->delete(route('cover-letters.destroy', $letter))->assertNotFound();

        $this->assertNotSame('Hacked', $letter->fresh()->content);
    }
}
