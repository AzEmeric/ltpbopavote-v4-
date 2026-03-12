<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <script>
        (function() {
            var t = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Concours de l'Excellence 2026 - Lycée Technique et Professionnel de Bopa. Votez pour votre candidat favori.">
    <meta name="theme-color" content="#FAFAF8">

    <title>@yield('title', 'Concours de l\'Excellence 2026 — LTP Bopa')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">


    @stack('styles')
</head>
<body>
    @yield('content')

    <script>
        window.APP_CONFIG = {
            baseUrl: '{{ url("/") }}',
            apiUrl: '{{ url("/api") }}',
            csrfToken: '{{ csrf_token() }}',
            votePrice: {{ config('concours.vote_price', 100) }},
            currency: '{{ config("concours.currency", "XOF") }}'
        };
    </script>
    <script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>

    @stack('scripts')
</body>
</html>
