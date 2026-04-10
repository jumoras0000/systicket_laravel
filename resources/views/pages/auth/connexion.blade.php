@extends('layouts.auth')
@section('title', 'Connexion')
@section('body-class', 'auth-page auth-page-connexion')
@section('skip-target', 'login-form')
@section('skip-label', 'Aller au formulaire de connexion')
@section('tagline', 'Gestion de tickets et suivi du temps pour les équipes')

@section('features')
<ul class="auth-features" aria-hidden="true">
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">🎫</span>
        <span>Gérez vos tickets et projets</span>
    </li>
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">⏱️</span>
        <span>Suivi du temps et rapports</span>
    </li>
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">✅</span>
        <span>Validation et facturation</span>
    </li>
</ul>
@endsection

@section('form-wrapper-class', 'auth-form-connexion')

@section('content')
<header class="auth-form-header">
    <h1>Connexion</h1>
    <p>Entrez vos identifiants pour accéder à votre espace</p>
</header>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form id="login-form" class="auth-form" action="{{ route('login') }}" method="post">
    @csrf

    <div class="auth-form-group">
        <label for="email" class="auth-label">Email ou identifiant</label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">✉</span>
            <input type="email" id="email" name="email" class="auth-input" placeholder="Email" required autocomplete="email" value="{{ old('email') }}">
        </div>
    </div>

    <div class="auth-form-group">
        <label for="password" class="auth-label">Mot de passe</label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">🔒</span>
            <input type="password" id="password" name="password" class="auth-input" placeholder="Mot de passe" required autocomplete="current-password">
        </div>
    </div>

    <div class="auth-form-row">
        <label class="auth-checkbox">
            <input type="checkbox" name="remember" id="remember">
            <span class="auth-checkbox-box"></span>
            <span class="auth-checkbox-label">Se souvenir de moi</span>
        </label>
        <a href="{{ route('password.request') }}" class="auth-link">Mot de passe oublié ?</a>
    </div>

    <div class="auth-btn-flee-wrapper">
        <button type="submit" class="auth-btn auth-btn-primary" id="auth-submit-btn">
            Se connecter
        </button>
    </div>
</form>

<footer class="auth-form-footer">
    <p>Vous n'avez pas de compte ? <a href="{{ route('register') }}" class="auth-link auth-link-strong">Créer un compte</a></p>
</footer>
@endsection

@push('scripts')
<script src="{{ asset('js/auth-button.js') }}"></script>
<script src="{{ asset('js/forms-validation.js') }}"></script>
@endpush
