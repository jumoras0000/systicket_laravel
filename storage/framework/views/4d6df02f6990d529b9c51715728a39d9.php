<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Systicket'); ?> - Systicket</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body class="<?php echo $__env->yieldContent('body-class', 'auth-page'); ?>">
    <a href="#<?php echo $__env->yieldContent('skip-target', 'main-form'); ?>" class="skip-link"><?php echo $__env->yieldContent('skip-label', 'Aller au formulaire'); ?></a>

    <div class="auth-layout">
        <!-- Panneau gauche -->
        <aside class="auth-brand">
            <div class="auth-brand-inner">
                <a href="<?php echo e(url('/')); ?>" class="auth-logo">
                    <span class="auth-logo-icon">ST</span>
                    <span class="auth-logo-text">Systicket</span>
                </a>
                <p class="auth-tagline"><?php echo $__env->yieldContent('tagline', 'Gestion de tickets et suivi du temps pour les équipes'); ?></p>
                <?php if (! empty(trim($__env->yieldContent('features')))): ?>
                    <?php echo $__env->yieldContent('features'); ?>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Formulaire -->
        <main class="auth-form-panel <?php echo $__env->yieldContent('form-panel-class'); ?>">
            <div class="auth-form-wrapper <?php echo $__env->yieldContent('form-wrapper-class'); ?>">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/layouts/auth.blade.php ENDPATH**/ ?>