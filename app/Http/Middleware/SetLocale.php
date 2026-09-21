<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['en', 'id'];

    /**
     * Picks the language for this request: the logged-in user's saved choice,
     * otherwise the one stored in the session (guests), otherwise the app default.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->language ?? session('language') ?? config('app.locale');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}