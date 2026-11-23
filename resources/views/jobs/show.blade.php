<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            <a href="{{ route('jobs.index') }}" class="inline-flex w-fit items-center gap-1 text-sm text-muted hover:text-slate-900 dark:hover:text-white">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                All jobs
            </a>
            <div class="page-header sm:items-start">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="page-title">{{ $listing->title }}</h1>
                        @if ($listing->is_expired)
                            <span class="badge-neutral">Expired</span>
                        @endif
                    </div>
                    <p class="page-description">
                        <span class="font-medium text-slate-700 dark:text-slate-200">{{ $listing->company }}</span>
                        @if ($listing->location) · {{ $listing->location }}@endif
                        @if ($listing->source) · {{ $listing->source->name }}@endif
                        · {{ ($listing->posted_at ?? $listing->created_at)->format('j M Y') }}
                        @if ($listing->salary) · {{ $listing->salary }}@endif
                    </p>
                </div>
                <div class="shrink-0">
                    @include('partials.score', ['score' => $listing->match_score])
                </div>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
        <div class="min-w-0 space-y-6 lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Skill match</h3>
                </div>
                <div class="card-body">
                    @if ($match['matched'] === [] && $match['missing'] === [])
                        <p class="text-sm text-muted">No known skills were found in this posting.</p>
                    @else
                        <div class="space-y-5">
                            <div>
                                <div class="section-label mb-2">You have</div>
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($match['matched'] as $skill)
                                        <span class="badge-success badge-wrap py-1 text-sm">{{ $skill }}</span>
                                    @empty
                                        <span class="text-sm text-muted">None of the listed skills yet.</span>
                                    @endforelse
                                </div>
                            </div>
                            <div>
                                <div class="section-label mb-2">Missing</div>
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($match['missing'] as $skill)
                                        <span class="badge-danger badge-wrap py-1 text-sm">{{ $skill }}</span>
                                    @empty
                                        <span class="text-sm text-muted">Nothing missing.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($listing->description)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Description</h3>
                    </div>
                    <div class="card-body whitespace-pre-line break-words text-sm leading-relaxed text-slate-700 dark:text-slate-300">{{ $listing->description }}</div>
                </div>
            @endif
        </div>

        <aside class="card lg:sticky lg:top-24">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body flex flex-col gap-3">
                @if ($listing->application)
                    <a href="{{ route('applications.show', $listing->application) }}" class="btn-secondary w-full">
                        In tracker: {{ $listing->application->stage->label() }}
                    </a>
                @else
                    <form method="POST" action="{{ route('applications.store') }}">
                        @csrf
                        <input type="hidden" name="job_listing_id" value="{{ $listing->id }}">
                        <x-primary-button class="w-full">Save to tracker</x-primary-button>
                    </form>
                @endif

                @if ($listing->tailoredResume)
                    <a href="{{ route('resumes.show', $listing->tailoredResume) }}" class="btn-secondary w-full">View tailored resume</a>
                @else
                    <form method="POST" action="{{ route('jobs.resume', $listing) }}">
                        @csrf
                        <x-secondary-button type="submit" class="w-full">Tailor my resume</x-secondary-button>
                    </form>
                @endif

                @if ($listing->url)
                    <a href="{{ $listing->url }}" target="_blank" rel="noopener noreferrer" class="btn-secondary w-full">
                        Open original posting
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Zm7.25-.75a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V6.56l-5.22 5.22a.75.75 0 1 1-1.06-1.06l5.22-5.22H12.25a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" /></svg>
                    </a>
                @endif
            </div>
            <div class="card-footer justify-between">
                <form method="POST" action="{{ route('jobs.expired', $listing) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-ghost btn-sm">{{ $listing->is_expired ? 'Mark active' : 'Mark expired' }}</button>
                </form>

                <form method="POST" action="{{ route('jobs.destroy', $listing) }}" onsubmit="return confirm('Delete this job and its tracker entry?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-ghost-danger btn-sm">Delete</button>
                </form>
            </div>
        </aside>
    </div>
</x-app-layout>
