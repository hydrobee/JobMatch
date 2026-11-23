<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('Jobs') }}</h1>
                <p class="page-description">Every posting you have collected, scored against your skills.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('jobs.create') }}" class="btn-primary">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" /></svg>
                    Add job
                </a>
            </div>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('jobs.index') }}" class="card">
        <div class="card-body grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
            <div class="sm:col-span-2">
                <x-input-label for="q" value="Search" />
                <x-text-input id="q" name="q" class="mt-1.5 w-full" :value="request('q')" placeholder="Title, company or description" />
            </div>
            <div>
                <x-input-label for="location" value="Location" />
                <x-text-input id="location" name="location" class="mt-1.5 w-full" :value="request('location')" />
            </div>
            <div>
                <x-input-label for="source" value="Source" />
                <select id="source" name="source" class="input mt-1.5 w-full">
                    <option value="">Any</option>
                    @foreach ($sources as $source)
                        <option value="{{ $source->id }}" @selected((string) request('source') === (string) $source->id)>{{ $source->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="min_score" value="Min match %" />
                <x-text-input id="min_score" name="min_score" type="number" min="0" max="100" class="mt-1.5 w-full" :value="request('min_score')" />
            </div>
            <div>
                <x-input-label for="posted_days" value="Posted within" />
                <select id="posted_days" name="posted_days" class="input mt-1.5 w-full">
                    <option value="">Any time</option>
                    @foreach ([1 => '24 hours', 7 => '7 days', 14 => '14 days', 30 => '30 days'] as $days => $label)
                        <option value="{{ $days }}" @selected((string) request('posted_days') === (string) $days)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-footer justify-between">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                <div class="flex items-center gap-2">
                    <label for="sort" class="text-sm text-muted">Sort by</label>
                    <select id="sort" name="sort" class="input input-sm">
                        <option value="date" @selected(request('sort') !== 'match')>Newest</option>
                        <option value="match" @selected(request('sort') === 'match')>Best match</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="checkbox" name="show_expired" value="1" class="checkbox" @checked(request()->boolean('show_expired'))>
                    Show expired jobs
                </label>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('jobs.index') }}" class="btn-ghost">Reset</a>
                <x-primary-button>Apply filters</x-primary-button>
            </div>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="list-divided">
            @forelse ($listings as $job)
                <a href="{{ route('jobs.show', $job) }}" class="list-row-link items-start">
                    <div class="min-w-0">
                        <div class="font-medium {{ $job->is_expired ? 'text-slate-400 line-through dark:text-slate-500' : 'text-slate-900 dark:text-white' }}">{{ $job->title }}</div>
                        <div class="mt-0.5 text-sm text-muted">
                            {{ $job->company }}@if ($job->location) · {{ $job->location }}@endif
                            @if ($job->source) · {{ $job->source->name }}@endif
                            · {{ ($job->posted_at ?? $job->created_at)->diffForHumans() }}
                        </div>
                        @if ($job->skills)
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach (array_slice($job->skills, 0, 6) as $skill)
                                    <span class="badge-neutral badge-wrap">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-1.5">
                        @include('partials.score', ['score' => $job->match_score])
                        @if ($job->application)
                            <span class="badge-info">In tracker: {{ $job->application->stage->label() }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <p class="empty-state">No jobs match. Add one by hand or set up a source to collect them automatically.</p>
            @endforelse
        </div>
    </div>

    {{ $listings->links() }}
</x-app-layout>
