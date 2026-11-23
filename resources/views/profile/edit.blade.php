<x-app-layout width="narrow">
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('Profile') }}</h1>
                <p class="page-description">{{ __('Manage your account details and security.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="card card-body">
        @include('profile.partials.update-profile-information-form')
    </div>

    <div class="card card-body">
        @include('profile.partials.update-password-form')
    </div>

    <div class="card card-body border-red-200 dark:border-red-500/30">
        @include('profile.partials.delete-user-form')
    </div>
</x-app-layout>
