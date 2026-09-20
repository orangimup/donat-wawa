@extends('layouts.admin')

@section('title', 'User Management — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/user.css')
@endpush

@section('content')

    <div class="admin-page-header">
        <div>
            <h1>User Management</h1>
            <p>View and manage customer accounts.</p>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-toolbar">
            <div class="admin-filter-pills">
                @php
                    $tabs = ['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'];
                @endphp
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('admin.user', array_filter(['status' => $key, 'search' => $search, 'sort' => $sort])) }}"
                        class="admin-filter-pill {{ $status === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <form method="GET" class="admin-sort" id="sortForm">
                <label for="sortTrigger">Sort by:</label>
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="sort" id="sortValue" value="{{ $sort }}">

                @php
                    $sortLabels = [
                        'latest' => 'Latest',
                        'oldest' => 'Oldest',
                        'name' => 'Name',
                        'email' => 'Email',
                    ];
                @endphp

                <div class="admin-select" id="sortSelect">
                    <button type="button" class="admin-select-trigger" id="sortTrigger" aria-haspopup="listbox"
                        aria-expanded="false">
                        <span id="sortTriggerLabel">{{ $sortLabels[$sort] ?? 'Latest' }}</span>
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <ul class="admin-select-menu" id="sortMenu" role="listbox">
                        @foreach ($sortLabels as $key => $label)
                            <li role="option" data-value="{{ $key }}"
                                class="admin-select-option {{ $sort === $key ? 'is-selected' : '' }}">{{ $label }}</li>
                        @endforeach
                    </ul>
                </div>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>No. Telp</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                            <td>{{ $user->user_code }}</td>
                            <td>
                                <div class="admin-table-name" style="display:flex; align-items:center; gap:10px;">
                                    @if ($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                            class="admin-table-thumb" style="width:34px; height:34px; border-radius:50%;">
                                    @else
                                        <span class="admin-avatar admin-avatar-initial" style="width:34px; height:34px; font-size:13px;">
                                            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                        </span>
                                    @endif
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?: '—' }}</td>
                            <td>
                                <span
                                    class="admin-status-pill {{ $user->status === 'active' ? 'admin-status-active' : 'admin-status-inactive' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.user.show', $user) }}" class="admin-icon-btn"
                                        aria-label="View {{ $user->name }}">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <button type="button" class="admin-icon-btn" aria-label="Edit status of {{ $user->name }}"
                                        onclick="openUserModal(this)"
                                        data-id="{{ $user->id }}"
                                        data-user-code="{{ $user->user_code }}"
                                        data-name="{{ $user->name }}"
                                        data-status="{{ $user->status }}"
                                        data-deactivation-reason="{{ $user->deactivation_reason }}"
                                        data-update-url="{{ route('admin.user.update', $user) }}">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="admin-empty-row">
                            <td colspan="7">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            <span class="admin-pagination-info">
                Showing {{ $users->count() }} of {{ $users->total() }} entries
            </span>
            {{ $users->onEachSide(1)->links('admin.partials.pagination') }}
        </div>
    </div>

    {{-- Account Status modal: the only thing an admin can edit on a user --}}
    <div class="admin-modal-overlay admin-modal-overlay-center" id="userModalOverlay">
        <div class="admin-modal admin-modal-sm account-status-modal">
            <div class="admin-modal-head">
                <h2>
                    <span class="account-status-modal-icon" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </span>
                    Account Status
                </h2>
                <button type="button" class="admin-modal-close" onclick="closeUserModal()" aria-label="Close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <p class="account-status-modal-desc">Control access to the application for <strong id="userModalName"></strong>.</p>

            <form id="userForm" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="_edit_id" id="userFormEditId" value="{{ old('_edit_id') }}">

                <div class="account-status-toggle" role="tablist" aria-label="Account status">
                    <button type="button" class="account-status-option is-active" id="statusOptionActive"
                        role="tab" aria-selected="true">Active</button>
                    <button type="button" class="account-status-option" id="statusOptionInactive"
                        role="tab" aria-selected="false">Inactive</button>
                </div>
                <input type="hidden" name="status" id="status" value="{{ old('status', 'active') }}">
                @error('status')
                    <span class="admin-form-error">{{ $message }}</span>
                @enderror

                <div class="admin-form-group full" id="deactivationReasonGroup">
                    <label for="deactivation_reason">Reason for Deactivation</label>
                    <textarea name="deactivation_reason" id="deactivation_reason"
                        placeholder="Enter reason...">{{ old('deactivation_reason') }}</textarea>
                    @error('deactivation_reason')
                        <span class="admin-form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="admin-form-actions account-status-modal-actions">
                    <div class="admin-form-actions-right">
                        <button type="button" class="admin-btn admin-btn-outline" onclick="closeUserModal()">Cancel</button>
                        <button type="submit" class="admin-btn">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const sortSelect = document.getElementById('sortSelect');
            const sortTrigger = document.getElementById('sortTrigger');
            const sortTriggerLabel = document.getElementById('sortTriggerLabel');
            const sortMenu = document.getElementById('sortMenu');
            const sortValue = document.getElementById('sortValue');
            const sortForm = document.getElementById('sortForm');

            function closeSortMenu() {
                sortSelect.classList.remove('is-open');
                sortTrigger.setAttribute('aria-expanded', 'false');
            }

            sortTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = sortSelect.classList.toggle('is-open');
                sortTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            sortMenu.querySelectorAll('.admin-select-option').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    sortValue.value = opt.dataset.value;
                    sortTriggerLabel.textContent = opt.textContent;
                    sortMenu.querySelectorAll('.admin-select-option').forEach(function (o) { o.classList.remove('is-selected'); });
                    opt.classList.add('is-selected');
                    closeSortMenu();
                    sortForm.submit();
                });
            });

            document.addEventListener('click', function (e) {
                if (!sortSelect.contains(e.target)) closeSortMenu();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSortMenu();
            });
        })();

        (function () {
            const overlay = document.getElementById('userModalOverlay');
            const form = document.getElementById('userForm');
            const editIdField = document.getElementById('userFormEditId');
            const statusField = document.getElementById('status');
            const optionActive = document.getElementById('statusOptionActive');
            const optionInactive = document.getElementById('statusOptionInactive');
            const reasonInput = document.getElementById('deactivation_reason');
            const modalName = document.getElementById('userModalName');

            function setStatus(isActive) {
                statusField.value = isActive ? 'active' : 'inactive';
                optionActive.classList.toggle('is-active', isActive);
                optionActive.setAttribute('aria-selected', isActive ? 'true' : 'false');
                optionInactive.classList.toggle('is-active', !isActive);
                optionInactive.setAttribute('aria-selected', !isActive ? 'true' : 'false');
            }

            optionActive.addEventListener('click', function () { setStatus(true); });
            optionInactive.addEventListener('click', function () { setStatus(false); });

            window.openUserModal = function (btn) {
                form.reset();
                form.action = btn.dataset.updateUrl;
                editIdField.value = btn.dataset.id;
                modalName.textContent = btn.dataset.name;
                reasonInput.value = btn.dataset.deactivationReason || '';
                setStatus(btn.dataset.status === 'active');

                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            };

            window.closeUserModal = function () {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            };

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeUserModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeUserModal();
            });

            @if ($errors->any())
                @if ($retryUser)
                    form.action = "{{ route('admin.user.update', $retryUser) }}";
                    modalName.textContent = "{{ $retryUser->name }}";
                @endif
                setStatus(document.getElementById('status').value === 'active');
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            @endif
        })();
    </script>
@endpush