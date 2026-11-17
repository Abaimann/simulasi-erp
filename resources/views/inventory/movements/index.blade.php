@php
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout title="Pergerakan Stok">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Buku Besar Pergerakan Stok</h1>
        <p class="mt-1 text-sm text-slate-600">
            Catatan pergerakan stok tidak dapat diubah langsung; setiap perubahan saldo harus memiliki dokumen referensi dan jejak audit.
        </p>
    </div>

    @if ($canTransferRacks)
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-white p-5 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Catat Perpindahan Rak Internal ({{ $activeCompany->short_name }})</h2>
            <p class="text-xs text-slate-500">
                Memindahkan barang secara fisik antar-rak di gudang yang dikelola {{ $activeCompany->short_name }} tanpa mengubah PT pemilik barang.
            </p>

            @if ($transferableBalances->isNotEmpty() && $managedRacks->count() > 1)
                <form method="POST" action="{{ route('inventory.movements.rack-transfer') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @csrf
                    <div>
                        <label for="source_bucket" class="block text-xs font-semibold text-slate-700">Stok Sumber</label>
                        <select id="source_bucket" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                            @foreach ($transferableBalances as $item)
                                <option
                                    data-product="{{ $item->product_id }}"
                                    data-lot="{{ $item->lot_id }}"
                                    data-owner="{{ $item->owner_company_id }}"
                                    data-warehouse="{{ $item->warehouse_id }}"
                                    data-rack="{{ $item->rack_id }}"
                                    data-status="{{ $item->status->value }}"
                                    @selected((int) old('lot_id') === $item->lot_id && (int) old('from_rack_id') === $item->rack_id && (int) old('owner_company_id') === $item->owner_company_id)>
                                    {{ $item->product->name }} ({{ $item->lot->lot_number }}) · {{ $item->rack?->code }} · Milik {{ $item->ownerCompany->short_name }} · Saldo {{ IndonesianFormatter::quantity($item->quantity, $item->product->unit) }}
                                </option>
                            @endforeach
                        </select>
                        @php $firstItem = $transferableBalances->first(); @endphp
                        <input type="hidden" id="tf_product_id" name="product_id" value="{{ old('product_id', $firstItem?->product_id) }}">
                        <input type="hidden" id="tf_lot_id" name="lot_id" value="{{ old('lot_id', $firstItem?->lot_id) }}">
                        <input type="hidden" id="tf_owner_id" name="owner_company_id" value="{{ old('owner_company_id', $firstItem?->owner_company_id) }}">
                        <input type="hidden" id="tf_warehouse_id" name="warehouse_id" value="{{ old('warehouse_id', $firstItem?->warehouse_id) }}">
                        <input type="hidden" id="tf_from_rack_id" name="from_rack_id" value="{{ old('from_rack_id', $firstItem?->rack_id) }}">
                        <input type="hidden" id="tf_status" name="status" value="{{ old('status', $firstItem?->status->value) }}">
                    </div>

                    <div>
                        <label for="to_rack_id" class="block text-xs font-semibold text-slate-700">Rak Tujuan</label>
                        <select id="to_rack_id" name="to_rack_id" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                            @foreach ($managedRacks as $rack)
                                <option value="{{ $rack->id }}" @selected((int) old('to_rack_id') === $rack->id)>
                                    {{ $rack->warehouse->code }} · {{ $rack->code }} — {{ $rack->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="quantity" class="block text-xs font-semibold text-slate-700">Kuantitas Dipindah</label>
                        <input id="quantity" name="quantity" type="number" step="0.001" min="0.001" value="{{ old('quantity', '50') }}" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="block text-xs font-semibold text-slate-700">Catatan</label>
                        <input id="notes" name="notes" type="text" value="{{ old('notes') }}" placeholder="Penataan rak gudang Cikarang"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-800">
                            Simpan Perpindahan Rak
                        </button>
                    </div>
                </form>
            @else
                <p class="mt-3 text-xs text-slate-500">Belum ada stok atau rak tujuan yang tersedia untuk perpindahan rak.</p>
            @endif
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Waktu (WIB)</th>
                        <th class="px-4 py-3">No. Dokumen</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Produk &amp; Lot</th>
                        <th class="px-4 py-3">Pemilik Asal &rarr; Tujuan</th>
                        <th class="px-4 py-3">Lokasi Asal &rarr; Tujuan</th>
                        <th class="px-4 py-3">Referensi</th>
                        <th class="px-4 py-3 text-right">Kuantitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ IndonesianFormatter::wib($movement->occurred_at) }}</td>
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-800">{{ $movement->movement_number }}</td>
                            <td class="px-4 py-3 text-xs font-medium text-slate-800">{{ $movement->movement_type->label() }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-900">{{ $movement->product->name }}</span>
                                <span class="block font-mono text-xs text-slate-500">{{ $movement->lot->lot_number }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-700">
                                {{ $movement->fromOwnerCompany?->short_name ?? 'Saldo Awal' }}
                                &rarr;
                                <strong>{{ $movement->toOwnerCompany?->short_name ?? '-' }}</strong>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                {{ $movement->fromWarehouse?->code ?? '-' }} {{ $movement->fromRack ? '('.$movement->fromRack->code.')' : '' }}
                                &rarr;
                                {{ $movement->toWarehouse?->code ?? '-' }} {{ $movement->toRack ? '('.$movement->toRack->code.')' : '' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                <span class="font-mono">{{ $movement->reference_number }}</span>
                                <span class="block text-[11px] text-slate-400">Oleh: {{ $movement->performer->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">
                                {{ IndonesianFormatter::quantity($movement->quantity, $movement->product->unit) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada pergerakan stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
