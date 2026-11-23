<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnsuresOwnership;
use App\Models\CoverLetter;
use App\Models\JobListing;
use App\Services\CoverLetter\CoverLetterWriter;
use App\Services\Skills\SkillMatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CoverLetterController extends Controller
{
    use EnsuresOwnership;

    /**
     * Make (or open) the cover letter for one job.
     */
    public function generate(Request $request, JobListing $listing, CoverLetterWriter $writer, SkillMatcher $matcher): RedirectResponse
    {
        $this->ensureOwned($listing);

        $user = $request->user();
        $existing = $listing->coverLetter;

        // Never overwrite hand edits unless asked.
        if ($existing && ! $request->boolean('regenerate')) {
            return redirect()->route('cover-letters.show', $existing);
        }

        $master = $user->masterResume()->first();
        $hasMasterResume = $master && trim($master->content) !== '';

        if ($writer->isConfigured() && ! $hasMasterResume) {
            return redirect()->route('resume.master')
                ->with('error', 'Add your master resume first, so the cover letter can draw on your real experience.');
        }

        $content = null;
        $error = null;

        if ($writer->isConfigured()) {
            set_time_limit(180);

            try {
                $content = $writer->write($master->content, $listing);
                $message = 'Cover letter drafted. Check that every claim is true before you send it.';
            } catch (Throwable $e) {
                Log::warning('Cover letter writing failed: '.$e->getMessage());
                $error = 'AI writing failed ('.Str::limit($e->getMessage(), 120).'). A template was saved instead.';
            }
        } else {
            $message = 'Saved a template letter for this job. Fill in the bracketed parts; '
                .'add an GEMINI_API_KEY to .env to get a draft written from your resume.';
        }

        if ($content === null) {
            $matched = $matcher->match($listing->skills ?? [], $user->skills()->pluck('name')->all())['matched'];
            $content = $writer->template($listing, $user->name, $matched);
        }

        $coverLetter = $user->coverLetters()->updateOrCreate(
            ['job_listing_id' => $listing->id],
            [
                'title' => 'Cover letter - '.$listing->title.' - '.$listing->company,
                'content' => $content,
            ],
        );

        return redirect()->route('cover-letters.show', $coverLetter)
            ->with($error ? 'error' : 'status', $error ?? $message);
    }

    public function show(CoverLetter $coverLetter, CoverLetterWriter $writer): View
    {
        $this->ensureOwned($coverLetter);

        return view('cover-letters.show', [
            'coverLetter' => $coverLetter,
            'listing' => $coverLetter->jobListing,
            'aiConfigured' => $writer->isConfigured(),
        ]);
    }

    public function update(Request $request, CoverLetter $coverLetter): RedirectResponse
    {
        $this->ensureOwned($coverLetter);

        $coverLetter->update($request->validate([
            'content' => ['required', 'string', 'max:20000'],
        ]));

        return back()->with('status', 'Cover letter saved.');
    }

    public function download(CoverLetter $coverLetter): StreamedResponse
    {
        $this->ensureOwned($coverLetter);

        return response()->streamDownload(
            function () use ($coverLetter) {
                echo $coverLetter->content;
            },
            Str::slug($coverLetter->title ?: 'cover-letter').'.txt',
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    public function print(CoverLetter $coverLetter): View
    {
        $this->ensureOwned($coverLetter);

        return view('cover-letters.print', ['coverLetter' => $coverLetter]);
    }

    public function destroy(CoverLetter $coverLetter): RedirectResponse
    {
        $this->ensureOwned($coverLetter);

        $listing = $coverLetter->jobListing;
        $coverLetter->delete();

        return redirect()->route('jobs.show', $listing)->with('status', 'Cover letter deleted.');
    }
}
