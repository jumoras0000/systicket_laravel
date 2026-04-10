@extends('layouts.app')
@section('title', $projet->name)
@section('page', 'projet-detail')

@section('content')
<!-- Navigation -->
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Accueil</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('projets.index') }}">Projets</a>
    <span class="breadcrumb-separator">/</span>
    <span id="breadcrumb-projet">{{ $projet->name }}</span>
</nav>

<!-- En-tête projet -->
<header class="ticket-header">
    <div class="ticket-header-left">
        <h1 id="projet-name">{{ $projet->name }}</h1>
        <div class="ticket-meta" id="projet-meta">
            <span class="badge badge-{{ $projet->status }}" id="projet-status-badge">{{ ucfirst($projet->status) }}</span>
            <span id="projet-client-name">Client : {{ $projet->client?->full_name ?? '—' }}</span>
        </div>
    </div>
    <div class="ticket-header-right">
        @if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur())
        <a href="{{ route('projets.edit', $projet) }}" class="btn btn-secondary btn-edit-project">✏️ Éditer</a>
        <a href="{{ route('tickets.create', ['projet_id' => $projet->id]) }}" class="btn btn-primary btn-create-ticket">+ Créer un ticket</a>
        @endif
    </div>
</header>

<div class="ticket-layout">
    <div class="ticket-main">
        <!-- Informations générales -->
        <section id="editer" class="ticket-section">
            <h2>Informations générales</h2>
            <div class="ticket-description" id="projet-info">
                <p id="projet-description">{{ $projet->description ?: 'Aucune description.' }}</p>
                <p><strong>Date de début :</strong> <span id="projet-start">{{ $projet->start_date?->format('d/m/Y') ?? '—' }}</span></p>
                <p><strong>Date de fin prévue :</strong> <span id="projet-end">{{ $projet->end_date?->format('d/m/Y') ?? '—' }}</span></p>
                <p><strong>Responsable projet :</strong> <span id="projet-manager">{{ $projet->manager?->full_name ?? '—' }}</span></p>
                <p><strong>Créé le :</strong> <span id="projet-created">{{ $projet->created_at?->format('d/m/Y') ?? '—' }}</span></p>
            </div>
        </section>

        <!-- Contrat -->
        @php $contrat = $projet->contrats->first(); @endphp
        <section class="ticket-section" id="projet-contrat-section">
            <h2>Contrat</h2>
            <div class="info-card">
                <dl class="info-list">
                    <dt>Heures incluses</dt>
                    <dd class="info-list-large" id="projet-contrat-hours">{{ $contrat ? $contrat->hours . 'h' : '—' }}</dd>
                    <dt>Heures consommées</dt>
                    <dd class="info-list-xlarge" id="projet-contrat-used">{{ $contrat ? $contrat->consumed_hours . 'h' : '—' }}</dd>
                    <dt>Heures restantes</dt>
                    <dd class="info-list-success" id="projet-contrat-remaining">{{ $contrat ? $contrat->remaining_hours . 'h' : '—' }}</dd>
                    <dt>Taux horaire supplémentaire</dt>
                    <dd id="projet-contrat-rate">{{ $contrat ? number_format($contrat->rate, 2) . ' €/h' : '—' }}</dd>
                    <dt>Montant à payer</dt>
                    <dd class="info-list-xlarge" id="projet-contrat-amount">{{ $contrat && $contrat->consumed_hours > $contrat->hours ? number_format(($contrat->consumed_hours - $contrat->hours) * $contrat->rate, 2) . ' €' : '0.00 €' }}</dd>
                    <dt>Période de validité</dt>
                    <dd id="projet-contrat-period">{{ $contrat ? ($contrat->start_date?->format('d/m/Y') . ' — ' . $contrat->end_date?->format('d/m/Y')) : '—' }}</dd>
                </dl>
                @if($contrat)
                @php $pct = $contrat->hours > 0 ? round(($contrat->consumed_hours / $contrat->hours) * 100) : 0; @endphp
                <div class="project-progress project-progress-spacing">
                    <div class="progress-bar">
                        <div class="progress-fill" id="projet-contrat-progress" style="width: {{ min($pct, 100) }}%;"></div>
                    </div>
                    <span class="progress-text" id="projet-contrat-progress-text">{{ $pct }}%</span>
                </div>
                @endif
            </div>
        </section>

        <!-- Collaborateurs -->
        <section class="ticket-section">
            <h2>Collaborateurs assignés</h2>
            <div class="assignees-list" id="projet-assignees">
                @forelse($projet->users as $user)
                <div class="assignee-item">
                    <span>{{ $user->full_name }}</span>
                </div>
                @empty
                <p class="text-secondary text-sm">Aucun collaborateur assigné.</p>
                @endforelse
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur())
            <div class="mt-sm" id="add-projet-assignee-wrap">
                <select class="form-select" id="add-projet-assignee-select">
                    <option value="">+ Ajouter un collaborateur</option>
                </select>
            </div>
            @endif
        </section>

        <!-- Tickets du projet -->
        <section class="ticket-section">
            <div class="section-header">
                <h2>Tickets du projet</h2>
                @if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur())
                <a href="{{ route('tickets.create', ['projet_id' => $projet->id]) }}" class="btn btn-primary btn-small btn-create-ticket">+ Créer un ticket</a>
                @endif
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Statut</th>
                            <th>Priorité</th>
                            <th>Type</th>
                            <th>Assigné</th>
                            <th>Temps</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="projet-tickets-tbody">
                        @forelse($projet->tickets as $ticket)
                        <tr>
                            <td><a href="{{ route('tickets.show', $ticket) }}">#{{ $ticket->id }}</a></td>
                            <td>{{ $ticket->title }}</td>
                            <td><span class="badge badge-{{ $ticket->status }}">{{ ucfirst(str_replace('-', ' ', $ticket->status)) }}</span></td>
                            <td><span class="badge badge-priority-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span></td>
                            <td>{{ $ticket->type === 'billable' ? 'Facturable' : 'Inclus' }}</td>
                            <td>{{ $ticket->assignee_names ?? '—' }}</td>
                            <td>{{ $ticket->spent_hours }}h / {{ $ticket->estimated_hours ?? '—' }}h</td>
                            <td><a href="{{ route('tickets.show', $ticket) }}" class="btn btn-text btn-small">Voir</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="table-empty">Aucun ticket.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
    window.PROJET_ID = {{ $projet->id }};
</script>
@endsection
