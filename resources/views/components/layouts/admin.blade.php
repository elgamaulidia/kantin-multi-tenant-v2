<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dasbor Pengelola' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-white shadow-sm px-4 py-3 flex justify-between items-center">
        <h1 class="text-lg font-semibold">{{ $title ?? 'Pengelola Kantin' }}</h1>
        <nav class="flex gap-4 text-sm">
            <a href="#">Tenant</a>
            <a href="#">Meja</a>
            <a href="#">Pencairan</a>
        </nav>
    </header>

    <main class="p-4 overflow-x-auto">
        {{ $slot }}
    </main>
</body>
</html>