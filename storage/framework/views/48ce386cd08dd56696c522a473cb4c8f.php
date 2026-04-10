<?php $__env->startSection('title', isset($contrat) ? 'Modifier le contrat' : 'Nouveau contrat'); ?>
<?php $__env->startSection('page', 'contrat-form'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <a href="<?php echo e(route('contrats.index')); ?>" class="btn btn-text">← Retour</a>
        <h1><?php echo e(isset($contrat) ? 'Modifier : '.$contrat->reference : 'Nouveau contrat'); ?></h1>
    </div>
</div>

<div class="form-container">
    <form class="form" id="contrat-form" novalidate data-api-url="<?php echo e(isset($contrat) ? '/api/contrats/'.$contrat->id : '/api/contrats'); ?>" data-method="<?php echo e(isset($contrat) ? 'PUT' : 'POST'); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-row">
            <div class="form-group">
                <label for="reference" class="form-label">Référence <span class="required">*</span></label>
                <input type="text" id="reference" name="reference" class="form-input" value="<?php echo e($contrat->reference ?? ''); ?>" required
                    data-validate="required" data-label="Référence">
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="project_id" class="form-label">Projet <span class="required">*</span></label>
                <select id="project_id" name="project_id" class="form-select" required data-validate="required" data-label="Projet">
                    <option value="">— Choisir —</option>
                    <?php $__currentLoopData = $projets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($projet->id); ?>" <?php echo e((isset($contrat) && $contrat->project_id == $projet->id) ? 'selected' : ''); ?>>
                        <?php echo e($projet->name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <span class="form-error"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="client_id" class="form-label">Client <span class="required">*</span></label>
                <select id="client_id" name="client_id" class="form-select" required data-validate="required" data-label="Client">
                    <option value="">— Choisir —</option>
                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($client->id); ?>" <?php echo e((isset($contrat) && $contrat->client_id == $client->id) ? 'selected' : ''); ?>>
                        <?php echo e($client->full_name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="status" class="form-label">Statut</label>
                <select id="status" name="status" class="form-select">
                    <?php $__currentLoopData = ['active' => 'Actif', 'expired' => 'Expiré', 'cancelled' => 'Annulé']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($val); ?>" <?php echo e((isset($contrat) && $contrat->status === $val) ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="hours" class="form-label">Heures <span class="required">*</span></label>
                <input type="number" id="hours" name="hours" class="form-input" step="0.5" min="0" value="<?php echo e($contrat->hours ?? ''); ?>" required
                    data-validate="required|min:0" data-label="Heures">
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="rate" class="form-label">Tarif horaire (€)</label>
                <input type="number" id="rate" name="rate" class="form-input" step="0.01" min="0" value="<?php echo e($contrat->rate ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="start_date" class="form-label">Date de début</label>
                <input type="date" id="start_date" name="start_date" class="form-input" value="<?php echo e(isset($contrat) ? $contrat->start_date?->format('Y-m-d') : ''); ?>">
            </div>
            <div class="form-group">
                <label for="end_date" class="form-label">Date de fin</label>
                <input type="date" id="end_date" name="end_date" class="form-input" value="<?php echo e(isset($contrat) ? $contrat->end_date?->format('Y-m-d') : ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="notes" class="form-label">Notes</label>
            <textarea id="notes" name="notes" class="form-textarea" rows="3"><?php echo e($contrat->notes ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo e(isset($contrat) ? 'Enregistrer' : 'Créer le contrat'); ?></button>
            <a href="<?php echo e(route('contrats.index')); ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/pages/contrats/form.blade.php ENDPATH**/ ?>