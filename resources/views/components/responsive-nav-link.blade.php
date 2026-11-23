@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex w-full items-center rounded-lg bg-indigo-50 px-3 py-2 text-start text-sm font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300'
            : 'flex w-full items-center rounded-lg px-3 py-2 text-start text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
