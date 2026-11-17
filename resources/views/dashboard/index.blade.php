@php
    use App\Enums\StockStatus;
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout title="Beranda PT">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ruang Kerja Operasional</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $activeCompany->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $activeCompany->description }}</p>
        </div>
        <div class="rounded-xl border px-4 py-2.5 text-xs {{ $activeCompany->code->headerClasses() }}">
            <p class="font-semibold">Role Aktif di {{ $activeCompany->short_name }}:</p>
            <p class="mt-0.5">{{ collect($activeCompanyRoles)->map(fn ($r) => $r->label())->implode(', ') }}</p>
        </div>
    </div>

    {{-- Ringkasan Metrik PT Aktif --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <p class="text-xs font-medium text-slate-500">Stok Milik PT Aktif (Tersedia)</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ IndonesianFormatter::quantity($statusSummary[StockStatus::Available->value]) }}</p>
            <p class="mt-1 text-xs text-slate-500">Siap dialokasikan sesuai izin PT</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <p class="text-xs font-medium text-slate-500">Direservasi / Dalam Perjalanan</p>
            <p class="mt-2 text-2xl font-bold text-amber-700">
                {{ IndonesianFormatter::quantity(bcadd($statusSummary[StockStatus::Reserved->value], $statusSummary[StockStatus::InTransit->value], 3)) }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                Reservasi {{ IndonesianFormatter::quantity($statusSummary[StockStatus::Reserved->value]) }} · Transit {{ IndonesianFormatter::quantity($statusSummary[StockStatus::InTransit->value]) }}
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <p class="text-xs font-medium text-slate-500">Stok Karantina</p>
            <p class="mt-2 text-2xl font-bold text-rose-700">{{ IndonesianFormatter::quantity($statusSummary[StockStatus::Quarantine->value]) }}</p>
            <p class="mt-1 text-xs text-slate-500">Tidak boleh dijual atau dikirim</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <p class="text-xs font-medium text-slate-500">Master Data Cakupan PT</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $accessibleProductsCount }} Produk · {{ $managedWarehousesCount }} Gudang</p>
            <p class="mt-1 text-xs text-slate-500">{{ $visibleLotsCount }} Lot terlacak</p>
        </div>
    </div>

    {{-- Stok Milik PT vs Barang Titipan di Gudang PT --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Stok Dimiliki {{ $activeCompany->short_name }}</h2>
                    <p class="text-xs text-slate-500">Kepemilikan hukum tetap pada PT ini meski disimpan di gudang PT lain.</p>
                </div>
                <a href="{{ route('inventory.balances.index', ['kepemilikan' => 'owned']) }}" class="text-xs font-semibold text-slate-700 hover:underline">Lihat semua</a>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2 pr-3">Produk</th>
                            <th class="py-2 pr-3">Lokasi Fisik</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($ownedBalances as $balance)
                            <tr>
                                <td class="py-2.5 pr-3 font-medium text-slate-900">{{ $balance->product->name }}</td>
                                <td class="py-2.5 pr-3 text-xs text-slate-600">
                                    {{ $balance->warehouse?->name ?? 'Dalam Perjalanan' }}
                                    @if ($balance->warehouse)
                                        <span class="block text-[11px] text-slate-400">
                                            Pengelola: {{ $balance->warehouse->company->short_name }}
                                            @if ($balance->rack)
                                                · {{ $balance->rack->code }}
                                            @endif
                                        </span>
                                    @endif
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
                                <td colspan="4" class="py-6 text-center text-xs text-slate-500">Belum ada stok yang dimiliki oleh PT ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Barang Titipan di Gudang {{ $activeCompany->short_name }}</h2>
                    <p class="text-xs text-slate-500">Stok fisik milik PT lain yang dikelola di gudang PT ini.</p>
                </div>
                <a href="{{ route('inventory.balances.index', ['kepemilikan' => 'custodial']) }}" class="text-xs font-semibold text-slate-700 hover:underline">Lihat semua</a>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2 pr-3">Produk</th>
                            <th class="py-2 pr-3">Pemilik Barang</th>
                            <th class="py-2 pr-3">Gudang / Rak</th>
                            <th class="py-2 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($custodialBalances as $balance)
                            <tr>
                                <td class="py-2.5 pr-3 font-medium text-slate-900">{{ $balance->product->name }}</td>
                                <td class="py-2.5 pr-3">
                                    <x-badge :classes="$balance->ownerCompany->code->badgeClasses()">
                                        {{ $balance->ownerCompany->short_name }}
                                    </x-badge>
                                </td>
                                <td class="py-2.5 pr-3 text-xs text-slate-600">
                                    {{ $balance->warehouse?->code }} · {{ $balance->rack?->code }}
                                </td>
                                <td class="py-2.5 text-right font-semibold text-slate-900">
                                    {{ IndonesianFormatter::quantity($balance->quantity, $balance->product->unit) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-xs text-slate-500">Tidak ada barang titipan PT lain di gudang PT aktif ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pergerakan Stok Terakhir --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Pergerakan Stok Terakhir (Cakupan PT Aktif)</h2>
                <p class="text-xs text-slate-500">Setiap perubahan saldo tercatat dengan referensi dokumen dan waktu WIB.</p>
            </div>
            <a href="{{ route('inventory.movements.index') }}" class="text-xs font-semibold text-slate-700 hover:underline">Buka buku pergerakan</a>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-2 pr-3">Waktu (WIB)</th>
                        <th class="py-2 pr-3">No. Pergerakan</th>
                        <th class="py-2 pr-3">Jenis</th>
                        <th class="py-2 pr-3">Produk &amp; Lot</th>
                        <th class="py-2 pr-3">Pemilik</th>
                        <th class="py-2 pr-3">Referensi</th>
                        <th class="py-2 text-right">Kuantitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentMovements as $movement)
                        <tr>
                            <td class="py-2.5 pr-3 text-xs text-slate-600">{{ IndonesianFormatter::wib($movement->occurred_at) }}</td>
                            <td class="py-2.5 pr-3 font-mono text-xs text-slate-800">{{ $movement->movement_number }}</td>
                            <td class="py-2.5 pr-3 text-xs font-medium text-slate-800">{{ $movement->movement_type->label() }}</td>
                            <td class="py-2.5 pr-3">
                                <span class="font-medium text-slate-900">{{ $movement->product->name }}</span>
                                <span class="block text-xs text-slate-500">Lot: {{ $movement->lot->lot_number }}</span>
                            </td>
                            <td class="py-2.5 pr-3 text-xs text-slate-700">
                                {{ $movement->toOwnerCompany?->short_name ?? $movement->fromOwnerCompany?->short_name ?? '-' }}
                            </td>
                            <td class="py-2.5 pr-3 font-mono text-xs text-slate-600">{{ $movement->reference_number }}</td>
                            <td class="py-2.5 text-right font-semibold text-slate-900">
                                {{ IndonesianFormatter::quantity($movement->quantity, $movement->product->unit) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-slate-500">Belum ada pergerakan stok tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
