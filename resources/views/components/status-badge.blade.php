@props(['status'])

@php
$colors = [
    'menunggu_pembayaran' => 'bg-gray-200 text-gray-700',
    'diterima' => 'bg-blue-100 text-blue-700',
    'dimasak' => 'bg-yellow-100 text-yellow-700',
    'siap_diambil' => 'bg-green-100 text-green-700',
    'selesai' => 'bg-gray-100 text-gray-500',
];
$labels = [
    'menunggu_pembayaran' => 'Menunggu Pembayaran',
    'diterima' => 'Diterima',
    'dimasak' => 'Dimasak',
    'siap_diambil' => 'Siap Diambil',
    'selesai' => 'Selesai',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-block px-2 py-1 rounded-full text-xs font-medium ' . ($colors[$status] ?? 'bg-gray-100 text-gray-600')]) }}>
    {{ $labels[$status] ?? $status }}
</span>