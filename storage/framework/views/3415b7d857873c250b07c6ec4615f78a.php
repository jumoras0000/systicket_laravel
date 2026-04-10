<?php $__env->startSection('title', 'Connexion'); ?>
<?php $__env->startSection('body-class', 'auth-page auth-page-connexion'); ?>
<?php $__env->startSection('skip-target', 'login-form'); ?>
<?php $__env->startSection('skip-label', 'Aller au formulaire de connexion'); ?>
<?php $__env->startSection('tagline', 'Gestion de tickets et suivi du temps pour les équipes'); ?>

<?php $__env->startSection('features'); ?>
<ul class="auth-features" aria-hidden="true">
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">🎫</span>
        <span>Gérez vos tickets et projets</span>
    </li>
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">⏱️</span>
        <span>Suivi du temps et rapports</span>
    </li>
    <li class="auth-feature">
        <span class="auth-feature-icon" aria-hidden="true">✅</span>
        <span>Validation et facturation</span>
    </li>
</ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('form-wrapper-class', 'auth-form-connexion'); ?>

<?php $__env->startSection('content'); ?>
<header class="auth-form-header">
    <h1>Connexion</h1>
    <p>Entrez vos identifiants pour accéder à votre espace</p>
</header>

<?php if($errors->any()): ?>
    <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form id="login-form" class="auth-form" action="<?php echo e(route('login')); ?>" method="post">
    <?php echo csrf_field(); ?>

    <div class="auth-form-group">
        <label for="email" class="auth-label">Email ou identifiant</label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">✉</span>
            <input type="email" id="email" name="email" class="auth-input" placeholder="Email" required autocomplete="email" value="<?php echo e(old('email')); ?>">
        </div>
    </div>

    <div class="auth-form-group">
        <label for="password" class="auth-label">Mot de passe</label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon" aria-hidden="true">🔒</span>
            <input type="password" id="password" name="password" class="auth-input" placeholder="Mot de passe" required autocomplete="current-password">
        </div>
    </div>

    <div class="auth-form-row">
        <label class="auth-checkbox">
            <input type="checkbox" name="remember" id="remember">
            <span class="auth-checkbox-box"></span>
            <span class="auth-checkbox-label">Se souvenir de moi</span>
        </label>
        <a href="<?php echo e(route('password.request')); ?>" class="auth-link">Mot de passe oublié ?</a>
    </div>

    <div class="auth-btn-flee-wrapper">
        <button type="submit" class="auth-btn auth-btn-primary" id="auth-submit-btn">
            Se connecter
        </button>
    </div>
</form>

<footer class="auth-form-footer">
    <p>Vous n'avez pas de compte ? <a href="<?php echo e(route('register')); ?>" class="auth-link auth-link-strong">Créer un compte</a></p>
</footer>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/auth-button.js')); ?>"></script>
<script src="<?php echo e(asset('js/forms-validation.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/pages/auth/connexion.blade.php ENDPATH**/ ?>