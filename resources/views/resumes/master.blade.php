<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('Master resume') }}</h1>
                <p class="page-description">Everything true about you, in one place. Each job gets its own tailored copy.</p>
            </div>
            @if ($resume)
                <div class="page-actions">
                    <a href="{{ route('resumes.download', $resume) }}" class="btn-secondary">Download .txt</a>
                    <a href="{{ route('resumes.print', $resume) }}" target="_blank" class="btn-secondary">Print / save as PDF</a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('resume.save') }}" class="card lg:col-span-2">
            @csrf
            @method('PUT')

            <div class="card-header">
                <div>
                    <h3 class="card-title">Resume text</h3>
                    <p class="card-description">
                        Paste your full resume as plain text (or upload a PDF). Put everything true about you here (all projects, skills and
                        experience); each job gets its own tailored copy, so this one can be longer than a normal resume.
                        Use headings such as Summary, Skills, Experience, Projects and Education, each on its own line,
                        and simple "-" bullets.
                    </p>
                </div>
            </div>

            <div class="card-body">
                <textarea name="content" rows="26" aria-label="Resume text" class="textarea font-mono text-[13px] leading-relaxed" placeholder="Your Name&#10;email@example.com | +60 12-345 6789 | Kuching, Sarawak&#10;&#10;Summary&#10;...">{{ old('content', $resume?->content) }}</textarea>
                <x-input-error :messages="$errors->get('content')" class="mt-2" />
            </div>

            <div class="card-footer">
                <x-primary-button>Save master resume</x-primary-button>
            </div>
        </form>

        <div class="space-y-6">
            <form method="POST" action="{{ route('resume.upload') }}" enctype="multipart/form-data" class="card">
                @csrf
                <div class="card-header">
                    <h3 class="card-title">Upload a PDF</h3>
                </div>
                <div class="card-body space-y-4">
                    <p class="text-sm text-muted">
                        We read the text out of the PDF and put it in the box so you can check it. Nothing is saved
                        until you press Save, and the PDF file itself is not kept. A PDF made from a picture (a scan) has
                        no text to read, so paste the text instead.
                    </p>
                    <input type="file" name="pdf" accept="application/pdf" class="file-input" required aria-label="Resume PDF">
                    <x-input-error :messages="$errors->get('pdf')" />
                </div>
                <div class="card-footer">
                    <x-primary-button>Read PDF</x-primary-button>
                </div>
            </form>

            @if ($analysis)
                @include('partials.ats-checklist', ['checks' => $analysis['ats']])
            @endif
        </div>
    </div>
</x-app-layout>
