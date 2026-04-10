<?php $__env->startSection('title', 'Inscription'); ?>
<?php $__env->startSection('body-class', 'auth-page'); ?>
<?php $__env->startSection('skip-target', 'register-form'); ?>
<?php $__env->startSection('skip-label', "Aller au formulaire d'inscription"); ?>
<?php $__env->startSection('tagline', 'Rejoignez la plateforme de gestion de tickets'); ?>

<?php $__env->startSection('features'); ?>
<ul class="auth-features" aria-hidden="true">
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">📁</span>
        <span>Projets et clients centralisés</span>
    </li>
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">📊</span>
        <span>Tableaux de bord et statistiques</span>
    </li>
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">🔐</span>
        <span>Accès sécurisé par rôle</span>
    </li>
</ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('form-panel-class', 'auth-form-panel-scroll'); ?>

<?php $__env->startSection('content'); ?>
<header class="auth-form-header">
    <h1>Créer un compte</h1>
    <p>Remplissez le formulaire pour vous inscrire</p>
</header>

<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div><?php echo e($error); ?></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form id="register-form" class="auth-form" action="<?php echo e(route('register')); ?>" method="post">
    <?php echo csrf_field(); ?>

    <div class="auth-form-row auth-form-row-2">
        <div class="auth-form-group">
            <label for="nom" class="auth-label">Nom <span class="auth-required">*</span></label>
            <div class="auth-input-wrap auth-input-wrap-no-icon">
                <input type="text" id="nom" name="last_name" class="auth-input" placeholder="Nom" required autocomplete="family-name" value="<?php echo e(old('last_name')); ?>">
            </div>
        </div>
        <div class="auth-form-group">
            <label for="prenom" class="auth-label">Prénom <span class="auth-required">*</span></label>
            <div class="auth-input-wrap auth-input-wrap-no-icon">
                <input type="text" id="prenom" name="first_name" class="auth-input" placeholder="Prénom" required autocomplete="given-name" value="<?php echo e(old('first_name')); ?>">
            </div>
        </div>
    </div>

    <div class="auth-form-group">
        <label for="email" class="auth-label">Email <span class="auth-required">*</span></label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">✉</span>
            <input type="email" id="email" name="email" class="auth-input" placeholder="Email" required autocomplete="email" value="<?php echo e(old('email')); ?>">
        </div>
    </div>

    <div class="auth-form-group">
        <label for="password" class="auth-label">Mot de passe <span class="auth-required">*</span></label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">🔒</span>
            <input type="password" id="password" name="password" class="auth-input" placeholder="Mot de passe" minlength="8" required autocomplete="new-password">
        </div>
        <span class="auth-help">Minimum 8 caractères</span>
    </div>

    <div class="auth-form-group">
        <label for="password-confirm" class="auth-label">Confirmer le mot de passe <span class="auth-required">*</span></label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">🔒</span>
            <input type="password" id="password-confirm" name="password_confirmation" class="auth-input" placeholder="Confirmer le mot de passe" required autocomplete="new-password">
        </div>
    </div>

    <div class="auth-form-group">
        <span class="auth-label">Rôle <span class="auth-required">*</span></span>
        <div class="auth-radio-group">
            <label class="auth-radio">
                <input type="radio" name="role" value="collaborateur" required <?php echo e(old('role') === 'collaborateur' ? 'checked' : ''); ?>>
                <span class="auth-radio-dot"></span>
                <span>Collaborateur</span>
            </label>
            <label class="auth-radio">
                <input type="radio" name="role" value="client" required <?php echo e(old('role') === 'client' ? 'checked' : ''); ?>>
                <span class="auth-radio-dot"></span>
                <span>Client</span>
            </label>
        </div>
        <span class="auth-help">Seuls les collaborateurs et clients peuvent s'inscrire.</span>
    </div>

    <div class="auth-form-group">
        <label class="auth-checkbox">
            <input type="checkbox" name="cgu" id="cgu" required>
            <span class="auth-checkbox-box"></span>
            <span class="auth-checkbox-label">J'accepte les <a href="<?php echo e(route('cgu')); ?>" class="auth-link" target="_blank" rel="noopener">conditions générales d'utilisation</a> <span class="auth-required">*</span></span>
        </label>
    </div>

    <div class="auth-form-footer auth-form-footer-before-submit">
        <p>Vous avez déjà un compte ? <a href="<?php echo e(route('login')); ?>" class="auth-link auth-link-strong">Se connecter</a></p>
    </div>

    <div class="auth-btn-flee-wrapper">
        <button type="submit" class="auth-btn auth-btn-primary" id="auth-submit-btn">
            Créer mon compte
        </button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/auth-button.js')); ?>"></script>
<script src="<?php echo e(asset('js/forms-validation.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/pages/auth/inscription.blade.php ENDPATH**/ ?>