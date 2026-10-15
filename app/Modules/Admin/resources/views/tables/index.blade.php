@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Kelola Meja dan QR Code</h1>

    @if (session('status'))
        <div class="mb-4 rounded bg-green-100 p-3 text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-100 p-3 text-red-800">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if (session('issued_qr_url'))
        <div class="mb-4 rounded border p-3">
            <p class="font-semibold">URL QR yang baru diterbitkan:</p>
            <input type="text" readonly
                value="{{ session('issued_qr_url') }}"
                class="mt-2 w-full rounded border p-2"
                onclick="this.select()">
            <p class="mt-2 text-sm">Simpan URL ini sebelum menerbitkan QR baru.</p>
        </div>
    @endif

    <div class="mb-6 rounded border p-4">
        <h2 class="mb-3 text-lg font-semibold">Tambah Meja</h2>

        <form method="POST" action="{{ route('admin.tables.store') }}">
            @csrf

            <div class="mb-3">
                <label for="code" class="block mb-1">Kode meja</label>
                <input id="code" name="code" required maxlength="30"
                    value="{{ old('code') }}"
                    class="w-full rounded border p-2" placeholder="Contoh: M01">
            </div>

            <div class="mb-3">
                <label for="label" class="block mb-1">Nama meja</label>
                <input id="label" name="label" required maxlength="60"
                    value="{{ old('label') }}"
                    class="w-full rounded border p-2" placeholder="Contoh: Meja 1">
            </div>

            <div class="mb-3">
                <label for="zone" class="block mb-1">Area (opsional)</label>
                <input id="zone" name="zone" maxlength="60"
                    value="{{ old('zone') }}"
                    class="w-full rounded border p-2" placeholder="Contoh: Dalam ruangan">
            </div>

            <div class="mb-3">
                <label for="status" class="block mb-1">Status</label>
                <select id="status" name="status" required
                    class="w-full rounded border p-2">
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak aktif</option>
                </select>
            </div>

            <button type="submit"
                class="rounded bg-blue-600 px-4 py-2 text-white">
                Tambah Meja
            </button>
        </form>
    </div>

    <h2 class="mb-3 text-lg font-semibold">Daftar Meja</h2>

    @forelse ($tables as $table)
        <div class="mb-4 rounded border p-4">
            <form method="POST"
                action="{{ route('admin.tables.update', $table->id) }}">
                @csrf
                @method('PUT')

                <p class="mb-2 font-semibold">
                    {{ $table->label }} ({{ $table->code }})
                </p>

                <div class="mb-3">
                    <label class="block mb-1">Kode meja</label>
                    <input name="code" required maxlength="30"
                        value="{{ $table->code }}" class="w-full rounded border p-2">
                </div>

                <div class="mb-3">
                    <label class="block mb-1">Nama meja</label>
                    <input name="label" required maxlength="60"
                        value="{{ $table->label }}" class="w-full rounded border p-2">
                </div>

                <div class="mb-3">
                    <label class="block mb-1">Area</label>
                    <input name="zone" maxlength="60"
                        value="{{ $table->zone }}" class="w-full rounded border p-2">
                </div>

                <div class="mb-3">
                    <label class="block mb-1">Status</label>
                    <select name="status" required class="w-full rounded border p-2">
                        <option value="active" @selected($table->status === 'active')>Aktif</option>
                        <option value="inactive" @selected($table->status === 'inactive')>Tidak aktif</option>
                    </select>
                </div>

                <button type="submit"
                    class="rounded bg-blue-600 px-4 py-2 text-white">
                    Simpan Perubahan
                </button>
            </form>

            <form method="POST"
                action="{{ route('admin.tables.qr.issue', $table->id) }}"
                class="mt-3">
                @csrf
                <button type="submit" @disabled($table->status !== 'active')
                    class="rounded bg-green-600 px-4 py-2 text-white disabled:opacity-50">
                    Terbitkan QR Baru
                </button>
            </form>

            @foreach ($table->qrTokens as $token)
                <div class="mt-3 flex items-center justify-between gap-3 border-t pt-3">
                    <span>QR #{{ $token->id }} — {{ $token->status }}</span>

                    @if ($token->status === 'active')
                        <form method="POST"
                            action="{{ route('admin.tables.qr.revoke', [$table->id, $token->id]) }}"
                            onsubmit="return confirm('Cabut QR ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="rounded bg-red-600 px-3 py-2 text-white">
                                Cabut QR
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @empty
        <p>Belum ada meja yang terdaftar.</p>
    @endforelse
</div>
@endsection
