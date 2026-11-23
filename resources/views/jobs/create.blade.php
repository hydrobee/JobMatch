<x-app-layout width="narrow">
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('Add a job') }}</h1>
                <p class="page-description">Paste a posting by hand. Skills are detected from the description and scored against yours.</p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('jobs.store') }}" class="card">
        @csrf

        <div class="card-body space-y-5">
            <div>
                <x-input-label for="title" value="Job title" />
                <x-text-input id="title" name="title" class="mt-1.5 w-full" :value="old('title')" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <x-input-label for="company" value="Company" />
                    <x-text-input id="company" name="company" class="mt-1.5 w-full" :value="old('company')" required />
                    <x-input-error :messages="$errors->get('company')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="location" value="Location" />
                    <x-text-input id="location" name="location" class="mt-1.5 w-full" :value="old('location')" />
                    <x-input-error :messages="$errors->get('location')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="url" value="Link to the posting" />
                    <x-text-input id="url" name="url" type="url" class="mt-1.5 w-full" :value="old('url')" placeholder="https://" />
                    <x-input-error :messages="$errors->get('url')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="salary" value="Salary (optional)" />
                    <x-text-input id="salary" name="salary" class="mt-1.5 w-full" :value="old('salary')" />
                </div>
                <div>
                    <x-input-label for="posted_at" value="Date posted (optional)" />
                    <x-text-input id="posted_at" name="posted_at" type="date" class="mt-1.5 w-full" :value="old('posted_at')" />
                    <x-input-error :messages="$errors->get('posted_at')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <textarea id="description" name="description" rows="10" class="textarea mt-1.5">{{ old('description') }}</textarea>
                <p class="hint">Paste the full posting; skills are detected from this text.</p>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('jobs.index') }}" class="btn-secondary">Cancel</a>
            <x-primary-button>Save job</x-primary-button>
        </div>
    </form>
</x-app-layout>
