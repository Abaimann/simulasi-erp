@php
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout title="Master Produk">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Master Produk &amp; Bahan</h1>
            <p class="mt-1 text-sm text-slate-600">
                Daftar produk yang tercakup untuk {{ $activeCompany->name }}.
            </p>
        </div>
        @if ($canManageProducts)
            <a href="{{ route('master.products.create') }}"
                class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                + Tambah Produk
            </a>
        @endif
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Nama Produk</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Satuan</th>
                        <th class="px-4 py-3">Cakupan PT</th>
                        @if ($canViewCustomerPrice)
                            <th class="px-4 py-3 text-right">Harga Antar-PT</th>
                            <th class="px-4 py-3 text-right">Harga Jual Pelanggan</th>
                        @endif
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-800">{{ $product->sku }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                {{ $product->name }}
                                @if ($product->description)
                                    <span class="block text-xs font-normal text-slate-500">{{ $product->description }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :classes="$product->category->badgeClasses()">{{ $product->category->label() }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $product->unit }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($product->accessibleCompanies as $company)
                                        <x-badge :classes="$company->code->badgeClasses()">{{ $company->short_name }}</x-badge>
                                    @endforeach
                                </div>
                            </td>
                            @if ($canViewCustomerPrice)
                                <td class="px-4 py-3 text-right font-medium text-slate-700">
                                    {{ IndonesianFormatter::rupiah($product->intercompany_price) }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-blue-700">
                                    {{ IndonesianFormatter::rupiah($product->customer_selling_price) }}
                                </td>
                            @endif
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('master.products.show', $product) }}"
                                    class="text-xs font-semibold text-slate-700 hover:text-slate-900 hover:underline">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canViewCustomerPrice ? 8 : 6 }}" class="px-4 py-8 text-center text-sm text-slate-500">
                                Tidak ada produk dalam cakupan PT ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
