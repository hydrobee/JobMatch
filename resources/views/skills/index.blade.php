<x-app-layout width="narrow">
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('My skills') }}</h1>
                <p class="page-description">Match scores compare each job against this list.</p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('skills.store') }}" class="card">
        @csrf
        <div class="card-body">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input-label for="name" value="Skill" />
                    <x-text-input id="name" name="name" class="mt-1.5 w-full" :value="old('name')" placeholder="e.g. Laravel" required />
                </div>
                <div class="sm:w-36">
                    <x-input-label for="level" value="Level (1-5)" />
                    <select id="level" name="level" class="input mt-1.5 w-full">
                        @foreach (range(1, 5) as $level)
                            <option value="{{ $level }}" @selected((int) old('level', 3) === $level)>{{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button>Add skill</x-primary-button>
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="card-header">
            <h3 class="card-title">Your skills</h3>
            <span class="badge-neutral">{{ $skills->count() }}</span>
        </div>
        <div class="list-divided">
            @forelse ($skills as $skill)
                <div class="list-row py-3">
                    <div class="font-medium text-slate-900 dark:text-white">{{ $skill->name }}</div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('skills.update', $skill) }}">
                            @csrf
                            @method('PATCH')
                            <select name="level" class="input input-sm" onchange="this.form.submit()" aria-label="Level for {{ $skill->name }}">
                                @foreach (range(1, 5) as $level)
                                    <option value="{{ $level }}" @selected($skill->level === $level)>Level {{ $level }}</option>
                                @endforeach
                            </select>
                        </form>
                        <form method="POST" action="{{ route('skills.destroy', $skill) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-ghost-danger btn-sm">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="empty-state">No skills yet. Match scores need at least one.</p>
            @endforelse
        </div>
    </div>

    <p class="text-sm text-muted">
        Common names are matched to the same skill ("nodejs" becomes "Node.js"). Adding or removing a skill
        recalculates the match score of every saved job. Skills the app can detect in job postings are listed in
        <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">config/jobmatch.php</code>.
    </p>
</x-app-layout>
