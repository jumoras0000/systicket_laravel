<?php $currentPage = View::yieldContent('page'); ?>
<aside class="sidebar">
    <nav class="sidebar-nav" aria-label="Menu principal">
        <ul class="nav-list">
            <li class="nav-item <?php echo e($currentPage === 'dashboard' ? 'active' : ''); ?>" data-page="dashboard">
                <a href="<?php echo e(route('dashboard')); ?>">
                    <span class="nav-icon">📊</span>
                    <span class="nav-label">Tableau de bord</span>
                </a>
            </li>
            <li class="nav-item nav-item-projets <?php echo e(in_array($currentPage, ['projets', 'projet-detail', 'projet-form']) ? 'active' : ''); ?>" data-page="projets">
                <a href="<?php echo e(route('projets.index')); ?>">
                    <span class="nav-icon">📁</span>
                    <span class="nav-label">Projets</span>
                </a>
            </li>
            <li class="nav-item nav-item-tickets <?php echo e(in_array($currentPage, ['tickets', 'ticket-detail', 'ticket-form']) ? 'active' : ''); ?>" data-page="tickets">
                <a href="<?php echo e(route('tickets.index')); ?>">
                    <span class="nav-icon">🎫</span>
                    <span class="nav-label">Tickets</span>
                </a>
            </li>
            <li class="nav-item nav-item-contrats role-admin-client <?php echo e(in_array($currentPage, ['contrats', 'contrat-detail', 'contrat-form']) ? 'active' : ''); ?>" data-page="contrats">
                <a href="<?php echo e(route('contrats.index')); ?>">
                    <span class="nav-icon">📄</span>
                    <span class="nav-label">Contrats</span>
                </a>
            </li>
            <li class="nav-item nav-item-temps role-admin-collaborateur <?php echo e($currentPage === 'temps' ? 'active' : ''); ?>" data-page="temps">
                <a href="<?php echo e(route('temps.index')); ?>">
                    <span class="nav-icon">⏱️</span>
                    <span class="nav-label">Temps</span>
                </a>
            </li>
            <li class="nav-item nav-item-rapports role-admin-only <?php echo e($currentPage === 'rapports' ? 'active' : ''); ?>" data-page="rapports">
                <a href="<?php echo e(route('rapports')); ?>">
                    <span class="nav-icon">📈</span>
                    <span class="nav-label">Rapports</span>
                </a>
            </li>
            <li class="nav-item nav-item-utilisateurs role-admin-only <?php echo e(in_array($currentPage, ['utilisateurs', 'user-form']) ? 'active' : ''); ?>" data-page="utilisateurs">
                <a href="<?php echo e(route('users.index')); ?>">
                    <span class="nav-icon">👥</span>
                    <span class="nav-label">Utilisateurs</span>
                </a>
            </li>
            <li class="nav-item nav-item-validation role-client-only <?php echo e($currentPage === 'ticket-validation' ? 'active' : ''); ?>" data-page="validation">
                <a href="<?php echo e(route('validations.index')); ?>">
                    <span class="nav-icon">✅</span>
                    <span class="nav-label">Validation</span>
                </a>
            </li>
            <li class="nav-separator"></li>
            <li class="nav-item <?php echo e($currentPage === 'profil' ? 'active' : ''); ?>" data-page="profil">
                <a href="<?php echo e(route('profil')); ?>">
                    <span class="nav-icon">👤</span>
                    <span class="nav-label">Mon profil</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
<?php /**PATH D:\JUMORAS\Documents\documents2\perso\ticketing_laravel\systicket\resources\views/components/sidebar.blade.php ENDPATH**/ ?>