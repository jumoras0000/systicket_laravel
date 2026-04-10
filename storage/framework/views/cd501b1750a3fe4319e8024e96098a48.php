<?php $__env->startSection('title', isset($projet) ? 'Modifier le projet' : 'Nouveau projet'); ?>
<?php $__env->startSection('page', 'projet-form'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <a href="<?php echo e(route('projets.index')); ?>" class="btn btn-text">← Retour</a>
        <h1><?php echo e(isset($projet) ? 'Modifier : '.$projet->name : 'Nouveau projet'); ?></h1>
    </div>
</div>

<div class="form-container">
    <form class="form" id="projet-form" novalidate data-id="<?php echo e(isset($projet) ? $projet->id : ''); ?>" data-api-url="<?php echo e(isset($projet) ? '/api/projets/'.$projet->id : '/api/projets'); ?>" data-method="<?php echo e(isset($projet) ? 'PUT' : 'POST'); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-messages" role="alert" aria-live="polite"></div>
        <div class="form-group">
            <label for="name" class="form-label">Nom du projet <span class="required">*</span></label>
            <input type="text" id="name" name="name" class="form-input" value="<?php echo e($projet->name ?? ''); ?>" required
                data-validate="required|min:3" data-label="Nom">
            <span class="form-error"></span>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-textarea" rows="4"><?php echo e($projet->description ?? ''); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="client_id" class="form-label">Client</label>
                <select id="client_id" name="client_id" class="form-select">
                    <option value="">— Aucun —</option>
                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($client->id); ?>" <?php echo e((isset($projet) && $projet->client_id == $client->id) ? 'selected' : ''); ?>>
                        <?php echo e($client->full_name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label for="manager_id" class="form-label">Responsable</label>
                <select id="manager_id" name="manager_id" class="form-select">
                    <option value="">— Aucun —</option>
                    <?php $__currentLoopData = $collaborateurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $collab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($collab->id); ?>" <?php echo e((isset($projet) && $projet->manager_id == $collab->id) ? 'selected' : ''); ?>>
                        <?php echo e($collab->full_name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="start_date" class="form-label">Date de début</label>
                <input type="date" id="start_date" name="start_date" class="form-input" value="<?php echo e(isset($projet) ? $projet->start_date?->format('Y-m-d') : ''); ?>">
            </div>
            <div class="form-group">
                <label for="end_date" class="form-label">Date de fin</label>
                <input type="date" id="end_date" name="end_date" class="form-input" value="<?php echo e(isset($projet) ? $projet->end_date?->format('Y-m-d') : ''); ?>">
            </div>
            <div class="form-group">
                <label for="status" class="form-label">Statut</label>
                <select id="status" name="status" class="form-select">
                    <?php $__currentLoopData = ['active' => 'Actif', 'paused' => 'En pause', 'completed' => 'Terminé']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($val); ?>" <?php echo e((isset($projet) && $projet->status === $val) ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Membres du projet</label>
            <div id="assignees-list" class="checkbox-group">
                <?php $__currentLoopData = $collaborateurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $collab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="form-checkbox">
                    <input type="checkbox" name="assignees[]" value="<?php echo e($collab->id); ?>"
                        <?php echo e((isset($projet) && $projet->users->contains('id', $collab->id)) ? 'checked' : ''); ?>>
                    <?php echo e($collab->full_name); ?>

                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo e(isset($projet) ? 'Enregistrer' : 'Créer le projet'); ?></button>
            <a href="<?php echo e(route('projets.index')); ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/pages/projets/form.blade.php ENDPATH**/ ?>