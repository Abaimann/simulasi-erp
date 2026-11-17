@php
    use App\Enums\ProductCategory;
@endphp

<x-app-layout title="Tambah Produk">
    <div class="mb-6">
        <a href="{{ route('master.products.index') }}" class="text-xs font-semibold text-slate-600 hover:underline">&larr; Kembali ke Master Produk</a>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Tambah Produk Master</h1>
    </div>

    <div class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xs">
        <form method="POST" action="{{ route('master.products.store') }}" class="space-y-4">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="sku" class="block text-sm font-medium text-slate-700">Kode SKU</label>
                    <input id="sku" name="sku" type="text" value="{{ old('sku') }}" required placeholder="FG-KKT-002"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="unit" class="block text-sm font-medium text-slate-700">Satuan</label>
                    <input id="unit" name="unit" type="text" value="{{ old('unit', 'kotak') }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Nama Produk</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-slate-700">Kategori</label>
                <select id="category" name="category" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    @foreach (ProductCategory::cases() as $category)
                        <option value="{{ $category->value }}" @selected(old('category') === $category->value)>
                            {{ $category->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="intercompany_price" class="block text-sm font-medium text-slate-700">Harga Antar-PT (Rp)</label>
                    <input id="intercompany_price" name="intercompany_price" type="number" min="0" value="{{ old('intercompany_price') }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="customer_selling_price" class="block text-sm font-medium text-slate-700">Harga Jual Pelanggan (Rp)</label>
                    <input id="customer_selling_price" name="customer_selling_price" type="number" min="0" value="{{ old('customer_selling_price') }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <p class="block text-sm font-medium text-slate-700">Cakupan Akses PT</p>
                <div class="mt-2 grid gap-2 sm:grid-cols-3">
                    @foreach ($companies as $company)
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium">
                            <input type="checkbox" name="company_ids[]" value="{{ $company->id }}"
                                @checked(in_array($company->id, old('company_ids', [])))>
                            <span>{{ $company->short_name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi</label>
                <textarea id="description" name="description" rows="2"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                Simpan Produk
            </button>
        </form>
    </div>
</x-app-layout>
