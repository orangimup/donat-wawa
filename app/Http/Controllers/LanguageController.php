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
            'currentLanguage' => session('language', app()->getLocale()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'language' => ['required', 'in:id,en'],
        ]);

        session(['language' => $validated['language']]);

        // so the confirmation below is already written in the new language
        app()->setLocale($validated['language']);

        return redirect()->route('settings.languages')->with('status', __('Language updated successfully.'));
    }

    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['id', 'en']), 404);

        session(['language' => $locale]);

        return back();
    }
}