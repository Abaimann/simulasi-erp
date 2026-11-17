@php
    use App\Enums\RackZoneType;
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout :title="$warehouse->name">
    <div class="mb-6">
        <a href="{{ route('master.warehouses.index') }}" class="text-xs font-semibold text-slate-600 hover:underline">&larr; Kembali ke Daftar Gudang</a>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="font-mono text-xs font-semibold text-slate-500">{{ $warehouse->code }} · {{ $warehouse->city }}</span>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $warehouse->name }}</h1>
            </div>
            <x-badge :classes="$warehouse->company->code->badgeClasses()">
                Pengelola Gudang: {{ $warehouse->company->name }}
            </x-badge>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Daftar Rak + Tambah Rak --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
                <h2 class="text-base font-bold text-slate-900">Daftar Rak Gudang</h2>
                <div class="mt-4 space-y-2.5">
                    @foreach ($warehouse->racks as $rack)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 px-3.5 py-2.5">
                            <div>
                                <p class="font-mono text-xs font-bold text-slate-900">{{ $rack->code }}</p>
                                <p class="text-xs text-slate-600">{{ $rack->name }}</p>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <x-badge :classes="$rack->zone_type->badgeClasses()">{{ $rack->zone_type->label() }}</x-badge>
                                    @if (! $rack->is_active)
                                        <x-badge classes="bg-slate-200 text-slate-700 border-slate-300">Nonaktif</x-badge>
                                    @endif
                                </div>
                            </div>
                            @if ($canManageRacks)
                                <form method="POST" action="{{ route('master.warehouses.racks.toggle', [$warehouse, $rack]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg border border-slate-300 px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-100">
                                        {{ $rack->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($canManageRacks)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
                    <h2 class="text-base font-bold text-slate-900">Tambah Rak Baru</h2>
                    <form method="POST" action="{{ route('master.warehouses.racks.store', $warehouse) }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label for="code" class="block text-xs font-semibold text-slate-700">Kode Rak</label>
                            <input id="code" name="code" type="text" value="{{ old('code') }}" required placeholder="RAK-A03"
                                class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-700">Nama Rak</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required placeholder="Rak Penyimpanan A-03"
                                class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label for="zone_type" class="block text-xs font-semibold text-slate-700">Zona</label>
                            <select id="zone_type" name="zone_type" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                @foreach (RackZoneType::cases() as $zone)
                                    <option value="{{ $zone->value }}">{{ $zone->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                            Simpan Rak
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Isi Fisik Gudang (termasuk barang titipan milik PT lain) --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs lg:col-span-2">
            <h2 class="text-base font-bold text-slate-900">Stok Fisik Tersimpan di {{ $warehouse->name }}</h2>
            <p class="text-xs text-slate-500">Menampilkan pemilik hukum barang secara terpisah dari pengelola gudang.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2 pr-3">Rak</th>
                            <th class="py-2 pr-3">Produk &amp; Lot</th>
                            <th class="py-2 pr-3">Pemilik Barang (PT)</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($balances as $balance)
                            <tr>
                                <td class="py-2.5 pr-3 font-mono text-xs font-semibold text-slate-800">{{ $balance->rack?->code ?? '-' }}</td>
                                <td class="py-2.5 pr-3">
                                    <span class="font-medium text-slate-900">{{ $balance->product->name }}</span>
                                    <span class="block font-mono text-xs text-slate-500">{{ $balance->lot->lot_number }}</span>
                                </td>
                                <td class="py-2.5 pr-3">
                                    <x-badge :classes="$balance->ownerCompany->code->badgeClasses()">
                                        {{ $balance->ownerCompany->name }}
                                    </x-badge>
                                </td>
                                <td class="py-2.5 pr-3">
                                    <x-badge :classes="$balance->status->badgeClasses()">{{ $balance->status->label() }}</x-badge>
                                </td>
                                <td class="py-2.5 text-right font-semibold text-slate-900">
                                    {{ IndonesianFormatter::quantity($balance->quantity, $balance->product->unit) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-slate-500">Belum ada stok fisik tersimpan di gudang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
