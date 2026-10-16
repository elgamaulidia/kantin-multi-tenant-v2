<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kantin sedang tutup</title>
</head>
<body style="font-family: sans-serif; max-width: 480px; margin: 0 auto; padding: 16px;">
    <h1>Kantin sedang tutup</h1>
    <p>{{ $canteen->name }} belum menerima pemesanan saat ini.</p>
    @if ($range)
        <p>Jam operasional hari ini: {{ $range['opens'] }} - {{ $range['closes'] }} WIB.</p>
    @endif
</body>
</html>