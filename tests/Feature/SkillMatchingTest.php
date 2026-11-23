<?php

namespace Tests\Feature;

use App\Services\Skills\KeywordSkillExtractor;
use App\Services\Skills\SkillCatalog;
use App\Services\Skills\SkillMatcher;
use Tests\TestCase;

class SkillMatchingTest extends TestCase
{
    public function test_keyword_extractor_finds_whole_word_skills(): void
    {
        $skills = (new KeywordSkillExtractor)->extract(
            'Build apps in JavaScript and Node.js with PostgreSQL. C# is a plus.'
        );

        $this->assertContains('JavaScript', $skills);
        $this->assertContains('Node.js', $skills);
        $this->assertContains('PostgreSQL', $skills);
        $this->assertContains('C#', $skills);
        // "Java" must not be found inside "JavaScript".
        $this->assertNotContains('Java', $skills);
    }

    public function test_keyword_extractor_is_case_insensitive_and_returns_each_skill_once(): void
    {
        $skills = (new KeywordSkillExtractor)->extract('php PHP Php and LARAVEL');

        $this->assertSame(['PHP', 'Laravel'], $skills);
    }

    public function test_catalog_maps_aliases_to_canonical_names(): void
    {
        $this->assertSame('Node.js', SkillCatalog::canonical('nodejs'));
        $this->assertSame('PostgreSQL', SkillCatalog::canonical(' postgres '));
        $this->assertSame('Underwater Basket Weaving', SkillCatalog::canonical('Underwater Basket Weaving'));
    }

    public function test_matcher_scores_matched_and_missing_skills(): void
    {
        $result = (new SkillMatcher)->match(
            ['PHP', 'Laravel', 'Docker', 'Git'],
            ['php', 'LARAVEL'],
        );

        $this->assertSame(50, $result['score']);
        $this->assertSame(['PHP', 'Laravel'], $result['matched']);
        $this->assertSame(['Docker', 'Git'], $result['missing']);
    }

    public function test_matcher_has_no_score_when_the_job_lists_no_skills(): void
    {
        $result = (new SkillMatcher)->match([], ['PHP']);

        $this->assertNull($result['score']);
    }
}
