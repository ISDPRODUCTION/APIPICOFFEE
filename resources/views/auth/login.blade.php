<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Apipi Coffe POS</title>
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
        .btn-login { width: 100%; background: #F97316; color: #fff; border: none; border-radius: 14px; padding: 15px; font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s, transform 0.1s; margin-top: 8px; }
        .btn-login:hover { background: #EA580C; }
        .btn-login:active { transform: scale(0.98); }
        .error-msg { background: #FEF2F2; border: 1px solid #FECACA; color: #DC2626; padding: 10px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <h1 style="font-size:28px;font-weight:800;color:#1C1917;margin-bottom:6px;">Selamat Datang Kembali</h1>
        <p style="color:#F97316;font-size:14px;font-weight:500;margin-bottom:32px;">Silakan masuk ke akun POS Anda</p>

        @if($errors->any())
        <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <label style="font-size:13px;font-weight:600;color:#1C1917;display:block;margin-bottom:8px;">Nama Pengguna</label>
            <div class="input-wrap">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <input type="text" name="email" placeholder="Username atau email" value="{{ old('email') }}" autocomplete="username">
            </div>

            <label style="font-size:13px;font-weight:600;color:#1C1917;display:block;margin-bottom:8px;">Kata Sandi</label>
            <div class="input-wrap">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input type="password" name="password" id="password" placeholder="Masukkan kata sandi" autocomplete="current-password">
                <button type="button" class="eye-btn" onclick="togglePassword()">
                    <svg id="eye-icon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>

            <div style="text-align:right;margin-bottom:20px;">
                <a href="{{ route('password.request') }}" style="color:#F97316;font-size:13px;font-weight:600;text-decoration:none;">Lupa Kata Sandi?</a>
            </div>

            <button type="submit" class="btn-login">
                Masuk
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </button>
        </form>
    </div>

    {{-- Footer Logo --}}
    <div style="margin-top:36px;display:flex;flex-direction:column;align-items:center;gap:10px;">
        <div style="display:flex;align-items:center;gap:10px;">
            {{-- Logo: pakai gambar jika sudah diupload, fallback ke SVG --}}
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
            <span style="font-size:20px;font-weight:800;color:#1C1917;">Apipi <span style="color:#F97316;">Coffe</span></span>
        </div>
        <p style="font-size:11px;font-weight:600;color:#B0A89E;letter-spacing:0.15em;text-transform:uppercase;">Point of Sale System V2.4</p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>