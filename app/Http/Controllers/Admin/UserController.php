<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function index(Request $request): View
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $sort = $request->get('sort', 'latest');

        $query = User::query()
            ->where('role', 'user')
            ->search($search)
            ->ofStatus($status);

        match ($sort) {
            'name' => $query->orderBy('name'),
            'email' => $query->orderBy('email'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        $users = $query->paginate(10)->withQueryString();

        $retryUser = $request->old('_edit_id') ? User::find($request->old('_edit_id')) : null;

        return view('admin.user-index', [
            'users' => $users,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'retryUser' => $retryUser,
        ]);
    }

    public function show(User $user): View
    {
        $recentOrders = collect();

        return view('admin.user-show', [
            'user' => $user,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
            'deactivation_reason' => ['nullable', 'string', 'max:1000', 'required_if:status,inactive'],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'deactivation_reason.required_if' => 'Mohon isi alasan penonaktifan akun.',
        ]);

        $user->update([
            'status' => $validated['status'],
            'deactivation_reason' => $validated['status'] === 'inactive'
                ? $validated['deactivation_reason']
                : null,
        ]);

        return redirect()
            ->route('admin.user')
            ->with('status', 'Status akun berhasil diperbarui.');
    }
}