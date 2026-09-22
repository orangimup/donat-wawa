@extends('layouts.app')

@section('title', 'Donat Wawa - ' . __('Language Settings'))
@section('body-class', 'settings-page')
@section('hide-footer')
@endsection

@push('styles')
    @vite('resources/css/settings.css')
@endpush

@section('content')

    <div class="container-ww settings-layout">

        @include('partials.settings-sidebar', ['active' => 'languages'])

        <div class="settings-content">
            <h1>{{ __('Language Settings') }}</h1>

            @if (session('status'))
                <div class="settings-alert">{{ session('status') }}</div>
            @endif

            <h3 class="settings-section-title settings-section-title--first">{{ __('Select your preferred language') }}</h3>

            <form method="POST" action="{{ route('settings.languages.update') }}">
                @csrf

                <div class="settings-card">
                    @php
                        $languages = [
                            ['code' => 'id', 'label' => 'Bahasa Indonesia (Primary)'],
                            ['code' => 'en', 'label' => 'English (US)'],
                        ];
                    @endphp

                    @foreach ($languages as $lang)
                        <label class="settings-row settings-lang-row">
                            <span class="settings-lang-option">
                                <input type="radio" name="language" value="{{ $lang['code'] }}" class="settings-radio"
                                    {{ old('language', $currentLanguage) === $lang['code'] ? 'checked' : '' }}>
                                <span class="settings-lang-label">{{ $lang['label'] }}</span>
                            </span>
                            @if ($currentLanguage === $lang['code'])
                                <span class="settings-lang-badge">{{ __('Active') }}</span>
                            @endif
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary settings-save">{{ __('Save Changes') }}</button>
            </form>
        </div>
    </div>

@endsection