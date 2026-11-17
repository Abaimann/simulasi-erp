<x-guest-layout title="Masuk">
    <div class="mx-auto w-full max-w-4xl">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:col-span-6">
                <div class="mb-6">
                    <span class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                        Simulasi ERP · Grup Usaha Indonesia
                    </span>
                    <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Masuk ke Simulasi ERP</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Operasional terpadu PT Produksi, PT Distribusi, dan PT Logistik.
                    </p>
                </div>

                <x-alert />

                <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-2xs focus:border-slate-900 focus:outline-none">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
                        <input id="password" name="password" type="password" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-2xs focus:border-slate-900 focus:outline-none">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-slate-900">
                            <span>Ingat sesi saya</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-slate-800">
                        Masuk ke Aplikasi
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-6">
                @if (app()->isLocal())
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Akun Demo Lokal (Kata Sandi: <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">password</code>)</h2>
                    <p class="mt-1 text-xs text-slate-500">Klik akun di bawah untuk mengisi email &amp; kata sandi demo lokal.</p>

                    @php
                        $demoQuickAccounts = [
                            ['email' => 'admin@simulasi-erp.local', 'label' => 'Admin Grup (3 PT)', 'badge' => 'Tata Kelola'],
                            ['email' => 'sales.distribusi@simulasi-erp.local', 'label' => 'Sales Distribusi (PT Distribusi)', 'badge' => 'Lihat Harga Pelanggan'],
                            ['email' => 'purchasing.distribusi@simulasi-erp.local', 'label' => 'Purchasing (PT Distribusi)', 'badge' => 'PO Distribusi'],
                            ['email' => 'purchasing.produksi@simulasi-erp.local', 'label' => 'Purchasing (PT Produksi)', 'badge' => 'PO Bahan'],
                            ['email' => 'supervisor.produksi@simulasi-erp.local', 'label' => 'Supervisor Produksi (PT Produksi)', 'badge' => 'Produksi'],
                            ['email' => 'qc.produksi@simulasi-erp.local', 'label' => 'Petugas QC (PT Produksi)', 'badge' => 'QC Lot'],
                            ['email' => 'logistik@simulasi-erp.local', 'label' => 'Petugas Gudang & Logistik (PT Logistik)', 'badge' => 'Tanpa Harga Pelanggan'],
                            ['email' => 'finance.distribusi@simulasi-erp.local', 'label' => 'Finance (PT Distribusi)', 'badge' => 'Invoice Distribusi'],
                            ['email' => 'manajemen@simulasi-erp.local', 'label' => 'Manajemen Grup (3 PT)', 'badge' => 'Ringkasan Lintas PT'],
                            ['email' => 'multiperan@simulasi-erp.local', 'label' => 'Multi-PT (Purchasing Produksi / Finance Logistik)', 'badge' => 'Uji Ganti PT'],
                        ];
                    @endphp

                    <div class="mt-3 grid gap-2 sm:grid-cols-1">
                        @foreach ($demoQuickAccounts as $demo)
                            <button type="button" data-demo-email="{{ $demo['email'] }}"
                                class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2 text-left transition hover:border-slate-400 hover:bg-slate-50">
                                <div>
                                    <p class="text-xs font-semibold text-slate-900">{{ $demo['label'] }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $demo['email'] }}</p>
                                </div>
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-700">{{ $demo['badge'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <h2 class="text-base font-bold text-slate-900">Portal Internal Simulasi ERP</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Gunakan akun resmi yang diterbitkan oleh Admin Grup untuk mengakses ruang kerja PT Anda.
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-guest-layout>
