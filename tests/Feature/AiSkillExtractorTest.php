<?php

namespace Tests\Feature;

use App\Services\Skills\AiSkillExtractor;
use App\Services\Skills\KeywordSkillExtractor;
use App\Services\Skills\SkillExtractor;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSkillExtractorTest extends TestCase
{
    public function test_keyword_extraction_is_the_default(): void
    {
        $this->assertInstanceOf(KeywordSkillExtractor::class, app(SkillExtractor::class));
    }

    public function test_ai_extraction_can_be_switched_on_in_config(): void
    {
        config(['jobmatch.extractor' => 'ai']);

        $this->assertInstanceOf(AiSkillExtractor::class, app(SkillExtractor::class));
    }

    public function test_skills_are_read_from_the_ai_reply_and_normalised(): void
    {
        config(['jobmatch.gemini.api_key' => 'test-key', 'jobmatch.gemini.model' => 'gemini-3.5-flash-lite']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Here you go: ["nodejs", "PostgreSQL", "Basket Weaving", ""]']]]]],
        ])]);

        $skills = app(AiSkillExtractor::class)->extract('Some posting');

        $this->assertSame(['Node.js', 'PostgreSQL', 'Basket Weaving'], $skills);
        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'test-key')
            && str_contains($request->url(), 'models/gemini-3.5-flash-lite:generateContent'));
    }

    public function test_it_falls_back_to_keywords_without_an_api_key(): void
    {
        config(['jobmatch.gemini.api_key' => null]);
        Http::fake();

        $skills = app(AiSkillExtractor::class)->extract('We use PHP.');

        $this->assertSame(['PHP'], $skills);
        Http::assertNothingSent();
    }

    public function test_it_falls_back_to_keywords_when_the_api_fails(): void
    {
        config(['jobmatch.gemini.api_key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response('overloaded', 529)]);

        $this->assertSame(['PHP'], app(AiSkillExtractor::class)->extract('We use PHP.'));
    }

    public function test_it_falls_back_to_keywords_when_the_reply_is_not_json(): void
    {
        config(['jobmatch.gemini.api_key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Sorry, I cannot do that.']]]]],
        ])]);

        $this->assertSame(['PHP'], app(AiSkillExtractor::class)->extract('We use PHP.'));
    }
}
