<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => __('Full name is required.'),
            'email.required' => __('Email is required.'),
            'email.email' => __('The email format is invalid.'),
            'email.unique' => __('This email is already registered. Please sign in.'),
            'phone.required' => __('Phone number is required.'),
            'password.required' => __('Password is required.'),
            'password.min' => __('Password must be at least 8 characters.'),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'language' => in_array(session('language'), ['en', 'id'], true) ? session('language') : config('app.locale'),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->back()->with('status', __('Registration successful! Welcome, :name.', ['name' => $user->name]));
    }
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => __('Email is required.'),
            'email.email' => __('The email format is invalid.'),
            'password.required' => __('Password is required.'),
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('The email or password you entered is incorrect.'),
            ]);
        }

        $request->session()->regenerate();

        if (in_array(session('language'), ['en', 'id'], true)) {
            Auth::user()->update(['language' => session('language')]);
            app()->setLocale(session('language'));
        }

        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('status', __('Signed in successfully. Welcome back!'));
        }

        return redirect()->back()->with('status', __('Signed in successfully. Welcome back!'));
    }
    public function logout(Request $request): RedirectResponse
    {
        $wasAdmin = Auth::user()?->role === 'admin';

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($wasAdmin) {
            return redirect()->route('home')->with('status', __('You have been signed out.'));
        }

        return redirect()->back()->with('status', __('You have been signed out.'));
    }
}