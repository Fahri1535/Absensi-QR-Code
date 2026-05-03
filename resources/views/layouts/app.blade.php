<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#0F172A" id="meta-theme-color">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Presensi QR') — PT. Nugraha Tirta Sejati</title>

  {{-- ① Preload CSS utama — browser fetch lebih awal, tidak blocking --}}
  <link rel="preload" href="{{ asset('css/app.css') }}" as="style">

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

  {{-- ⑤ App CSS — load setelah preload hint dikirim --}}
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

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
        <div class="logo-icon">📋</div>
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

      <div class="nav-section">
        <div class="nav-section-label">Utama</div>
        <a href="{{ route('karyawan.dashboard') }}" class="nav-item {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-house"></i></span> Dashboard
        </a>
        <a href="{{ route('karyawan.presensi') }}" class="nav-item {{ request()->routeIs('karyawan.presensi') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span> Presensi QR
        </a>
      </div>

      <div class="nav-section">
        <div class="nav-section-label">Riwayat</div>
        <a href="{{ route('karyawan.riwayat') }}" class="nav-item {{ request()->routeIs('karyawan.riwayat') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span> Riwayat Presensi
        </a>
        <a href="{{ route('karyawan.laporan') }}" class="nav-item {{ request()->routeIs('karyawan.laporan*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span> Laporan Presensi
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

      <div class="nav-section">
        <div class="nav-section-label">Akun</div>
        <a href="{{ route('karyawan.profil') }}" class="nav-item {{ request()->routeIs('karyawan.profil') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-user"></i></span> Profil Saya
        </a>
      </div>

      {{-- ── OPERATOR MENU ─────────────────────── --}}
      @elseif(auth()->user()->role === 'operator')

      <div class="nav-section">
        <div class="nav-section-label">Utama</div>
        <a href="{{ route('operator.dashboard') }}" class="nav-item {{ request()->routeIs('operator.dashboard') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-house"></i></span> Dashboard
        </a>
        <a href="{{ route('operator.presensi') }}" class="nav-item {{ request()->routeIs('operator.presensi*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span> Data Presensi
        </a>
      </div>

      <div class="nav-section">
        <div class="nav-section-label">Kelola</div>
        <a href="{{ route('operator.karyawan') }}" class="nav-item {{ request()->routeIs('operator.karyawan*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-users"></i></span> Data Karyawan
        </a>
        <a href="{{ route('operator.jadwal') }}" class="nav-item {{ request()->routeIs('operator.jadwal*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-clock"></i></span> Jadwal Kerja &amp; Lokasi Kantor
        </a>
        <a href="{{ route('operator.qrcode') }}" class="nav-item {{ request()->routeIs('operator.qrcode*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span> Kelola QR Code
        </a>
      </div>

      <div class="nav-section">
        <div class="nav-section-label">Laporan</div>
        <a href="{{ route('operator.laporan') }}" class="nav-item {{ request()->routeIs('operator.laporan*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span> Laporan Presensi
        </a>
        <a href="{{ route('operator.bantuan.index') }}" class="nav-item {{ request()->routeIs('operator.bantuan*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-headset"></i></span> Kelola Bantuan
        </a>
      </div>

      {{-- ── HRD MENU ──────────────────────────── --}}
      @elseif(auth()->user()->role === 'hrd')

      <div class="nav-section">
        <div class="nav-section-label">Utama</div>
        <a href="{{ route('hrd.dashboard') }}" class="nav-item {{ request()->routeIs('hrd.dashboard') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-house"></i></span> Dashboard
        </a>
      </div>

      <div class="nav-section">
        <div class="nav-section-label">Persetujuan</div>
        <a href="{{ route('hrd.izin') }}" class="nav-item {{ request()->routeIs('hrd.izin*') ? 'active' : '' }}">
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

      <div class="nav-section">
        <div class="nav-section-label">Data</div>
        <a href="{{ route('hrd.karyawan') }}" class="nav-item {{ request()->routeIs('hrd.karyawan*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-users"></i></span> Data Karyawan
        </a>
      </div>

      <div class="nav-section">
        <div class="nav-section-label">Laporan</div>
        <a href="{{ route('hrd.laporan') }}" class="nav-item {{ request()->routeIs('hrd.laporan*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span> Laporan Presensi
        </a>
      </div>

      @endif

      {{-- Notifikasi (semua role) --}}
      <div class="nav-section">
        <div class="nav-section-label">Sistem</div>
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

    </nav>

    {{-- User Card --}}
    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar">
          {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
        </div>
        <div class="user-info">
          <div class="user-name">{{ auth()->user()->username }}</div>
          <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
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

</div>{{-- /wrapper --}}

{{-- Sidebar Overlay (mobile) --}}
<div id="sidebar-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:99; backdrop-filter:blur(2px);"
     onclick="document.getElementById('sidebar').classList.remove('open'); this.style.display='none';"></div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
