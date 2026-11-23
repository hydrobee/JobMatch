<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnsuresOwnership;
use App\Models\JobListing;
use App\Services\Jobs\JobListingService;
use App\Services\Skills\SkillMatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobListingController extends Controller
{
    use EnsuresOwnership;

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $user->jobListings()->with(['source', 'application']);

        if (($q = trim((string) $request->input('q'))) !== '') {
            $term = '%'.mb_strtolower($q).'%';
            $query->where(function ($w) use ($term) {
                $w->whereRaw('LOWER(title) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(company) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            });
        }

        if (($location = trim((string) $request->input('location'))) !== '') {
            $query->whereRaw('LOWER(location) LIKE ?', ['%'.mb_strtolower($location).'%']);
        }

        if ($request->filled('source')) {
            $query->where('job_source_id', (int) $request->input('source'));
        }

        if ($request->filled('min_score')) {
            $query->where('match_score', '>=', (int) $request->input('min_score'));
        }

        if ($request->filled('posted_days')) {
            $query->where('posted_at', '>=', now()->subDays((int) $request->input('posted_days')));
        }

        if (! $request->boolean('show_expired')) {
            $query->where('is_expired', false);
        }

        if ($request->input('sort') === 'match') {
            $query->orderByRaw('CASE WHEN match_score IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('match_score');
        } else {
            $query->orderByRaw('COALESCE(posted_at, created_at) DESC');
        }

        return view('jobs.index', [
            'listings' => $query->orderByDesc('id')->paginate(15)->withQueryString(),
            'sources' => $user->jobSources()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('jobs.create');
    }

    public function store(Request $request, JobListingService $service): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'salary' => ['nullable', 'string', 'max:255'],
            'posted_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:20000'],
        ]);

        [$listing, $created] = $service->store($request->user(), $data);

        return redirect()
            ->route('jobs.show', $listing)
            ->with('status', $created ? 'Job added.' : 'You already have this job saved, so nothing was added.');
    }

    public function show(JobListing $listing, Request $request, SkillMatcher $matcher): View
    {
        $this->ensureOwned($listing);

        $skillNames = $request->user()->skills()->pluck('name')->all();

        return view('jobs.show', [
            'listing' => $listing->load(['source', 'application', 'tailoredResume']),
            'match' => $matcher->match($listing->skills ?? [], $skillNames),
        ]);
    }

    public function toggleExpired(JobListing $listing): RedirectResponse
    {
        $this->ensureOwned($listing);

        $listing->update(['is_expired' => ! $listing->is_expired]);

        return back()->with('status', $listing->is_expired ? 'Marked as expired.' : 'Marked as active again.');
    }

    public function destroy(JobListing $listing): RedirectResponse
    {
        $this->ensureOwned($listing);

        $listing->delete();

        return redirect()->route('jobs.index')->with('status', 'Job deleted.');
    }
}
