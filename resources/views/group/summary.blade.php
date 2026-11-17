@php
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout title="Ringkasan Lintas PT">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                Manajemen Grup · Baca-Saja (Read-Only)
            </span>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Ringkasan Lintas Tiga PT</h1>
            <p class="mt-1 text-sm text-slate-600">
                Pemantauan kepemilikan stok vs lokasi fisik gudang serta struktur harga antar-PT dan pelanggan.
            </p>
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        @foreach ($companySummaries as $summary)
            @php $company = $summary['company']; @endphp
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
                <div class="flex items-center justify-between">
                    <x-badge :classes="$company->code->badgeClasses()">{{ $company->short_name }}</x-badge>
                    <span class="text-xs text-slate-500">{{ $company->city }}</span>
                </div>
                <h2 class="mt-2 text-base font-bold text-slate-900">{{ $company->name }}</h2>

                <dl class="mt-4 space-y-2 border-t border-slate-100 pt-3 text-xs">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Total Stok Dimiliki</dt>
                        <dd class="font-bold text-slate-900">{{ IndonesianFormatter::quantity($summary['owned_total']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Total Stok Fisik di Gudang PT</dt>
                        <dd class="font-bold text-slate-900">{{ IndonesianFormatter::quantity($summary['stored_total']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Anggota Aktif / Gudang</dt>
                        <dd class="font-semibold text-slate-700">{{ $company->active_members_count }} org · {{ $company->warehouses_count }} gudang</dd>
                    </div>
                </dl>
            </div>
        @endforeach
    </div>

    {{-- Matriks Kepemilikan vs Gudang Fisik --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
        <h2 class="text-base font-bold text-slate-900">Posisi Stok Lintas PT (Tanpa Penghitungan Ganda)</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-2 pr-3">Produk &amp; Lot</th>
                        <th class="py-2 pr-3">PT Pemilik Barang</th>
                        <th class="py-2 pr-3">Gudang &amp; Pengelola Fisik</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($allPositiveBalances as $balance)
                        <tr>
                            <td class="py-2.5 pr-3">
                                <span class="font-medium text-slate-900">{{ $balance->product->name }}</span>
                                <span class="block font-mono text-xs text-slate-500">{{ $balance->lot->lot_number }}</span>
                            </td>
                            <td class="py-2.5 pr-3">
                                <x-badge :classes="$balance->ownerCompany->code->badgeClasses()">{{ $balance->ownerCompany->name }}</x-badge>
                            </td>
                            <td class="py-2.5 pr-3 text-xs text-slate-700">
                                {{ $balance->warehouse?->name ?? 'Dalam Perjalanan' }}
                                @if ($balance->warehouse)
                                    <span class="block text-slate-400">
                                        Pengelola: {{ $balance->warehouse->company->short_name }} · {{ $balance->rack?->code }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 pr-3">
                                <x-badge :classes="$balance->status->badgeClasses()">{{ $balance->status->label() }}</x-badge>
                            </td>
                            <td class="py-2.5 text-right font-bold text-slate-900">
                                {{ IndonesianFormatter::quantity($balance->quantity, $balance->product->unit) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Ringkasan Referensi Harga --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
        <h2 class="text-base font-bold text-slate-900">Referensi Harga Produk</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-2 pr-3">SKU</th>
                        <th class="py-2 pr-3">Produk</th>
                        <th class="py-2 pr-3 text-right">Harga Antar-PT</th>
                        <th class="py-2 text-right">Harga Jual Pelanggan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($products as $product)
                        <tr>
                            <td class="py-2 pr-3 font-mono text-xs">{{ $product->sku }}</td>
                            <td class="py-2 pr-3 font-medium">{{ $product->name }}</td>
                            <td class="py-2 pr-3 text-right">{{ IndonesianFormatter::rupiah($product->intercompany_price) }}</td>
                            <td class="py-2 text-right font-bold text-blue-700">{{ IndonesianFormatter::rupiah($product->customer_selling_price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
