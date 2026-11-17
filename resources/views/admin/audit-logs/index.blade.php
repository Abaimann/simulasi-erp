@php
    use App\Support\IndonesianFormatter;
@endphp

<x-app-layout title="Audit Log">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Audit Log Aktivitas &amp; Dokumen</h1>
        <p class="mt-1 text-sm text-slate-600">
            Mencatat pelaku, konteks PT, tindakan, dokumen terkait, dan waktu dalam zona Asia/Jakarta (WIB).
        </p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Waktu (WIB)</th>
                        <th class="px-4 py-3">Pelaku</th>
                        <th class="px-4 py-3">PT Konteks</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Dokumen</th>
                        <th class="px-4 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ IndonesianFormatter::wib($log->occurred_at) }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-900">{{ $log->actor?->name ?? 'Sistem' }}</td>
                            <td class="px-4 py-3">
                                @if ($log->company)
                                    <x-badge :classes="$log->company->code->badgeClasses()">{{ $log->company->short_name }}</x-badge>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $log->action }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $log->document_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-700">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada catatan audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
