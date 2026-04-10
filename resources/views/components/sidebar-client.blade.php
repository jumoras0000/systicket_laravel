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
            <li class="nav-item {{ in_array($currentPage, ['projets', 'projet-detail']) ? 'active' : '' }}" data-page="projets">
                <a href="{{ route('projets.index') }}">
                    <span class="nav-icon">📁</span>
                    <span class="nav-label">Projets</span>
                </a>
            </li>
            <li class="nav-item {{ in_array($currentPage, ['tickets', 'ticket-detail']) ? 'active' : '' }}" data-page="tickets">
                <a href="{{ route('tickets.index') }}">
                    <span class="nav-icon">🎫</span>
                    <span class="nav-label">Tickets</span>
                </a>
            </li>
            <li class="nav-item {{ in_array($currentPage, ['contrats', 'contrat-detail']) ? 'active' : '' }}" data-page="contrats">
                <a href="{{ route('contrats.index') }}">
                    <span class="nav-icon">📄</span>
                    <span class="nav-label">Contrats</span>
                </a>
            </li>
            <li class="nav-item {{ $currentPage === 'ticket-validation' ? 'active' : '' }}" data-page="validation">
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
