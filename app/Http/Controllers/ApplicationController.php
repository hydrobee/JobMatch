<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationChannel;
use App\Enums\ApplicationStage;
use App\Http\Controllers\Concerns\EnsuresOwnership;
use App\Models\Application;
use App\Models\JobListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    use EnsuresOwnership;

    public function index(Request $request): View
    {
        $board = $request->user()->applications()
            ->with('jobListing')
            ->orderByRaw('follow_up_at IS NULL')
            ->orderBy('follow_up_at')
            ->get()
            ->groupBy(fn (Application $a) => $a->stage->value);

        return view('applications.index', [
            'stages' => ApplicationStage::cases(),
            'board' => $board,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_listing_id' => ['required', 'integer'],
        ]);

        $listing = JobListing::findOrFail($data['job_listing_id']);
        $this->ensureOwned($listing);

        $existing = $request->user()->applications()->where('job_listing_id', $listing->id)->first();

        if ($existing) {
            return redirect()->route('applications.show', $existing)
                ->with('status', 'This job is already in your tracker.');
        }

        $application = $request->user()->applications()->create([
            'job_listing_id' => $listing->id,
            'stage' => ApplicationStage::Saved,
        ]);

        $application->stageChanges()->create(['from_stage' => null, 'to_stage' => ApplicationStage::Saved->value]);

        return redirect()->route('applications.show', $application)->with('status', 'Saved to your tracker.');
    }

    public function show(Application $application): View
    {
        $this->ensureOwned($application);

        return view('applications.show', [
            'application' => $application->load(['jobListing', 'stageChanges']),
            'stages' => ApplicationStage::cases(),
            'channels' => ApplicationChannel::cases(),
        ]);
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        $this->ensureOwned($application);

        $data = $request->validate([
            'stage' => ['required', Rule::enum(ApplicationStage::class)],
            'applied_at' => ['nullable', 'date'],
            'applied_via' => ['nullable', Rule::enum(ApplicationChannel::class)],
            'follow_up_at' => ['nullable', 'date'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'resume_version' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $application->fill([
            'applied_at' => $data['applied_at'] ?? null,
            'applied_via' => $data['applied_via'] ?? null,
            'follow_up_at' => $data['follow_up_at'] ?? null,
            'contact_person' => $data['contact_person'] ?? null,
            'resume_version' => $data['resume_version'] ?? null,
            'notes' => $data['notes'] ?? null,
        ])->save();

        $application->moveTo(ApplicationStage::from($data['stage']));

        return back()->with('status', 'Application updated.');
    }

    public function stage(Request $request, Application $application): RedirectResponse
    {
        $this->ensureOwned($application);

        $data = $request->validate([
            'stage' => ['required', Rule::enum(ApplicationStage::class)],
        ]);

        $application->moveTo(ApplicationStage::from($data['stage']));

        return back()->with('status', 'Moved to '.$application->stage->label().'.');
    }

    public function destroy(Application $application): RedirectResponse
    {
        $this->ensureOwned($application);

        $application->delete();

        return redirect()->route('applications.index')->with('status', 'Removed from your tracker.');
    }
}
