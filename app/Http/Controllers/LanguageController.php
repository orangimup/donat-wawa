<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function show(Request $request): View
    {
        return view('user.settings.languages', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'language' => ['required', 'in:id,en'],
        ]);

        $user = $request->user();
        $user->language = $validated['language'];
        $user->save();

        // so the confirmation below is already written in the new language
        app()->setLocale($validated['language']);

        return redirect()->route('settings.languages')->with('status', __('Language updated successfully.'));
    }
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['id', 'en']), 404);

        if (auth()->check()) {
            auth()->user()->update(['language' => $locale]);
        } else {
            session(['language' => $locale]);
        }

        return back();
    }
}