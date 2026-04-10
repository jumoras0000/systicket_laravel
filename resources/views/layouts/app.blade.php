<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Systicket') - Systicket</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/roles.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="role-{{ auth()->user()->role ?? 'guest' }}" data-role="{{ auth()->user()->role ?? 'guest' }}" data-page="@yield('page')">
    <!-- Skip link -->
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    @auth
        <!-- Header -->
        @include('components.header')

        <!-- Overlay mobile -->
        <div class="sidebar-overlay"></div>

        <div class="container">
            <!-- Sidebar -->
            @if(auth()->user()->role === 'client')
                @include('components.sidebar-client')
            @else
                @include('components.sidebar')
            @endif

            <!-- Contenu principal -->
            <main id="main-content" class="main-content">
                @if(session('success'))
                <div class="validation-toast validation-toast-success" style="display:block;" id="flash-toast">
                    {{ session('success') }}
                </div>
                <script>setTimeout(function(){ document.getElementById('flash-toast').style.display='none'; }, 3000);</script>
                @endif

                @if(session('error'))
                <div class="validation-toast validation-toast-error" style="display:block;" id="flash-toast">
                    {{ session('error') }}
                </div>
                <script>setTimeout(function(){ document.getElementById('flash-toast').style.display='none'; }, 3000);</script>
                @endif

                @yield('content')
            </main>
        </div>
    @else
        <main id="main-content">
            @yield('content')
        </main>
    @endauth

    <!-- Scripts frontend -->
    <script>
        window.SYSTICKET = {
            baseUrl: '',
            apiUrl: '/api',
            csrfToken: '{{ csrf_token() }}',
            @auth
            user: @json(auth()->user()),
            role: '{{ auth()->user()->role }}'
            @else
            user: null,
            role: 'guest'
            @endauth
        };
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
    @auth
    <script src="{{ asset('js/forms-validation.js') }}"></script>
    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script src="{{ asset('js/roles.js') }}"></script>
    <script src="{{ asset('js/list-filters.js') }}"></script>
    <script src="{{ asset('js/ticket-validation.js') }}"></script>
    <script src="{{ asset('js/rapports-export.js') }}"></script>
    <script src="{{ asset('js/rapports.js') }}"></script>
    @endauth
    @stack('scripts')
</body>
</html>
