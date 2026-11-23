<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreferencesController extends Controller
{
    public function edit(Request $request): View
    {
        return view('preferences.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target_roles' => ['nullable', 'string', 'max:255'],
            'preferred_locations' => ['nullable', 'string', 'max:255'],
            'digest_min_score' => ['required', 'integer', 'between:0,100'],
        ]);

        $request->user()->update($data + [
            'willing_to_relocate' => $request->boolean('willing_to_relocate'),
            'digest_enabled' => $request->boolean('digest_enabled'),
        ]);

        return back()->with('status', 'Preferences saved.');
    }
}
