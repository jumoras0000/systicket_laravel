<?php $__env->startSection('title', $projet->name); ?>
<?php $__env->startSection('page', 'projet-detail'); ?>

<?php $__env->startSection('content'); ?>
<!-- Navigation -->
<nav class="breadcrumb">
    <a href="<?php echo e(route('dashboard')); ?>">Accueil</a>
    <span class="breadcrumb-separator">/</span>
    <a href="<?php echo e(route('projets.index')); ?>">Projets</a>
    <span class="breadcrumb-separator">/</span>
    <span id="breadcrumb-projet"><?php echo e($projet->name); ?></span>
</nav>

<!-- En-tête projet -->
<header class="ticket-header">
    <div class="ticket-header-left">
        <h1 id="projet-name"><?php echo e($projet->name); ?></h1>
        <div class="ticket-meta" id="projet-meta">
            <span class="badge badge-<?php echo e($projet->status); ?>" id="projet-status-badge"><?php echo e(ucfirst($projet->status)); ?></span>
            <span id="projet-client-name">Client : <?php echo e($projet->client?->full_name ?? '—'); ?></span>
        </div>
    </div>
    <div class="ticket-header-right">
        <?php if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur()): ?>
        <a href="<?php echo e(route('projets.edit', $projet)); ?>" class="btn btn-secondary btn-edit-project">✏️ Éditer</a>
        <a href="<?php echo e(route('tickets.create', ['projet_id' => $projet->id])); ?>" class="btn btn-primary btn-create-ticket">+ Créer un ticket</a>
        <?php endif; ?>
    </div>
</header>

<div class="ticket-layout">
    <div class="ticket-main">
        <!-- Informations générales -->
        <section id="editer" class="ticket-section">
            <h2>Informations générales</h2>
            <div class="ticket-description" id="projet-info">
                <p id="projet-description"><?php echo e($projet->description ?: 'Aucune description.'); ?></p>
                <p><strong>Date de début :</strong> <span id="projet-start"><?php echo e($projet->start_date?->format('d/m/Y') ?? '—'); ?></span></p>
                <p><strong>Date de fin prévue :</strong> <span id="projet-end"><?php echo e($projet->end_date?->format('d/m/Y') ?? '—'); ?></span></p>
                <p><strong>Responsable projet :</strong> <span id="projet-manager"><?php echo e($projet->manager?->full_name ?? '—'); ?></span></p>
                <p><strong>Créé le :</strong> <span id="projet-created"><?php echo e($projet->created_at?->format('d/m/Y') ?? '—'); ?></span></p>
            </div>
        </section>

        <!-- Contrat -->
        <?php $contrat = $projet->contrats->first(); ?>
        <section class="ticket-section" id="projet-contrat-section">
            <h2>Contrat</h2>
            <div class="info-card">
                <dl class="info-list">
                    <dt>Heures incluses</dt>
                    <dd class="info-list-large" id="projet-contrat-hours"><?php echo e($contrat ? $contrat->hours . 'h' : '—'); ?></dd>
                    <dt>Heures consommées</dt>
                    <dd class="info-list-xlarge" id="projet-contrat-used"><?php echo e($contrat ? $contrat->consumed_hours . 'h' : '—'); ?></dd>
                    <dt>Heures restantes</dt>
                    <dd class="info-list-success" id="projet-contrat-remaining"><?php echo e($contrat ? $contrat->remaining_hours . 'h' : '—'); ?></dd>
                    <dt>Taux horaire supplémentaire</dt>
                    <dd id="projet-contrat-rate"><?php echo e($contrat ? number_format($contrat->rate, 2) . ' €/h' : '—'); ?></dd>
                    <dt>Montant à payer</dt>
                    <dd class="info-list-xlarge" id="projet-contrat-amount"><?php echo e($contrat && $contrat->consumed_hours > $contrat->hours ? number_format(($contrat->consumed_hours - $contrat->hours) * $contrat->rate, 2) . ' €' : '0.00 €'); ?></dd>
                    <dt>Période de validité</dt>
                    <dd id="projet-contrat-period"><?php echo e($contrat ? ($contrat->start_date?->format('d/m/Y') . ' — ' . $contrat->end_date?->format('d/m/Y')) : '—'); ?></dd>
                </dl>
                <?php if($contrat): ?>
                <?php $pct = $contrat->hours > 0 ? round(($contrat->consumed_hours / $contrat->hours) * 100) : 0; ?>
                <div class="project-progress project-progress-spacing">
                    <div class="progress-bar">
                        <div class="progress-fill" id="projet-contrat-progress" style="width: <?php echo e(min($pct, 100)); ?>%;"></div>
                    </div>
                    <span class="progress-text" id="projet-contrat-progress-text"><?php echo e($pct); ?>%</span>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Collaborateurs -->
        <section class="ticket-section">
            <h2>Collaborateurs assignés</h2>
            <div class="assignees-list" id="projet-assignees">
                <?php $__empty_1 = true; $__currentLoopData = $projet->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="assignee-item">
                    <span><?php echo e($user->full_name); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-secondary text-sm">Aucun collaborateur assigné.</p>
                <?php endif; ?>
            </div>
            <?php if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur()): ?>
            <div class="mt-sm" id="add-projet-assignee-wrap">
                <select class="form-select" id="add-projet-assignee-select">
                    <option value="">+ Ajouter un collaborateur</option>
                </select>
            </div>
            <?php endif; ?>
        </section>

        <!-- Tickets du projet -->
        <section class="ticket-section">
            <div class="section-header">
                <h2>Tickets du projet</h2>
                <?php if(auth()->user()->isAdmin() || auth()->user()->isCollaborateur()): ?>
                <a href="<?php echo e(route('tickets.create', ['projet_id' => $projet->id])); ?>" class="btn btn-primary btn-small btn-create-ticket">+ Créer un ticket</a>
                <?php endif; ?>
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
                        <?php $__empty_1 = true; $__currentLoopData = $projet->tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><a href="<?php echo e(route('tickets.show', $ticket)); ?>">#<?php echo e($ticket->id); ?></a></td>
                            <td><?php echo e($ticket->title); ?></td>
                            <td><span class="badge badge-<?php echo e($ticket->status); ?>"><?php echo e(ucfirst(str_replace('-', ' ', $ticket->status))); ?></span></td>
                            <td><span class="badge badge-priority-<?php echo e($ticket->priority); ?>"><?php echo e(ucfirst($ticket->priority)); ?></span></td>
                            <td><?php echo e($ticket->type === 'billable' ? 'Facturable' : 'Inclus'); ?></td>
                            <td><?php echo e($ticket->assignee_names ?? '—'); ?></td>
                            <td><?php echo e($ticket->spent_hours); ?>h / <?php echo e($ticket->estimated_hours ?? '—'); ?>h</td>
                            <td><a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="btn btn-text btn-small">Voir</a></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="table-empty">Aucun ticket.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
    window.PROJET_ID = <?php echo e($projet->id); ?>;
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/pages/projets/detail.blade.php ENDPATH**/ ?>