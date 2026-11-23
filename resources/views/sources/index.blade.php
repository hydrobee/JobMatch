<x-app-layout width="medium">
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('Job sources') }}</h1>
                <p class="page-description">
                    Enabled sources are fetched every day at 07:00 (Kuching time) when the scheduler is running.
                    Only RSS and Atom feeds are supported; many job sites offer one for a saved search.
                </p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('sources.store') }}" class="card">
        @csrf
        <div class="card-body">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_1.5fr_auto] md:items-end">
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" class="mt-1.5 w-full" :value="old('name')" placeholder="e.g. Example Careers" required />
                </div>
                <div>
                    <x-input-label for="url" value="RSS or Atom feed URL" />
                    <x-text-input id="url" name="url" type="url" class="mt-1.5 w-full" :value="old('url')" placeholder="https://" required />
                </div>
                <x-primary-button>Add source</x-primary-button>
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
            <x-input-error :messages="$errors->get('url')" class="mt-2" />
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="list-divided">
            @forelse ($sources as $source)
                <div class="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-slate-900 dark:text-white">{{ $source->name }}</span>
                            <span class="{{ $source->is_enabled ? 'badge-success' : 'badge-neutral' }}">{{ $source->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                        </div>
                        <div class="mt-0.5 max-w-md truncate text-xs text-muted">{{ $source->url }}</div>
                        <div class="mt-1 text-xs">
                            @if ($source->latestFetchLog)
                                @if ($source->latestFetchLog->status === 'success')
                                    <span class="text-emerald-700 dark:text-emerald-400">Last fetch {{ $source->last_fetched_at?->diffForHumans() }}: {{ $source->latestFetchLog->jobs_created }} new of {{ $source->latestFetchLog->jobs_found }}</span>
                                @else
                                    <span class="text-red-700 dark:text-red-400">Last fetch failed: {{ $source->latestFetchLog->error }}</span>
                                @endif
                            @else
                                <span class="text-slate-400 dark:text-slate-500">Never fetched</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <form method="POST" action="{{ route('sources.update', $source) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="is_enabled" value="{{ $source->is_enabled ? 0 : 1 }}">
                            <button type="submit" class="btn-ghost btn-sm">{{ $source->is_enabled ? 'Disable' : 'Enable' }}</button>
                        </form>
                        <form method="POST" action="{{ route('sources.fetch', $source) }}">
                            @csrf
                            <button type="submit" class="btn-secondary btn-sm">Fetch now</button>
                        </form>
                        <form method="POST" action="{{ route('sources.destroy', $source) }}" onsubmit="return confirm('Remove this source? Jobs already collected are kept.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-ghost-danger btn-sm">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="empty-state">No sources yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
