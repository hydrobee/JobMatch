@if (is_null($score))
    <span class="badge-neutral">No skills detected</span>
@else
    <span class="{{ $score >= 70 ? 'badge-success' : ($score >= 40 ? 'badge-warning' : 'badge-danger') }}">
        {{ $score }}% match
    </span>
@endif
