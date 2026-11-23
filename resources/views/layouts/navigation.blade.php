@php
    $navItems = [
        ['dashboard', 'Dashboard', 'dashboard'],
        ['jobs.index', 'Jobs', 'jobs.*'],
        ['applications.index', 'Tracker', 'applications.*'],
        ['skills.index', 'Skills', 'skills.*'],
        ['resume.master', 'Resume', 'resume*'],
        ['sources.index', 'Sources', 'sources.*'],
    ];

    $initials = \Illuminate\Support\Str::of(Auth::user()->name)
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <x-application-logo class="h-8 w-8" />
                    <span class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">{{ config('app.name', 'JobMatch') }}</span>
                </a>

                <div class="hidden items-center gap-1 md:flex">
                    @foreach ($navItems as [$route, $label, $pattern])
                        <x-nav-link :href="route($route)" :active="request()->routeIs($pattern)">
                            {{ $label }}
                        </x-nav-link>
                    @endforeach
                </div>
            </div>

            <div class="hidden items-center gap-2 md:flex">
                <x-theme-toggle />

                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button type="button" class="flex h-10 items-center gap-2 rounded-lg pl-1 pr-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-slate-200 dark:hover:bg-slate-800">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">{{ $initials }}</span>
                            <span class="max-w-[10rem] truncate">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                            <div class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ Auth::user()->name }}</div>
                            <div class="truncate text-xs text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</div>
                        </div>

                        <div class="py-1">
                            <x-dropdown-link :href="route('preferences.edit')">{{ __('Job preferences') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        </div>

                        <div class="border-t border-slate-200 py-1 dark:border-slate-800">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center gap-1 md:hidden">
                <x-theme-toggle />

                <button type="button" @click="open = ! open" class="btn-ghost btn-icon" :aria-expanded="open" aria-label="Toggle navigation">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path x-show="! open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" x-cloak x-transition.opacity class="border-t border-slate-200 md:hidden dark:border-slate-800">
        <div class="space-y-1 px-4 py-3">
            @foreach ($navItems as [$route, $label, $pattern])
                <x-responsive-nav-link :href="route($route)" :active="request()->routeIs($pattern)">
                    {{ $label }}
                </x-responsive-nav-link>
            @endforeach
        </div>

        <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
            <div class="px-3 pb-3">
                <div class="text-sm font-medium text-slate-900 dark:text-white">{{ Auth::user()->name }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="space-y-1">
                <x-responsive-nav-link :href="route('preferences.edit')">{{ __('Job preferences') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
