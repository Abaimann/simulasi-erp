@php
    use App\Enums\StockStatus;
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout title="Saldo Stok & Kepemilikan">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Saldo Stok &amp; Kepemilikan Barang</h1>
        <p class="mt-1 text-sm text-slate-600">
            Menampilkan pemisahan tegas antara <strong>PT Pemilik Barang</strong>, <strong>Gudang / Pengelola Fisik</strong>, dan <strong>Status Stok</strong>.
        </p>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('inventory.balances.index') }}"
        class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-2xs sm:grid-cols-4">
        <div>
            <label for="status" class="block text-xs font-semibold text-slate-600">Status Stok</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (StockStatus::cases() as $statusCase)
                    <option value="{{ $statusCase->value }}" @selected($selectedStatus === $statusCase->value)>
                        {{ $statusCase->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="product_id" class="block text-xs font-semibold text-slate-600">Produk</label>
            <select id="product_id" name="product_id" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Produk</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected($selectedProductId === $product->id)>
                        {{ $product->sku }} — {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="kepemilikan" class="block text-xs font-semibold text-slate-600">Cakupan Kepemilikan</label>
            <select id="kepemilikan" name="kepemilikan" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua (Milik &amp; Titipan)</option>
                <option value="owned" @selected($ownershipScope === 'owned')>Milik {{ $activeCompany->short_name }}</option>
                <option value="custodial" @selected($ownershipScope === 'custodial')>Barang Titipan di Gudang {{ $activeCompany->short_name }}</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Terapkan Filter
            </button>
            <a href="{{ route('inventory.balances.index') }}"
                class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Lot</th>
                        <th class="px-4 py-3">Pemilik Barang (PT)</th>
                        <th class="px-4 py-3">Lokasi Gudang &amp; Rak</th>
                        <th class="px-4 py-3">Pengelola Gudang</th>
                        <th class="px-4 py-3">Status Stok</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($balances as $balance)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-900">{{ $balance->product->name }}</span>
                                <span class="block font-mono text-xs text-slate-500">{{ $balance->product->sku }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('inventory.lots.show', $balance->lot) }}"
                                    class="font-mono text-xs font-semibold text-slate-800 hover:underline">
                                    {{ $balance->lot->lot_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :classes="$balance->ownerCompany->code->badgeClasses()">
                                    {{ $balance->ownerCompany->name }}
                                </x-badge>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-700">
                                <span class="font-medium text-slate-900">{{ $balance->warehouse?->name ?? 'Dalam Perjalanan' }}</span>
                                @if ($balance->rack)
                                    <span class="block text-slate-500">Rak {{ $balance->rack->code }} ({{ $balance->rack->name }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-700">
                                {{ $balance->warehouse?->company->short_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :classes="$balance->status->badgeClasses()">{{ $balance->status->label() }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">
                                {{ IndonesianFormatter::quantity($balance->quantity, $balance->product->unit) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                                Tidak ada saldo stok yang cocok dengan filter atau cakupan PT aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
