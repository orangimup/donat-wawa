@extends('layouts.app')

@section('title', 'Donat Wawa - Account Settings')
@section('body-class', 'settings-page')
@section('hide-footer')
@endsection

@push('styles')
    @vite('resources/css/settings.css')
@endpush

@section('content')

    <div class="container-ww settings-layout">

        @include('partials.settings-sidebar', ['active' => 'account'])

        <div class="settings-content">
            <h1>Account Settings</h1>

            @if (session('status'))
                <div class="settings-alert">{{ session('status') }}</div>
            @endif

            <form id="accountForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf

                <h3 class="settings-section-title settings-section-title--first">Basic info</h3>

                <div class="settings-card">
                    <div class="settings-avatar-block">
                        <div class="settings-avatar">
                            @if ($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Foto profil {{ $user->name }}">
                            @else
                                <div class="settings-avatar-initial" aria-label="Foto profil {{ $user->name }}">
                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            <button type="button" class="settings-avatar-edit" aria-label="Ganti foto profil"
                                onclick="document.getElementById('avatarInput').click()">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z">
                                    </path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </button>
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" class="settings-avatar-input"
                                onchange="submitAccountForm()">
                        </div>
                        <div class="settings-avatar-name">{{ $user->name }}</div>
                    </div>

                    <button type="button" class="settings-row" onclick="openSettingsModal('nameModal')">
                        <span class="settings-row-label">Name</span>
                        <span class="settings-row-value">
                            <span>{{ old('name', $user->name) }}</span>
                            <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </span>
                    </button>

                    <div class="settings-row settings-row-static">
                        <span class="settings-row-label">Email</span>
                        <span class="settings-row-value">
                            <span>{{ $user->email }}</span>
                            <svg class="chevron chevron-muted" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </span>
                    </div>

                    <button type="button" class="settings-row" onclick="openSettingsModal('phoneModal')">
                        <span class="settings-row-label">No. Telp</span>
                        <span class="settings-row-value">
                            <span>{{ old('phone', $user->phone) }}</span>
                            <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </span>
                    </button>
                </div>

                <!-- input asli yang dikirim ke server, disinkron dari modal -->
                <input type="hidden" name="name" id="nameInput" value="{{ old('name', $user->name) }}">
                <input type="hidden" name="phone" id="phoneInput" value="{{ old('phone', $user->phone) }}">
            </form>

            <h3 class="settings-section-title">Account info</h3>
            <div class="settings-card">
                <button type="button" class="settings-row" onclick="openSettingsModal('passwordModal')">
                    <span class="settings-row-label">Password</span>
                    <span class="settings-row-value">
                        <span class="settings-dots">••••••••••</span>
                        <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Edit Name -->
    <div class="settings-modal-overlay" id="nameModal">
        <div class="settings-modal">
            <h4 class="settings-modal-title">Edit Name</h4>
            <input type="text" class="settings-modal-input" id="nameModalInput" value="{{ old('name', $user->name) }}">
            @error('name')
                <div class="settings-error">{{ $message }}</div>
            @enderror
            <div class="settings-modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeSettingsModal('nameModal')">Cancel</button>
                <button type="button" class="btn btn-primary" id="nameSaveBtn" onclick="saveSettingsField('name')">Save
                    Changes</button>
            </div>
        </div>
    </div>

    <!-- Modal: Edit No. Telp -->
    <div class="settings-modal-overlay" id="phoneModal">
        <div class="settings-modal">
            <h4 class="settings-modal-title">Edit No. Telp</h4>
            <input type="text" class="settings-modal-input" id="phoneModalInput" value="{{ old('phone', $user->phone) }}">
            @error('phone')
                <div class="settings-error">{{ $message }}</div>
            @enderror
            <div class="settings-modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeSettingsModal('phoneModal')">Cancel</button>
                <button type="button" class="btn btn-primary" id="phoneSaveBtn" onclick="saveSettingsField('phone')">Save
                    Changes</button>
            </div>
        </div>
    </div>

    <!-- Modal: Change Password -->
    <div class="settings-modal-overlay" id="passwordModal">
        <div class="settings-modal">
            <h4 class="settings-modal-title">Change Password</h4>
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf

                <input type="password" class="settings-modal-input" name="current_password" placeholder="Enter current password"
                    autocomplete="current-password">
                @error('current_password')
                    <div class="settings-error">{{ $message }}</div>
                @enderror

                <input type="password" class="settings-modal-input" name="new_password" placeholder="Enter new password"
                    autocomplete="new-password">
                @error('new_password')
                    <div class="settings-error">{{ $message }}</div>
                @enderror

                <input type="password" class="settings-modal-input" name="new_password_confirmation"
                    placeholder="Confirm new password" autocomplete="new-password">

                <div class="settings-modal-actions">
                    <button type="button" class="btn btn-outline"
                        onclick="closeSettingsModal('passwordModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="passwordSaveBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openSettingsModal(id) {
            document.getElementById(id).classList.add('is-open');
        }
        function closeSettingsModal(id) {
            document.getElementById(id).classList.remove('is-open');
        }
        function saveSettingsField(field) {
            const modalInput = document.getElementById(field + 'ModalInput');
            const hiddenInput = document.getElementById(field + 'Input');
            hiddenInput.value = modalInput.value;

            const saveBtn = document.getElementById(field + 'SaveBtn');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving...';
            }

            document.getElementById('accountForm').submit();
        }

        function submitAccountForm() {
            const avatarEdit = document.querySelector('.settings-avatar-edit');
            if (avatarEdit) {
                avatarEdit.setAttribute('aria-busy', 'true');
                avatarEdit.style.opacity = '0.6';
            }
            document.getElementById('accountForm').submit();
        }

        @if ($errors->has('name'))
            document.addEventListener('DOMContentLoaded', () => openSettingsModal('nameModal'));
        @endif
        @if ($errors->has('phone'))
            document.addEventListener('DOMContentLoaded', () => openSettingsModal('phoneModal'));
        @endif
        @if ($errors->has('current_password') || $errors->has('new_password'))
            document.addEventListener('DOMContentLoaded', () => openSettingsModal('passwordModal'));
        @endif
    </script>
@endpush