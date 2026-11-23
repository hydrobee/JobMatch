<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen">
        <div class="flex min-h-screen flex-col">
            @include('layouts.navigation')

            <main class="flex-1">
                <div class="mx-auto w-full {{ $maxWidthClass() }} space-y-6 px-4 py-8 sm:px-6 lg:px-8">
                    @isset($header)
                        <header>
                            {{ $header }}
                        </header>
                    @endisset

                    @include('partials.flash')

                    {{ $slot }}
                </div>
            </main>

            <footer class="border-t border-slate-200 dark:border-slate-800">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-6 text-xs text-slate-500 sm:px-6 lg:px-8 dark:text-slate-400">
                    <span>&copy; {{ date('Y') }} {{ config('app.name', 'JobMatch') }}</span>
                    <span>Find, track and tailor your applications.</span>
                </div>
            </footer>
        </div>
    </body>
</html>
