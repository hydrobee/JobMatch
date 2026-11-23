<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $counts = $user->applications()
            ->selectRaw('stage, count(*) as total')
            ->groupBy('stage')
            ->pluck('total', 'stage');

        $stages = collect(ApplicationStage::cases())
            ->mapWithKeys(fn (ApplicationStage $s) => [$s->label() => (int) ($counts[$s->value] ?? 0)]);

        $topMatches = $user->jobListings()
            ->where('is_expired', false)
            ->whereNotNull('match_score')
            ->orderByDesc('match_score')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $dueFollowUps = $user->applications()
            ->with('jobListing')
            ->whereNotNull('follow_up_at')
            ->whereDate('follow_up_at', '<=', now()->addDays(3)->toDateString())
            ->whereIn('stage', [
                ApplicationStage::Saved->value,
                ApplicationStage::Applied->value,
                ApplicationStage::Interview->value,
            ])
            ->orderBy('follow_up_at')
            ->get();

        return view('dashboard', [
            'stages' => $stages,
            'totalJobs' => $user->jobListings()->count(),
            'newThisWeek' => $user->jobListings()->where('created_at', '>=', now()->subDays(7))->count(),
            'topMatches' => $topMatches,
            'dueFollowUps' => $dueFollowUps,
            'topMissing' => $this->topMissingSkills($request),
        ]);
    }

    /**
     * Skills asked for by jobs you are tracking that you do not have yet.
     *
     * @return array<string, int>
     */
    private function topMissingSkills(Request $request): array
    {
        $user = $request->user();
        $have = $user->skills()->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
        $counts = [];

        foreach ($user->jobListings()->whereHas('application')->get(['id', 'skills']) as $listing) {
            foreach ($listing->skills ?? [] as $skill) {
                if (! in_array(mb_strtolower($skill), $have, true)) {
                    $counts[$skill] = ($counts[$skill] ?? 0) + 1;
                }
            }
        }

        arsort($counts);

        return array_slice($counts, 0, 8, true);
    }
}
