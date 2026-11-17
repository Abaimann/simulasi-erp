@php
    use App\Support\IndonesianFormatter;
    $user = auth()->user();
    $companyCode = $activeCompany->code;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Beranda' }} — {{ config('app.name', 'Simulasi ERP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <div id="mobile-sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden"></div>

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside id="mobile-sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:static lg:translate-x-0">
            <div class="flex h-16 items-center justify-between border-b border-slate-200 px-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white">SE</span>
                    <div>
                        <p class="text-sm font-bold tracking-tight text-slate-900">Simulasi ERP</p>
                        <p class="text-[11px] font-medium text-slate-500">Tahap 1 · Fondasi</p>
                    </div>
                </a>
                <button type="button" id="close-sidebar-btn" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="Tutup menu">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>

            {{-- Active PT Card inside Sidebar --}}
            <div class="border-b border-slate-200 p-4">
                <div class="rounded-xl border p-3 {{ $companyCode->headerClasses() }}">
                    <p class="text-[11px] font-semibold uppercase tracking-wider opacity-75">PT Aktif Saat Ini</p>
                    <p class="mt-0.5 text-sm font-bold leading-snug">{{ $activeCompany->name }}</p>
                    <p class="text-xs opacity-80">{{ $activeCompany->city }} · {{ $activeCompany->code->value }}</p>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach ($activeCompanyRoles as $role)
                            <span class="inline-flex items-center rounded bg-white/80 px-2 py-0.5 text-[11px] font-semibold text-slate-800 shadow-2xs">
                                {{ $role->label() }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4 text-sm">
                <div>
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 font-medium {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <span>Beranda PT</span>
                    </a>
                </div>

                <div>
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Master Data</p>
                    <div class="mt-1.5 space-y-1">
                        <a href="{{ route('master.products.index') }}"
                            class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('master.products.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            Master Produk
                        </a>
                        @can('view-customer-pricing')
                            <a href="{{ route('master.pricing.index') }}"
                                class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('master.pricing.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                                Harga Jual Pelanggan
                            </a>
                        @endcan
                        <a href="{{ route('master.warehouses.index') }}"
                            class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('master.warehouses.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            Gudang &amp; Rak
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Stok &amp; Lot</p>
                    <div class="mt-1.5 space-y-1">
                        <a href="{{ route('inventory.balances.index') }}"
                            class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('inventory.balances.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            Saldo Stok &amp; Kepemilikan
                        </a>
                        <a href="{{ route('inventory.lots.index') }}"
                            class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('inventory.lots.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            Daftar Lot
                        </a>
                        <a href="{{ route('inventory.movements.index') }}"
                            class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('inventory.movements.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            Pergerakan Stok
                        </a>
                    </div>
                </div>

                @if ($companyContext->canViewGroupSummary())
                    <div>
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Eksekutif Grup</p>
                        <div class="mt-1.5 space-y-1">
                            <a href="{{ route('group.summary.index') }}"
                                class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('group.summary.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                                Ringkasan Lintas PT
                            </a>
                            <a href="{{ route('admin.audit-logs.index') }}"
                                class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.audit-logs.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                                Audit Log Grup
                            </a>
                        </div>
                    </div>
                @endif

                @can('manage-group-master')
                    <div>
                        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Admin Grup</p>
                        <div class="mt-1.5 space-y-1">
                            <a href="{{ route('admin.companies.index') }}"
                                class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.companies.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                                Perusahaan (3 PT)
                            </a>
                            <a href="{{ route('admin.users.index') }}"
                                class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.users.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                                Pengguna &amp; Role
                            </a>
                            <a href="{{ route('admin.audit-logs.index') }}"
                                class="flex items-center rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.audit-logs.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                                Audit Log Sistem
                            </a>
                        </div>
                    </div>
                @endcan
            </nav>

            <div class="border-t border-slate-200 p-4 text-xs text-slate-500">
                <p class="font-semibold text-slate-700">{{ $user?->name }}</p>
                <p class="truncate">{{ $user?->email }}</p>
                <p class="mt-1 text-[11px]">Waktu: {{ IndonesianFormatter::wib(now(), 'd M Y H:i') }}</p>
            </div>
        </aside>

        {{-- Main content area --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur-xs">
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button type="button" id="open-sidebar-btn" class="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                        </button>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $companyCode->badgeClasses() }}">
                                <span class="h-2 w-2 rounded-full bg-current"></span>
                                PT Aktif: {{ $activeCompany->short_name }} ({{ $activeCompany->city }})
                            </span>
                            <span class="hidden text-xs text-slate-500 sm:inline">
                                {{ $activeCompany->name }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        @if ($accessibleCompanies->count() > 1)
                            <form method="POST" action="{{ route('active-company.switch') }}" class="flex items-center gap-2">
                                @csrf
                                <label for="company_switcher" class="sr-only">Pilih PT Aktif</label>
                                <select id="company_switcher" name="company_id"
                                    class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-800 shadow-2xs focus:border-slate-900 focus:outline-none">
                                    @foreach ($accessibleCompanies as $companyOption)
                                        <option value="{{ $companyOption->id }}" @selected($companyOption->id === $activeCompany->id)>
                                            {{ $companyOption->short_name }} — {{ $companyOption->city }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">
                                    Ganti PT
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <div class="mx-auto max-w-7xl">
                    <x-alert />
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>
</html>
