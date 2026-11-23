<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Reset your password') }}</h1>
        <p class="mt-1 text-sm text-muted">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">{{ __('Email Password Reset Link') }}</x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        <a class="link" href="{{ route('login') }}">{{ __('Back to log in') }}</a>
    </p>
</x-guest-layout>
