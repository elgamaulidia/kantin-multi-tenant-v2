<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Kantin Multi-Tenant' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <header class="bg-white shadow-sm px-4 py-3 sticky top-0 z-10">
        <h1 class="text-lg font-semibold">{{ $title ?? 'Kantin' }}</h1>
    </header>

    <main class="px-4 py-4">
        {{ $slot }}
    </main>

    <footer class="text-center text-xs text-gray-400 py-4">
        Kantin Multi-Tenant
    </footer>
</body>
</html>