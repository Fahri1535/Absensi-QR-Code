<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Presensi QR') — PT. Nugraha Tirta Sejati</title>

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

  {{-- Icons --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  {{-- App CSS (FIXED: hanya sekali, tidak duplikat) --}}
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  {{-- Anti-flash: terapkan tema SEBELUM render untuk cegah kedip --}}
  <script>
    (function(){
      var t = localStorage.getItem('theme');
      if(t === 'light') document.documentElement.classList.add('light-mode-early');
    })();
  </script>
  <style>
    html.light-mode-early body { background: #F8FAFC !important; }
  </style>

  @stack('styles')
</head>
<body>
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
        <a href="{{ route('karyawan.izin') }}" class="nav-item {{ request()->routeIs('karyawan.izin*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-file-medical"></i></span> Pengajuan Izin
          {{-- FIXED: gunakan null-safe query, jangan crash jika karyawan null --}}
          @php $pendingIzin = auth()->user()->karyawan?->izin()->where('status','pending')->count() ?? 0; @endphp
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
          <span class="nav-icon"><i class="fa-solid fa-clock"></i></span> Jadwal Kerja
        </a>
        <a href="{{ route('operator.qrcode') }}" class="nav-item {{ request()->routeIs('operator.qrcode*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span> Kelola QR Code
        </a>
      </div>

      <div class="nav-section">
        <div class="nav-section-label">Laporan</div>
        <a href="{{ route('operator.laporan') }}" class="nav-item {{ request()->routeIs('operator.laporan*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-file-chart-column"></i></span> Laporan Presensi
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
          {{-- FIXED: gunakan try-catch model bukan class yang tidak ada --}}
          @php
            try {
              $pendingApproval = \App\Models\Izin::where('status','pending')->count();
            } catch (\Exception $e) {
              $pendingApproval = 0;
            }
          @endphp
          @if($pendingApproval > 0)
            <span class="nav-badge">{{ $pendingApproval }}</span>
          @endif
        </a>
      </div>

      <div class="nav-section">
        <div class="nav-section-label">Laporan</div>
        <a href="{{ route('hrd.laporan') }}" class="nav-item {{ request()->routeIs('hrd.laporan*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-file-chart-column"></i></span> Laporan Presensi
        </a>
      </div>

      @endif

      {{-- Notifikasi (semua role) --}}
      <div class="nav-section">
        <div class="nav-section-label">Sistem</div>
        <a href="{{ route('notifikasi') }}" class="nav-item {{ request()->routeIs('notifikasi') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-bell"></i></span> Notifikasi
          {{-- FIXED: pakai $unreadNavCount agar tidak crash, hitung di sini --}}
          @php
            try {
              $unreadNavCount = \App\Models\Notifikasi::where('user_id', auth()->id())->where('is_read',0)->count();
            } catch (\Exception $e) {
              $unreadNavCount = 0;
            }
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
<script>
/* ── Live Clock ───────────────────────────────────────────── */
function updateClock() {
  const now  = new Date();
  const opts = { weekday:'short', day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' };
  const el   = document.getElementById('topbar-clock');
  if (el) el.textContent = now.toLocaleDateString('id-ID', opts);
}
updateClock();
setInterval(updateClock, 30000);

/* ── Sidebar Toggle (Desktop collapse + Mobile slide) ────── */
const sidebar      = document.getElementById('sidebar');
const overlay      = document.getElementById('sidebar-overlay');
const toggleBtn    = document.getElementById('sidebar-toggle');
const toggleIcon   = document.getElementById('sidebar-toggle-icon');
const isMobile     = () => window.innerWidth <= 768;

// Simpan state di localStorage supaya ingat pilihan user
let sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

function applySidebarState() {
  if (isMobile()) {
    // Mobile: pakai class .open di sidebar, bukan body.collapsed
    document.body.classList.remove('sidebar-collapsed');
    return;
  }
  // Desktop: toggle body.sidebar-collapsed
  if (sidebarCollapsed) {
    document.body.classList.add('sidebar-collapsed');
    toggleBtn.classList.add('sidebar-toggle-active');
  } else {
    document.body.classList.remove('sidebar-collapsed');
    toggleBtn.classList.remove('sidebar-toggle-active');
  }
}

toggleBtn?.addEventListener('click', () => {
  if (isMobile()) {
    // Mobile: slide in/out
    const isOpen = sidebar.classList.toggle('open');
    overlay.style.display = isOpen ? 'block' : 'none';
  } else {
    // Desktop: collapse/expand
    sidebarCollapsed = !sidebarCollapsed;
    localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
    applySidebarState();
  }
});

// Tutup sidebar mobile kalau resize ke desktop
window.addEventListener('resize', () => {
  if (!isMobile()) {
    sidebar.classList.remove('open');
    overlay.style.display = 'none';
    applySidebarState();
  }
});

// Terapkan state awal saat halaman load
applySidebarState();

/* ── Theme Toggle (Dark / Light) ─────────────────────────── */
const themeBtn  = document.getElementById('theme-toggle');
const iconSun   = themeBtn?.querySelector('.icon-sun');
const iconMoon  = themeBtn?.querySelector('.icon-moon');

// Ambil preferensi tersimpan, default = dark
let currentTheme = localStorage.getItem('theme') || 'dark';

function applyTheme(theme) {
  if (theme === 'light') {
    document.body.classList.add('light-mode');
    if (iconSun)  iconSun.style.display  = 'block';
    if (iconMoon) iconMoon.style.display = 'none';
    themeBtn?.setAttribute('title', 'Ganti ke Mode Gelap');
  } else {
    document.body.classList.remove('light-mode');
    if (iconSun)  iconSun.style.display  = 'none';
    if (iconMoon) iconMoon.style.display = 'block';
    themeBtn?.setAttribute('title', 'Ganti ke Mode Terang');
  }
  // Bersihkan class early setelah body sudah siap
  document.documentElement.classList.remove('light-mode-early');
}

themeBtn?.addEventListener('click', () => {
  currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
  localStorage.setItem('theme', currentTheme);
  applyTheme(currentTheme);
});

// Terapkan tema saat halaman load
applyTheme(currentTheme);
</script>

@stack('scripts')
</body>
</html>