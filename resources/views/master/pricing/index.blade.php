@php
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout title="Harga Jual Pelanggan">
    <div class="mb-6">
        <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-900">
            Akses Terbatas · Komersial &amp; Keuangan
        </span>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Daftar Harga Jual Pelanggan</h1>
        <p class="mt-1 text-sm text-slate-600">
            Halaman ini hanya dapat diakses oleh role berwenang pada PT Distribusi dan Manajemen Grup. Petugas Gudang &amp; Logistik tidak memiliki akses.
        </p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Produk Jadi</th>
                        <th class="px-4 py-3">Satuan</th>
                        <th class="px-4 py-3 text-right">Harga Beli Antar-PT</th>
                        <th class="px-4 py-3 text-right">Harga Jual Pelanggan</th>
                        <th class="px-4 py-3 text-right">Selisih Simulasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($products as $product)
                        @php
                            $margin = ($product->customer_selling_price ?? 0) - ($product->intercompany_price ?? 0);
                        @endphp
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-800">{{ $product->sku }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $product->unit }}</td>
                            <td class="px-4 py-3 text-right text-slate-700">{{ IndonesianFormatter::rupiah($product->intercompany_price) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-blue-700">{{ IndonesianFormatter::rupiah($product->customer_selling_price) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ IndonesianFormatter::rupiah($margin) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
