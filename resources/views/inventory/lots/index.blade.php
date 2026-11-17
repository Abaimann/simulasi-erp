@php
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout title="Daftar Lot">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Daftar Lot &amp; Batch</h1>
        <p class="mt-1 text-sm text-slate-600">
            Ketertelusuran lot yang berada dalam cakupan {{ $activeCompany->name }}.
        </p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Nomor Lot</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">PT Asal</th>
                        <th class="px-4 py-3">Sumber</th>
                        <th class="px-4 py-3">Tanggal Lot (WIB)</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($lots as $lot)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs font-bold text-slate-900">{{ $lot->lot_number }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $lot->product->name }}</td>
                            <td class="px-4 py-3">
                                <x-badge :classes="$lot->originCompany->code->badgeClasses()">{{ $lot->originCompany->short_name }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-700">{{ $lot->source_type->label() }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ IndonesianFormatter::wib($lot->manufactured_at) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('inventory.lots.show', $lot) }}" class="text-xs font-semibold text-slate-800 hover:underline">
                                    Telusuri Lot &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada lot dalam cakupan PT ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
