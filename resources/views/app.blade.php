<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
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

  {{-- App CSS --}}
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

  {{-- Theme CSS --}}
  <style>
    /* ========== THEME VARIABLES (BIRU + DARK/LIGHT MODE) ========== */
    :root {
      --bg-primary: #0F172A;
      --bg-secondary: #1E293B;
      --sidebar-bg: rgba(15, 23, 42, 0.98);
      --card-bg: rgba(30, 41, 59, 0.95);
      --text-primary: #F1F5F9;
      --text-secondary: #94A3B8;
      --border-color: rgba(59, 130, 246, 0.2);
      --blue-primary: #2563EB;
      --blue-secondary: #1D4ED8;
      --blue-light: #3B82F6;
      --blue-glow: rgba(37, 99, 235, 0.15);
      --green: #10B981;
      --amber: #F59E0B;
      --red: #EF4444;
      --transition: all 0.3s ease;
    }

    /* Light Mode */
    body.light-mode {
      --bg-primary: #F8FAFC;
      --bg-secondary: #E2E8F0;
      --sidebar-bg: #FFFFFF;
      --card-bg: #FFFFFF;
      --text-primary: #1E293B;
      --text-secondary: #64748B;
      --border-color: #E2E8F0;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
      background: var(--bg-primary);
      color: var(--text-primary);
      transition: var(--transition);
    }

    /* Wrapper Layout */
    .wrapper {
      display: flex;
      min-height: 100vh;
    }

    /* ========== SIDEBAR ========== */
    .sidebar {
      width: 280px;
      background: var(--sidebar-bg);
      backdrop-filter: blur(10px);
      border-right: 1px solid var(--border-color);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      z-index: 100;
      transition: var(--transition);
      overflow-y: auto;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.open {
        transform: translateX(0);
      }
    }

    .sidebar-logo {
      padding: 24px 20px;
      border-bottom: 1px solid var(--border-color);
    }

    .logo-mark {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .logo-icon {
      width: 42px;
      height: 42px;
      background: linear-gradient(135deg, var(--blue-primary), var(--blue-secondary));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .logo-text {
      font-family: 'DM Sans', sans-serif;
      font-weight: 800;
      font-size: 1.2rem;
    }
    .logo-text span { color: var(--blue-light); }
    .logo-sub { font-size: 0.65rem; color: var(--text-secondary); margin-top: 2px; }

    /* Navigation */
    .sidebar-nav {
      flex: 1;
      padding: 20px 16px;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .nav-section-label {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--text-secondary);
      margin-bottom: 8px;
      padding-left: 12px;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 12px;
      border-radius: 12px;
      color: var(--text-secondary);
      text-decoration: none;
      transition: var(--transition);
      margin-bottom: 4px;
      position: relative;
    }

    .nav-item:hover {
      background: var(--blue-glow);
      color: var(--blue-light);
    }

    .nav-item.active {
      background: linear-gradient(135deg, var(--blue-primary), var(--blue-secondary));
      color: white;
    }

    .nav-icon {
      width: 24px;
      text-align: center;
      font-size: 1rem;
    }

    .nav-badge {
      background: var(--red);
      color: white;
      font-size: 0.65rem;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 50px;
      margin-left: auto;
    }

    /* Sidebar Footer / User Card */
    .sidebar-footer {
      padding: 20px;
      border-top: 1px solid var(--border-color);
    }

    .user-card {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, var(--blue-primary), var(--blue-secondary));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 1rem;
    }

    .user-info {
      flex: 1;
    }
    .user-name { font-weight: 600; font-size: 0.9rem; }
    .user-role { font-size: 0.7rem; color: var(--text-secondary); }

    /* ========== MAIN CONTENT ========== */
    .main-content {
      flex: 1;
      margin-left: 280px;
      min-height: 100vh;
      transition: var(--transition);
    }

    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
      }
    }

    /* Topbar */
    .topbar {
      background: var(--sidebar-bg);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--border-color);
      padding: 14px 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .topbar-btn {
      background: transparent;
      border: none;
      color: var(--text-secondary);
      font-size: 1.2rem;
      cursor: pointer;
      padding: 8px;
      border-radius: 10px;
      transition: var(--transition);
    }

    .topbar-btn:hover {
      background: var(--blue-glow);
      color: var(--blue-light);
    }

    .topbar-title {
      font-family: 'DM Sans', sans-serif;
      font-weight: 700;
      font-size: 1.1rem;
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .topbar-date {
      font-size: 0.8rem;
      color: var(--text-secondary);
    }

    /* Theme Toggle khusus di topbar */
    .theme-toggle-top {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 50px;
      padding: 6px 14px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: var(--transition);
      font-size: 0.75rem;
      color: var(--text-primary);
    }

    .theme-toggle-top:hover {
      border-color: var(--blue-light);
      transform: scale(1.02);
    }

    /* Page Content */
    .page-content {
      padding: 28px;
    }

    /* Alert */
    .alert {
      padding: 14px 18px;
      border-radius: 12px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.85rem;
    }
    .alert-success {
      background: rgba(16, 185, 129, 0.1);
      border: 1px solid rgba(16, 185, 129, 0.3);
      color: var(--green);
    }
    .alert-danger {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: var(--red);
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: var(--bg-secondary); }
    ::-webkit-scrollbar-thumb { background: var(--blue-light); border-radius: 4px; }
  </style>

      /* ========== OVERRIDE WARNA HIJAU (PASTIKAN BIRU SEMUA) ========== */
    .btn-primary,
    .btn-primary:hover,
    .btn-primary:focus,
    .btn-primary:active {
      background: linear-gradient(135deg, var(--blue-primary), var(--blue-secondary)) !important;
      color: white !important;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #3B82F6, #2563EB) !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4) !important;
    }

    .text-teal,
    .badge-teal,
    [class*="teal"]:not(.blue):not(.text-primary) {
      color: var(--blue-light) !important;
    }

    .bg-teal,
    [class*="bg-teal"] {
      background: var(--blue-primary) !important;
    }

    .nav-item:hover,
    .topbar-btn:hover,
    .theme-toggle-top:hover {
      color: var(--blue-light) !important;
    }

    .btn-outline {
      border-color: var(--blue-primary) !important;
      color: var(--blue-primary) !important;
    }

    .btn-outline:hover {
      background: var(--blue-glow) !important;
      color: var(--blue-primary) !important;
    }

    /* Matikan efek hijau dari app.css */
    a:not(.btn):hover {
      color: var(--blue-light) !important;
    }

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
          @php $pendingIzin = auth()->user()->karyawan?->pengajuanIzin()->where('status','pending')->count() ?? 0; @endphp
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
        <a href="{{ route('hrd.presensi') }}" class="nav-item {{ request()->routeIs('hrd.presensi*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span> Monitoring Presensi
        </a>
      </div>

      <div class="nav-section">
        <div class="nav-section-label">Persetujuan</div>
        <a href="{{ route('hrd.izin') }}" class="nav-item {{ request()->routeIs('hrd.izin*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-file-circle-check"></i></span> Persetujuan Izin
          @php $pendingApproval = \App\Models\PengajuanIzin::where('status','pending')->count(); @endphp
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
        <a href="{{ route('hrd.karyawan') }}" class="nav-item {{ request()->routeIs('hrd.karyawan*') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-users"></i></span> Data Karyawan
        </a>
      </div>

      @endif

      {{-- Shared bottom --}}
      <div class="nav-section" style="margin-top: auto;">
        <div class="nav-section-label">Sistem</div>
        <a href="{{ route('bantuan') }}" class="nav-item {{ request()->routeIs('bantuan') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-circle-question"></i></span> Bantuan &amp; Kontak
        </a>
        <a href="{{ route('notifikasi') }}" class="nav-item {{ request()->routeIs('notifikasi') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-bell"></i></span> Notifikasi
          @php $unread = auth()->user()->notifikasi()->where('is_read',0)->count(); @endphp
          @if($unread > 0)
            <span class="nav-badge">{{ $unread }}</span>
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
          <button type="submit" class="topbar-btn" title="Logout" style="border:none; cursor:pointer;">
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
      <button class="topbar-btn" id="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
      <div class="topbar-right">
        <div class="topbar-date" id="topbar-clock">—</div>
        
        {{-- THEME TOGGLE BUTTON --}}
        <button class="theme-toggle-top" onclick="toggleTheme()" id="themeToggleBtn">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
          <span id="themeText">Dark Mode</span>
        </button>

        <a href="{{ route('notifikasi') }}" class="topbar-btn {{ $unread ?? 0 > 0 ? 'notif-badge' : '' }}" title="Notifikasi">
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

    {{-- Page Content --}}
    <main class="page-content">
      @yield('content')
    </main>

  </div>{{-- /main-content --}}

</div>{{-- /wrapper --}}

{{-- Sidebar Overlay (mobile) --}}
<div id="sidebar-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:99; backdrop-filter:blur(2px);"
     onclick="document.getElementById('sidebar').classList.remove('open'); this.style.display='none';"></div>

<script>
// Live clock
function updateClock() {
  const now = new Date();
  const opts = { weekday:'short', day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' };
  document.getElementById('topbar-clock').textContent = now.toLocaleDateString('id-ID', opts);
}
updateClock(); setInterval(updateClock, 30000);

// Sidebar mobile
document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
  const overlay = document.getElementById('sidebar-overlay');
  const sidebar = document.getElementById('sidebar');
  overlay.style.display = sidebar.classList.contains('open') ? 'none' : 'block';
});

// ========== DARK/LIGHT MODE TOGGLE ==========
function toggleTheme() {
  const body = document.body;
  const themeIcon = document.getElementById('themeIcon');
  const themeText = document.getElementById('themeText');
  
  body.classList.toggle('light-mode');
  
  if (body.classList.contains('light-mode')) {
    themeIcon.className = 'fa-solid fa-sun';
    themeText.textContent = 'Light Mode';
    localStorage.setItem('theme', 'light');
  } else {
    themeIcon.className = 'fa-solid fa-moon';
    themeText.textContent = 'Dark Mode';
    localStorage.setItem('theme', 'dark');
  }
}

// Load saved theme dari localStorage
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'light') {
  document.body.classList.add('light-mode');
  const themeIcon = document.getElementById('themeIcon');
  const themeText = document.getElementById('themeText');
  if (themeIcon) themeIcon.className = 'fa-solid fa-sun';
  if (themeText) themeText.textContent = 'Light Mode';
}
</script>

@stack('scripts')
</body>
</html>
