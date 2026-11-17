<x-app-layout title="Gudang & Rak">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Master Gudang &amp; Rak</h1>
        <p class="mt-1 text-sm text-slate-600">
            Gudang dikelola oleh PT pemilik lokasi fisik, namun dapat menyimpan barang titipan milik PT lain.
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($warehouses as $warehouse)
            <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-semibold text-slate-500">{{ $warehouse->code }}</span>
                        <x-badge :classes="$warehouse->company->code->badgeClasses()">
                            Pengelola: {{ $warehouse->company->short_name }}
                        </x-badge>
                    </div>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $warehouse->name }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $warehouse->city }} · {{ $warehouse->address }}</p>
                    @if ($warehouse->is_third_party_storage)
                        <p class="mt-2 inline-flex rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-800">
                            Gudang Titipan Antar-PT
                        </p>
                    @endif
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-3">
                    <span class="text-xs font-medium text-slate-600">{{ $warehouse->racks_count }} Rak Terdaftar</span>
                    <a href="{{ route('master.warehouses.show', $warehouse) }}"
                        class="text-xs font-semibold text-slate-900 hover:underline">
                        Kelola / Lihat Rak &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                Tidak ada gudang yang dikelola oleh PT aktif ini.
            </div>
        @endforelse
    </div>
</x-app-layout>
