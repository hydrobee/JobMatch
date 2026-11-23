<?php

namespace App\Services\Resume;

/**
 * Simple rule-based checks for the things that most often break applicant tracking
 * systems. It cannot promise a pass; it catches the common, fixable problems.
 */
class AtsChecker
{
    private const SECTIONS = [
        'Summary' => ['summary', 'professional summary', 'profile', 'objective', 'about me'],
        'Skills' => ['skills', 'technical skills', 'key skills'],
        'Experience' => ['experience', 'work experience', 'employment history', 'internship', 'internships', 'work history'],
        'Education' => ['education', 'academic background', 'qualifications'],
    ];

    /**
     * @return array<int, array{label: string, passed: bool, tip: string}>
     */
    public function check(string $text): array
    {
        $checks = [];

        $checks[] = $this->result(
            'Email address',
            preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $text) === 1,
            'Put your email address in the text at the top, not in an image or header.',
        );

        $checks[] = $this->result(
            'Phone number',
            $this->hasPhoneNumber($text),
            'Add a phone number near the top.',
        );

        $headings = $this->headings($text);

        foreach (self::SECTIONS as $label => $aliases) {
            $checks[] = $this->result(
                "\"{$label}\" section heading",
                count(array_intersect($aliases, $headings)) > 0,
                "Add a heading called \"{$label}\" on its own line. ATS software looks for standard headings.",
            );
        }

        $checks[] = $this->result(
            'Dates on your experience',
            preg_match('/\b(19|20)\d{2}\b/', $text) === 1,
            'Include years (for example "Mar 2025 - Aug 2025") so the ATS can read your timeline.',
        );

        $checks[] = $this->result(
            'No tables or columns',
            strpos($text, "\t") === false && preg_match('/\S {4,}\S/', $text) !== 1,
            'Tabs or wide gaps inside a line usually mean columns or a table. Use one column with one item per line.',
        );

        $checks[] = $this->result(
            'No icons or fancy symbols',
            preg_match('/[\x{2190}-\x{21FF}\x{2500}-\x{27BF}\x{2B00}-\x{2BFF}\x{1F300}-\x{1FAFF}]/u', $text) !== 1,
            'Replace arrows, boxes, stars, check marks and emoji with plain "-" bullets.',
        );

        $words = str_word_count(strip_tags($text));
        $checks[] = $this->result(
            'Sensible length',
            $words >= 200 && $words <= 1000,
            "Your resume is about {$words} words. Aim for 200 to 1000 (one to two pages).",
        );

        return $checks;
    }

    /**
     * A run of 9-15 digits (spaces, dashes and brackets allowed). Year ranges like
     * "2025 - 2026" have too few digits to count.
     */
    private function hasPhoneNumber(string $text): bool
    {
        preg_match_all('/\+?\d[\d \-()]{6,}\d/', $text, $matches);

        foreach ($matches[0] as $candidate) {
            $digits = strlen((string) preg_replace('/\D/', '', $candidate));

            if ($digits >= 9 && $digits <= 15) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lines that look like a section heading: short, on their own, optionally ending in ":".
     *
     * @return array<int, string>
     */
    private function headings(string $text): array
    {
        $found = [];

        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $line = mb_strtolower(trim($line, " \t:-_=*#"));

            if ($line !== '' && mb_strlen($line) <= 40) {
                $found[] = $line;
            }
        }

        return $found;
    }

    /**
     * @return array{label: string, passed: bool, tip: string}
     */
    private function result(string $label, bool $passed, string $tip): array
    {
        return ['label' => $label, 'passed' => $passed, 'tip' => $tip];
    }
}
