<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Kata Sandi – {{ \App\Support\BusinessSettings::businessName() }} POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #F5F0EB; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; }
        .card { background: #fff; border-radius: 24px; padding: 48px 44px 44px; width: 100%; max-width: 460px; box-shadow: 0 4px 40px rgba(0,0,0,0.07); }
        .input-wrap { position: relative; margin-bottom: 16px; }
        .input-wrap .icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #B0A89E; width: 18px; height: 18px; }
        .input-wrap input { width: 100%; padding: 14px 16px 14px 44px; border: 1.5px solid #E8E2DA; border-radius: 14px; font-size: 14px; color: #1C1917; background: #fff; outline: none; transition: border-color 0.2s; }
        .input-wrap input:focus { border-color: #F97316; }
        .input-wrap input::placeholder { color: #B0A89E; }
        .eye-btn { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #B0A89E; padding: 4px; }
        .btn-primary { width: 100%; background: #F97316; color: #fff; border: none; border-radius: 14px; padding: 15px; font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s, transform 0.1s; margin-top: 8px; }
        .btn-primary:hover { background: #EA580C; }
        .btn-primary:active { transform: scale(0.98); }
        .error-msg { background: #FEF2F2; border: 1px solid #FECACA; color: #DC2626; padding: 10px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; }
        .step { display: none; }
        .step.active { display: block; }
    </style>
</head>
<body>
    <div class="card">
        {{-- Step 1: Input Email --}}
        <div class="step active" id="step-1">
            <div style="margin-bottom:28px;">
                <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:6px;color:#78716C;font-size:13px;font-weight:600;text-decoration:none;margin-bottom:20px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali ke Login
                </a>
                <h1 style="font-size:26px;font-weight:800;color:#1C1917;margin-bottom:6px;">Lupa Kata Sandi?</h1>
                <p style="color:#78716C;font-size:14px;">Masukkan email akun Anda untuk melanjutkan reset kata sandi.</p>
            </div>

            <div id="step1-error" class="error-msg" style="display:none;"></div>

            <label style="font-size:13px;font-weight:600;color:#1C1917;display:block;margin-bottom:8px;">Email Akun</label>
            <div class="input-wrap">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <input type="email" id="reset-email" placeholder="contoh@email.com">
            </div>

            <button class="btn-primary" onclick="checkEmail()">
                <span id="step1-btn-text">Lanjutkan</span>
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </button>
        </div>

        {{-- Step 2: Set New Password --}}
        <div class="step" id="step-2">
            <div style="margin-bottom:28px;">
                <button onclick="goStep(1)" style="display:inline-flex;align-items:center;gap:6px;color:#78716C;font-size:13px;font-weight:600;background:none;border:none;cursor:pointer;margin-bottom:20px;padding:0;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </button>
                <div style="width:48px;height:48px;background:#FFF7ED;border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#F97316" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 style="font-size:26px;font-weight:800;color:#1C1917;margin-bottom:6px;">Reset Kata Sandi</h1>
                <p style="color:#78716C;font-size:14px;">Akun ditemukan untuk <strong id="display-email" style="color:#1C1917;"></strong>. Buat kata sandi baru.</p>
            </div>

            <div id="step2-error" class="error-msg" style="display:none;"></div>

            <label style="font-size:13px;font-weight:600;color:#1C1917;display:block;margin-bottom:8px;">Kata Sandi Baru</label>
            <div class="input-wrap">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input type="password" id="new-password" placeholder="Minimal 8 karakter">
                <button type="button" class="eye-btn" onclick="togglePass('new-password')">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>

            <label style="font-size:13px;font-weight:600;color:#1C1917;display:block;margin-bottom:8px;">Konfirmasi Kata Sandi</label>
            <div class="input-wrap">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input type="password" id="confirm-password" placeholder="Ulangi kata sandi baru">
                <button type="button" class="eye-btn" onclick="togglePass('confirm-password')">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>

            <button class="btn-primary" onclick="resetPassword()">
                <span id="step2-btn-text">Simpan Kata Sandi Baru</span>
            </button>
        </div>

        {{-- Step 3: Success --}}
        <div class="step" id="step-3" style="text-align:center;">
            <div style="width:72px;height:72px;background:#F0FDF4;border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#22C55E" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 style="font-size:24px;font-weight:800;color:#1C1917;margin-bottom:8px;">Kata Sandi Berhasil Diubah!</h1>
            <p style="color:#78716C;font-size:14px;margin-bottom:28px;">Silakan login menggunakan kata sandi baru Anda.</p>
            <a href="{{ route('login') }}" class="btn-primary" style="text-decoration:none;display:flex;">
                Ke Halaman Login
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="margin-left:8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Footer --}}
    <div style="margin-top:36px;display:flex;flex-direction:column;align-items:center;gap:10px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:48px;height:48px;background:#FFF7ED;border-radius:14px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                @php
                    $logoUrl = null;
                    try {
                        $logoUrl = \Illuminate\Support\Facades\Storage::disk('s3')->exists('settings/logo.png')
                            ? \App\Support\StorageUrl::public('settings/logo.png')
                            : null;
                    } catch (\Exception $e) { /* S3/R2 belum dikonfigurasi */ }
                @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#F97316" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                @endif
            </div>
            @php [$brandMain, $brandAccent] = \App\Support\BusinessSettings::brandParts(); @endphp
            <span style="font-size:20px;font-weight:800;color:#1C1917;">{{ $brandMain }}@if($brandAccent) <span style="color:#F97316;">{{ $brandAccent }}</span>@endif</span>
        </div>
        <p style="font-size:11px;font-weight:600;color:#B0A89E;letter-spacing:0.15em;text-transform:uppercase;">Point of Sale System V2.4</p>
    </div>

    <script>
        let verifiedEmail = '';

        function goStep(n) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById('step-' + n).classList.add('active');
        }

        async function checkEmail() {
            const email = document.getElementById('reset-email').value.trim();
            const errEl = document.getElementById('step1-error');
            const btnText = document.getElementById('step1-btn-text');

            if (!email) { showError(errEl, 'Masukkan email terlebih dahulu.'); return; }

            btnText.textContent = 'Memeriksa...';
            errEl.style.display = 'none';

            const res = await fetch('{{ route("password.check-email") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ email })
            });
            const data = await res.json();
            btnText.textContent = 'Lanjutkan';

            if (data.exists) {
                verifiedEmail = email;
                document.getElementById('display-email').textContent = email;
                goStep(2);
            } else {
                showError(errEl, 'Email tidak ditemukan dalam sistem.');
            }
        }

        async function resetPassword() {
            const password = document.getElementById('new-password').value;
            const confirm  = document.getElementById('confirm-password').value;
            const errEl    = document.getElementById('step2-error');
            const btnText  = document.getElementById('step2-btn-text');

            errEl.style.display = 'none';

            if (password.length < 8) { showError(errEl, 'Kata sandi minimal 8 karakter.'); return; }
            if (password !== confirm) { showError(errEl, 'Konfirmasi kata sandi tidak cocok.'); return; }

            btnText.textContent = 'Menyimpan...';

            const res = await fetch('{{ route("password.reset-direct") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ email: verifiedEmail, password, password_confirmation: confirm })
            });
            const data = await res.json();
            btnText.textContent = 'Simpan Kata Sandi Baru';

            if (data.success) { goStep(3); }
            else { showError(errEl, data.message || 'Gagal mereset kata sandi.'); }
        }

        function showError(el, msg) {
            el.textContent = msg;
            el.style.display = 'block';
        }

        function togglePass(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>