<?php $__env->startSection('title', isset($user) ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur'); ?>
<?php $__env->startSection('page', 'user-form'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div class="page-header-left">
        <a href="<?php echo e(route('users.index')); ?>" class="btn btn-text">← Retour</a>
        <h1><?php echo e(isset($user) ? 'Modifier : '.$user->full_name : 'Nouvel utilisateur'); ?></h1>
    </div>
</div>

<div class="form-container">
    <form class="form" id="user-form" novalidate data-api-url="<?php echo e(isset($user) ? '/api/users/'.$user->id : '/api/users'); ?>" data-method="<?php echo e(isset($user) ? 'PUT' : 'POST'); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-row">
            <div class="form-group">
                <label for="last_name" class="form-label">Nom <span class="required">*</span></label>
                <input type="text" id="last_name" name="last_name" class="form-input" value="<?php echo e($user->last_name ?? ''); ?>" required
                    data-validate="required|min:2" data-label="Nom">
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="first_name" class="form-label">Prénom <span class="required">*</span></label>
                <input type="text" id="first_name" name="first_name" class="form-input" value="<?php echo e($user->first_name ?? ''); ?>" required
                    data-validate="required|min:2" data-label="Prénom">
                <span class="form-error"></span>
            </div>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-input" value="<?php echo e($user->email ?? ''); ?>" required
                data-validate="required|email" data-label="Email">
            <span class="form-error"></span>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="role" class="form-label">Rôle <span class="required">*</span></label>
                <select id="role" name="role" class="form-select" required data-validate="required" data-label="Rôle">
                    <?php $__currentLoopData = ['admin' => 'Administrateur', 'collaborateur' => 'Collaborateur', 'client' => 'Client']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($val); ?>" <?php echo e((isset($user) && $user->role === $val) ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <span class="form-error"></span>
            </div>
            <div class="form-group">
                <label for="status" class="form-label">Statut</label>
                <select id="status" name="status" class="form-select">
                    <option value="active" <?php echo e((isset($user) && $user->status === 'active') ? 'selected' : ''); ?>>Actif</option>
                    <option value="inactive" <?php echo e((isset($user) && $user->status === 'inactive') ? 'selected' : ''); ?>>Inactif</option>
                </select>
            </div>
            <div class="form-group">
                <label for="phone" class="form-label">Téléphone</label>
                <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo e($user->phone ?? ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Mot de passe <?php echo e(isset($user) ? '(laisser vide pour ne pas changer)' : ''); ?> <?php if(!isset($user)): ?><span class="required">*</span><?php endif; ?></label>
            <input type="password" id="password" name="password" class="form-input"
                <?php echo e(isset($user) ? '' : 'required'); ?>

                data-validate="<?php echo e(isset($user) ? 'min:8' : 'required|min:8'); ?>" data-label="Mot de passe">
            <span class="form-error"></span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo e(isset($user) ? 'Enregistrer' : 'Créer l\'utilisateur'); ?></button>
            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/pages/users/form.blade.php ENDPATH**/ ?>