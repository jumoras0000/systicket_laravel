@extends('layouts.auth')
@section('title', 'Mot de passe oublié')
@section('body-class', 'auth-page auth-page-connexion')
@section('skip-target', 'reset-form')
@section('skip-label', 'Aller au formulaire')
@section('tagline', 'Réinitialisez votre mot de passe en quelques clics')

@section('form-wrapper-class', 'auth-form-connexion')

@section('content')
<header class="auth-form-header">
    <h1>Mot de passe oublié</h1>
    <p>Indiquez votre adresse email pour recevoir un lien de réinitialisation</p>
</header>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form id="reset-form" class="auth-form" action="{{ route('password.request') }}" method="post">
    @csrf
    <div class="auth-form-group">
        <label for="email" class="auth-label">Email</label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">✉</span>
            <input type="email" id="email" name="email" class="auth-input" placeholder="Email" required autocomplete="email">
        </div>
    </div>

    <div class="auth-btn-flee-wrapper">
        <button type="submit" class="auth-btn auth-btn-primary">
            Envoyer le lien
        </button>
    </div>
</form>

<footer class="auth-form-footer">
    <p><a href="{{ route('login') }}" class="auth-link auth-link-strong">← Retour à la connexion</a></p>
</footer>
@endsection

@push('scripts')
<script src="{{ asset('js/forms-validation.js') }}"></script>
@endpush
