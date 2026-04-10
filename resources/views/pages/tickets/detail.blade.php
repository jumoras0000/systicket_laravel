@extends('layouts.app')
@section('title', 'Ticket #'.$ticket->id)
@section('page', 'ticket-detail')

@section('content')
<!-- Navigation -->
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Accueil</a>
    <span class="breadcrumb-separator">/</span>
    <a href="{{ route('tickets.index') }}">Tickets</a>
    <span class="breadcrumb-separator">/</span>
    <span id="breadcrumb-ticket">{{ $ticket->title }}</span>
</nav>

<!-- En-tête ticket -->
<header class="ticket-header">
    <div class="ticket-header-left">
        <h1 id="ticket-title">{{ $ticket->title }}</h1>
        <div class="ticket-meta" id="ticket-meta">
            <span class="ticket-id">#{{ $ticket->id }}</span>
            <span class="badge badge-{{ $ticket->status }}" id="ticket-status-badge">{{ ucfirst(str_replace('-', ' ', $ticket->status)) }}</span>
            <span class="badge badge-priority-{{ $ticket->priority }}" id="ticket-priority-badge">{{ ucfirst($ticket->priority) }}</span>
            <span class="badge badge-{{ $ticket->type === 'billable' ? 'warning' : 'success' }}" id="ticket-type-badge">{{ $ticket->type === 'billable' ? 'Facturable' : 'Inclus' }}</span>
        </div>
    </div>
    <div class="ticket-header-right">
        @if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur())
        <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-secondary btn-edit-ticket">✏️ Éditer</a>
        <a href="{{ route('temps.index', ['ticket' => $ticket->id]) }}" class="btn btn-primary">⏱️ Ajouter du temps</a>
        @endif
    </div>
</header>

<div class="ticket-layout">
    <div class="ticket-main">
        <!-- Description -->
        <section class="ticket-section">
            <h2>Description</h2>
            <div class="ticket-description" id="ticket-description">
                <p>{{ $ticket->description ?: 'Aucune description.' }}</p>
            </div>
        </section>

        <!-- Temps passé -->
        <section class="ticket-section">
            <div class="section-header">
                <h2>Temps passé</h2>
                <span class="time-total" id="ticket-time-total">Total : {{ $ticket->spent_hours }}h</span>
            </div>
            <div class="time-entries" id="ticket-time-entries">
                @forelse($ticket->temps as $temps)
                <div class="time-entry">
                    <span class="time-entry-date">{{ $temps->date }}</span>
                    <span class="time-entry-user">{{ $temps->user?->full_name }}</span>
                    <span class="time-entry-hours">{{ $temps->hours }}h</span>
                    <span class="time-entry-desc">{{ $temps->description }}</span>
                </div>
                @empty
                <p class="text-secondary text-sm">Aucune entrée de temps.</p>
                @endforelse
            </div>
            <div class="time-estimated time-estimated-border" id="ticket-time-estimated">
                <strong>Temps estimé :</strong> {{ $ticket->estimated_hours ?? '—' }}h
            </div>
        </section>

        <!-- Commentaires -->
        <section class="ticket-section">
            <h2>Commentaires</h2>
            <div class="comments-list" id="ticket-comments">
                @forelse($ticket->commentaires as $comment)
                <div class="comment">
                    <div class="comment-header">
                        <strong>{{ $comment->author_name }}</strong>
                        <span class="text-secondary text-sm">{{ $comment->created_at }}</span>
                    </div>
                    <div class="comment-body">{{ $comment->content }}</div>
                </div>
                @empty
                <p class="text-secondary text-sm">Aucun commentaire.</p>
                @endforelse
            </div>
            <form class="comment-form" id="comment-form" data-no-validate>
                <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                <textarea class="form-textarea" name="contenu" placeholder="Ajouter un commentaire..." rows="3"></textarea>
                <button type="submit" class="btn btn-primary">Publier</button>
            </form>
        </section>

        <!-- Historique -->
        <section class="ticket-section ticket-timeline-section" id="ticket-timeline-section" style="display:none;">
            <h2>Historique &amp; activité</h2>
            <ul class="timeline" aria-label="Historique du ticket" id="ticket-timeline"></ul>
        </section>
    </div>

    <!-- Sidebar -->
    <aside class="ticket-sidebar">
        <div class="info-card">
            <h3>Informations</h3>
            <dl class="info-list" id="ticket-info">
                <dt>Projet</dt>
                <dd><a href="{{ route('projets.show', $ticket->projet) }}">{{ $ticket->projet?->name }}</a></dd>
                <dt>Client</dt>
                <dd>{{ $ticket->projet?->client?->full_name ?? '—' }}</dd>
                <dt>Créé le</dt>
                <dd>{{ $ticket->created_at?->format('d/m/Y H:i') }}</dd>
                <dt>Modifié le</dt>
                <dd>{{ $ticket->updated_at?->format('d/m/Y H:i') }}</dd>
                <dt>Créé par</dt>
                <dd>{{ $ticket->creator?->full_name }}</dd>
                <dt>Temps écoulé</dt>
                <dd><span class="text-primary">{{ $ticket->spent_hours }}h</span> / {{ $ticket->estimated_hours ?? '—' }}h</dd>
            </dl>
        </div>

        <div class="info-card">
            <h3>Assignation</h3>
            <div class="assignees-list" id="ticket-assignees">
                @forelse($ticket->assignees as $assignee)
                <div class="assignee-item">
                    <span>{{ $assignee->full_name }}</span>
                </div>
                @empty
                <div class="assignee-item">
                    <span class="text-secondary">Non assigné</span>
                </div>
                @endforelse
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur())
            <div class="mt-sm" id="add-assignee-wrap">
                <select class="form-select" id="add-assignee-select">
                    <option value="">+ Ajouter un collaborateur</option>
                </select>
            </div>
            @endif
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur())
        <div class="info-card">
            <h3>Actions rapides</h3>
            <div class="action-list">
                <select class="form-select form-select-spacing" id="ticket-status-change">
                    <option value="">Changer le statut</option>
                    <option value="new">Nouveau</option>
                    <option value="in-progress">En cours</option>
                    <option value="waiting-client">En attente client</option>
                    <option value="done">Terminé</option>
                    <option value="to-validate">À valider</option>
                </select>
                <a href="{{ route('tickets.create', ['duplicate' => $ticket->id]) }}" class="btn btn-text btn-small btn-block mt-sm">📋 Dupliquer</a>
            </div>
        </div>
        @endif

        <!-- Historique validations -->
        @if($ticket->validations->count() > 0)
        <div class="info-card">
            <h3>Validations</h3>
            @foreach($ticket->validations as $validation)
            <div class="validation-entry">
                <span class="badge badge-{{ $validation->status }}">{{ ucfirst($validation->status) }}</span>
                <span class="text-sm">{{ $validation->user?->full_name }}</span>
                @if($validation->comment)
                <p class="text-sm text-secondary">{{ $validation->comment }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </aside>
</div>

<script>
    window.TICKET_ID = {{ $ticket->id }};
</script>
@endsection
