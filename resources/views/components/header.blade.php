<header class="header">
    <div class="header-left">
        <button type="button" class="menu-toggle" aria-label="Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <a href="{{ route('dashboard') }}" class="header-logo">
            <span class="header-logo-icon">ST</span>
            <span class="header-logo-text">Systicket</span>
        </a>
    </div>
    <div class="header-right">
        <div class="user-menu">
            <span class="user-name">
                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                <span class="user-role-badge {{ auth()->user()->role }}">{{ ucfirst(auth()->user()->role) }}</span>
            </span>
            <a href="{{ route('profil') }}" class="btn btn-text btn-small" title="Mon profil">👤</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-text btn-small" title="Se déconnecter">🚪</button>
            </form>
        </div>
    </div>
</header>
