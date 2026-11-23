<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen">
        <div class="flex min-h-screen flex-col">
            <header class="border-b border-slate-200 dark:border-slate-800">
                <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <a href="/" class="flex items-center gap-2.5">
                        <x-application-logo class="h-8 w-8" />
                        <span class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">{{ config('app.name', 'JobMatch') }}</span>
                    </a>

                    <nav class="flex items-center gap-2">
                        <x-theme-toggle />
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn-primary">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="btn-ghost">Log in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn-primary">Get started</a>
                                @endif
                            @endauth
                        @endif
                    </nav>
                </div>
            </header>

            <main class="flex-1">
                <section class="mx-auto max-w-6xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8">
                    <span class="badge-info">Your job search, organised</span>
                    <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl dark:text-white">
                        Find the jobs that fit you, and land them.
                    </h1>
                    <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-600 dark:text-slate-400">
                        Collect postings from your favourite feeds, see how well each one matches your skills,
                        tailor your resume in minutes and track every application in one place.
                    </p>
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primary h-11 px-6">Go to dashboard</a>
                        @else
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-primary h-11 px-6">Create a free account</a>
                            @endif
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="btn-secondary h-11 px-6">Log in</a>
                            @endif
                        @endauth
                    </div>
                </section>

                <section class="mx-auto grid max-w-6xl grid-cols-1 gap-6 px-4 pb-24 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
                    @foreach ([
                        ['Collect', 'Pull new postings from RSS and Atom feeds every morning, or add them by hand.', 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z'],
                        ['Match', 'Every job is scored against your skills, so the best fits rise to the top.', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                        ['Tailor', 'Create a focused resume for each posting from your master copy, with an ATS check.', 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
                        ['Track', 'Move applications from saved to offer, with follow-up reminders and a daily digest.', 'M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6.75'],
                    ] as [$title, $text, $icon])
                        <div class="card p-6 text-left">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                                </svg>
                            </div>
                            <h2 class="mt-4 text-base font-semibold text-slate-900 dark:text-white">{{ $title }}</h2>
                            <p class="mt-2 text-sm text-muted">{{ $text }}</p>
                        </div>
                    @endforeach
                </section>
            </main>

            <footer class="border-t border-slate-200 dark:border-slate-800">
                <div class="mx-auto max-w-6xl px-4 py-6 text-xs text-slate-500 sm:px-6 lg:px-8 dark:text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'JobMatch') }}
                </div>
            </footer>
        </div>
    </body>
</html>
