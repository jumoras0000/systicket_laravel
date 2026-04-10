@extends('layouts.app')
@section('title', isset($contrat) ? 'Modifier le contrat' : 'Nouveau contrat')
@section('page', 'contrat-form')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <a href="{{ route('contrats.index') }}" class="btn btn-text">← Retour</a>
        <h1>{{ isset($contrat) ? 'Modifier : '.$contrat->reference : 'Nouveau contrat' }}</h1>
    </div>
</div>

<div class="form-container">
    <form class="form" id="contrat-form" novalidate data-id="{{ isset($contrat) ? $contrat->id : '' }}" data-api-url="{{ isset($contrat) ? '/api/contrats/'.$contrat->id : '/api/contrats' }}" data-method="{{ isset($contrat) ? 'PUT' : 'POST' }}">
        @csrf
        <div class="form-messages" role="alert" aria-live="polite"></div>
        <div class="form-row">
            <div class="form-group">
                <label for="reference" class="form-label">Référence <span class="required">*</span></label>
                <input type="text" id="reference" name="reference" class="form-input" value="{{ $contrat->reference ?? '' }}" required
                    data-validate="required" data-label="Référence">
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="project_id" class="form-label">Projet <span class="required">*</span></label>
                <select id="project_id" name="project_id" class="form-select" required data-validate="required" data-label="Projet">
                    <option value="">— Choisir —</option>
                    @foreach($projets as $projet)
                    <option value="{{ $projet->id }}" {{ (isset($contrat) && $contrat->project_id == $projet->id) ? 'selected' : '' }}>
                        {{ $projet->name }}
                    </option>
                    @endforeach
                </select>
                <span class="form-error"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="client_id" class="form-label">Client <span class="required">*</span></label>
                <select id="client_id" name="client_id" class="form-select" required data-validate="required" data-label="Client">
                    <option value="">— Choisir —</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (isset($contrat) && $contrat->client_id == $client->id) ? 'selected' : '' }}>
                        {{ $client->full_name }}
                    </option>
                    @endforeach
                </select>
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="status" class="form-label">Statut</label>
                <select id="status" name="status" class="form-select">
                    @foreach(['active' => 'Actif', 'expired' => 'Expiré', 'cancelled' => 'Annulé'] as $val => $label)
                    <option value="{{ $val }}" {{ (isset($contrat) && $contrat->status === $val) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="hours" class="form-label">Heures <span class="required">*</span></label>
                <input type="number" id="hours" name="hours" class="form-input" step="0.5" min="0" value="{{ $contrat->hours ?? '' }}" required
                    data-validate="required|min:0" data-label="Heures">
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="rate" class="form-label">Tarif horaire (€)</label>
                <input type="number" id="rate" name="rate" class="form-input" step="0.01" min="0" value="{{ $contrat->rate ?? '' }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="start_date" class="form-label">Date de début</label>
                <input type="date" id="start_date" name="start_date" class="form-input" value="{{ isset($contrat) ? $contrat->start_date?->format('Y-m-d') : '' }}">
            </div>
            <div class="form-group">
                <label for="end_date" class="form-label">Date de fin</label>
                <input type="date" id="end_date" name="end_date" class="form-input" value="{{ isset($contrat) ? $contrat->end_date?->format('Y-m-d') : '' }}">
            </div>
        </div>

        <div class="form-group">
            <label for="notes" class="form-label">Notes</label>
            <textarea id="notes" name="notes" class="form-textarea" rows="3">{{ $contrat->notes ?? '' }}</textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ isset($contrat) ? 'Enregistrer' : 'Créer le contrat' }}</button>
            <a href="{{ route('contrats.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
