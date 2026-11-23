<div style="font-family: Arial, sans-serif; font-size: 14px; color: #222;">
    <p>Hi {{ $user->name }},</p>

    @if ($jobs->isNotEmpty())
        <h3 style="margin-bottom: 4px;">New matches</h3>
        <ul>
            @foreach ($jobs as $job)
                <li>
                    <a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a>, {{ $job->company }}
                    @if ($job->location) ({{ $job->location }})@endif
                    - {{ $job->match_score }}% match
                </li>
            @endforeach
        </ul>
    @endif

    @if ($followUps->isNotEmpty())
        <h3 style="margin-bottom: 4px;">Follow-ups due</h3>
        <ul>
            @foreach ($followUps as $app)
                <li>
                    <a href="{{ route('applications.show', $app) }}">{{ $app->jobListing->title }}</a>, {{ $app->jobListing->company }}
                    ({{ $app->stage->label() }}, due {{ $app->follow_up_at->format('j M') }})
                </li>
            @endforeach
        </ul>
    @endif

    <p style="color: #777; font-size: 12px;">You can turn this email off under Job preferences.</p>
</div>
