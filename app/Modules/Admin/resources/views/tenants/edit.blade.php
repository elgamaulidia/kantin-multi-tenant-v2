<x-layouts.admin title="{{ $tenant->display_name }}">
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800 dark:bg-green-900/40 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800 dark:bg-red-900/40 dark:text-red-300">
            <ul class="list-disc ps-4">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center gap-3">
        <h1 class="text-xl font-semibold">
            {{ $tenant->display_name }}
        </h1>

        <x-status-badge :status="$tenant->status">
            {{ $tenant->status }}
        </x-status-badge>
    </div>

    {{-- Daftar skema komisi --}}
    <section class="mt-6">
        <h2 class="font-semibold">
            Skema Komisi (effective-dated)
        </h2>

        <ul class="mt-2 space-y-1 text-sm">
            @forelse ($commissions as $c)
                <li>
                    {{ number_format((float) $c->commission_rate, 2) }}%
                    —
                    {{ $c->valid_from }}
                    s/d
                    {{ $c->valid_to ?? 'sekarang' }}
                </li>
            @empty
                <li class="text-zinc-500">
                    Belum ada skema komisi.
                </li>
            @endforelse
        </ul>
    </section>

    {{-- Form sesuai modul --}}
    <section class="mt-8">
        <h2 class="font-semibold">
            Jadwalkan Skema Komisi Baru
        </h2>

        <form
            method="POST"
            action="{{ route('admin.tenants.commission.store', $tenant) }}"
            class="mt-3 flex flex-wrap items-end gap-2"
        >
            @csrf

            <x-input
                name="commission_rate"
                label="Komisi baru (%)"
                type="number"
                step="0.01"
                required
            />

            <x-input
                name="effective_at"
                label="Berlaku sejak"
                type="datetime-local"
                required
            />

            <x-button type="submit">
                Jadwalkan
            </x-button>
        </form>
    </section>
</x-layouts.admin>