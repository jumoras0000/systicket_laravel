<header class="header">
    <div class="header-left">
        <button type="button" class="menu-toggle" aria-label="Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <a href="<?php echo e(route('dashboard')); ?>" class="header-logo">
            <span class="header-logo-icon">ST</span>
            <span class="header-logo-text">Systicket</span>
        </a>
    </div>
    <div class="header-right">
        <div class="user-menu">
            <span class="user-name">
                <?php echo e(auth()->user()->first_name); ?> <?php echo e(auth()->user()->last_name); ?>

                <span class="user-role-badge <?php echo e(auth()->user()->role); ?>"><?php echo e(ucfirst(auth()->user()->role)); ?></span>
            </span>
            <a href="<?php echo e(route('profil')); ?>" class="btn btn-text btn-small" title="Mon profil">👤</a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" style="display:inline;">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-text btn-small" title="Se déconnecter">🚪</button>
            </form>
        </div>
    </div>
</header>
<?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/components/header.blade.php ENDPATH**/ ?>