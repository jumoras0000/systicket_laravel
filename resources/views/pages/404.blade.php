@extends('layouts.auth')
@section('title', 'Page introuvable')
@section('body-class', 'auth-page')
@section('skip-target', 'main-404')
@section('skip-label', 'Aller au contenu')
@section('tagline', 'Page introuvable')

@section('content')
<div class="error-page-inner" id="main-404">
    <p class="error-404-number">404</p>
    <h1 class="error-404-title">Page introuvable</h1>
    <p class="error-404-text">
        La page que vous recherchez n'existe pas ou a été déplacée.
    </p>
    <div class="error-actions">
        <a href="{{ url('/') }}" class="btn btn-primary">Accueil</a>
        <a href="javascript:history.back()" class="btn btn-secondary">Retour</a>
    </div>
</div>
@endsection
