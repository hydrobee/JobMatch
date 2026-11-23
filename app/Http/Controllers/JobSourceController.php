<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnsuresOwnership;
use App\Models\JobSource;
use App\Services\Jobs\SourceFetchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobSourceController extends Controller
{
    use EnsuresOwnership;

    public function index(Request $request): View
    {
        return view('sources.index', [
            'sources' => $request->user()->jobSources()->with('latestFetchLog')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:http,https', 'max:2048'],
        ]);

        $request->user()->jobSources()->create($data);

        return back()->with('status', 'Source added.');
    }

    public function update(Request $request, JobSource $source): RedirectResponse
    {
        $this->ensureOwned($source);

        $source->update(['is_enabled' => $request->boolean('is_enabled')]);

        return back()->with('status', $source->is_enabled ? 'Source enabled.' : 'Source disabled.');
    }

    public function destroy(JobSource $source): RedirectResponse
    {
        $this->ensureOwned($source);

        // Jobs already collected stay; they just lose their source link.
        $source->delete();

        return back()->with('status', 'Source removed.');
    }

    public function fetch(JobSource $source, SourceFetchService $service): RedirectResponse
    {
        $this->ensureOwned($source);

        $log = $service->fetch($source);

        if ($log->status === 'failed') {
            return back()->with('error', 'Fetch failed: '.$log->error);
        }

        return back()->with('status', "Fetched {$log->jobs_found} jobs, {$log->jobs_created} new.");
    }
}
