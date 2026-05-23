@extends('layouts.app')

@section('title', 'Settings')

@section('content')
@php $isManager = auth()->user()->role === 'manager'; @endphp
<div class="p-4 md:p-6">
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-[#1C1917]">Pengaturan</h1>
        <p class="text-sm text-primary mt-1">Kelola tampilan, identitas bisnis, dan manajemen karyawan Anda di sini.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Appearance --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-start gap-3 mb-5">
                <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-[#1C1917]">Tampilan & Tema</h2>
                    <p class="text-xs text-[#78716C]">Ubah warna aksen dan mode tampilan.</p>
                </div>
            </div>

            <p class="text-sm font-semibold text-[#1C1917] mb-3">Warna Aksen Utama</p>
            <div class="flex gap-3 mb-5 flex-wrap">
                @php
                    $colors = [
                        '#F97316' => 'Orange',
                        '#3B82F6' => 'Biru',
                        '#10B981' => 'Hijau',
                        '#8B5CF6' => 'Ungu',
                        '#EF4444' => 'Merah',
                    ];
                    $currentColor = auth()->user()->theme_color ?? '#F97316';
                @endphp
                @foreach($colors as $hex => $name)
                <button type="button"
                        onclick="settingsModule.setColor('{{ $hex }}')"
                        data-color="{{ $hex }}"
                        title="{{ $name }}"
                        class="color-btn w-10 h-10 rounded-full flex items-center justify-center transition-all hover:scale-110 {{ $currentColor === $hex ? 'ring-2 ring-offset-2' : '' }}"
                        style="background-color: {{ $hex }};">
                    @if($currentColor === $hex)
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    @endif
                </button>
                @endforeach
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-stone-100">
                <div>
                    <p class="text-sm font-semibold text-[#1C1917]">Mode Gelap</p>
                    <p class="text-xs text-[#78716C]">Ubah latar menjadi gelap</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="dark-mode-toggle" class="sr-only peer"
                           {{ (auth()->user()->dark_mode ?? false) ? 'checked' : '' }}
                           onchange="settingsModule.toggleDarkMode(this.checked)">
                    <div class="w-11 h-6 bg-stone-200 peer-checked:bg-primary rounded-full peer transition-all
                                after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                after:bg-white after:rounded-full after:h-5 after:w-5
                                after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>

            <p id="theme-saved-msg" class="text-xs text-green-600 text-center mt-3 hidden">✓ Tema berhasil disimpan!</p>
        </div>

        {{-- Business Identity --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-start gap-3 mb-5">
                <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-[#1C1917]">Identitas Bisnis</h2>
                    <p class="text-xs text-[#78716C]">Kelola logo dan informasi kedai.</p>
                </div>
            </div>
            <form id="business-identity-form" enctype="multipart/form-data">
                @csrf
                <div class="flex items-start gap-4 mb-4">
                    <div class="relative flex-shrink-0">
                        <div id="logo-container" class="w-16 h-16 bg-stone-700 rounded-full flex items-center justify-center overflow-hidden">
                            @if(isset($settings['logo']) && $settings['logo'])
                                <img id="logo-preview" src="{{ $settings['logo'] }}" alt="Logo" class="w-full h-full object-cover">
                            @else
                                <span id="logo-text" class="text-white text-xs font-bold">COFFEE</span>
                                <img id="logo-preview" src="" alt="Logo" class="w-full h-full object-cover hidden">
                            @endif
                        </div>
                        @if($isManager)
                        <button type="button" onclick="document.getElementById('logo-file-input').click()"
                                class="absolute bottom-0 right-0 w-6 h-6 bg-primary rounded-full flex items-center justify-center hover:bg-[#EA580C] transition-colors">
                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <input type="file" id="logo-file-input" name="logo" accept="image/*" class="hidden"
                                onchange="settingsModule.previewLogo(this)">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-[#1C1917] mb-1">Logo Bisnis</p>
                        <p class="text-xs text-[#78716C] mb-3">PNG/JPG max 2MB, 500×500px.</p>
                        @if($isManager)
                        <div class="flex gap-2 flex-wrap">
                            <button type="button" onclick="document.getElementById('logo-file-input').click()"
                                    class="px-4 py-2 bg-[#F97316] hover:bg-[#EA580C] text-white text-xs font-semibold rounded-xl transition-colors shadow-md">
                                Upload Logo
                            </button>
                            <button type="button" onclick="settingsModule.removeLogo()"
                                    class="px-3 py-1.5 border border-stone-200 text-xs font-semibold text-[#78716C] rounded-xl hover:bg-stone-50 transition-colors">
                                Hapus
                            </button>
                        </div>
                        @endif
                        <p id="logo-filename" class="text-xs text-green-600 mt-1 hidden"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Nama Bisnis</label>
                    <input type="text" name="business_name" value="{{ $settings['business_name'] ?? 'Apipi Coffee' }}"
                            @unless($isManager) readonly @endunless
                            class="w-full px-4 py-2.5 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none {{ $isManager ? '' : 'bg-stone-50 text-[#78716C]' }}">
                </div>
                @if($isManager)
                <button type="submit" id="save-identity-btn"
                        class="w-full py-2.5 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors">
                    Simpan Perubahan
                </button>
                @endif
                <p id="save-success-msg" class="text-xs text-green-600 text-center mt-2 hidden">✓ Perubahan berhasil disimpan!</p>
            </form>
        </div>
    </div>

    {{-- Employee Management --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-4 md:px-6 py-4 md:py-5 border-b border-stone-100 gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-base font-bold text-[#1C1917]">Manajemen Karyawan</h2>
                    <p class="text-xs text-[#78716C]">Atur hak akses dan status akun staf.</p>
                </div>
            </div>
            @if($isManager)
            <button onclick="settingsModule.openAddEmployee()"
                    class="flex-shrink-0 flex items-center gap-1.5 px-3 md:px-4 py-2 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors shadow-lg shadow-orange-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v8M8 12h8"/>
                </svg>
                <span class="hidden sm:inline">+ Tambah Karyawan</span>
                <span class="sm:hidden">Tambah</span>
            </button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[540px]">
                <thead>
                    <tr class="border-b border-stone-100">
                        <th class="text-left py-3 px-4 md:px-6 text-xs font-bold text-[#78716C] uppercase tracking-wider">Nama Karyawan</th>
                        <th class="text-left py-3 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider">Peran</th>
                        <th class="text-left py-3 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider">Email</th>
                        <th class="text-left py-3 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider">Status</th>
                        @if($isManager)
                        <th class="text-left py-3 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($employees as $employee)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="py-3 px-4 md:px-6">
                            <div class="flex items-center gap-3">
                                <img src="{{ $employee->avatar_url }}" alt="{{ $employee->name }}"
                                    class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-[#1C1917] truncate">{{ $employee->name }}</p>
                                    <p class="text-xs text-[#78716C]">ID: {{ $employee->employee_id ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 bg-orange-100 text-primary text-xs font-bold rounded-lg uppercase whitespace-nowrap">
                                {{ strtoupper($employee->role) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-sm text-[#78716C] max-w-[140px] truncate">{{ $employee->email }}</td>
                        <td class="py-3 px-4">
                            <span class="flex items-center gap-1.5 text-sm font-medium whitespace-nowrap {{ $employee->status === 'active' ? 'text-green-600' : 'text-stone-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current flex-shrink-0"></span>
                                {{ $employee->status === 'active' ? 'Aktif' : ($employee->status === 'leave' ? 'Cuti' : 'Nonaktif') }}
                            </span>
                        </td>
                        @if($isManager)
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <button onclick="settingsModule.openEditEmployee({{ $employee->id }}, '{{ addslashes($employee->name) }}', '{{ $employee->email }}', '{{ $employee->role }}', '{{ $employee->status }}')"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button onclick="settingsModule.openDeleteEmployee({{ $employee->id }}, '{{ addslashes($employee->name) }}')"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($isManager)
{{-- Modals --}}
<div id="add-employee-modal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="settingsModule.closeAddEmployee()"></div>
    <div class="relative bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl w-full sm:max-w-sm z-10 max-h-[90vh] overflow-y-auto">
        <div class="p-5 md:p-6">
            <div class="flex justify-center mb-3 sm:hidden"><div class="w-10 h-1 bg-stone-200 rounded-full"></div></div>
            <div class="flex items-start justify-between mb-5">
                <h2 class="text-xl font-bold text-[#1C1917]">Tambah Karyawan Baru</h2>
                <button onclick="settingsModule.closeAddEmployee()" class="text-stone-400 hover:text-[#1C1917]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="add-employee-form" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Nama Karyawan</label>
                    <input type="text" name="name" placeholder="Nama lengkap" class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Email Karyawan</label>
                    <input type="email" name="email" placeholder="contoh@email.com" class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Kata Sandi</label>
                    <input type="password" name="password" placeholder="Minimal 8 karakter" class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Peran Karyawan</label>
                    <div class="flex gap-2 flex-wrap" id="role-btns">
                        @foreach(['cashier' => 'Kasir', 'admin' => 'Admin', 'manager' => 'Manager'] as $val => $label)
                        <button type="button" data-role="{{ $val }}"
                                class="role-btn px-4 py-2 rounded-2xl text-sm font-semibold border-2 transition-colors {{ $val === 'cashier' ? 'border-primary text-primary' : 'border-stone-200 text-[#78716C]' }}"
                                onclick="settingsModule.selectRole(this)">{{ $label }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="role" id="selected-role" value="cashier">
                </div>
                <div class="flex gap-3 pt-2 pb-2">
                    <button type="button" onclick="settingsModule.closeAddEmployee()" class="flex-1 py-3 text-sm font-semibold border-2 border-stone-200 rounded-2xl text-[#78716C]">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-employee-modal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="settingsModule.closeEditEmployee()"></div>
    <div class="relative bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl w-full sm:max-w-sm z-10 max-h-[90vh] overflow-y-auto">
        <div class="p-5 md:p-6">
            <div class="flex justify-center mb-3 sm:hidden"><div class="w-10 h-1 bg-stone-200 rounded-full"></div></div>
            <div class="flex items-start justify-between mb-5">
                <h2 class="text-xl font-bold text-[#1C1917]">Edit Karyawan</h2>
                <button onclick="settingsModule.closeEditEmployee()" class="text-stone-400 hover:text-[#1C1917]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-employee-form" class="space-y-4">
                @csrf
                <input type="hidden" id="edit-employee-id">
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Nama Karyawan</label>
                    <input type="text" id="edit-emp-name" name="name" class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Email</label>
                    <input type="email" id="edit-emp-email" name="email" class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Peran</label>
                    <select id="edit-emp-role" name="role" class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none bg-white">
                        <option value="cashier">Kasir</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Status</label>
                    <select id="edit-emp-status" name="status" class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none bg-white">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                        <option value="leave">Cuti</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2 pb-2">
                    <button type="button" onclick="settingsModule.closeEditEmployee()" class="flex-1 py-3 text-sm font-semibold border-2 border-stone-200 rounded-2xl text-[#78716C]">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="delete-employee-modal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="settingsModule.closeDeleteEmployee()"></div>
    <div class="relative bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl w-full sm:max-w-sm z-10 p-6 text-center">
        <div class="flex justify-center mb-3 sm:hidden"><div class="w-10 h-1 bg-stone-200 rounded-full"></div></div>
        <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-[#1C1917] mb-2">Hapus Karyawan</h2>
        <p id="delete-emp-text" class="text-sm text-[#78716C] mb-6"></p>
        <input type="hidden" id="delete-employee-id">
        <div class="flex gap-3">
            <button onclick="settingsModule.closeDeleteEmployee()" class="flex-1 py-3 text-sm font-semibold border-2 border-stone-200 rounded-2xl text-[#78716C]">Batal</button>
            <button onclick="settingsModule.confirmDeleteEmployee()" class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white rounded-2xl text-sm font-semibold transition-colors">Hapus</button>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
window.settingsModule = {
    // ── State tema ────────────────────────────────────────────────
    _currentColor: '{{ auth()->user()->theme_color ?? "#F97316" }}',
    _darkMode: {{ (auth()->user()->dark_mode ?? false) ? 'true' : 'false' }},

    // ── Tema: ganti warna aksen ───────────────────────────────────
    setColor(hex) {
        this._currentColor = hex;
        document.querySelectorAll('.color-btn').forEach(btn => {
            const isActive = btn.dataset.color === hex;
            btn.classList.toggle('ring-2', isActive);
            btn.classList.toggle('ring-offset-2', isActive);
            btn.innerHTML = isActive
                ? `<svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`
                : '';
        });
        if (window.themeStore) themeStore.applyTheme(hex, this._darkMode);
        this._saveTheme();
    },

    // ── Tema: toggle dark mode ────────────────────────────────────
    toggleDarkMode(isDark) {
        this._darkMode = isDark;
        if (window.themeStore) themeStore.applyTheme(this._currentColor, isDark);
        this._saveTheme();
    },

    // ── Tema: simpan ke database ──────────────────────────────────
    async _saveTheme() {
        const res = await fetch('{{ route("settings.theme.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ theme_color: this._currentColor, dark_mode: this._darkMode })
        });
        const data = await res.json();
        if (data.success) {
            const msg = document.getElementById('theme-saved-msg');
            if (msg) { msg.classList.remove('hidden'); setTimeout(() => msg.classList.add('hidden'), 2000); }
        }
    },

    // ── Logo ──────────────────────────────────────────────────────
    previewLogo(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) { alert('Ukuran file maksimal 2MB!'); input.value = ''; return; }
            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = document.getElementById('logo-preview');
                const text = document.getElementById('logo-text');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (text) text.classList.add('hidden');
                document.getElementById('logo-filename').textContent = '✓ ' + file.name;
                document.getElementById('logo-filename').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    },
    removeLogo() {
        const preview = document.getElementById('logo-preview');
        const text = document.getElementById('logo-text');
        preview.src = ''; preview.classList.add('hidden');
        if (text) text.classList.remove('hidden');
        document.getElementById('logo-file-input').value = '';
        document.getElementById('logo-filename').classList.add('hidden');
    },

    // ── Karyawan ──────────────────────────────────────────────────
    openAddEmployee() { document.getElementById('add-employee-modal').classList.remove('hidden'); },
    closeAddEmployee() { document.getElementById('add-employee-modal').classList.add('hidden'); document.getElementById('add-employee-form').reset(); },
    openEditEmployee(id, name, email, role, status) {
        document.getElementById('edit-employee-id').value = id;
        document.getElementById('edit-emp-name').value = name;
        document.getElementById('edit-emp-email').value = email;
        document.getElementById('edit-emp-role').value = role;
        document.getElementById('edit-emp-status').value = status;
        document.getElementById('edit-employee-modal').classList.remove('hidden');
    },
    closeEditEmployee() { document.getElementById('edit-employee-modal').classList.add('hidden'); },
    openDeleteEmployee(id, name) {
        document.getElementById('delete-employee-id').value = id;
        document.getElementById('delete-emp-text').innerHTML = `Hapus karyawan <strong>${name}</strong>? Tindakan ini tidak bisa dibatalkan.`;
        document.getElementById('delete-employee-modal').classList.remove('hidden');
    },
    closeDeleteEmployee() { document.getElementById('delete-employee-modal').classList.add('hidden'); },
    async confirmDeleteEmployee() {
        const id = document.getElementById('delete-employee-id').value;
        const res = await fetch(`/settings/employees/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) window.location.reload();
        else alert(data.message || 'Gagal menghapus karyawan');
    },
    selectRole(btn) {
        document.querySelectorAll('.role-btn').forEach(b => {
            b.classList.remove('border-primary', 'text-primary');
            b.classList.add('border-stone-200', 'text-[#78716C]');
        });
        btn.classList.add('border-primary', 'text-primary');
        btn.classList.remove('border-stone-200', 'text-[#78716C]');
        document.getElementById('selected-role').value = btn.dataset.role;
    }
};

async function parseJsonResponse(res) {
    const text = await res.text();
    try {
        return JSON.parse(text);
    } catch {
        throw new Error(res.ok ? 'Respons server tidak valid.' : 'Server error (' + res.status + '). Coba lagi.');
    }
}

function setSubmitLoading(btn, loading, label = 'Menyimpan...') {
    if (!btn) return;
    if (loading) {
        btn.dataset.originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('opacity-70', 'cursor-not-allowed', 'pointer-events-none');
        btn.innerHTML = `<span class="inline-flex items-center justify-center gap-2">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>${label}
        </span>`;
    } else {
        btn.disabled = false;
        btn.classList.remove('opacity-70', 'cursor-not-allowed', 'pointer-events-none');
        btn.innerHTML = btn.dataset.originalHtml || 'Simpan Perubahan';
    }
}

// ── Form submits ──────────────────────────────────────────────────────────────
document.getElementById('business-identity-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('save-identity-btn');
    setSubmitLoading(btn, true);
    try {
        const res = await fetch('{{ route("settings.identity.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: new FormData(this)
        });
        const data = await parseJsonResponse(res);
        if (data.success) {
            if (data.logo) {
                const preview = document.getElementById('logo-preview');
                const text = document.getElementById('logo-text');
                if (preview) { preview.src = data.logo; preview.classList.remove('hidden'); }
                if (text) text.classList.add('hidden');
            }
            if (data.business_name) {
                const parts = data.business_name.trim().split(/\s+/);
                const main = document.getElementById('app-brand-main');
                const accent = document.getElementById('app-brand-accent');
                if (main) main.textContent = parts[0] || data.business_name;
                if (accent) {
                    if (parts.length > 1) {
                        accent.textContent = parts.slice(1).join(' ');
                        accent.classList.remove('hidden');
                    } else {
                        accent.textContent = '';
                        accent.classList.add('hidden');
                    }
                }
                document.title = data.business_name + ' – Pengaturan';
            }
            document.getElementById('save-success-msg').classList.remove('hidden');
            setTimeout(() => document.getElementById('save-success-msg').classList.add('hidden'), 3000);
        } else {
            alert(data.message || 'Gagal menyimpan');
        }
    } catch (err) {
        alert(err.message || 'Gagal menyimpan');
    } finally {
        setSubmitLoading(btn, false);
    }
});

document.getElementById('add-employee-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.delete('_token'); // hapus CSRF dari body

    const payload = Object.fromEntries(formData);

    const res = await fetch('{{ route("settings.employees.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'  // ← tambah ini agar Laravel return JSON bukan HTML
        },
        body: JSON.stringify(payload)
    });

    const json = await res.json();
    if (json.success) window.location.reload();
    else alert(json.message || 'Gagal menambah karyawan');
});

document.getElementById('edit-employee-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('edit-employee-id').value;
    const data = Object.fromEntries(new FormData(this));
    const res = await fetch(`/settings/employees/${id}?_method=PUT`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify(data)
    });
    const json = await res.json();
    if (json.success) window.location.reload();
    else alert(json.message || 'Gagal update karyawan');
});
</script>
@endpush