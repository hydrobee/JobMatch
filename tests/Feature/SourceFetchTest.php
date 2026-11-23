<?php

namespace Tests\Feature;

use App\Models\JobListing;
use App\Models\JobSource;
use App\Models\User;
use App\Services\Jobs\RssJobFetcher;
use App\Services\Jobs\SourceFetchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class SourceFetchTest extends TestCase
{
    use RefreshDatabase;

    private const RSS = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Jobs</title>
    <item>
      <title>PHP Developer at Acme Sdn Bhd</title>
      <link>https://example.com/1</link>
      <description><![CDATA[<p>Build with <b>Laravel</b> and PostgreSQL.</p>]]></description>
      <pubDate>Tue, 17 Nov 2026 08:00:00 +0800</pubDate>
      <location>Kuching</location>
    </item>
    <item>
      <title>IT Executive</title>
      <link>https://example.com/2</link>
      <description>Support users with Excel.</description>
      <author>Borneo Tech</author>
    </item>
  </channel>
</rss>
XML;

    private const ATOM = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>Jobs</title>
  <entry>
    <title>QA Tester</title>
    <link href="https://example.com/qa"/>
    <updated>2026-11-18T10:00:00Z</updated>
    <author><name>Sarawak Labs</name></author>
    <summary>Write PHPUnit tests.</summary>
  </entry>
</feed>
XML;

    public function test_rss_items_are_parsed(): void
    {
        $jobs = (new RssJobFetcher)->parse(self::RSS);

        $this->assertCount(2, $jobs);

        $this->assertSame('PHP Developer', $jobs[0]['title']);
        $this->assertSame('Acme Sdn Bhd', $jobs[0]['company']);
        $this->assertSame('Kuching', $jobs[0]['location']);
        $this->assertSame('Build with Laravel and PostgreSQL.', $jobs[0]['description']);
        $this->assertSame('https://example.com/1', $jobs[0]['url']);
        $this->assertSame('2026-11-17', $jobs[0]['posted_at']->toDateString());

        $this->assertSame('IT Executive', $jobs[1]['title']);
        $this->assertSame('Borneo Tech', $jobs[1]['company']);
    }

    public function test_atom_entries_are_parsed(): void
    {
        $jobs = (new RssJobFetcher)->parse(self::ATOM);

        $this->assertCount(1, $jobs);
        $this->assertSame('QA Tester', $jobs[0]['title']);
        $this->assertSame('Sarawak Labs', $jobs[0]['company']);
        $this->assertSame('https://example.com/qa', $jobs[0]['url']);
    }

    public function test_invalid_xml_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);

        (new RssJobFetcher)->parse('this is not xml');
    }

    public function test_fetching_a_source_saves_jobs_and_logs_the_run(): void
    {
        Http::fake(['jobs.example.com/*' => Http::response(self::RSS, 200)]);
        $user = User::factory()->create();
        $user->skills()->create(['name' => 'Laravel', 'level' => 3]);
        $source = JobSource::factory()->create(['user_id' => $user->id, 'url' => 'https://jobs.example.com/feed.xml']);

        $log = app(SourceFetchService::class)->fetch($source);

        $this->assertSame('success', $log->status);
        $this->assertSame(2, $log->jobs_found);
        $this->assertSame(2, $log->jobs_created);
        $this->assertSame(2, JobListing::count());
        $this->assertNotNull($source->fresh()->last_fetched_at);

        $php = JobListing::where('title', 'PHP Developer')->firstOrFail();
        $this->assertSame($source->id, $php->job_source_id);
        $this->assertSame(33, $php->match_score); // PHP, Laravel, PostgreSQL found; only Laravel owned
    }

    public function test_a_job_without_a_company_uses_the_source_name(): void
    {
        Http::fake(['jobs.example.com/*' => Http::response(
            '<rss version="2.0"><channel><item><title>Helpdesk</title></item></channel></rss>'
        )]);
        $source = JobSource::factory()->create(['name' => 'Kuching Jobs', 'url' => 'https://jobs.example.com/f.xml']);

        app(SourceFetchService::class)->fetch($source);

        $this->assertSame('Kuching Jobs', JobListing::firstOrFail()->company);
    }

    public function test_fetching_twice_does_not_duplicate_jobs(): void
    {
        Http::fake(['jobs.example.com/*' => Http::response(self::RSS, 200)]);
        $source = JobSource::factory()->create(['url' => 'https://jobs.example.com/feed.xml']);
        $service = app(SourceFetchService::class);

        $service->fetch($source);
        $second = $service->fetch($source);

        $this->assertSame(2, $second->jobs_found);
        $this->assertSame(0, $second->jobs_created);
        $this->assertSame(2, JobListing::count());
    }

    public function test_a_broken_feed_is_logged_as_failed_without_throwing(): void
    {
        Http::fake(['jobs.example.com/*' => Http::response('Server error', 500)]);
        $source = JobSource::factory()->create(['url' => 'https://jobs.example.com/feed.xml']);

        $log = app(SourceFetchService::class)->fetch($source);

        $this->assertSame('failed', $log->status);
        $this->assertNotEmpty($log->error);
        $this->assertSame(0, JobListing::count());
    }

    public function test_a_source_can_be_added_and_fetched_from_the_ui(): void
    {
        Http::fake(['jobs.example.com/*' => Http::response(self::RSS, 200)]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('sources.store'), [
            'name' => 'Example',
            'url' => 'https://jobs.example.com/feed.xml',
        ])->assertRedirect();

        $source = $user->jobSources()->firstOrFail();

        $this->post(route('sources.fetch', $source))->assertSessionHas('status');
        $this->assertSame(2, $user->jobListings()->count());
        $this->get(route('sources.index'))->assertOk()->assertSee('Example');
    }

    public function test_a_source_url_must_be_http_or_https(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('sources.store'), ['name' => 'Bad', 'url' => 'file:///etc/passwd'])
            ->assertSessionHasErrors('url');
    }

    public function test_other_users_sources_are_off_limits(): void
    {
        $source = JobSource::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('sources.fetch', $source))
            ->assertNotFound();
    }

    public function test_the_fetch_command_only_fetches_enabled_sources(): void
    {
        Http::fake(['jobs.example.com/*' => Http::response(self::RSS, 200)]);
        $user = User::factory()->create();
        JobSource::factory()->create(['user_id' => $user->id, 'url' => 'https://jobs.example.com/on.xml']);
        JobSource::factory()->create(['user_id' => $user->id, 'url' => 'https://jobs.example.com/off.xml', 'is_enabled' => false]);

        $this->artisan('jobmatch:fetch')->assertSuccessful();

        Http::assertSentCount(1);
        $this->assertSame(2, JobListing::count());
    }
}
