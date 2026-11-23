<?php

namespace Tests\Feature;

use App\Services\Resume\AtsChecker;
use App\Services\Resume\ResumeAnalyzer;
use Tests\TestCase;

class ResumeAnalysisTest extends TestCase
{
    private const GOOD_RESUME = <<<'TXT'
Steve Example
steve@example.com | +60 12-345 6789 | Kuching, Sarawak

Summary
Software engineering graduate who builds web apps with PHP and Laravel.

Skills
PHP, Laravel, PostgreSQL, Git, Angular, JavaScript, HTML, CSS

Experience
Software Developer Intern - Example Sdn Bhd
Mar 2025 - Aug 2025
- Built a bug tracker used by a team of 6 developers.
- Wrote PHPUnit tests for the main features.
- Fixed bugs reported by testers and reviewed code with a senior developer.
- Added filters and search to the issue list so testers could find reports faster.
- Helped set up the database and wrote the migrations for the project.
- Documented the setup steps so new developers could run the project on their own.
- Took part in weekly planning and demoed finished work to the team each Friday.

Projects
CodeMark - A review tool for student programming assignments built for my final year project.
- Built the grading screens and the code upload flow with Angular.
- Stored submissions and review notes in PostgreSQL.
- Presented the finished tool to two examiners and answered their questions about the design.
- Wrote the setup guide and test plan so another student could continue the work later.
- Tested the upload flow with sample submissions and fixed the problems found along the way.

Education
Bachelor of Software Engineering (Hons), UNIMAS, 2021 - 2025
Studied software testing, databases, web development, and software design, with a final year project in code review tooling.
Completed group projects with classmates and presented the results to lecturers at the end of each semester.
TXT;

    /**
     * @param  array<int, array{label: string, passed: bool}>  $checks
     */
    private function failed(array $checks): array
    {
        return array_values(array_map(
            fn ($c) => $c['label'],
            array_filter($checks, fn ($c) => ! $c['passed']),
        ));
    }

    public function test_a_clean_resume_passes_every_check(): void
    {
        $this->assertSame([], $this->failed((new AtsChecker)->check(self::GOOD_RESUME)));
    }

    public function test_missing_contact_details_are_flagged(): void
    {
        $text = str_replace(['steve@example.com', '+60 12-345 6789'], ['', ''], self::GOOD_RESUME);

        $failed = $this->failed((new AtsChecker)->check($text));

        $this->assertContains('Email address', $failed);
        $this->assertContains('Phone number', $failed);
    }

    public function test_year_ranges_are_not_mistaken_for_a_phone_number(): void
    {
        $text = str_replace('+60 12-345 6789', '', self::GOOD_RESUME);

        $this->assertContains('Phone number', $this->failed((new AtsChecker)->check($text)));
    }

    public function test_missing_section_headings_are_flagged(): void
    {
        $text = str_replace("Skills\n", "Things I can do\n", self::GOOD_RESUME);

        $this->assertContains('"Skills" section heading', $this->failed((new AtsChecker)->check($text)));
    }

    public function test_tables_and_columns_are_flagged(): void
    {
        $text = self::GOOD_RESUME."\nPHP\t\tLaravel";

        $this->assertContains('No tables or columns', $this->failed((new AtsChecker)->check($text)));
    }

    public function test_fancy_symbols_are_flagged(): void
    {
        $text = str_replace('- Built a bug tracker', "\u{2605} Built a bug tracker", self::GOOD_RESUME);

        $this->assertContains('No icons or fancy symbols', $this->failed((new AtsChecker)->check($text)));
    }

    public function test_a_very_short_resume_is_flagged(): void
    {
        $this->assertContains('Sensible length', $this->failed((new AtsChecker)->check('Too short')));
    }

    public function test_keywords_are_split_into_found_safe_to_add_and_gaps(): void
    {
        $analysis = app(ResumeAnalyzer::class)->analyse(
            'I build things with PHP and Git.',
            ['PHP', 'Docker', 'Kubernetes'],
            ['PHP', 'Docker'],
        );

        $this->assertSame(['PHP'], $analysis['keywords']['found']);
        $this->assertSame(['Docker'], $analysis['keywords']['add_if_true']);
        $this->assertSame(['Kubernetes'], $analysis['keywords']['gaps']);
    }

    public function test_no_keyword_section_without_a_job(): void
    {
        $analysis = app(ResumeAnalyzer::class)->analyse(self::GOOD_RESUME, null, []);

        $this->assertNull($analysis['keywords']);
        $this->assertNotEmpty($analysis['ats']);
    }
}
