@php
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout :title="'Lot '.$lot->lot_number">
    <div class="mb-6">
        <a href="{{ route('inventory.lots.index') }}" class="text-xs font-semibold text-slate-600 hover:underline">&larr; Kembali ke Daftar Lot</a>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="font-mono text-xs font-semibold text-slate-500">{{ $lot->lot_number }} · {{ $lot->source_type->label() }}</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $lot->product->name }}</h1>
                @if ($lot->notes)
                    <p class="mt-1 text-sm text-slate-600">{{ $lot->notes }}</p>
                @endif
            </div>
            <x-badge :classes="$lot->originCompany->code->badgeClasses()">Asal: {{ $lot->originCompany->short_name }}</x-badge>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Saldo Saat Ini</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2 pr-3">Pemilik</th>
                            <th class="py-2 pr-3">Gudang / Rak</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2 text-right">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($balances as $balance)
                            <tr>
                                <td class="py-2.5 pr-3">
                                    <x-badge :classes="$balance->ownerCompany->code->badgeClasses()">{{ $balance->ownerCompany->short_name }}</x-badge>
                                </td>
                                <td class="py-2.5 pr-3 text-xs text-slate-700">
                                    {{ $balance->warehouse?->name ?? 'Dalam Perjalanan' }}
                                    @if ($balance->rack)
                                        <span class="block text-slate-400">{{ $balance->rack->code }}</span>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-3">
                                    <x-badge :classes="$balance->status->badgeClasses()">{{ $balance->status->label() }}</x-badge>
                                </td>
                                <td class="py-2.5 text-right font-semibold">{{ IndonesianFormatter::quantity($balance->quantity, $lot->product->unit) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-xs text-slate-500">Saldo kosong.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Riwayat Pergerakan Lot</h2>
            <div class="mt-4 space-y-3">
                @forelse ($movements as $movement)
                    <div class="rounded-xl border border-slate-200 p-3.5 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono font-bold text-slate-900">{{ $movement->movement_number }}</span>
                            <span class="text-slate-500">{{ IndonesianFormatter::wib($movement->occurred_at) }}</span>
                        </div>
                        <p class="mt-1 font-semibold text-slate-800">
                            {{ $movement->movement_type->label() }} — {{ IndonesianFormatter::quantity($movement->quantity, $lot->product->unit) }}
                        </p>
                        <p class="mt-0.5 text-slate-600">
                            Ref: <span class="font-mono">{{ $movement->reference_number }}</span> · Oleh {{ $movement->performer->name }}
                        </p>
                    </div>
                @empty
                    <p class="py-6 text-center text-xs text-slate-500">Belum ada riwayat pergerakan.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
