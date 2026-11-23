<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            <a href="{{ route('applications.index') }}" class="inline-flex w-fit items-center gap-1 text-sm text-muted hover:text-slate-900 dark:hover:text-white">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                Back to board
            </a>
            <div class="page-header">
                <div class="min-w-0">
                    <h1 class="page-title">{{ $application->jobListing->title }}</h1>
                    <p class="page-description">{{ $application->jobListing->company }}</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('jobs.show', $application->jobListing) }}" class="btn-secondary">View job</a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('applications.update', $application) }}" class="card min-w-0 lg:col-span-2">
            @csrf
            @method('PATCH')

            <div class="card-header">
                <h3 class="card-title">Details</h3>
            </div>

            <div class="card-body space-y-5">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="stage" value="Stage" />
                        <select id="stage" name="stage" class="input mt-1.5 w-full">
                            @foreach ($stages as $stage)
                                <option value="{{ $stage->value }}" @selected(old('stage', $application->stage->value) === $stage->value)>{{ $stage->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('stage')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="contact_person" value="Contact person" />
                        <x-text-input id="contact_person" name="contact_person" class="mt-1.5 w-full" :value="old('contact_person', $application->contact_person)" />
                    </div>
                    <div>
                        <x-input-label for="applied_at" value="Date applied" />
                        <x-text-input id="applied_at" name="applied_at" type="date" class="mt-1.5 w-full" :value="old('applied_at', $application->applied_at?->toDateString())" />
                        <x-input-error :messages="$errors->get('applied_at')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="applied_via" value="Applied via" />
                        <select id="applied_via" name="applied_via" class="input mt-1.5 w-full">
                            <option value="">Not set</option>
                            @foreach ($channels as $channel)
                                <option value="{{ $channel->value }}" @selected(old('applied_via', $application->applied_via?->value) === $channel->value)>{{ $channel->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('applied_via')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="follow_up_at" value="Follow up on" />
                        <x-text-input id="follow_up_at" name="follow_up_at" type="date" class="mt-1.5 w-full" :value="old('follow_up_at', $application->follow_up_at?->toDateString())" />
                        <x-input-error :messages="$errors->get('follow_up_at')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="resume_version" value="Resume version used" />
                        <x-text-input id="resume_version" name="resume_version" class="mt-1.5 w-full" :value="old('resume_version', $application->resume_version)" placeholder="e.g. resume-2026-11-backend.pdf" />
                    </div>
                </div>
                <div>
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" name="notes" rows="6" class="textarea mt-1.5">{{ old('notes', $application->notes) }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>
            </div>

            <div class="card-footer">
                <x-primary-button>Save changes</x-primary-button>
            </div>
        </form>

        <div class="space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">History</h3>
                </div>
                <ol class="card-body space-y-4">
                    @foreach ($application->stageChanges as $change)
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-indigo-500"></span>
                            <div class="text-sm">
                                <div class="font-medium text-slate-900 dark:text-white">
                                    @if ($change->from_stage)
                                        {{ ucfirst($change->from_stage) }} → {{ ucfirst($change->to_stage) }}
                                    @else
                                        Saved to tracker
                                    @endif
                                </div>
                                <div class="text-xs text-muted">{{ $change->created_at->format('j M Y, g:i a') }}</div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="card border-red-200 dark:border-red-500/30">
                <div class="card-body flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Remove from tracker</h3>
                        <p class="mt-1 text-xs text-muted">The job itself is kept.</p>
                    </div>
                    <form method="POST" action="{{ route('applications.destroy', $application) }}" onsubmit="return confirm('Remove this from your tracker? The job itself is kept.')">
                        @csrf
                        @method('DELETE')
                        <x-danger-button class="btn-sm">Remove</x-danger-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
