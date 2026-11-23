<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('Dashboard') }}</h1>
                <p class="page-description">Welcome back, {{ Auth::user()->name }}. Here is where your search stands.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('jobs.index', ['sort' => 'match']) }}" class="btn-secondary">Browse jobs</a>
                <a href="{{ route('jobs.create') }}" class="btn-primary">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" /></svg>
                    Add job
                </a>
            </div>
        </div>
    </x-slot>

    <section class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-7">
        <div class="card p-5">
            <div class="text-sm text-muted">Jobs collected</div>
            <div class="stat-value mt-2">{{ $totalJobs }}</div>
        </div>
        <div class="card p-5">
            <div class="text-sm text-muted">New this week</div>
            <div class="stat-value mt-2">{{ $newThisWeek }}</div>
        </div>
        @foreach ($stages as $label => $count)
            <a href="{{ route('applications.index') }}" class="card p-5 transition-colors hover:border-indigo-300 dark:hover:border-indigo-500/50">
                <div class="text-sm text-muted">{{ $label }}</div>
                <div class="stat-value mt-2">{{ $count }}</div>
            </a>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="card flex flex-col">
            <div class="card-header">
                <h3 class="card-title">Best matches</h3>
                <a href="{{ route('jobs.index', ['sort' => 'match']) }}" class="link text-sm">See all</a>
            </div>
            <div class="list-divided flex-1">
                @forelse ($topMatches as $job)
                    <a href="{{ route('jobs.show', $job) }}" class="list-row-link">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $job->title }}</div>
                            <div class="truncate text-xs text-muted">{{ $job->company }}@if ($job->location) · {{ $job->location }}@endif</div>
                        </div>
                        @include('partials.score', ['score' => $job->match_score])
                    </a>
                @empty
                    <p class="empty-state">
                        No scored jobs yet. <a class="link" href="{{ route('skills.index') }}">Add your skills</a>
                        and <a class="link" href="{{ route('jobs.create') }}">add a job</a> or a
                        <a class="link" href="{{ route('sources.index') }}">source</a>.
                    </p>
                @endforelse
            </div>
        </div>

        <div class="card flex flex-col">
            <div class="card-header">
                <h3 class="card-title">Follow-ups due soon</h3>
                <a href="{{ route('applications.index') }}" class="link text-sm">Open tracker</a>
            </div>
            <div class="list-divided flex-1">
                @forelse ($dueFollowUps as $app)
                    <a href="{{ route('applications.show', $app) }}" class="list-row-link">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $app->jobListing->title }}</div>
                            <div class="truncate text-xs text-muted">{{ $app->jobListing->company }} · {{ $app->stage->label() }}</div>
                        </div>
                        <span class="{{ $app->follow_up_at->isPast() && ! $app->follow_up_at->isToday() ? 'badge-danger' : 'badge-neutral' }}">
                            {{ $app->follow_up_at->format('j M') }}
                        </span>
                    </a>
                @empty
                    <p class="empty-state">Nothing due in the next 3 days.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Skills to learn next</h3>
                <p class="card-description">Skills asked for by jobs in your tracker that are not on your skills list.</p>
            </div>
        </div>
        <div class="card-body">
            @if (empty($topMissing))
                <p class="text-sm text-muted">Nothing missing yet, or no jobs in your tracker.</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach ($topMissing as $skill => $count)
                        <span class="badge-danger badge-wrap py-1 text-sm">
                            {{ $skill }}
                            <span class="text-xs opacity-75">{{ $count }} {{ \Illuminate\Support\Str::plural('job', $count) }}</span>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
