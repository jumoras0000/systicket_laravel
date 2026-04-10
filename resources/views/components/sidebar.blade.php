@php $currentPage = View::yieldContent('page'); @endphp
<aside class="sidebar">
    <nav class="sidebar-nav" aria-label="Menu principal">
        <ul class="nav-list">
            <li class="nav-item {{ $currentPage === 'dashboard' ? 'active' : '' }}" data-page="dashboard">
                <a href="{{ route('dashboard') }}">
                    <span class="nav-icon">📊</span>
                    <span class="nav-label">Tableau de bord</span>
                </a>
            </li>
            <li class="nav-item nav-item-projets {{ in_array($currentPage, ['projets', 'projet-detail', 'projet-form']) ? 'active' : '' }}" data-page="projets">
                <a href="{{ route('projets.index') }}">
                    <span class="nav-icon">📁</span>
                    <span class="nav-label">Projets</span>
                </a>
            </li>
            <li class="nav-item nav-item-tickets {{ in_array($currentPage, ['tickets', 'ticket-detail', 'ticket-form']) ? 'active' : '' }}" data-page="tickets">
                <a href="{{ route('tickets.index') }}">
                    <span class="nav-icon">🎫</span>
                    <span class="nav-label">Tickets</span>
                </a>
            </li>
            <li class="nav-item nav-item-contrats role-admin-client {{ in_array($currentPage, ['contrats', 'contrat-detail', 'contrat-form']) ? 'active' : '' }}" data-page="contrats">
                <a href="{{ route('contrats.index') }}">
                    <span class="nav-icon">📄</span>
                    <span class="nav-label">Contrats</span>
                </a>
            </li>
            <li class="nav-item nav-item-temps role-admin-collaborateur {{ $currentPage === 'temps' ? 'active' : '' }}" data-page="temps">
                <a href="{{ route('temps.index') }}">
                    <span class="nav-icon">⏱️</span>
                    <span class="nav-label">Temps</span>
                </a>
            </li>
            <li class="nav-item nav-item-rapports role-admin-only {{ $currentPage === 'rapports' ? 'active' : '' }}" data-page="rapports">
                <a href="{{ route('rapports') }}">
                    <span class="nav-icon">📈</span>
                    <span class="nav-label">Rapports</span>
                </a>
            </li>
            <li class="nav-item nav-item-utilisateurs role-admin-only {{ in_array($currentPage, ['utilisateurs', 'user-form']) ? 'active' : '' }}" data-page="utilisateurs">
                <a href="{{ route('users.index') }}">
                    <span class="nav-icon">👥</span>
                    <span class="nav-label">Utilisateurs</span>
                </a>
            </li>
            <li class="nav-item nav-item-validation role-client-only {{ $currentPage === 'ticket-validation' ? 'active' : '' }}" data-page="validation">
                <a href="{{ route('validations.index') }}">
                    <span class="nav-icon">✅</span>
                    <span class="nav-label">Validation</span>
                </a>
            </li>
            <li class="nav-separator"></li>
            <li class="nav-item {{ $currentPage === 'profil' ? 'active' : '' }}" data-page="profil">
                <a href="{{ route('profil') }}">
                    <span class="nav-icon">👤</span>
                    <span class="nav-label">Mon profil</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
