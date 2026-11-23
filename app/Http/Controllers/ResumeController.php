<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnsuresOwnership;
use App\Models\JobListing;
use App\Models\Resume;
use App\Services\Resume\PdfTextExtractor;
use App\Services\Resume\ResumeAnalyzer;
use App\Services\Resume\ResumeTailor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ResumeController extends Controller
{
    use EnsuresOwnership;

    public function master(Request $request, ResumeAnalyzer $analyzer): View
    {
        $resume = $request->user()->masterResume()->first();

        return view('resumes.master', [
            'resume' => $resume,
            'analysis' => $resume ? $analyzer->analyse($resume->content, null, []) : null,
        ]);
    }

    public function saveMaster(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'max:30000'],
        ]);

        $user = $request->user();
        $resume = $user->masterResume()->first() ?? $user->resumes()->make(['title' => 'Master resume']);
        $resume->content = $data['content'];
        $resume->save();

        return redirect()->route('resume.master')->with('status', 'Master resume saved.');
    }

    /**
     * Read a PDF and drop its text into the master resume box for review. Nothing is saved
     * until the user presses Save, and the PDF itself is not kept.
     */
    public function uploadMaster(Request $request, PdfTextExtractor $extractor): RedirectResponse
    {
        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        try {
            $text = $extractor->extract($request->file('pdf')->getRealPath());
        } catch (Throwable $e) {
            Log::warning('PDF resume upload failed: '.$e->getMessage());

            return redirect()->route('resume.master')
                ->with('error', 'Could not read that PDF: '.Str::limit($e->getMessage(), 150));
        }

        if (mb_strlen($text) < 50) {
            return redirect()->route('resume.master')
                ->with('error', 'No text found in that PDF. It may be a scanned picture of a resume. Paste the text in by hand instead.');
        }

        return redirect()->route('resume.master')
            ->withInput(['content' => Str::limit($text, 30000, '')])
            ->with('status', 'Text pulled from your PDF. Check it, fix anything odd (columns and tables can come out jumbled), then press Save.');
    }

    /**
     * Make (or open) the version of the master resume for one job.
     */
    public function tailor(Request $request, JobListing $listing, ResumeTailor $tailor): RedirectResponse
    {
        $this->ensureOwned($listing);

        $user = $request->user();
        $master = $user->masterResume()->first();

        if (! $master || trim($master->content) === '') {
            return redirect()->route('resume.master')
                ->with('error', 'Add your master resume first, then tailor it to a job.');
        }

        $existing = $listing->tailoredResume;

        // Never overwrite hand edits unless asked.
        if ($existing && ! $request->boolean('regenerate')) {
            return redirect()->route('resumes.show', $existing);
        }

        $content = $master->content;
        $notes = null;
        $error = null;

        if ($tailor->isConfigured()) {
            set_time_limit(180);

            try {
                $result = $tailor->tailor($master->content, $listing);
                $content = $result['resume'];
                $notes = ['suggestions' => $result['suggestions'], 'gaps' => $result['gaps']];
                $message = 'Tailored resume created. Check that every change is true before you send it.';
            } catch (Throwable $e) {
                Log::warning('Resume tailoring failed: '.$e->getMessage());
                $error = 'AI tailoring failed ('.Str::limit($e->getMessage(), 120).'). A copy of your master resume was saved instead.';
            }
        } else {
            $message = 'Saved a copy of your master resume for this job. The checks below work now; '
                .'add an GEMINI_API_KEY to .env to get AI suggestions and a tailored draft.';
        }

        $resume = $user->resumes()->updateOrCreate(
            ['job_listing_id' => $listing->id],
            [
                'title' => 'Resume - '.$listing->title.' - '.$listing->company,
                'content' => $content,
                'ai_notes' => $notes,
            ],
        );

        return redirect()->route('resumes.show', $resume)
            ->with($error ? 'error' : 'status', $error ?? $message);
    }

    public function show(Request $request, Resume $resume, ResumeAnalyzer $analyzer): View
    {
        $this->ensureOwned($resume);

        $listing = $resume->jobListing;
        $userSkills = $request->user()->skills()->pluck('name')->all();

        return view('resumes.show', [
            'resume' => $resume,
            'listing' => $listing,
            'analysis' => $analyzer->analyse($resume->content, $listing ? ($listing->skills ?? []) : null, $userSkills),
            'aiConfigured' => app(ResumeTailor::class)->isConfigured(),
        ]);
    }

    public function update(Request $request, Resume $resume): RedirectResponse
    {
        $this->ensureOwned($resume);

        $resume->update($request->validate([
            'content' => ['required', 'string', 'max:30000'],
        ]));

        return back()->with('status', 'Resume saved.');
    }

    public function download(Resume $resume): StreamedResponse
    {
        $this->ensureOwned($resume);

        return response()->streamDownload(
            function () use ($resume) {
                echo $resume->content;
            },
            Str::slug($resume->title ?: 'resume').'.txt',
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    public function print(Resume $resume): View
    {
        $this->ensureOwned($resume);

        return view('resumes.print', ['resume' => $resume]);
    }

    public function destroy(Resume $resume): RedirectResponse
    {
        $this->ensureOwned($resume);

        $isMaster = $resume->isMaster();
        $resume->delete();

        return redirect()
            ->route($isMaster ? 'resume.master' : 'jobs.index')
            ->with('status', 'Resume deleted.');
    }
}
