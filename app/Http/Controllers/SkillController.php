<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnsuresOwnership;
use App\Models\Skill;
use App\Services\Jobs\JobListingService;
use App\Services\Skills\SkillCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkillController extends Controller
{
    use EnsuresOwnership;

    public function index(Request $request): View
    {
        return view('skills.index', [
            'skills' => $request->user()->skills()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, JobListingService $listings): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'level' => ['required', 'integer', 'between:1,5'],
        ]);

        $request->user()->skills()->updateOrCreate(
            ['name' => SkillCatalog::canonical($data['name'])],
            ['level' => $data['level']],
        );

        $listings->rescoreAll($request->user());

        return back()->with('status', 'Skill saved and match scores updated.');
    }

    public function update(Request $request, Skill $skill): RedirectResponse
    {
        $this->ensureOwned($skill);

        $skill->update($request->validate([
            'level' => ['required', 'integer', 'between:1,5'],
        ]));

        return back()->with('status', 'Skill level updated.');
    }

    public function destroy(Skill $skill, JobListingService $listings): RedirectResponse
    {
        $this->ensureOwned($skill);

        $skill->delete();

        $listings->rescoreAll($skill->user);

        return back()->with('status', 'Skill removed and match scores updated.');
    }
}
