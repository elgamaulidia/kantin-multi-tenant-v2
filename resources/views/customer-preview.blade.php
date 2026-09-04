<x-layouts.customer title="Kantin Pusat">
    <div class="space-y-3">
        <p class="text-sm text-gray-500">Preview layout customer — Tahap 4</p>

        <div class="bg-white rounded-lg p-4 shadow-sm">
            <h2 class="font-semibold">Nasi Goreng Spesial</h2>
            <p class="text-sm text-gray-500">Rp 25.000</p>
            <x-status-badge status="dimasak" class="mt-2" />
        </div>

        <x-button>Tambah ke Keranjang</x-button>

        <x-input placeholder="Cari menu..." />

        <x-empty-state message="Belum ada pesanan" />
    </div>
</x-layouts.customer>