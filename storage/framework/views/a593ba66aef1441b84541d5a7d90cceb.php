<?php $__env->startSection('title', 'Contrat '.$contrat->reference); ?>
<?php $__env->startSection('page', 'contrat-detail'); ?>

<?php $__env->startSection('content'); ?>
<nav class="breadcrumb">
    <a href="<?php echo e(route('dashboard')); ?>">Accueil</a>
    <span class="breadcrumb-separator">/</span>
    <a href="<?php echo e(route('contrats.index')); ?>">Contrats</a>
    <span class="breadcrumb-separator">/</span>
    <span id="breadcrumb-contrat"><?php echo e($contrat->reference); ?></span>
</nav>

<header class="ticket-header">
    <div class="ticket-header-left">
        <h1 id="contrat-title">Contrat — <?php echo e($contrat->reference); ?></h1>
        <div class="ticket-meta">
            <span id="contrat-client">Client : <?php echo e($contrat->client?->full_name ?? '—'); ?></span>
            <span id="contrat-projet">Projet : <?php echo e($contrat->projet?->name ?? '—'); ?></span>
            <span class="badge badge-<?php echo e($contrat->status); ?>" id="contrat-status-badge"><?php echo e(ucfirst($contrat->status)); ?></span>
        </div>
    </div>
    <div class="ticket-header-right">
        <?php if(auth()->user()->isAdmin()): ?>
        <a href="<?php echo e(route('contrats.edit', $contrat)); ?>" class="btn btn-secondary btn-edit-contract">✏️ Éditer</a>
        <?php endif; ?>
    </div>
</header>

<div class="ticket-layout">
    <div class="ticket-main">
        <section class="ticket-section">
            <h2>Détail du contrat</h2>
            <div class="info-card">
                <dl class="info-list">
                    <dt>Référence</dt>
                    <dd id="contrat-reference"><?php echo e($contrat->reference); ?></dd>
                    <dt>Statut</dt>
                    <dd id="contrat-contract-status"><?php echo e(ucfirst($contrat->status)); ?></dd>
                    <dt>Heures incluses</dt>
                    <dd class="info-list-large" id="contrat-hours"><?php echo e($contrat->hours); ?>h</dd>
                    <dt>Heures consommées</dt>
                    <dd class="info-list-xlarge" id="contrat-used"><?php echo e($contrat->consumed_hours); ?>h</dd>
                    <dt>Heures restantes</dt>
                    <dd class="info-list-success" id="contrat-remaining"><?php echo e($contrat->remaining_hours); ?>h</dd>
                    <dt>Taux horaire</dt>
                    <dd id="contrat-rate"><?php echo e($contrat->rate ? number_format($contrat->rate, 2) . ' €/h' : '—'); ?></dd>
                    <dt>Période de validité</dt>
                    <dd id="contrat-period"><?php echo e($contrat->start_date?->format('d/m/Y') ?? '—'); ?> — <?php echo e($contrat->end_date?->format('d/m/Y') ?? '—'); ?></dd>
                </dl>
                <?php $pct = $contrat->hours > 0 ? round(($contrat->consumed_hours / $contrat->hours) * 100) : 0; ?>
                <div class="project-progress project-progress-spacing">
                    <div class="progress-bar">
                        <div class="progress-fill" id="contrat-progress" style="min-width: 0%; width: <?php echo e(min($pct, 100)); ?>%;"></div>
                    </div>
                    <span class="progress-text" id="contrat-progress-text"><?php echo e($pct); ?>%</span>
                </div>
            </div>
        </section>

        <section class="ticket-section">
            <h2>Notes</h2>
            <div class="ticket-description" id="contrat-notes-section">
                <p id="contrat-notes"><?php echo e($contrat->notes ?: 'Aucune note.'); ?></p>
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
                        <?php $linkedTickets = $contrat->getLinkedTickets(); ?>
                        <?php $__empty_1 = true; $__currentLoopData = $linkedTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a href="<?php echo e(route('tickets.show', $ticket->id)); ?>">#<?php echo e($ticket->id); ?></a></td>
                            <td><?php echo e($ticket->title); ?></td>
                            <td><span class="badge badge-<?php echo e($ticket->status); ?>"><?php echo e(ucfirst(str_replace('-', ' ', $ticket->status))); ?></span></td>
                            <td><?php echo e($ticket->spent_hours); ?>h</td>
                            <td><a href="<?php echo e(route('tickets.show', $ticket->id)); ?>" class="btn btn-text btn-small">Voir</a></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="table-empty">Aucun ticket lié.</td></tr>
                        <?php endif; ?>
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
                <dd id="contrat-sidebar-projet"><a href="<?php echo e(route('projets.show', $contrat->projet)); ?>"><?php echo e($contrat->projet?->name ?? '—'); ?></a></dd>
                <dt>Client</dt>
                <dd id="contrat-sidebar-client"><?php echo e($contrat->client?->full_name ?? '—'); ?></dd>
                <dt>Début</dt>
                <dd id="contrat-sidebar-start"><?php echo e($contrat->start_date?->format('d/m/Y') ?? '—'); ?></dd>
                <dt>Fin</dt>
                <dd id="contrat-sidebar-end"><?php echo e($contrat->end_date?->format('d/m/Y') ?? '—'); ?></dd>
            </dl>
        </div>
    </aside>
</div>

<script>
    window.CONTRAT_ID = <?php echo e($contrat->id); ?>;
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/pages/contrats/detail.blade.php ENDPATH**/ ?>