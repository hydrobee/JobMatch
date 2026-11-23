@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex h-9 items-center rounded-lg bg-slate-100 px-3 text-sm font-medium text-slate-900 dark:bg-slate-800 dark:text-white'
            : 'inline-flex h-9 items-center rounded-lg px-3 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active ?? false) aria-current="page" @endif>
    {{ $slot }}
</a>
