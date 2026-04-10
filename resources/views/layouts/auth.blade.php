<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Systicket') - Systicket</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="@yield('body-class', 'auth-page')">
    <a href="#@yield('skip-target', 'main-form')" class="skip-link">@yield('skip-label', 'Aller au formulaire')</a>

    <div class="auth-layout">
        <!-- Panneau gauche -->
        <aside class="auth-brand">
            <div class="auth-brand-inner">
                <a href="{{ url('/') }}" class="auth-logo">
                    <span class="auth-logo-icon">ST</span>
                    <span class="auth-logo-text">Systicket</span>
                </a>
                <p class="auth-tagline">@yield('tagline', 'Gestion de tickets et suivi du temps pour les équipes')</p>
                @hasSection('features')
                    @yield('features')
                @endif
            </div>
        </aside>

        <!-- Formulaire -->
        <main class="auth-form-panel @yield('form-panel-class')">
            <div class="auth-form-wrapper @yield('form-wrapper-class')">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
