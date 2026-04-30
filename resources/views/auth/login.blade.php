<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — Presensi QR PT. Nugraha Tirta Sejati</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  {{-- Anti-flash: terapkan tema SEBELUM render --}}
  <script>
    (function(){
      if(localStorage.getItem('theme') === 'light'){
        document.documentElement.style.background = '#F8FAFC';
      }
    })();
  </script>

  <style>
    /* ========== VARIABLES (Default DARK MODE) ========== */
    :root {
      --bg-primary: #0F172A;
      --bg-secondary: rgba(30, 41, 59, 0.8);
      --text-primary: #F1F5F9;
      --text-secondary: #94A3B8;
      --border-color: rgba(59, 130, 246, 0.2);
      --blue-primary: #2563EB;
      --blue-secondary: #1D4ED8;
      --blue-light: #3B82F6;
      --blue-glow: rgba(37, 99, 235, 0.1);
      --card-bg: rgba(30, 41, 59, 0.8);
      --input-bg: rgba(15, 23, 42, 0.6);
      --transition: all 0.3s ease;
    }

    /* ========== LIGHT MODE ========== */
    body.light-mode {
      --bg-primary: #F8FAFC;
      --bg-secondary: rgba(255, 255, 255, 0.95);
      --text-primary: #1E293B;
      --text-secondary: #64748B;
      --border-color: rgba(37, 99, 235, 0.2);
      --card-bg: rgba(255, 255, 255, 0.95);
      --input-bg: rgba(255, 255, 255, 0.95);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg-primary);
      color: var(--text-primary);
      transition: var(--transition);
    }

    /* Theme Toggle Button */
    .theme-toggle {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 100;
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 50px;
      padding: 10px 18px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      backdrop-filter: blur(10px);
      transition: var(--transition);
      font-size: 0.85rem;
      color: var(--text-primary);
    }

    .theme-toggle:hover {
      border-color: var(--blue-light);
      transform: scale(1.05);
    }

    /* Form elements */
    .form-group {
      margin-bottom: 24px;
    }

    .form-label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.85rem;
      font-weight: 500;
      color: var(--text-primary);
    }

    .form-control {
      width: 100%;
      padding: 12px 16px;
      background: var(--input-bg);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      color: var(--text-primary);
      font-size: 0.9rem;
      transition: var(--transition);
    }

    .form-control:focus {
      outline: none;
      border-color: var(--blue-primary);
      box-shadow: 0 0 0 3px var(--blue-glow);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 24px;
      border-radius: 12px;
      font-weight: 600;
      transition: var(--transition);
      cursor: pointer;
      border: none;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--blue-primary), var(--blue-secondary));
      color: white;
    }

    .btn-primary:hover,
    .btn-primary:focus,
    .btn-primary:active {
      background: linear-gradient(135deg, #3B82F6, #2563EB) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4) !important;
      color: white !important;
    }

    .btn-full {
      width: 100%;
    }

    .btn-lg {
      padding: 14px 28px;
      font-size: 1rem;
    }

    .alert {
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .alert-danger {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #F87171;
    }

    .divider {
      margin: 20px 0;
      border: none;
      height: 1px;
      background: var(--border-color);
    }

    /* Animations */
    @keyframes slideUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .animate-slideup {
      animation: slideUp 0.5s ease-out both;
    }

    /* Background orbs */
    .login-bg-orbs {
      position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none;
    }
    .orb {
      position: absolute; border-radius: 50%;
      filter: blur(80px); opacity: .35;
    }
    .orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, #2563EB, transparent 70%); top: -200px; left: -100px; animation: orbFloat 12s ease-in-out infinite alternate; }
    .orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, #1E3A5F, transparent 70%); bottom: -100px; right: -80px; animation: orbFloat 10s ease-in-out infinite alternate-reverse; }
    .orb-3 { width: 250px; height: 250px; background: radial-gradient(circle, #3B82F6, transparent 70%); bottom: 30%; left: 20%; opacity: .15; animation: orbFloat 14s ease-in-out infinite alternate; }
    
    @keyframes orbFloat { 
      from { transform: translate(0,0) scale(1); } 
      to { transform: translate(30px,20px) scale(1.08); } 
    }

    /* ========== LAYOUT VERTICAL ========== */
    .login-container {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      z-index: 1;
      padding: 40px 20px;
    }

    .login-card {
      max-width: 500px;
      width: 100%;
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--border-color);
      border-radius: 32px;
      padding: 48px 40px;
      position: relative;
      z-index: 1;
      animation: slideUp 0.5s ease-out both;
    }

    /* Info section */
    .info-section {
      text-align: center;
      margin-bottom: 40px;
    }

        /* ========== EFEK HOVER UNTUK BADGE SISTEM AKTIF ========== */
    .info-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--blue-glow);
      border: 1px solid rgba(37, 99, 235, .25);
      border-radius: 50px;
      padding: 6px 16px;
      font-size: .75rem;
      color: var(--blue-light);
      font-weight: 600;
      margin-bottom: 24px;
      transition: all 0.25s ease-in-out;
      cursor: pointer;
    }
    
    .info-badge:hover {
      background: rgba(37, 99, 235, 0.25);
      border-color: var(--blue-primary);
      color: var(--blue-primary);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px var(--blue-glow);
    }
    
    .info-badge .dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--blue-light);
      animation: pulse 2s infinite;
      transition: all 0.25s ease;
    }
    
    .info-badge:hover .dot {
      background: var(--blue-primary);
      transform: scale(1.2);
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .info-title {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: clamp(1.8rem, 5vw, 2.5rem);
      line-height: 1.2;
      margin-bottom: 16px;
    }
    .info-title span { color: var(--blue-light); }

    .info-desc {
      color: var(--text-secondary);
      font-size: .9rem;
      line-height: 1.6;
      max-width: 380px;
      margin: 0 auto 24px;
    }

        .feature-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      margin-bottom: 32px;
      padding-bottom: 32px;
      border-bottom: 1px solid var(--border-color);
    }
    
    .feature-pill {
      display: flex;
      align-items: center;
      gap: 8px;
      background: var(--input-bg);
      border: 1px solid var(--border-color);
      border-radius: 50px;
      padding: 6px 14px;
      font-size: .75rem;
      color: var(--text-secondary);
      transition: all 0.25s ease-in-out;
      cursor: pointer;
    }
    
    .feature-pill i { 
      color: var(--blue-light); 
      transition: all 0.2s ease;
    }
    
    /* EFEK HOVER */
    .feature-pill:hover {
      background: rgba(37, 99, 235, 0.15);
      border-color: var(--blue-primary);
      color: var(--blue-primary);
      transform: translateY(-2px);
    }
    
    .feature-pill:hover i {
      color: var(--blue-primary);
      transform: scale(1.05);
    }

    .feature-pill i { color: var(--blue-light); }

    .input-group { position: relative; }
    .input-icon {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      color: var(--text-secondary); font-size: .9rem; pointer-events: none;
    }
    .input-group .form-control { padding-left: 42px; }
    .input-toggle {
      position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
      color: var(--text-secondary); cursor: pointer; font-size: .9rem; transition: var(--transition);
    }
    .input-toggle:hover { color: var(--blue-light); }

    .login-footer {
      margin-top: 24px;
      text-align: center;
      font-size: .78rem;
      color: var(--text-secondary);
    }

    @media (max-width: 560px) {
      .login-card { padding: 32px 24px; margin: 0 16px; }
      .feature-grid { gap: 8px; }
      .feature-pill { font-size: .7rem; padding: 4px 12px; }
    }

    /* ========== FIX FONT TIDAK GEPENG - PAKAI SYSTEM FONT ========== */
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
      background: var(--bg-primary);
      color: var(--text-primary);
      transition: var(--transition);
      letter-spacing: normal !important;
      line-height: 1.55 !important;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* Heading / Judul tetap pakai Syne biar keren */
    .info-title, .login-form-title, h1, h2, h3, h4 {
      font-family: 'Syne', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
      letter-spacing: normal !important;
      line-height: 1.2 !important;
      font-weight: 700 !important;
    }

    /* Paragraf dan teks biasa */
    .info-desc, .login-form-sub, .text-muted, p, .login-footer {
      letter-spacing: normal !important;
      line-height: 1.6 !important;
    }

    /* Label form */
    .form-label {
      letter-spacing: normal !important;
      font-weight: 600 !important;
    }

    /* Feature pills */
    .feature-pill {
      letter-spacing: normal !important;
      font-weight: 500 !important;
    }

    /* Button */
    .btn, .btn-primary, button, .theme-toggle {
      letter-spacing: normal !important;
      font-weight: 600 !important;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }

    /* Input */
    .form-control {
      letter-spacing: normal !important;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }
    
    /* Feature pills */
    .feature-pill {
      letter-spacing: 0px !important;
    }

  </style>
