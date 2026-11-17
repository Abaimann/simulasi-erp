<x-app-layout title="Pengguna & Role">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengguna, Keanggotaan PT, dan Role</h1>
        <p class="mt-1 text-sm text-slate-600">
            Satu pengguna dapat memiliki role berbeda di beberapa PT. Pergantian PT aktif hanya mengubah konteks kerja, bukan menambah hak akses.
        </p>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        {{-- Form Tambah Pengguna Baru --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Tambah Pengguna Baru</h2>
            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 space-y-3">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700">Nama Lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700">Kata Sandi</label>
                        <input id="password" name="password" type="password" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="user_company_id" class="block text-xs font-semibold text-slate-700">PT Penugasan Awal</label>
                        <select id="user_company_id" name="company_id" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->short_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="user_role" class="block text-xs font-semibold text-slate-700">Role Awal</label>
                        <select id="user_role" name="role" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                    Simpan Pengguna
                </button>
            </form>
        </div>

        {{-- Form Tambah Keanggotaan / Role Tambahan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
            <h2 class="text-base font-bold text-slate-900">Tambah Keanggotaan PT / Role</h2>
            <form method="POST" action="{{ route('admin.memberships.store') }}" class="mt-4 space-y-3">
                @csrf
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="member_user_id" class="block text-xs font-semibold text-slate-700">Pengguna</label>
                        <select id="member_user_id" name="user_id" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            @foreach ($users as $userOption)
                                <option value="{{ $userOption->id }}">{{ $userOption->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="member_company_id" class="block text-xs font-semibold text-slate-700">PT</label>
                        <select id="member_company_id" name="company_id" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->short_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="member_role" class="block text-xs font-semibold text-slate-700">Role</label>
                        <select id="member_role" name="role" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-xs text-slate-600">
                    <input type="checkbox" name="is_default" value="1">
                    <span>Jadikan PT default saat pengguna ini login</span>
                </label>

                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                    Tetapkan Keanggotaan
                </button>
            </form>
        </div>
    </div>

    {{-- Tabel Pengguna & Keanggotaan --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Pengguna</th>
                        <th class="px-4 py-3">Status Akun</th>
                        <th class="px-4 py-3">Keanggotaan PT &amp; Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $userRow)
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <p class="font-semibold text-slate-900">{{ $userRow->name }}</p>
                                <p class="text-xs text-slate-500">{{ $userRow->email }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <x-badge :classes="$userRow->is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-200 text-slate-700 border-slate-300'">
                                    {{ $userRow->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($userRow->memberships as $membership)
                                        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs">
                                            <x-badge :classes="$membership->company->code->badgeClasses()">
                                                {{ $membership->company->short_name }}
                                            </x-badge>
                                            <span class="font-semibold text-slate-800">{{ $membership->role->label() }}</span>
                                            @if (! $membership->is_active)
                                                <span class="text-rose-600">(Nonaktif)</span>
                                            @endif
                                            <form method="POST" action="{{ route('admin.memberships.toggle', $membership) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-[11px] font-medium text-slate-600 hover:text-slate-900 hover:underline">
                                                    {{ $membership->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
