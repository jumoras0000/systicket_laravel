@extends('layouts.app')
@section('title', 'Contrat '.$contrat->reference)
@section('page', 'contrat-detail')

@section('content')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Accueil</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('contrats.index') }}">Contrats</a>
    <span class="breadcrumb-separator">/</span>
    <span id="breadcrumb-contrat">{{ $contrat->reference }}</span>
</nav>

<header class="ticket-header">
    <div class="ticket-header-left">
        <h1 id="contrat-title">Contrat — {{ $contrat->reference }}</h1>
        <div class="ticket-meta">
            <span id="contrat-client">Client : {{ $contrat->client?->full_name ?? '—' }}</span>
            <span id="contrat-projet">Projet : {{ $contrat->projet?->name ?? '—' }}</span>
            <span class="badge badge-{{ $contrat->status }}" id="contrat-status-badge">{{ ucfirst($contrat->status) }}</span>
        </div>
    </div>
    <div class="ticket-header-right">
        @if(auth()->user()->isAdmin())
        <a href="{{ route('contrats.edit', $contrat) }}" class="btn btn-secondary btn-edit-contract">✏️ Éditer</a>
        @endif
    </div>
</header>

<div class="ticket-layout">
    <div class="ticket-main">
        <section class="ticket-section">
            <h2>Détail du contrat</h2>
            <div class="info-card">
                <dl class="info-list">
                    <dt>Référence</dt>
                    <dd id="contrat-reference">{{ $contrat->reference }}</dd>
                    <dt>Statut</dt>
                    <dd id="contrat-contract-status">{{ ucfirst($contrat->status) }}</dd>
                    <dt>Heures incluses</dt>
                    <dd class="info-list-large" id="contrat-hours">{{ $contrat->hours }}h</dd>
                    <dt>Heures consommées</dt>
                    <dd class="info-list-xlarge" id="contrat-used">{{ $contrat->consumed_hours }}h</dd>
                    <dt>Heures restantes</dt>
                    <dd class="info-list-success" id="contrat-remaining">{{ $contrat->remaining_hours }}h</dd>
                    <dt>Taux horaire</dt>
                    <dd id="contrat-rate">{{ $contrat->rate ? number_format($contrat->rate, 2) . ' €/h' : '—' }}</dd>
                    <dt>Période de validité</dt>
                    <dd id="contrat-period">{{ $contrat->start_date?->format('d/m/Y') ?? '—' }} — {{ $contrat->end_date?->format('d/m/Y') ?? '—' }}</dd>
                </dl>
                @php $pct = $contrat->hours > 0 ? round(($contrat->consumed_hours / $contrat->hours) * 100) : 0; @endphp
                <div class="project-progress project-progress-spacing">
                    <div class="progress-bar">
                        <div class="progress-fill" id="contrat-progress" style="min-width: 0%; width: {{ min($pct, 100) }}%;"></div>
                    </div>
                    <span class="progress-text" id="contrat-progress-text">{{ $pct }}%</span>
                </div>
            </div>
        </section>

        <section class="ticket-section">
            <h2>Notes</h2>
            <div class="ticket-description" id="contrat-notes-section">
                <p id="contrat-notes">{{ $contrat->notes ?: 'Aucune note.' }}</p>
            </div>
        </section>

        <section class="ticket-section">
            <h2>Tickets liés au contrat</h2>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Statut</th>
                            <th>Heures</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="contrat-tickets-tbody">
                        @php $linkedTickets = $contrat->getLinkedTickets(); @endphp
                        @forelse($linkedTickets as $ticket)
                        <tr>
                            <td><a href="{{ route('tickets.show', $ticket->id) }}">#{{ $ticket->id }}</a></td>
                            <td>{{ $ticket->title }}</td>
                            <td><span class="badge badge-{{ $ticket->status }}">{{ ucfirst(str_replace('-', ' ', $ticket->status)) }}</span></td>
                            <td>{{ $ticket->spent_hours }}h</td>
                            <td><a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-text btn-small">Voir</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="table-empty">Aucun ticket lié.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <aside class="ticket-sidebar">
        <div class="info-card">
            <h3>Résumé</h3>
            <dl class="info-list">
                <dt>Projet</dt>
                <dd id="contrat-sidebar-projet"><a href="{{ route('projets.show', $contrat->projet) }}">{{ $contrat->projet?->name ?? '—' }}</a></dd>
                <dt>Client</dt>
                <dd id="contrat-sidebar-client">{{ $contrat->client?->full_name ?? '—' }}</dd>
                <dt>Début</dt>
                <dd id="contrat-sidebar-start">{{ $contrat->start_date?->format('d/m/Y') ?? '—' }}</dd>
                <dt>Fin</dt>
                <dd id="contrat-sidebar-end">{{ $contrat->end_date?->format('d/m/Y') ?? '—' }}</dd>
            </dl>
        </div>
    </aside>
</div>

<script>
    window.CONTRAT_ID = {{ $contrat->id }};
</script>
@endsection
