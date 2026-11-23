<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            <a href="{{ route('jobs.show', $listing) }}" class="inline-flex w-fit items-center gap-1 text-sm text-muted hover:text-slate-900 dark:hover:text-white">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                Back to job
            </a>
            <div class="page-header">
                <div class="min-w-0">
                    <h1 class="page-title">Cover letter for {{ $listing->title }}</h1>
                    <p class="page-description">For {{ $listing->company }}</p>
                </div>
                <div class="page-actions">
                    <a href="{{ route('cover-letters.download', $coverLetter) }}" class="btn-secondary">Download .txt</a>
                    <a href="{{ route('cover-letters.print', $coverLetter) }}" target="_blank" class="btn-secondary">Print / save as PDF</a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-5">
        <form method="POST" action="{{ route('cover-letters.update', $coverLetter) }}" class="card min-w-0 lg:col-span-3">
            @csrf
            @method('PUT')

            <div class="card-body">
                <textarea name="content" rows="24" aria-label="Cover letter text" class="textarea text-sm leading-relaxed">{{ old('content', $coverLetter->content) }}</textarea>
                <x-input-error :messages="$errors->get('content')" class="mt-2" />
            </div>

            <div class="card-footer">
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>

        <div class="min-w-0 space-y-6 lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Before you send it</h3>
                </div>
                <ul class="list-divided">
                    <li class="px-6 py-3 text-sm">Check every claim is true and matches your resume.</li>
                    <li class="px-6 py-3 text-sm">Fill in or remove anything in [square brackets].</li>
                    <li class="px-6 py-3 text-sm">Address it to a named person if the posting gives one.</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-body space-y-3">
                    <form method="POST" action="{{ route('jobs.cover-letter', $listing) }}" onsubmit="return confirm('This replaces the text on the left, including any edits you made. Continue?')">
                        @csrf
                        <input type="hidden" name="regenerate" value="1">
                        <x-secondary-button type="submit" class="w-full">{{ $aiConfigured ? 'Redo from master resume with AI' : 'Reset to template' }}</x-secondary-button>
                    </form>
                    @unless ($aiConfigured)
                        <p class="text-xs text-muted">Set GEMINI_API_KEY in .env to get a draft written from your resume.</p>
                    @endunless
                </div>
                <div class="card-footer">
                    <form method="POST" action="{{ route('cover-letters.destroy', $coverLetter) }}" onsubmit="return confirm('Delete this cover letter?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-ghost-danger btn-sm">Delete cover letter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