</head>
<body>
<div class="login-bg-orbs">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
</div>

<div class="theme-toggle" onclick="toggleTheme()">
  <i class="fa-solid fa-moon" id="themeIcon"></i>
  <span id="themeText">Dark Mode</span>
</div>

<div class="login-container">
  <div class="login-card">

    <div class="info-section">
      <div class="info-badge">
        <div class="dot"></div>
        Sistem Aktif
      </div>
      <div class="info-title">
        Presensi<br><span>Lebih Cepat</span>,<br>Lebih Akurat.
      </div>
      <p class="info-desc">
        Sistem informasi presensi karyawan berbasis QR Code untuk PT. Nugraha Tirta Sejati.
        Mudah, cepat, dan anti-manipulasi.
      </p>
      <div class="feature-grid">
        <div class="feature-pill"><i class="fa-solid fa-qrcode"></i> QR Code</div>
        <div class="feature-pill"><i class="fa-solid fa-clock"></i> Real-time</div>
        <div class="feature-pill"><i class="fa-solid fa-file-excel"></i> Laporan</div>
        <div class="feature-pill"><i class="fa-solid fa-shield-halved"></i> Anti Titip</div>
        <div class="feature-pill"><i class="fa-solid fa-bell"></i> Notifikasi</div>
        <div class="feature-pill"><i class="fa-solid fa-file-circle-check"></i> Izin Online</div>
      </div>
    </div>

    <div style="text-align:center; margin-bottom:32px;">
      <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#2563EB,#1D4ED8);display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 14px;box-shadow:0 8px 24px rgba(37,99,235,.3);">
        📋
      </div>
      <div class="login-form-title" style="font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;margin-bottom:6px;">Selamat Datang</div>
      <div class="login-form-sub" style="color:var(--text-secondary);font-size:.85rem;margin-bottom:0;">Masuk ke akun Anda untuk melanjutkan</div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:20px;">
      <i class="fa-solid fa-circle-xmark"></i>
      {{ $errors->first() }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:20px;">
      <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
      @csrf

      <div class="form-group">
        <label class="form-label">Username</label>
        <div class="input-group">
          <span class="input-icon"><i class="fa-solid fa-user"></i></span>
          <input type="text" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" autocomplete="username" required autofocus>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
          <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Masukkan password" autocomplete="current-password" required>
          <span class="input-toggle" onclick="togglePassword()">
            <i class="fa-solid fa-eye" id="eyeIcon"></i>
          </span>
        </div>
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <label style="display:flex;align-items:center;gap:8px;font-size:.85rem;color:var(--text-secondary);cursor:pointer;">
          <input type="checkbox" name="remember" style="accent-color:var(--blue-primary);"> Ingat saya
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" id="loginBtn">
        <i class="fa-solid fa-right-to-bracket"></i> Masuk
      </button>
    </form>

    <div class="login-footer">
      <hr class="divider">
      Butuh bantuan? Hubungi <span style="color:var(--blue-light);">Admin</span>
    </div>

  </div>
</div>

<script>
/* ── Password toggle ─────────────────────────────────── */
function togglePassword() {
  const inp = document.getElementById('passwordInput');
  const ico = document.getElementById('eyeIcon');
  inp.type  = inp.type === 'password' ? 'text' : 'password';
  ico.className = inp.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}

document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('loginBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
});

/* ── Theme — key 'theme' sama dengan app.blade.php ───── */
function applyLoginTheme(theme) {
  const icon = document.getElementById('themeIcon');
  const text = document.getElementById('themeText');
  if (theme === 'light') {
    document.body.classList.add('light-mode');
    document.documentElement.style.background = '';
    if (icon) icon.className = 'fa-solid fa-sun';
    if (text) text.textContent = 'Light Mode';
  } else {
    document.body.classList.remove('light-mode');
    document.documentElement.style.background = '';
    if (icon) icon.className = 'fa-solid fa-moon';
    if (text) text.textContent = 'Dark Mode';
  }
}

function toggleTheme() {
  const next = (localStorage.getItem('theme') || 'dark') === 'dark' ? 'light' : 'dark';
  localStorage.setItem('theme', next);
  applyLoginTheme(next);
}

// Terapkan saat load
applyLoginTheme(localStorage.getItem('theme') || 'dark');
</script>
</body>
</html>