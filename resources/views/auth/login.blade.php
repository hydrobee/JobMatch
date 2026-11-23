<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Welcome back') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('Log in to continue your job search.') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="link text-sm" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                @endif
            </div>
            <x-text-input id="password" class="mt-1.5 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" class="checkbox" name="remember">
            <span class="text-sm text-slate-600 dark:text-slate-300">{{ __('Remember me') }}</span>
        </label>

        <x-primary-button class="w-full">{{ __('Log in') }}</x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-muted">
            {{ __("Don't have an account?") }}
            <a class="link" href="{{ route('register') }}">{{ __('Create one') }}</a>
        </p>
    @endif
</x-guest-layout>
