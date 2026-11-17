@php
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout :title="$product->name">
    <div class="mb-6">
        <a href="{{ route('master.products.index') }}" class="text-xs font-semibold text-slate-600 hover:underline">&larr; Kembali ke Master Produk</a>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="font-mono text-xs font-semibold text-slate-500">{{ $product->sku }}</span>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $product->name }}</h1>
            </div>
            <x-badge :classes="$product->category->badgeClasses()">{{ $product->category->label() }}</x-badge>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Informasi Produk</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Satuan</dt>
                    <dd class="font-semibold text-slate-900">{{ $product->unit }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Status</dt>
                    <dd class="font-semibold text-emerald-700">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                @if ($canViewCustomerPrice)
                    <div class="flex justify-between border-t border-slate-100 pt-3">
                        <dt class="text-slate-500">Harga Antar-PT</dt>
                        <dd class="font-semibold text-slate-900">{{ IndonesianFormatter::rupiah($product->intercompany_price) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Harga Jual Pelanggan</dt>
                        <dd class="font-bold text-blue-700">{{ IndonesianFormatter::rupiah($product->customer_selling_price) }}</dd>
                    </div>
                @endif
                <div class="border-t border-slate-100 pt-3">
                    <dt class="text-slate-500">PT yang Berwenang</dt>
                    <dd class="mt-1.5 flex flex-wrap gap-1.5">
                        @foreach ($product->accessibleCompanies as $company)
                            <x-badge :classes="$company->code->badgeClasses()">{{ $company->short_name }}</x-badge>
                        @endforeach
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs lg:col-span-2">
            <h2 class="text-base font-bold text-slate-900">Posisi Saldo Stok ({{ $activeCompany->short_name }})</h2>
            <p class="text-xs text-slate-500">Kepemilikan barang dan pengelola gudang fisik ditampilkan terpisah.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2 pr-3">Lot</th>
                            <th class="py-2 pr-3">Pemilik Barang</th>
                            <th class="py-2 pr-3">Gudang &amp; Rak</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($balances as $balance)
                            <tr>
                                <td class="py-2.5 pr-3 font-mono text-xs text-slate-800">{{ $balance->lot->lot_number }}</td>
                                <td class="py-2.5 pr-3">
                                    <x-badge :classes="$balance->ownerCompany->code->badgeClasses()">{{ $balance->ownerCompany->short_name }}</x-badge>
                                </td>
                                <td class="py-2.5 pr-3 text-xs text-slate-600">
                                    {{ $balance->warehouse?->name ?? 'Dalam Perjalanan' }}
                                    @if ($balance->rack)
                                        <span class="block text-[11px] text-slate-400">Rak {{ $balance->rack->code }} ({{ $balance->warehouse->company->short_name }})</span>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-3">
                                    <x-badge :classes="$balance->status->badgeClasses()">{{ $balance->status->label() }}</x-badge>
                                </td>
                                <td class="py-2.5 text-right font-semibold text-slate-900">
                                    {{ IndonesianFormatter::quantity($balance->quantity, $product->unit) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-xs text-slate-500">Tidak ada saldo stok pada cakupan PT aktif ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
