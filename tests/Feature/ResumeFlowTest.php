<?php

namespace Tests\Feature;

use App\Models\JobListing;
use App\Models\Resume;
use App\Models\User;
use App\Services\Resume\ResumeTailor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class ResumeFlowTest extends TestCase
{
    use RefreshDatabase;

    private function userWithMaster(string $content = "Summary\nI build PHP apps."): User
    {
        $user = User::factory()->create();
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

    public function test_the_master_resume_can_be_saved_and_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('resume.save'), ['content' => 'First version'])->assertRedirect();
        $this->put(route('resume.save'), ['content' => 'Second version']);

        $this->assertSame(1, $user->resumes()->count());
        $this->assertSame('Second version', $user->masterResume()->first()->content);
        $this->get(route('resume.master'))->assertOk()->assertSee('Second version');
    }

    public function test_the_master_resume_cannot_be_empty(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('resume.save'), ['content' => ''])
            ->assertSessionHasErrors('content');
    }

    public function test_tailoring_needs_a_master_resume(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('jobs.resume', $job))
            ->assertRedirect(route('resume.master'))
            ->assertSessionHas('error');

        $this->assertSame(0, Resume::count());
    }

    public function test_without_an_api_key_a_copy_of_the_master_is_saved(): void
    {
        config(['jobmatch.gemini.api_key' => null]);
        Http::fake();
        $user = $this->userWithMaster('Summary\nMaster text');
        $job = JobListing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('jobs.resume', $job))->assertRedirect();

        $tailored = Resume::where('job_listing_id', $job->id)->firstOrFail();
        $this->assertSame('Summary\nMaster text', $tailored->content);
        $this->assertNull($tailored->ai_notes);
        Http::assertNothingSent();
    }

    public function test_ai_tailoring_saves_the_new_text_and_the_notes(): void
    {
        $this->fakeAiReply([
            'suggestions' => [['section' => 'Skills', 'change' => 'Moved PHP first', 'reason' => 'The posting leads with PHP']],
            'gaps' => ['Kubernetes'],
            'resume' => "Summary\nPHP developer.",
        ]);
        $user = $this->userWithMaster();
        $job = JobListing::factory()->create(['user_id' => $user->id, 'description' => 'Need PHP and Kubernetes']);

        $this->actingAs($user)->post(route('jobs.resume', $job))->assertRedirect();

        $tailored = Resume::where('job_listing_id', $job->id)->firstOrFail();
        $this->assertSame("Summary\nPHP developer.", $tailored->content);
        $this->assertSame('Moved PHP first', $tailored->ai_notes['suggestions'][0]['change']);
        $this->assertSame(['Kubernetes'], $tailored->ai_notes['gaps']);

        // The master resume is never touched.
        $this->assertSame("Summary\nI build PHP apps.", $user->masterResume()->first()->content);

        $this->get(route('resumes.show', $tailored))
            ->assertOk()
            ->assertSee('Moved PHP first')
            ->assertSee('Kubernetes');
    }

    public function test_the_request_to_the_ai_contains_the_posting_and_the_resume(): void
    {
        $this->fakeAiReply(['suggestions' => [], 'gaps' => [], 'resume' => 'Tailored']);
        $user = $this->userWithMaster('Summary\nMy unique resume line.');
        $job = JobListing::factory()->create(['user_id' => $user->id, 'title' => 'Unique Job Title']);

        $this->actingAs($user)->post(route('jobs.resume', $job));

        Http::assertSent(function ($request) {
            $body = json_encode($request->data());

            return $request->hasHeader('x-goog-api-key', 'test-key')
                && str_contains($body, 'Unique Job Title')
                && str_contains($body, 'My unique resume line.')
                && str_contains($body, 'Never invent');
        });
    }

    public function test_an_existing_tailored_resume_is_not_overwritten_unless_asked(): void
    {
        $this->fakeAiReply(['suggestions' => [], 'gaps' => [], 'resume' => 'Regenerated text']);
        $user = $this->userWithMaster();
        $job = JobListing::factory()->create(['user_id' => $user->id]);
        $existing = $user->resumes()->create([
            'job_listing_id' => $job->id,
            'title' => 'Mine',
            'content' => 'My hand edits',
        ]);

        $this->actingAs($user)->post(route('jobs.resume', $job))->assertRedirect(route('resumes.show', $existing));
        $this->assertSame('My hand edits', $existing->fresh()->content);
        Http::assertNothingSent();

        $this->post(route('jobs.resume', $job), ['regenerate' => 1]);
        $this->assertSame('Regenerated text', $existing->fresh()->content);
        $this->assertSame(1, Resume::where('job_listing_id', $job->id)->count());
    }

    public function test_an_ai_failure_saves_a_copy_of_the_master_and_shows_an_error(): void
    {
        config(['jobmatch.gemini.api_key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response('overloaded', 529)]);
        $user = $this->userWithMaster('Summary\nMaster text');
        $job = JobListing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('jobs.resume', $job))
            ->assertSessionHas('error');

        $this->assertSame('Summary\nMaster text', Resume::where('job_listing_id', $job->id)->firstOrFail()->content);
    }

    public function test_the_tailor_parses_replies_and_rejects_bad_ones(): void
    {
        $tailor = app(ResumeTailor::class);

        $parsed = $tailor->parse('Sure! '.json_encode([
            'suggestions' => [['section' => 'Summary', 'change' => 'Shortened it', 'reason' => 'Fits one page'], 'junk'],
            'gaps' => ['Docker', 5],
            'resume' => "  Summary\nText  ",
        ]));

        $this->assertSame("Summary\nText", $parsed['resume']);
        $this->assertCount(1, $parsed['suggestions']);
        $this->assertSame(['Docker'], $parsed['gaps']);

        $this->expectException(RuntimeException::class);
        $tailor->parse('I cannot help with that.');
    }

    public function test_a_resume_can_be_edited_downloaded_and_printed(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'Resume - Dev - Acme', 'content' => 'Original text']);

        $this->actingAs($user)->put(route('resumes.update', $resume), ['content' => 'Edited text'])->assertRedirect();
        $this->assertSame('Edited text', $resume->fresh()->content);

        $download = $this->get(route('resumes.download', $resume));
        $download->assertOk();
        $this->assertStringContainsString('resume-dev-acme.txt', $download->headers->get('Content-Disposition'));
        $this->assertSame('Edited text', $download->streamedContent());

        $this->get(route('resumes.print', $resume))->assertOk()->assertSee('Edited text');
    }

    public function test_other_users_resumes_and_jobs_are_off_limits(): void
    {
        $owner = $this->userWithMaster();
        $resume = $owner->masterResume()->first();
        $job = JobListing::factory()->create(['user_id' => $owner->id]);
        $intruder = User::factory()->create();
        $intruder->resumes()->create(['title' => 'Master resume', 'content' => 'Mine']);

        $this->actingAs($intruder);
        $this->get(route('resumes.show', $resume))->assertNotFound();
        $this->put(route('resumes.update', $resume), ['content' => 'x'])->assertNotFound();
        $this->get(route('resumes.download', $resume))->assertNotFound();
        $this->get(route('resumes.print', $resume))->assertNotFound();
        $this->delete(route('resumes.destroy', $resume))->assertNotFound();
        $this->post(route('jobs.resume', $job))->assertNotFound();
    }

    public function test_the_job_page_offers_to_tailor_then_links_to_the_result(): void
    {
        $user = $this->userWithMaster();
        $job = JobListing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('jobs.show', $job))->assertSee('Tailor my resume');

        $user->resumes()->create(['job_listing_id' => $job->id, 'title' => 'T', 'content' => 'x']);

        $this->get(route('jobs.show', $job))->assertSee('View tailored resume');
    }

    public function test_deleting_a_job_deletes_its_tailored_resume(): void
    {
        $user = $this->userWithMaster();
        $job = JobListing::factory()->create(['user_id' => $user->id]);
        $user->resumes()->create(['job_listing_id' => $job->id, 'title' => 'T', 'content' => 'x']);

        $this->actingAs($user)->delete(route('jobs.destroy', $job));

        $this->assertSame(1, Resume::count()); // only the master is left
    }
}
