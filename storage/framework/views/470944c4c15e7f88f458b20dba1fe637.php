<?php $__env->startSection('title', 'Mot de passe oublié'); ?>
<?php $__env->startSection('body-class', 'auth-page auth-page-connexion'); ?>
<?php $__env->startSection('skip-target', 'reset-form'); ?>
<?php $__env->startSection('skip-label', 'Aller au formulaire'); ?>
<?php $__env->startSection('tagline', 'Réinitialisez votre mot de passe en quelques clics'); ?>

<?php $__env->startSection('form-wrapper-class', 'auth-form-connexion'); ?>

<?php $__env->startSection('content'); ?>
<header class="auth-form-header">
    <h1>Mot de passe oublié</h1>
    <p>Indiquez votre adresse email pour recevoir un lien de réinitialisation</p>
</header>

<?php if($errors->any()): ?>
    <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form id="reset-form" class="auth-form" action="<?php echo e(route('password.request')); ?>" method="post">
    <?php echo csrf_field(); ?>
    <div class="auth-form-group">
        <label for="email" class="auth-label">Email</label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">✉</span>
            <input type="email" id="email" name="email" class="auth-input" placeholder="Email" required autocomplete="email">
        </div>
    </div>

    <div class="auth-btn-flee-wrapper">
        <button type="submit" class="auth-btn auth-btn-primary">
            Envoyer le lien
        </button>
    </div>
</form>

<footer class="auth-form-footer">
    <p><a href="<?php echo e(route('login')); ?>" class="auth-link auth-link-strong">← Retour à la connexion</a></p>
</footer>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/forms-validation.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/pages/mot-de-passe-oublie.blade.php ENDPATH**/ ?>