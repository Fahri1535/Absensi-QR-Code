<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
  <meta name="theme-color" content="#0F172A" id="meta-theme-color">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Presensi QR') — PT. Nugraha Tirta Sejati</title>



  {{-- ② Preconnect ke semua origin external —  kurangi DNS + TCP round-trip --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

  {{-- ③ Google Fonts: load DM Sans + Inter
       display=swap → teks langsung tampil pakai fallback, ganti setelah font ready --}}
  <link rel="preload" as="style"
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=Inter:wght@300;400;500;600&display=swap"
        onload="this.onload=null;this.rel='stylesheet'">
  <noscript>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=Inter:wght@300;400;500;600&display=swap">
  </noscript>

  {{-- ④ Font Awesome: preload + async load --}}
  <link rel="preload" as="style"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        onload="this.onload=null;this.rel='stylesheet'">
  <noscript>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  </noscript>

  {{-- ⑤ App CSS & JS (Cara Lama Aman untuk Railway) --}}
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script src="{{ asset('js/app.js') }}"></script>

  {{-- Anti-flash: terapkan tema SEBELUM render, cegah FOUC --}}
  <script>
    // Jalankan SEBELUM CSS load — cegah layar putih/flash
    (function(){
      var t = localStorage.getItem('theme') || 'dark';
      var sb = localStorage.getItem('sidebarCollapsed') === 'true';
      // Set background langsung di html element (sebelum body ada)
      document.documentElement.style.background = t === 'light' ? '#F8FAFC' : '#0F172A';
      document.documentElement.style.colorScheme = t === 'light' ? 'light' : 'dark';
      if (t === 'light') document.documentElement.classList.add('lm');
      if (sb) document.documentElement.classList.add('sb');
    })();
  </script>
  <style>
    /* Cegah flash — terapkan warna background sebelum CSS load */
    html { background: #0F172A; }
    html.lm { background: #F8FAFC; }
    /* Warna tubuh dokumen segera — kurangi kilat gelap saat navigasi MPA */
    body { margin: 0; background-color: #0F172A; }
    html.lm body { background-color: #F8FAFC; }
    /* Cegah sidebar flicker saat load */
    html.sb .sidebar { transform: translateX(-100%) !important; }
    html.sb .main-content { margin-left: 0 !important; }
    /* Tanpa View Transitions API — di MPA sering bikin kilatan hitam antar halaman */
  </style>

  @stack('styles')
</head>
<body>
<script>
(function () {
  try {
    if (localStorage.getItem('theme') === 'light') document.body.classList.add('light-mode');
  } catch (e) {}
})();
</script>
<div class="wrapper">

  {{-- ── Sidebar ──────────────────────────────────────────────── --}}
  <aside class="sidebar" id="sidebar">

    {{-- Logo --}}
    <div class="sidebar-logo">
      <div class="logo-mark">
        <div class="logo-icon" style="width: 50px; height: auto; background: none; border-radius: 0; box-shadow: none;">
          <!-- NTS SVG Logo -->
          <svg width="50" height="35" viewBox="0 0 200 140" xmlns="http://www.w3.org/2000/svg">
            <!-- Background -->
            <defs>
              <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#2563EB;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#1D4ED8;stop-opacity:1" />
              </linearGradient>
            </defs>
            <rect x="0" y="0" width="200" height="140" rx="15" fill="url(#bgGrad)"/>
            <!-- N -->
            <text x="30" y="95" font-family="Arial, sans-serif" font-size="72" font-weight="800" fill="#FFFFFF">N</text>
            <!-- T -->
            <text x="78" y="95" font-family="Arial, sans-serif" font-size="72" font-weight="800" fill="#FFFFFF">T</text>
            <!-- S -->
            <text x="126" y="95" font-family="Arial, sans-serif" font-size="72" font-weight="800" fill="#FFFFFF">S</text>
          </svg>
        </div>
        <div>
          <div class="logo-text">Presensi<span>QR</span></div>
          <div class="logo-sub">PT. Nugraha Tirta Sejati</div>
        </div>
      </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">

      {{-- ── KARYAWAN MENU ─────────────────────── --}}
      @if(auth()->user()->role === 'karyawan')

      <div class="nav-section" data-section="karyawan-utama">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Utama</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('karyawan.dashboard') }}" class="nav-item {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-house"></i></span> Dashboard
          </a>
          <a href="{{ route('karyawan.presensi') }}" class="nav-item {{ request()->routeIs('karyawan.presensi') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span> Presensi QR
          </a>
        </div>
      </div>

      <div class="nav-section" data-section="karyawan-riwayat">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Riwayat & Izin</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('karyawan.riwayat') }}" class="nav-item {{ request()->routeIs('karyawan.riwayat') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span> Riwayat Presensi
          </a>
          <a href="{{ route('karyawan.izin') }}" class="nav-item {{ request()->routeIs('karyawan.izin*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-file-medical"></i></span> Pengajuan Izin
            {{-- OPTIMASI: Cache count izin selama 2 menit --}}
            @php 
              $pendingIzin = cache()->remember('pending_izin_'.auth()->id(), 120, function() {
                return auth()->user()->karyawan?->izin()->where('status','pending')->count() ?? 0;
              });
            @endphp
            @if($pendingIzin > 0)
              <span class="nav-badge">{{ $pendingIzin }}</span>
            @endif
          </a>
        </div>
      </div>

      {{-- ── OPERATOR MENU ─────────────────────── --}}
      @elseif(auth()->user()->role === 'operator')

      <div class="nav-section" data-section="operator-utama">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Utama</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('operator.dashboard') }}" class="nav-item {{ request()->routeIs('operator.dashboard') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-house"></i></span> Dashboard
          </a>
          <a href="{{ route('operator.presensi') }}" class="nav-item {{ request()->routeIs('operator.presensi*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span> Data Presensi
          </a>
        </div>
      </div>

      <div class="nav-section" data-section="operator-kelola">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Kelola</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('operator.karyawan') }}" class="nav-item {{ request()->routeIs('operator.karyawan*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-users"></i></span> Data Karyawan &amp; HRD
          </a>
          <a href="{{ route('operator.jadwal') }}" class="nav-item {{ request()->routeIs('operator.jadwal*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-clock"></i></span> Jadwal Kerja &amp; Lokasi Kantor
          </a>
          <a href="{{ route('operator.qrcode') }}" class="nav-item {{ request()->routeIs('operator.qrcode*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span> Kelola QR Code
          </a>
        </div>
      </div>

      <div class="nav-section" data-section="operator-laporan">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Laporan</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('operator.laporan') }}" class="nav-item {{ request()->routeIs('operator.laporan*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span> Laporan Presensi
          </a>
          <a href="{{ route('operator.bantuan.index') }}" class="nav-item {{ request()->routeIs('operator.bantuan*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-headset"></i></span> Kelola Bantuan
          </a>
          <a href="{{ route('operator.setup') }}" class="nav-item {{ request()->routeIs('operator.setup') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-database"></i></span> Format Presensi
          </a>
        </div>
      </div>

      {{-- ── HRD MENU ──────────────────────────── --}}
      @elseif(auth()->user()->role === 'hrd')

      <div class="nav-section" data-section="hrd-utama">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Utama</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('hrd.dashboard') }}" class="nav-item {{ request()->routeIs('hrd.dashboard') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-house"></i></span> Dashboard
          </a>
          <a href="{{ route('hrd.presensi') }}" class="nav-item {{ request()->routeIs('hrd.presensi') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span> Presensi QR
          </a>
        </div>
      </div>

      <div class="nav-section" data-section="hrd-pribadi">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Pribadi</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('hrd.riwayat') }}" class="nav-item {{ request()->routeIs('hrd.riwayat') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span> Riwayat Presensi
          </a>
          <a href="{{ route('hrd.izin_pribadi') }}" class="nav-item {{ request()->routeIs('hrd.izin_pribadi') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-file-medical"></i></span> Pengajuan Izin
          </a>
        </div>
      </div>

      <div class="nav-section" data-section="hrd-persetujuan">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Persetujuan</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('hrd.izin') }}" class="nav-item {{ request()->routeIs('hrd.izin*') && !str_contains(request()->url(), 'pribadi') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-file-circle-check"></i></span> Persetujuan Izin
            {{-- OPTIMASI: Cache count approval HRD selama 1 menit --}}
            @php
              $pendingApproval = cache()->remember('pending_approval_hrd', 60, function() {
                try {
                  return \App\Models\Izin::where('status','pending')->count();
                } catch (\Exception $e) {
                  return 0;
                }
              });
            @endphp
            @if($pendingApproval > 0)
              <span class="nav-badge">{{ $pendingApproval }}</span>
            @endif
          </a>
        </div>
      </div>

      <div class="nav-section" data-section="hrd-data">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Data</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('hrd.karyawan') }}" class="nav-item {{ request()->routeIs('hrd.karyawan*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-users"></i></span> Data Karyawan
          </a>
        </div>
      </div>

      <div class="nav-section" data-section="hrd-laporan">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Laporan</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('hrd.laporan') }}" class="nav-item {{ request()->routeIs('hrd.laporan*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span> Laporan Presensi
          </a>
        </div>
      </div>

      @endif

      <div class="nav-section" data-section="akun">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Akun</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route(auth()->user()->role . '.profil') }}" class="nav-item {{ request()->routeIs('*.profil*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-user-gear"></i></span> Profil Saya
          </a>
        </div>
      </div>

      {{-- Notifikasi (semua role) --}}
      <div class="nav-section" data-section="sistem">
        <button class="nav-section-toggle" type="button">
          <span class="nav-section-label">Sistem</span>
          <i class="fa-solid fa-chevron-down nav-section-arrow"></i>
        </button>
        <div class="nav-section-items">
          <a href="{{ route('bantuan') }}" class="nav-item {{ request()->routeIs('bantuan') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-circle-question"></i></span> Bantuan &amp; Kontak
          </a>
          <a href="{{ route('notifikasi') }}" class="nav-item {{ request()->routeIs('notifikasi') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-bell"></i></span> Notifikasi
            {{-- OPTIMASI: Cache count notifikasi selama 1 menit --}}
            @php
              $unreadNavCount = cache()->remember('unread_notif_'.auth()->id(), 60, function() {
                try {
                  return \App\Models\Notifikasi::where('user_id', auth()->user()->getKey())->where('is_read',0)->count();
                } catch (\Exception $e) {
                  return 0;
                }
              });
            @endphp
            @if($unreadNavCount > 0)
              <span class="nav-badge">{{ $unreadNavCount }}</span>
            @endif
          </a>
        </div>
      </div>

    </nav>

    {{-- User Card --}}
    <div class="sidebar-footer">
      <div class="user-card">
        <a href="{{ route(auth()->user()->role . '.profil') }}" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px; flex: 1;">
          <div class="user-avatar" style="overflow:hidden;">
            @if(auth()->user()->karyawan?->foto)
              <img src="{{ asset('storage/'.auth()->user()->karyawan->foto) }}" style="width:100%;height:100%;object-fit:cover;aspect-ratio:1/1;" onerror="this.remove();">
            @endif
              {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
          </div>
          <div class="user-info">
            <div class="user-name">{{ auth()->user()->username }}</div>
            <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
          </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="topbar-btn" title="Logout" style="border:none; cursor:pointer; background:transparent;">
            <i class="fa-solid fa-right-from-bracket"></i>
          </button>
        </form>
      </div>
    </div>

  </aside>

  {{-- ── Main Content ─────────────────────────────────────────── --}}
  <div class="main-content">

    {{-- Top Bar --}}
    <header class="topbar">
      {{-- Hamburger: toggle sidebar di desktop (collapse) & mobile (slide) --}}
      <button class="topbar-btn" id="sidebar-toggle" title="Sembunyikan/Tampilkan Menu">
        <i class="fa-solid fa-bars" id="sidebar-toggle-icon"></i>
      </button>
      <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
      <div class="topbar-right">
        {{-- Theme Toggle: di kiri tanggal --}}
        <button class="topbar-btn theme-toggle-btn" id="theme-toggle" title="Ganti ke Mode Terang">
          <i class="fa-solid fa-sun  icon-sun"  style="display:none;"></i>
          <i class="fa-solid fa-moon icon-moon" style="display:block;"></i>
        </button>
        <div class="topbar-date" id="topbar-clock">—</div>
        <a href="{{ route('notifikasi') }}" class="topbar-btn {{ isset($unreadNavCount) && $unreadNavCount > 0 ? 'notif-badge' : '' }}" title="Notifikasi">
          <i class="fa-solid fa-bell"></i>
        </a>
      </div>
    </header>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div style="padding: 12px 28px 0;">
      <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    </div>
    @endif
    @if(session('error'))
    <div style="padding: 12px 28px 0;">
      <div class="alert alert-danger"><i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}</div>
    </div>
    @endif
    @if($errors->any() && !$errors->has('username'))
    <div style="padding: 12px 28px 0;">
      <div class="alert alert-danger">
        <i class="fa-solid fa-circle-xmark"></i>
        {{ $errors->first() }}
      </div>
    </div>
    @endif

    {{-- Page Content --}}
    <main class="page-content">
      @yield('content')
    </main>

  </div>{{-- /main-content --}}

  {{-- Sidebar Overlay (mobile) --}}
  <div id="sidebar-overlay" style="display:none; position:fixed; top:0; left:0; right:0; background:rgba(0,0,0,.5); z-index:9999998; touch-action: manipulation;"></div>

</div>{{-- /wrapper --}}

@stack('scripts')

{{-- Auto Logout on Idle (10 minutes) --}}
<div id="idle-modal" style="display:none;position:fixed;inset:0;z-index:99999999;background:rgba(0,0,0,.7);backdrop-filter:blur(6px);align-items:center;justify-content:center;">
  <div style="background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:16px;padding:32px;text-align:center;max-width:340px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.5);">
    <div style="font-size:2.5rem;margin-bottom:12px;">⏳</div>
    <h3 style="margin:0 0 8px;font-weight:700;">Sesi Hampir Habis</h3>
    <p style="color:var(--text-secondary,#94a3b8);font-size:.9rem;margin:0 0 6px;">Anda tidak aktif selama <strong id="idle-elapsed">9</strong> menit.</p>
    <p style="color:var(--text-secondary,#94a3b8);font-size:.8rem;margin:0 0 20px;">Sesi akan berakhir dalam <strong id="idle-countdown" style="color:var(--amber,#f59e0b);">60</strong> detik.</p>
    <button onclick="resetIdleTimer()" style="width:100%;padding:12px;border:none;border-radius:10px;background:var(--teal,#00c9a7);color:#0f172a;font-weight:700;font-size:.95rem;cursor:pointer;">
      Tetap Masuk
    </button>
  </div>
</div>

<script>
(function() {
  const IDLE_LIMIT  = 10 * 60 * 1000; // 10 minutes in ms
  const WARN_BEFORE = 60 * 1000;      // warn 1 minute before
  const LOGIN_URL   = '{{ route("login") }}';
  const LOGOUT_URL  = '{{ route("logout") }}';

  let idleTimer  = null;
  let warnTimer  = null;
  let countdown  = null;
  let lastActive = Date.now();

  const modal      = document.getElementById('idle-modal');
  const countdownEl = document.getElementById('idle-countdown');
  const elapsedEl   = document.getElementById('idle-elapsed');

  function resetIdleTimer() {
    lastActive = Date.now();
    modal.style.display = 'none';
    if (idleTimer) clearTimeout(idleTimer);
    if (warnTimer) clearTimeout(warnTimer);
    if (countdown) clearInterval(countdown);
    startIdleWatch();
  }
  window.resetIdleTimer = resetIdleTimer;

  function onActivity() {
    lastActive = Date.now();
    if (modal.style.display === 'flex') {
      resetIdleTimer();
    }
  }

  function showWarning() {
    const elapsedMin = Math.round((Date.now() - lastActive + IDLE_LIMIT - WARN_BEFORE) / 60000);
    elapsedEl.textContent = elapsedMin;
    modal.style.display = 'flex';
    let remaining = Math.ceil(WARN_BEFORE / 1000);
    countdownEl.textContent = remaining;
    countdown = setInterval(() => {
      remaining--;
      countdownEl.textContent = remaining;
      if (remaining <= 0) {
        clearInterval(countdown);
        doLogout();
      }
    }, 1000);
  }

  function doLogout() {
    // POST to logout route to destroy session, then redirect
    fetch(LOGOUT_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    }).finally(() => {
      window.location.href = LOGIN_URL;
    });
  }

  function startIdleWatch() {
    // Set main idle timer
    idleTimer = setTimeout(() => {
      showWarning();
    }, IDLE_LIMIT - WARN_BEFORE);

    // Also set a hard limit — force logout even if warning is dismissed
    warnTimer = setTimeout(() => {
      doLogout();
    }, IDLE_LIMIT);
  }

  // Track user activity
  ['mousemove','mousedown','keydown','touchstart','scroll','click'].forEach(evt => {
    document.addEventListener(evt, onActivity, { passive: true });
  });

  // Start watching
  startIdleWatch();

  // Reset on page visibility change (user comes back to tab)
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
      // Check if session already expired
      const elapsed = Date.now() - lastActive;
      if (elapsed >= IDLE_LIMIT) {
        doLogout();
      } else {
        onActivity();
      }
    }
  });
})();
</script>
</body>
</html>
