@extends('layouts.app')

@section('title', 'Profile Settings')

@section('content')
<div class="p-6">
    <div class="mb-2">
        <h1 class="text-3xl font-bold text-[#1C1917]">Profile Settings</h1>
        <p class="text-sm text-primary mt-1">Manage your personal information and account security.</p>
    </div>

    <div class="max-w-2xl mx-auto">
        {{-- Avatar --}}
        <div class="bg-white rounded-2xl shadow-sm p-8 mb-6">
            <div class="flex flex-col items-center">
                <div class="relative mb-3">
                    <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                        class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                    <button type="button" onclick="document.getElementById('avatar-form-input').click()"
                            class="absolute bottom-1 right-1 w-8 h-8 bg-primary rounded-full flex items-center justify-center shadow-md hover:bg-[#EA580C] transition-colors">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <circle cx="12" cy="13" r="3"/>
                        </svg>
                    </button>
                    {{-- INPUT FILE DIHAPUS DARI SINI --}}
                </div>
                <p class="text-lg font-bold text-[#1C1917]">{{ $user->name }}</p>
                <span class="mt-1.5 px-3 py-1 bg-orange-100 text-primary text-xs font-bold rounded-full uppercase tracking-wider">
                    {{ $user->role }}
                </span>
                <p id="avatar-filename" class="text-xs text-green-600 mt-2 hidden"></p>
            </div>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <form id="profile-form" enctype="multipart/form-data" class="space-y-5">
                @csrf
                {{-- INPUT FILE DIPINDAHKAN KE DALAM FORM --}}
                <input type="file" name="avatar" id="avatar-form-input" accept="image/*" class="hidden" onchange="previewAvatar(this)">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Full Name</label>
                        <input type="text" name="name" value="{{ $user->name }}"
                                class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Role</label>
                        <div class="relative">
                            <select disabled class="w-full px-4 py-3 rounded-2xl border border-stone-200 text-sm outline-none appearance-none bg-stone-50 text-[#78716C] cursor-not-allowed">
                                <option value="{{ $user->role }}" selected>{{ ucfirst($user->role) }}</option>
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#78716C] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Email Address</label>
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <input type="email" value="{{ $user->email }}" readonly
                                class="w-full pl-10 pr-4 py-3 rounded-2xl border border-stone-200 text-sm bg-stone-50 text-[#78716C] cursor-not-allowed">
                    </div>
                </div>

                {{-- Account Security --}}
                <div class="pt-4 border-t border-stone-100">
                    <p class="text-xs font-bold text-primary uppercase tracking-wider mb-3">Account Security</p>

                    {{-- Toggle button --}}
                    <button type="button" onclick="togglePasswordForm()"
                            class="w-full flex items-center justify-between p-4 bg-stone-50 rounded-2xl hover:bg-stone-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-orange-100 rounded-xl flex items-center justify-center">
                                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-semibold text-[#1C1917]">Change Password</p>
                                <p class="text-xs text-[#78716C]">Klik untuk ubah password</p>
                            </div>
                        </div>
                        <svg id="pw-chevron" class="w-4 h-4 text-[#78716C] transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Password Form (hidden by default) --}}
                    <div id="password-form-container" class="hidden mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Password Saat Ini</label>
                            <div class="relative">
                                <input type="password" id="current_password" placeholder="Masukkan password saat ini"
                                        class="w-full px-4 py-3 pr-11 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                                <button type="button" onclick="togglePw('current_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Password Baru</label>
                            <div class="relative">
                                <input type="password" id="new_password" placeholder="Minimal 8 karakter"
                                        class="w-full px-4 py-3 pr-11 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                                <button type="button" onclick="togglePw('new_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Konfirmasi Password Baru</label>
                            <div class="relative">
                                <input type="password" id="confirm_password" placeholder="Ulangi password baru"
                                        class="w-full px-4 py-3 pr-11 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                                <button type="button" onclick="togglePw('confirm_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div id="pw-success" class="hidden text-sm text-green-600 bg-green-50 px-4 py-3 rounded-2xl">✓ Password berhasil diubah!</div>
                        <div id="pw-error" class="hidden text-sm text-red-500 bg-red-50 px-4 py-3 rounded-2xl"></div>
                        <button type="button" onclick="submitPassword()"
                                class="w-full py-3 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors">
                            Ubah Password
                        </button>
                    </div>
                </div>

                <div id="profile-success" class="hidden text-sm text-green-600 bg-green-50 px-4 py-3 rounded-2xl">✓ Profil berhasil disimpan!</div>
                <div id="profile-error" class="hidden text-sm text-red-500 bg-red-50 px-4 py-3 rounded-2xl"></div>

                <div class="flex gap-3 pt-4">
                    <a href="{{ route('pos.index') }}"
                        class="flex-1 py-3.5 text-center text-sm font-semibold bg-stone-100 hover:bg-stone-200 text-[#78716C] rounded-2xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="flex-1 py-3.5 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors shadow-lg shadow-orange-200">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Preview avatar - langsung dari avatar-form-input
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) { 
            alert('Ukuran foto maksimal 2MB!'); 
            input.value = ''; 
            return; 
        }
        const reader = new FileReader();
        reader.onload = (e) => { 
            document.getElementById('avatar-preview').src = e.target.result; 
        };
        reader.readAsDataURL(file);
        document.getElementById('avatar-filename').textContent = '✓ ' + file.name;
        document.getElementById('avatar-filename').classList.remove('hidden');
    }
}

// Toggle password field visibility
function togglePw(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Toggle password form expand/collapse
function togglePasswordForm() {
    const container = document.getElementById('password-form-container');
    const chevron = document.getElementById('pw-chevron');
    container.classList.toggle('hidden');
    chevron.style.transform = container.classList.contains('hidden') ? '' : 'rotate(180deg)';
}

// Submit profile
document.getElementById('profile-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    const formData = new FormData(this);

    const res = await fetch('{{ route("settings.profile.update") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: formData
    });
    const data = await res.json();

    btn.textContent = 'Simpan Perubahan';
    btn.disabled = false;

    document.getElementById('profile-success').classList.toggle('hidden', !data.success);
    document.getElementById('profile-error').classList.toggle('hidden', data.success);
    if (!data.success) document.getElementById('profile-error').textContent = data.message || 'Gagal menyimpan profil.';
    if (data.success) setTimeout(() => document.getElementById('profile-success').classList.add('hidden'), 3000);
});

