<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            @if ($listing)
                <a href="{{ route('jobs.show', $listing) }}" class="inline-flex w-fit items-center gap-1 text-sm text-muted hover:text-slate-900 dark:hover:text-white">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                    Back to job
                </a>
            @endif
            <div class="page-header">
                <div class="min-w-0">
                    <h1 class="page-title">{{ $listing ? 'Resume for '.$listing->title : 'Master resume' }}</h1>
                    @if ($listing)
                        <p class="page-description">Tailored for {{ $listing->company }}</p>
                    @endif
                </div>
                <div class="page-actions">
                    <a href="{{ route('resumes.download', $resume) }}" class="btn-secondary">Download .txt</a>
                    <a href="{{ route('resumes.print', $resume) }}" target="_blank" class="btn-secondary">Print / save as PDF</a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-5">
        <form method="POST" action="{{ route('resumes.update', $resume) }}" class="card min-w-0 lg:col-span-3">
            @csrf
            @method('PUT')

            <div class="card-body">
                <textarea name="content" rows="32" aria-label="Resume text" class="textarea font-mono text-[13px] leading-relaxed">{{ old('content', $resume->content) }}</textarea>
                <x-input-error :messages="$errors->get('content')" class="mt-2" />
            </div>

            <div class="card-footer">
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>

        <div class="min-w-0 space-y-6 lg:col-span-2">
            @if ($resume->ai_notes)
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">What changed, and why</h3>
                            <p class="card-description">Read each one. Only keep changes that are true for you.</p>
                        </div>
                    </div>
                    <ul class="list-divided">
                        @forelse ($resume->ai_notes['suggestions'] ?? [] as $item)
                            <li class="px-6 py-3 text-sm">
                                @if ($item['section'])
                                    <span class="section-label">{{ $item['section'] }}</span>
                                @endif
                                <div class="text-slate-900 dark:text-white">{{ $item['change'] }}</div>
                                @if ($item['reason'])
                                    <div class="mt-0.5 text-muted">{{ $item['reason'] }}</div>
                                @endif
                            </li>
                        @empty
                            <li class="px-6 py-3 text-sm text-muted">No suggestions were returned.</li>
                        @endforelse
                    </ul>

                    @if (! empty($resume->ai_notes['gaps']))
                        <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                            <h4 class="mb-2 text-sm font-semibold text-slate-900 dark:text-white">The posting asks for, but your resume does not show</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($resume->ai_notes['gaps'] as $gap)
                                    <span class="badge-danger badge-wrap py-1 text-sm">{{ $gap }}</span>
                                @endforeach
                            </div>
                            <p class="hint">Do not add these unless you really have them.</p>
                        </div>
                    @endif
                </div>
            @endif

            @if ($analysis['keywords'])
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Keywords from the posting</h3>
                    </div>
                    <div class="card-body space-y-5">
                        <div>
                            <div class="mb-2 text-sm text-muted">Already in your resume</div>
                            <div class="flex flex-wrap gap-2">
                                @forelse ($analysis['keywords']['found'] as $skill)
                                    <span class="badge-success badge-wrap py-1 text-sm">{{ $skill }}</span>
                                @empty
                                    <span class="text-sm text-slate-400 dark:text-slate-500">None yet.</span>
                                @endforelse
                            </div>
                        </div>

                        @if ($analysis['keywords']['add_if_true'])
                            <div>
                                <div class="mb-2 text-sm text-muted">On your skills list but missing from this resume. Add them if you use them:</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($analysis['keywords']['add_if_true'] as $skill)
                                        <span class="badge-warning badge-wrap py-1 text-sm">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($analysis['keywords']['gaps'])
                            <div>
                                <div class="mb-2 text-sm text-muted">Not on your skills list. Do not claim these unless true:</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($analysis['keywords']['gaps'] as $skill)
                                        <span class="badge-danger badge-wrap py-1 text-sm">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @include('partials.ats-checklist', ['checks' => $analysis['ats']])

            @if ($listing)
                <div class="card">
                    <div class="card-body space-y-3">
                        <form method="POST" action="{{ route('jobs.resume', $listing) }}" onsubmit="return confirm('This replaces the text on the left, including any edits you made. Continue?')">
                            @csrf
                            <input type="hidden" name="regenerate" value="1">
                            <x-secondary-button type="submit" class="w-full">{{ $aiConfigured ? 'Redo from master resume with AI' : 'Reset to master resume' }}</x-secondary-button>
                        </form>
                        @unless ($aiConfigured)
                            <p class="text-xs text-muted">Set GEMINI_API_KEY in .env to get AI suggestions and a tailored draft.</p>
                        @endunless
                    </div>
                    <div class="card-footer">
                        <form method="POST" action="{{ route('resumes.destroy', $resume) }}" onsubmit="return confirm('Delete this tailored resume?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-ghost-danger btn-sm">Delete this version</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
