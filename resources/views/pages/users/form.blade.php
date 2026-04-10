@extends('layouts.app')
@section('title', isset($user) ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur')
@section('page', 'user-form')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <a href="{{ route('users.index') }}" class="btn btn-text">← Retour</a>
        <h1>{{ isset($user) ? 'Modifier : '.$user->full_name : 'Nouvel utilisateur' }}</h1>
    </div>
</div>

<div class="form-container">
    <form class="form" id="user-form" novalidate data-id="{{ isset($user) ? $user->id : '' }}" data-api-url="{{ isset($user) ? '/api/users/'.$user->id : '/api/users' }}" data-method="{{ isset($user) ? 'PUT' : 'POST' }}">
        @csrf
        <div class="form-messages" role="alert" aria-live="polite"></div>
        <div class="form-row">
            <div class="form-group">
                <label for="last_name" class="form-label">Nom <span class="required">*</span></label>
                <input type="text" id="last_name" name="last_name" class="form-input" value="{{ $user->last_name ?? '' }}" required
                    data-validate="required|min:2" data-label="Nom">
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="first_name" class="form-label">Prénom <span class="required">*</span></label>
                <input type="text" id="first_name" name="first_name" class="form-input" value="{{ $user->first_name ?? '' }}" required
                    data-validate="required|min:2" data-label="Prénom">
                <span class="form-error"></span>
            </div>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-input" value="{{ $user->email ?? '' }}" required
                data-validate="required|email" data-label="Email">
            <span class="form-error"></span>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="role" class="form-label">Rôle <span class="required">*</span></label>
                <select id="role" name="role" class="form-select" required data-validate="required" data-label="Rôle">
                    @foreach(['admin' => 'Administrateur', 'collaborateur' => 'Collaborateur', 'client' => 'Client'] as $val => $label)
                    <option value="{{ $val }}" {{ (isset($user) && $user->role === $val) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="status" class="form-label">Statut</label>
                <select id="status" name="status" class="form-select">
                    <option value="active" {{ (isset($user) && $user->status === 'active') ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ (isset($user) && $user->status === 'inactive') ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="form-group">
                <label for="phone" class="form-label">Téléphone</label>
                <input type="tel" id="phone" name="phone" class="form-input" value="{{ $user->phone ?? '' }}">
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Mot de passe {{ isset($user) ? '(laisser vide pour ne pas changer)' : '' }} @if(!isset($user))<span class="required">*</span>@endif</label>
            <input type="password" id="password" name="password" class="form-input"
                {{ isset($user) ? '' : 'required' }}
                data-validate="{{ isset($user) ? 'min:8' : 'required|min:8' }}" data-label="Mot de passe">
            <span class="form-error"></span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Enregistrer' : 'Créer l\'utilisateur' }}</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
