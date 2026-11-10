<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out anyone whose account was deactivated while they were logged in,
 * so an admin's "inactive" takes effect immediately, not only at the next login.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->status !== 'inactive') {
            return $next($request);
        }

        $reason = $user->deactivation_reason;
        $language = $request->session()->get('language');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($language) {
            $request->session()->put('language', $language);
        }

        return redirect()->route('home')->with('status', $reason
            ? __('Your account has been deactivated. Reason: :reason', ['reason' => $reason])
            : __('Your account has been deactivated.'));
    }
}
