@extends('layouts.app')
@section('title', isset($projet) ? 'Modifier le projet' : 'Nouveau projet')
@section('page', 'projet-form')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <a href="{{ route('projets.index') }}" class="btn btn-text">← Retour</a>
        <h1>{{ isset($projet) ? 'Modifier : '.$projet->name : 'Nouveau projet' }}</h1>
    </div>
</div>

<div class="form-container">
    <form class="form" id="projet-form" novalidate data-id="{{ isset($projet) ? $projet->id : '' }}" data-api-url="{{ isset($projet) ? '/api/projets/'.$projet->id : '/api/projets' }}" data-method="{{ isset($projet) ? 'PUT' : 'POST' }}">
        @csrf
        <div class="form-messages" role="alert" aria-live="polite"></div>
        <div class="form-group">
            <label for="name" class="form-label">Nom du projet <span class="required">*</span></label>
            <input type="text" id="name" name="name" class="form-input" value="{{ $projet->name ?? '' }}" required
                data-validate="required|min:3" data-label="Nom">
            <span class="form-error"></span>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-textarea" rows="4">{{ $projet->description ?? '' }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="client_id" class="form-label">Client</label>
                <select id="client_id" name="client_id" class="form-select">
                    <option value="">— Aucun —</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (isset($projet) && $projet->client_id == $client->id) ? 'selected' : '' }}>
                        {{ $client->full_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="manager_id" class="form-label">Responsable</label>
                <select id="manager_id" name="manager_id" class="form-select">
                    <option value="">— Aucun —</option>
                    @foreach($collaborateurs as $collab)
                    <option value="{{ $collab->id }}" {{ (isset($projet) && $projet->manager_id == $collab->id) ? 'selected' : '' }}>
                        {{ $collab->full_name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="start_date" class="form-label">Date de début</label>
                <input type="date" id="start_date" name="start_date" class="form-input" value="{{ isset($projet) ? $projet->start_date?->format('Y-m-d') : '' }}">
            </div>
            <div class="form-group">
                <label for="end_date" class="form-label">Date de fin</label>
                <input type="date" id="end_date" name="end_date" class="form-input" value="{{ isset($projet) ? $projet->end_date?->format('Y-m-d') : '' }}">
            </div>
            <div class="form-group">
                <label for="status" class="form-label">Statut</label>
                <select id="status" name="status" class="form-select">
                    @foreach(['active' => 'Actif', 'paused' => 'En pause', 'completed' => 'Terminé'] as $val => $label)
                    <option value="{{ $val }}" {{ (isset($projet) && $projet->status === $val) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Membres du projet</label>
            <div id="assignees-list" class="checkbox-group">
                @foreach($collaborateurs as $collab)
                <label class="form-checkbox">
                    <input type="checkbox" name="assignees[]" value="{{ $collab->id }}"
                        {{ (isset($projet) && $projet->users->contains('id', $collab->id)) ? 'checked' : '' }}>
                    {{ $collab->full_name }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ isset($projet) ? 'Enregistrer' : 'Créer le projet' }}</button>
            <a href="{{ route('projets.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
