<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dasbor Tenant' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen flex">
    <aside class="w-64 bg-white border-r hidden md:block">
        <div class="p-4 font-bold text-lg">Tenant</div>
        <nav class="flex flex-col">
            <a href="#" class="px-4 py-3 min-h-11 hover:bg-gray-50">Menu</a>
            <a href="#" class="px-4 py-3 min-h-11 hover:bg-gray-50">KDS</a>
            <a href="#" class="px-4 py-3 min-h-11 hover:bg-gray-50">Laporan</a>
        </nav>
    </aside>

    <div class="flex-1">
        <header class="bg-white shadow-sm px-4 py-3">
            <h1 class="text-lg font-semibold">{{ $title ?? 'Dasbor Tenant' }}</h1>
        </header>
        <main class="p-4">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>