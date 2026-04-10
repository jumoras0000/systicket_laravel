@extends('layouts.app')
@section('title', isset($ticket) ? 'Modifier le ticket' : 'Nouveau ticket')
@section('page', 'ticket-form')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <a href="{{ route('tickets.index') }}" class="btn btn-text">← Retour</a>
        <h1>{{ isset($ticket) ? 'Modifier le ticket #'.$ticket->id : 'Nouveau ticket' }}</h1>
    </div>
</div>

<div class="form-container">
    <form class="form" id="ticket-form" novalidate data-id="{{ isset($ticket) ? $ticket->id : '' }}" data-api-url="{{ isset($ticket) ? '/api/tickets/'.$ticket->id : '/api/tickets' }}" data-method="{{ isset($ticket) ? 'PUT' : 'POST' }}">
        @csrf
        <div class="form-messages" role="alert" aria-live="polite"></div>
        <div class="form-row">
            <div class="form-group form-group-large">
                <label for="title" class="form-label">Titre <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-input" value="{{ $ticket->title ?? '' }}" required
                    data-validate="required|min:3" data-label="Titre">
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="project_id" class="form-label">Projet <span class="required">*</span></label>
                <select id="project_id" name="project_id" class="form-select" required data-validate="required" data-label="Projet">
                    <option value="">— Choisir —</option>
                    @foreach($projets as $projet)
                    <option value="{{ $projet->id }}" {{ (isset($ticket) && $ticket->project_id == $projet->id) ? 'selected' : '' }}>
                        {{ $projet->name }}
                    </option>
                    @endforeach
                </select>
                <span class="form-error"></span>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-textarea" rows="5">{{ $ticket->description ?? '' }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="priority" class="form-label">Priorité</label>
                <select id="priority" name="priority" class="form-select">
                    @foreach(['low' => 'Faible', 'normal' => 'Normale', 'high' => 'Élevée', 'critical' => 'Critique'] as $val => $label)
                    <option value="{{ $val }}" {{ (isset($ticket) && $ticket->priority === $val) ? 'selected' : ((!isset($ticket) && $val === 'normal') ? 'selected' : '') }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="type" class="form-label">Type</label>
                <select id="type" name="type" class="form-select">
                    @foreach(['included' => 'Inclus', 'billable' => 'Facturable'] as $val => $label)
                    <option value="{{ $val }}" {{ (isset($ticket) && $ticket->type === $val) ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="estimated_hours" class="form-label">Heures estimées</label>
                <input type="number" id="estimated_hours" name="estimated_hours" class="form-input" step="0.5" min="0" value="{{ $ticket->estimated_hours ?? '' }}">
            </div>
        </div>

        @if(isset($ticket))
        <div class="form-group">
            <label for="status" class="form-label">Statut</label>
            <select id="status" name="status" class="form-select">
                @foreach(['new' => 'Nouveau', 'in-progress' => 'En cours', 'waiting-client' => 'En attente client', 'done' => 'Terminé', 'to-validate' => 'À valider', 'validated' => 'Validé', 'refused' => 'Refusé'] as $val => $label)
                <option value="{{ $val }}" {{ $ticket->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="form-group">
            <label class="form-label">Assigné(s)</label>
            <div id="assignees-list" class="checkbox-group">
                @foreach($collaborateurs as $collab)
                <label class="form-checkbox">
                    <input type="checkbox" name="assignees[]" value="{{ $collab->id }}"
                        {{ (isset($ticket) && $ticket->assignees->contains('id', $collab->id)) ? 'checked' : '' }}>
                    {{ $collab->full_name }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ isset($ticket) ? 'Enregistrer' : 'Créer le ticket' }}</button>
            <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
