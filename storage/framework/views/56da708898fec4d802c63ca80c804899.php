<?php $__env->startSection('title', isset($ticket) ? 'Modifier le ticket' : 'Nouveau ticket'); ?>
<?php $__env->startSection('page', 'ticket-form'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-text">← Retour</a>
        <h1><?php echo e(isset($ticket) ? 'Modifier le ticket #'.$ticket->id : 'Nouveau ticket'); ?></h1>
    </div>
</div>

<div class="form-container">
    <form class="form" id="ticket-form" novalidate data-id="<?php echo e(isset($ticket) ? $ticket->id : ''); ?>" data-api-url="<?php echo e(isset($ticket) ? '/api/tickets/'.$ticket->id : '/api/tickets'); ?>" data-method="<?php echo e(isset($ticket) ? 'PUT' : 'POST'); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-messages" role="alert" aria-live="polite"></div>
        <div class="form-row">
            <div class="form-group form-group-large">
                <label for="title" class="form-label">Titre <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-input" value="<?php echo e($ticket->title ?? ''); ?>" required
                    data-validate="required|min:3" data-label="Titre">
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="project_id" class="form-label">Projet <span class="required">*</span></label>
                <select id="project_id" name="project_id" class="form-select" required data-validate="required" data-label="Projet">
                    <option value="">— Choisir —</option>
                    <?php $__currentLoopData = $projets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($projet->id); ?>" <?php echo e((isset($ticket) && $ticket->project_id == $projet->id) ? 'selected' : ''); ?>>
                        <?php echo e($projet->name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <span class="form-error"></span>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-textarea" rows="5"><?php echo e($ticket->description ?? ''); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="priority" class="form-label">Priorité</label>
                <select id="priority" name="priority" class="form-select">
                    <?php $__currentLoopData = ['low' => 'Faible', 'normal' => 'Normale', 'high' => 'Élevée', 'critical' => 'Critique']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($val); ?>" <?php echo e((isset($ticket) && $ticket->priority === $val) ? 'selected' : ((!isset($ticket) && $val === 'normal') ? 'selected' : '')); ?>>
                        <?php echo e($label); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label for="type" class="form-label">Type</label>
                <select id="type" name="type" class="form-select">
                    <?php $__currentLoopData = ['included' => 'Inclus', 'billable' => 'Facturable']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($val); ?>" <?php echo e((isset($ticket) && $ticket->type === $val) ? 'selected' : ''); ?>>
                        <?php echo e($label); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label for="estimated_hours" class="form-label">Heures estimées</label>
                <input type="number" id="estimated_hours" name="estimated_hours" class="form-input" step="0.5" min="0" value="<?php echo e($ticket->estimated_hours ?? ''); ?>">
            </div>
        </div>

        <?php if(isset($ticket)): ?>
        <div class="form-group">
            <label for="status" class="form-label">Statut</label>
            <select id="status" name="status" class="form-select">
                <?php $__currentLoopData = ['new' => 'Nouveau', 'in-progress' => 'En cours', 'waiting-client' => 'En attente client', 'done' => 'Terminé', 'to-validate' => 'À valider', 'validated' => 'Validé', 'refused' => 'Refusé']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($val); ?>" <?php echo e($ticket->status === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label">Assigné(s)</label>
            <div id="assignees-list" class="checkbox-group">
                <?php $__currentLoopData = $collaborateurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $collab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="form-checkbox">
                    <input type="checkbox" name="assignees[]" value="<?php echo e($collab->id); ?>"
                        <?php echo e((isset($ticket) && $ticket->assignees->contains('id', $collab->id)) ? 'checked' : ''); ?>>
                    <?php echo e($collab->full_name); ?>

                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo e(isset($ticket) ? 'Enregistrer' : 'Créer le ticket'); ?></button>
            <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/pages/tickets/form.blade.php ENDPATH**/ ?>