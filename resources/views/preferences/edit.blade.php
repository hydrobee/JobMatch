<x-app-layout width="narrow">
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('Job preferences') }}</h1>
                <p class="page-description">What you are looking for, and how often you want to hear about it.</p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('preferences.update') }}" class="card">
        @csrf
        @method('PATCH')

        <div class="card-header">
            <h3 class="card-title">Search</h3>
        </div>
        <div class="card-body space-y-5">
            <div>
                <x-input-label for="target_roles" value="Roles you are looking for" />
                <x-text-input id="target_roles" name="target_roles" class="mt-1.5 w-full" :value="old('target_roles', $user->target_roles)" placeholder="e.g. Software developer, IT executive" />
                <x-input-error :messages="$errors->get('target_roles')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="preferred_locations" value="Preferred locations" />
                <x-text-input id="preferred_locations" name="preferred_locations" class="mt-1.5 w-full" :value="old('preferred_locations', $user->preferred_locations)" placeholder="e.g. Kuching, Kota Samarahan" />
                <x-input-error :messages="$errors->get('preferred_locations')" class="mt-2" />
            </div>
            <label class="flex items-center gap-3 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="willing_to_relocate" value="1" class="checkbox" @checked(old('willing_to_relocate', $user->willing_to_relocate))>
                Willing to relocate
            </label>
        </div>

        <div class="card-header border-t">
            <h3 class="card-title">Email digest</h3>
        </div>
        <div class="card-body space-y-5">
            <label class="flex items-center gap-3 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="digest_enabled" value="1" class="checkbox" @checked(old('digest_enabled', $user->digest_enabled))>
                Send me a daily email digest (new matches and follow-ups that are due)
            </label>
            <div>
                <x-input-label for="digest_min_score" value="Only include new jobs with at least this match %" />
                <x-text-input id="digest_min_score" name="digest_min_score" type="number" min="0" max="100" class="mt-1.5 w-32" :value="old('digest_min_score', $user->digest_min_score)" required />
                <x-input-error :messages="$errors->get('digest_min_score')" class="mt-2" />
            </div>
        </div>

        <div class="card-footer">
            <x-primary-button>Save preferences</x-primary-button>
        </div>
    </form>
</x-app-layout>
