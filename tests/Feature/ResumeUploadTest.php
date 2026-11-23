<?php

namespace Tests\Feature;

use App\Models\Resume;
use App\Models\User;
use App\Services\Resume\PdfTextExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Tests\TestCase;

class ResumeUploadTest extends TestCase
{
    use RefreshDatabase;

    private function fakeExtractor(string|\Closure $result): void
    {
        $this->app->instance(PdfTextExtractor::class, new class($result) extends PdfTextExtractor
        {
            public function __construct(private string|\Closure $result)
            {
            }

            public function extract(string $path): string
            {
                return is_string($this->result) ? $this->result : ($this->result)();
            }
        });
    }

    private function pdf(): UploadedFile
    {
        return UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');
    }

    public function test_a_pdf_fills_the_resume_box_without_saving_anything(): void
    {
        $this->fakeExtractor("Summary\nPHP developer who has built web apps with Laravel and PostgreSQL.");
        $user = User::factory()->create();

        $this->actingAs($user)
            ->followingRedirects()
            ->post(route('resume.upload'), ['pdf' => $this->pdf()])
            ->assertOk()
            ->assertSee('PHP developer who has built web apps')
            ->assertSee('Text pulled from your PDF');

        $this->assertSame(0, Resume::count());
    }

    public function test_the_pdf_does_not_overwrite_an_existing_master_until_saved(): void
    {
        $this->fakeExtractor("Summary\nNew text from the PDF that is long enough to count as a resume.");
        $user = User::factory()->create();
        $user->resumes()->create(['title' => 'Master resume', 'content' => 'My saved master']);

        $this->actingAs($user)->post(route('resume.upload'), ['pdf' => $this->pdf()]);

        $this->assertSame('My saved master', $user->masterResume()->first()->content);
    }

    public function test_only_pdf_files_are_accepted(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('resume.upload'), ['pdf' => UploadedFile::fake()->create('resume.txt', 10, 'text/plain')])
            ->assertSessionHasErrors('pdf');
    }

    public function test_a_file_is_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('resume.upload'), [])
            ->assertSessionHasErrors('pdf');
    }

    public function test_a_pdf_with_no_text_gives_a_helpful_error(): void
    {
        $this->fakeExtractor('');

        $this->actingAs(User::factory()->create())
            ->post(route('resume.upload'), ['pdf' => $this->pdf()])
            ->assertRedirect(route('resume.master'))
            ->assertSessionHas('error');
    }

    public function test_a_unreadable_pdf_gives_an_error_instead_of_crashing(): void
    {
        $this->fakeExtractor(fn () => throw new RuntimeException('Secret file structure is broken'));

        $this->actingAs(User::factory()->create())
            ->post(route('resume.upload'), ['pdf' => $this->pdf()])
            ->assertRedirect(route('resume.master'))
            ->assertSessionHas('error');
    }

    public function test_extracted_text_is_tidied(): void
    {
        $messy = "Steve\r\n\r\n\r\n\r\n\u{2022} Built a \u{FB01}ltering tool  \r\n\u{25CF}Wrote tests\u{00A0}for it\r\n\x0C";

        $clean = (new PdfTextExtractor)->normalise($messy);

        $this->assertSame("Steve\n\n- Built a filtering tool\n- Wrote tests for it", $clean);
    }
}
