<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('Application tracker') }}</h1>
                <p class="page-description">
                    Save jobs from the <a class="link" href="{{ route('jobs.index') }}">Jobs</a> page.
                    Change a card's stage with the dropdown on it.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($stages as $stage)
            @php($cards = $board->get($stage->value, collect()))
            <section class="flex flex-col rounded-xl border border-slate-200 bg-slate-100/70 dark:border-slate-800 dark:bg-slate-900/50">
                <div class="flex items-center justify-between px-4 py-3">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $stage->label() }}</h3>
                    <span class="badge-neutral">{{ $cards->count() }}</span>
                </div>

                <div class="flex flex-col gap-3 px-3 pb-3">
                    @forelse ($cards as $app)
                        <div class="card p-4">
                            <a href="{{ route('applications.show', $app) }}" class="group block">
                                <div class="text-sm font-medium text-slate-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400">{{ $app->jobListing->title }}</div>
                                <div class="mt-0.5 text-xs text-muted">{{ $app->jobListing->company }}@if ($app->applied_via) · via {{ $app->applied_via->label() }}@endif</div>
                            </a>
                            @if ($app->follow_up_at)
                                <div class="mt-2">
                                    <span class="{{ $app->follow_up_at->isPast() && ! $app->follow_up_at->isToday() ? 'badge-danger' : 'badge-neutral' }}">
                                        Follow up {{ $app->follow_up_at->format('j M') }}
                                    </span>
                                </div>
                            @endif
                            <form method="POST" action="{{ route('applications.stage', $app) }}" class="mt-3">
                                @csrf
                                @method('PATCH')
                                <select name="stage" class="input input-sm w-full" onchange="this.form.submit()" aria-label="Stage">
                                    @foreach ($stages as $option)
                                        <option value="{{ $option->value }}" @selected($option === $app->stage)>{{ $option->label() }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-slate-300 px-3 py-6 text-center text-xs text-slate-400 dark:border-slate-700 dark:text-slate-500">Empty</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</x-app-layout>
