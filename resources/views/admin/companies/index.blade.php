<x-app-layout title="Perusahaan Grup">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tiga Perusahaan dalam Grup</h1>
        <p class="mt-1 text-sm text-slate-600">
            Struktur entitas bisnis internal Simulasi ERP.
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        @foreach ($companies as $company)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
                <div class="flex items-center justify-between">
                    <x-badge :classes="$company->code->badgeClasses()">{{ $company->code->value }}</x-badge>
                    <span class="text-xs font-medium text-slate-500">{{ $company->city }}</span>
                </div>
                <h2 class="mt-3 text-lg font-bold text-slate-900">{{ $company->name }}</h2>
                <p class="mt-1 text-xs font-medium text-slate-500">{{ $company->business_type->label() }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ $company->description }}</p>

                <div class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3 text-xs">
                    <div>
                        <p class="text-slate-500">Keanggotaan Aktif</p>
                        <p class="text-base font-bold text-slate-900">{{ $company->active_memberships_count }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Gudang Dikelola</p>
                        <p class="text-base font-bold text-slate-900">{{ $company->warehouses_count }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
