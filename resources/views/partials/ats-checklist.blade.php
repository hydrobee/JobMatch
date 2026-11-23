<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">ATS check</h3>
            <p class="card-description">Common things that trip up applicant tracking systems. A pass here is a good sign, not a guarantee.</p>
        </div>
    </div>
    <ul class="list-divided">
        @foreach ($checks as $check)
            <li class="flex items-start gap-3 px-6 py-3 text-sm">
                @if ($check['passed'])
                    <span class="badge-success w-12 shrink-0 justify-center">Pass</span>
                    <span class="min-w-0 text-slate-700 dark:text-slate-300">{{ $check['label'] }}</span>
                @else
                    <span class="badge-danger w-12 shrink-0 justify-center">Fix</span>
                    <div class="min-w-0">
                        <div class="font-medium text-slate-900 dark:text-white">{{ $check['label'] }}</div>
                        <div class="mt-0.5 text-slate-500 dark:text-slate-400">{{ $check['tip'] }}</div>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
