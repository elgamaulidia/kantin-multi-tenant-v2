<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $canteen->name }}</title>
</head>
<body style="font-family: sans-serif; max-width: 480px; margin: 0 auto; padding: 16px;">
    <h1>Selamat datang, {{ $table->label }}@if($table->zone) — {{ $table->zone }}@endif</h1>
    <p>{{ $canteen->name }}</p>
    <h2>Tenant</h2>
    @forelse ($tenants as $tenant)
        <p>{{ $tenant->display_name }}
            <strong>{{ $tenant->status === 'active' ? 'BUKA' : 'TUTUP' }}</strong></p>
    @empty
        <p>Belum ada tenant.</p>
    @endforelse
</body>
</html>