<?php $__env->startSection('title', 'Ticket #'.$ticket->id); ?>
<?php $__env->startSection('page', 'ticket-detail'); ?>

<?php $__env->startSection('content'); ?>
<!-- Navigation -->
<nav class="breadcrumb">
    <a href="<?php echo e(route('dashboard')); ?>">Accueil</a>
    <span class="breadcrumb-separator">/</span>
    <a href="<?php echo e(route('tickets.index')); ?>">Tickets</a>
    <span class="breadcrumb-separator">/</span>
    <span id="breadcrumb-ticket"><?php echo e($ticket->title); ?></span>
</nav>

<!-- En-tête ticket -->
<header class="ticket-header">
    <div class="ticket-header-left">
        <h1 id="ticket-title"><?php echo e($ticket->title); ?></h1>
        <div class="ticket-meta" id="ticket-meta">
            <span class="ticket-id">#<?php echo e($ticket->id); ?></span>
            <span class="badge badge-<?php echo e($ticket->status); ?>" id="ticket-status-badge"><?php echo e(ucfirst(str_replace('-', ' ', $ticket->status))); ?></span>
            <span class="badge badge-priority-<?php echo e($ticket->priority); ?>" id="ticket-priority-badge"><?php echo e(ucfirst($ticket->priority)); ?></span>
            <span class="badge badge-<?php echo e($ticket->type === 'billable' ? 'warning' : 'success'); ?>" id="ticket-type-badge"><?php echo e($ticket->type === 'billable' ? 'Facturable' : 'Inclus'); ?></span>
        </div>
    </div>
    <div class="ticket-header-right">
        <?php if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur()): ?>
        <a href="<?php echo e(route('tickets.edit', $ticket)); ?>" class="btn btn-secondary btn-edit-ticket">✏️ Éditer</a>
        <a href="<?php echo e(route('temps.index', ['ticket' => $ticket->id])); ?>" class="btn btn-primary">⏱️ Ajouter du temps</a>
        <?php endif; ?>
    </div>
</header>

<div class="ticket-layout">
    <div class="ticket-main">
        <!-- Description -->
        <section class="ticket-section">
            <h2>Description</h2>
            <div class="ticket-description" id="ticket-description">
                <p><?php echo e($ticket->description ?: 'Aucune description.'); ?></p>
            </div>
        </section>

        <!-- Temps passé -->
        <section class="ticket-section">
            <div class="section-header">
                <h2>Temps passé</h2>
                <span class="time-total" id="ticket-time-total">Total : <?php echo e($ticket->spent_hours); ?>h</span>
            </div>
            <div class="time-entries" id="ticket-time-entries">
                <?php $__empty_1 = true; $__currentLoopData = $ticket->temps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $temps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="time-entry">
                    <span class="time-entry-date"><?php echo e($temps->date); ?></span>
                    <span class="time-entry-user"><?php echo e($temps->user?->full_name); ?></span>
                    <span class="time-entry-hours"><?php echo e($temps->hours); ?>h</span>
                    <span class="time-entry-desc"><?php echo e($temps->description); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-secondary text-sm">Aucune entrée de temps.</p>
                <?php endif; ?>
            </div>
            <div class="time-estimated time-estimated-border" id="ticket-time-estimated">
                <strong>Temps estimé :</strong> <?php echo e($ticket->estimated_hours ?? '—'); ?>h
            </div>
        </section>

        <!-- Commentaires -->
        <section class="ticket-section">
            <h2>Commentaires</h2>
            <div class="comments-list" id="ticket-comments">
                <?php $__empty_1 = true; $__currentLoopData = $ticket->commentaires; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="comment">
                    <div class="comment-header">
                        <strong><?php echo e($comment->author_name); ?></strong>
                        <span class="text-secondary text-sm"><?php echo e($comment->created_at); ?></span>
                    </div>
                    <div class="comment-body"><?php echo e($comment->content); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-secondary text-sm">Aucun commentaire.</p>
                <?php endif; ?>
            </div>
            <form class="comment-form" id="comment-form" data-no-validate>
                <input type="hidden" name="ticket_id" value="<?php echo e($ticket->id); ?>">
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
                <dd><a href="<?php echo e(route('projets.show', $ticket->projet)); ?>"><?php echo e($ticket->projet?->name); ?></a></dd>
                <dt>Client</dt>
                <dd><?php echo e($ticket->projet?->client?->full_name ?? '—'); ?></dd>
                <dt>Créé le</dt>
                <dd><?php echo e($ticket->created_at?->format('d/m/Y H:i')); ?></dd>
                <dt>Modifié le</dt>
                <dd><?php echo e($ticket->updated_at?->format('d/m/Y H:i')); ?></dd>
                <dt>Créé par</dt>
                <dd><?php echo e($ticket->creator?->full_name); ?></dd>
                <dt>Temps écoulé</dt>
                <dd><span class="text-primary"><?php echo e($ticket->spent_hours); ?>h</span> / <?php echo e($ticket->estimated_hours ?? '—'); ?>h</dd>
            </dl>
        </div>

        <div class="info-card">
            <h3>Assignation</h3>
            <div class="assignees-list" id="ticket-assignees">
                <?php $__empty_1 = true; $__currentLoopData = $ticket->assignees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="assignee-item">
                    <span><?php echo e($assignee->full_name); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="assignee-item">
                    <span class="text-secondary">Non assigné</span>
                </div>
                <?php endif; ?>
            </div>
            <?php if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur()): ?>
            <div class="mt-sm" id="add-assignee-wrap">
                <select class="form-select" id="add-assignee-select">
                    <option value="">+ Ajouter un collaborateur</option>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <?php if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur()): ?>
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
                <a href="<?php echo e(route('tickets.create', ['duplicate' => $ticket->id])); ?>" class="btn btn-text btn-small btn-block mt-sm">📋 Dupliquer</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Historique validations -->
        <?php if($ticket->validations->count() > 0): ?>
        <div class="info-card">
            <h3>Validations</h3>
            <?php $__currentLoopData = $ticket->validations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $validation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="validation-entry">
                <span class="badge badge-<?php echo e($validation->status); ?>"><?php echo e(ucfirst($validation->status)); ?></span>
                <span class="text-sm"><?php echo e($validation->user?->full_name); ?></span>
                <?php if($validation->comment): ?>
                <p class="text-sm text-secondary"><?php echo e($validation->comment); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
    </aside>
</div>

<script>
    window.TICKET_ID = <?php echo e($ticket->id); ?>;
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/pages/tickets/detail.blade.php ENDPATH**/ ?>