<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Systicket'); ?> - Systicket</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/roles.css')); ?>">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body class="role-<?php echo e(auth()->user()->role ?? 'guest'); ?>" data-role="<?php echo e(auth()->user()->role ?? 'guest'); ?>" data-page="<?php echo $__env->yieldContent('page'); ?>">
    <!-- Skip link -->
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    <?php if(auth()->guard()->check()): ?>
        <!-- Header -->
        <?php echo $__env->make('components.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- Overlay mobile -->
        <div class="sidebar-overlay"></div>

        <div class="container">
            <!-- Sidebar -->
            <?php if(auth()->user()->role === 'client'): ?>
                <?php echo $__env->make('components.sidebar-client', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <?php echo $__env->make('components.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

            <!-- Contenu principal -->
            <main id="main-content" class="main-content">
                <?php if(session('success')): ?>
                <div class="validation-toast validation-toast-success" style="display:block;" id="flash-toast">
                    <?php echo e(session('success')); ?>

                </div>
                <script>setTimeout(function(){ document.getElementById('flash-toast').style.display='none'; }, 3000);</script>
                <?php endif; ?>

                <?php if(session('error')): ?>
                <div class="validation-toast validation-toast-error" style="display:block;" id="flash-toast">
                    <?php echo e(session('error')); ?>

                </div>
                <script>setTimeout(function(){ document.getElementById('flash-toast').style.display='none'; }, 3000);</script>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    <?php else: ?>
        <main id="main-content">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    <?php endif; ?>

    <!-- Scripts frontend -->
    <script>
        window.SYSTICKET = {
            baseUrl: '',
            apiUrl: '/api',
            csrfToken: '<?php echo e(csrf_token()); ?>',
            <?php if(auth()->guard()->check()): ?>
            user: <?php echo json_encode(auth()->user(), 15, 512) ?>,
            role: '<?php echo e(auth()->user()->role); ?>'
            <?php else: ?>
            user: null,
            role: 'guest'
            <?php endif; ?>
        };
    </script>
    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    <?php if(auth()->guard()->check()): ?>
    <script src="<?php echo e(asset('js/forms-validation.js')); ?>"></script>
    <script src="<?php echo e(asset('js/sidebar.js')); ?>"></script>
    <script src="<?php echo e(asset('js/roles.js')); ?>"></script>
    <script src="<?php echo e(asset('js/list-filters.js')); ?>"></script>
    <script src="<?php echo e(asset('js/ticket-validation.js')); ?>"></script>
    <script src="<?php echo e(asset('js/rapports-export.js')); ?>"></script>
    <script src="<?php echo e(asset('js/rapports.js')); ?>"></script>
    <?php endif; ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/layouts/app.blade.php ENDPATH**/ ?>