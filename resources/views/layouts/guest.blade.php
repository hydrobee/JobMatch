<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen">
        <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-12">
            <div class="absolute right-4 top-4">
                <x-theme-toggle />
            </div>

            <a href="/" class="mb-8 flex items-center gap-3">
                <x-application-logo class="h-10 w-10" />
                <span class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ config('app.name', 'JobMatch') }}</span>
            </a>

            <div class="card w-full max-w-md p-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
