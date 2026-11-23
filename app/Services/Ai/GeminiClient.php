<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * The only place that talks to the Gemini API. To use a different AI provider,
 * change this class (and the gemini block in config/jobmatch.php); nothing else
 * in the app knows which provider is behind it.
 */
class GeminiClient
{
    public function isConfigured(): bool
    {
        return (bool) config('jobmatch.gemini.api_key');
    }

    /**
     * Send one prompt and return the model's text reply (JSON, as requested).
     *
     * @throws RuntimeException when no key is set or the reply has no text
     */
    public function generate(string $system, string $prompt, string $model, int $maxOutputTokens = 2048, int $timeout = 60): string
    {
        $apiKey = config('jobmatch.gemini.api_key');

        if (! $apiKey) {
            throw new RuntimeException('No Gemini API key is set.');
        }

        $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
            ->timeout($timeout)
            ->acceptJson()
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent', [
                'systemInstruction' => ['parts' => [['text' => $system]]],
                'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'maxOutputTokens' => $maxOutputTokens,
                    'responseMimeType' => 'application/json',
                ],
            ])
            ->throw();

        $parts = data_get($response->json(), 'candidates.0.content.parts', []);

        // Skip any "thought" parts; join the rest.
        $text = collect($parts)
            ->reject(fn ($part) => ! empty($part['thought']))
            ->pluck('text')
            ->filter()
            ->implode('');

        if (trim($text) === '') {
            throw new RuntimeException('Gemini returned no text (the request may have been blocked).');
        }

        return $text;
    }
}
