@extends('layouts.auth')
@section('title', 'Inscription')
@section('body-class', 'auth-page')
@section('skip-target', 'register-form')
@section('skip-label', "Aller au formulaire d'inscription")
@section('tagline', 'Rejoignez la plateforme de gestion de tickets')

@section('features')
<ul class="auth-features" aria-hidden="true">
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">📁</span>
        <span>Projets et clients centralisés</span>
    </li>
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">📊</span>
        <span>Tableaux de bord et statistiques</span>
    </li>
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">🔐</span>
        <span>Accès sécurisé par rôle</span>
    </li>
</ul>
@endsection

@section('form-panel-class', 'auth-form-panel-scroll')

@section('content')
<header class="auth-form-header">
    <h1>Créer un compte</h1>
    <p>Remplissez le formulaire pour vous inscrire</p>
</header>

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form id="register-form" class="auth-form" action="{{ route('register') }}" method="post">
    @csrf

    <div class="auth-form-row auth-form-row-2">
        <div class="auth-form-group">
            <label for="nom" class="auth-label">Nom <span class="auth-required">*</span></label>
            <div class="auth-input-wrap auth-input-wrap-no-icon">
                <input type="text" id="nom" name="last_name" class="auth-input" placeholder="Nom" required autocomplete="family-name" value="{{ old('last_name') }}">
            </div>
        </div>
        <div class="auth-form-group">
            <label for="prenom" class="auth-label">Prénom <span class="auth-required">*</span></label>
            <div class="auth-input-wrap auth-input-wrap-no-icon">
                <input type="text" id="prenom" name="first_name" class="auth-input" placeholder="Prénom" required autocomplete="given-name" value="{{ old('first_name') }}">
            </div>
        </div>
    </div>

    <div class="auth-form-group">
        <label for="email" class="auth-label">Email <span class="auth-required">*</span></label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">✉</span>
            <input type="email" id="email" name="email" class="auth-input" placeholder="Email" required autocomplete="email" value="{{ old('email') }}">
        </div>
    </div>

    <div class="auth-form-group">
        <label for="password" class="auth-label">Mot de passe <span class="auth-required">*</span></label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">🔒</span>
            <input type="password" id="password" name="password" class="auth-input" placeholder="Mot de passe" minlength="8" required autocomplete="new-password">
        </div>
        <span class="auth-help">Minimum 8 caractères</span>
    </div>

    <div class="auth-form-group">
        <label for="password-confirm" class="auth-label">Confirmer le mot de passe <span class="auth-required">*</span></label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">🔒</span>
            <input type="password" id="password-confirm" name="password_confirmation" class="auth-input" placeholder="Confirmer le mot de passe" required autocomplete="new-password">
        </div>
    </div>

    <div class="auth-form-group">
        <span class="auth-label">Rôle <span class="auth-required">*</span></span>
        <div class="auth-radio-group">
            <label class="auth-radio">
                <input type="radio" name="role" value="collaborateur" required {{ old('role') === 'collaborateur' ? 'checked' : '' }}>
                <span class="auth-radio-dot"></span>
                <span>Collaborateur</span>
            </label>
            <label class="auth-radio">
                <input type="radio" name="role" value="client" required {{ old('role') === 'client' ? 'checked' : '' }}>
                <span class="auth-radio-dot"></span>
                <span>Client</span>
            </label>
        </div>
        <span class="auth-help">Seuls les collaborateurs et clients peuvent s'inscrire.</span>
    </div>

    <div class="auth-form-group">
        <label class="auth-checkbox">
            <input type="checkbox" name="cgu" id="cgu" required>
            <span class="auth-checkbox-box"></span>
            <span class="auth-checkbox-label">J'accepte les <a href="{{ route('cgu') }}" class="auth-link" target="_blank" rel="noopener">conditions générales d'utilisation</a> <span class="auth-required">*</span></span>
        </label>
    </div>

    <div class="auth-form-footer auth-form-footer-before-submit">
        <p>Vous avez déjà un compte ? <a href="{{ route('login') }}" class="auth-link auth-link-strong">Se connecter</a></p>
    </div>

    <div class="auth-btn-flee-wrapper">
        <button type="submit" class="auth-btn auth-btn-primary" id="auth-submit-btn">
            Créer mon compte
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/auth-button.js') }}"></script>
<script src="{{ asset('js/forms-validation.js') }}"></script>
@endpush