// Submit password
async function submitPassword() {
    const current = document.getElementById('current_password').value;
    const newPass = document.getElementById('new_password').value;
    const confirm = document.getElementById('confirm_password').value;

    document.getElementById('pw-error').classList.add('hidden');
    document.getElementById('pw-success').classList.add('hidden');

    if (!current || !newPass || !confirm) {
        document.getElementById('pw-error').textContent = 'Semua field password harus diisi!';
        document.getElementById('pw-error').classList.remove('hidden');
        return;
    }
    if (newPass !== confirm) {
        document.getElementById('pw-error').textContent = 'Password baru dan konfirmasi tidak cocok!';
        document.getElementById('pw-error').classList.remove('hidden');
        return;
    }

    const res = await fetch('{{ route("settings.password.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ current_password: current, password: newPass, password_confirmation: confirm })
    });
    const data = await res.json();

    document.getElementById('pw-success').classList.toggle('hidden', !data.success);
    document.getElementById('pw-error').classList.toggle('hidden', data.success);
    if (!data.success) document.getElementById('pw-error').textContent = data.message || 'Gagal mengubah password.';
    if (data.success) {
        document.getElementById('current_password').value = '';
        document.getElementById('new_password').value = '';
        document.getElementById('confirm_password').value = '';
        setTimeout(() => {
            document.getElementById('pw-success').classList.add('hidden');
            document.getElementById('password-form-container').classList.add('hidden');
            document.getElementById('pw-chevron').style.transform = '';
        }, 3000);
    }
}
</script>
@endpush