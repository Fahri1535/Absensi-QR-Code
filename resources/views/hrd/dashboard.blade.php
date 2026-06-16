@extends('layouts.app')

@section('title', 'Dashboard HRD')
@section('page-title', 'Dashboard HRD')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Dashboard</span></div>
  <h1>Dashboard HRD</h1>
  <p class="text-muted">Monitoring presensi dan izin karyawan — {{ now()->translatedFormat('l, d F Y') }}</p>
</div>

<div class="animate-slideup">
{{-- ── Tombol Presensi QR (Pribadi) ────────────────────────── --}}
<div class="card mb-6" style="background: linear-gradient(135deg, var(--bg-card), var(--bg-secondary)); border-left: 4px solid var(--teal); box-shadow: 0 4px 20px rgba(0,201,167,0.1);">
  <div class="card-body" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
    <div>
      <h3 style="margin-bottom:4px; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-qrcode" style="color:var(--teal);"></i>
        Presensi Pribadi HRD
      </h3>
      <p class="text-muted text-sm">
        @if(!$presensiPribadi?->jam_datang)
          Waktu presensi masuk: <strong style="color:var(--teal);">{{ $jadwal?->jam_masuk ? \Carbon\Carbon::parse($jadwal->jam_masuk)->format('H:i') : '08:00' }} – {{ \Carbon\Carbon::parse($jadwal?->jam_masuk ?? '08:00')->addMinutes($jadwal?->toleransi_menit ?? 5)->format('H:i') }}</strong>
        @elseif(!$presensiPribadi?->jam_pulang)
          Waktu presensi pulang: <strong style="color:var(--green);">{{ $jadwal?->jam_pulang ? \Carbon\Carbon::parse($jadwal->jam_pulang)->format('H:i') : '17:00' }}</strong>
        @else
          Presensi pribadi Anda hari ini sudah lengkap 🎉
        @endif
      </p>
    </div>
    @if(!($presensiPribadi?->jam_datang && $presensiPribadi?->jam_pulang))
      <a href="{{ route('hrd.presensi') }}" class="btn btn-primary btn-lg">
        <i class="fa-solid fa-camera"></i>
        {{ !$presensiPribadi?->jam_datang ? 'Mulai Scan Masuk' : 'Mulai Scan Pulang' }}
      </a>
    @else
      <div class="badge badge-green" style="padding:12px 20px; font-size:.85rem;">
        <i class="fa-solid fa-circle-check"></i> Presensi Selesai
      </div>
    @endif
  </div>
</div>

{{-- Stats --}}
<div class="stat-grid stagger">
  <div class="stat-card">
    <div class="stat-icon teal"><i class="fa-solid fa-users"></i></div>
    <div class="stat-info">
      <div class="stat-label">Total Karyawan</div>
      <div class="stat-value">{{ $totalKaryawan ?? 0 }}</div>
      <div class="stat-delta">karyawan aktif</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-info">
      <div class="stat-label">Hadir</div>
      <div class="stat-value">{{ $hadirHariIni ?? 0 }}</div>
      <div class="stat-delta pos">presensi fisik</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-file-medical"></i></div>
    <div class="stat-info">
      <div class="stat-label">Izin</div>
      <div class="stat-value">{{ $jumlahIzinHariIni ?? 0 }}</div>
      <div class="stat-delta">sudah disetujui</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-user-xmark"></i></div>
    <div class="stat-info">
      <div class="stat-label">Alpa</div>
      <div class="stat-value">{{ $totalAlpa ?? 0 }}</div>
      <div class="stat-delta neg">hari ini</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon amber"><i class="fa-solid fa-file-medical"></i></div>
    <div class="stat-info">
      <div class="stat-label">Izin Pending</div>
      <div class="stat-value">{{ $izinPending ?? 0 }}</div>
      <div class="stat-delta" style="color:var(--amber);">menunggu</div>
    </div>
  </div>
</div>

<div class="main-sidebar-grid stagger">

  {{-- Presensi Terbaru --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-calendar-check" style="color:var(--blue-light);"></i>
      <h3>Presensi Terbaru Hari Ini</h3>
      <div class="card-actions">
        <a href="{{ route('hrd.laporan') }}" class="btn btn-ghost btn-sm">Lihat Laporan</a>
      </div>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Karyawan</th>
            <th>Masuk</th>
            <th>Pulang</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($presensiTerbaru ?? [] as $p)
          <tr>
            <td>{{ $p->karyawan?->nama_lengkap ?? '—' }}</td>
            <td>{{ $p->jam_datang ? \Carbon\Carbon::parse($p->jam_datang)->format('H:i') : '-' }}</td>
            <td>{{ $p->jam_pulang ? \Carbon\Carbon::parse($p->jam_pulang)->format('H:i') : '-' }}</td>
            <td>
              @php
                $statusColor = match($p->status_masuk ?? '') {
                  'tepat_waktu' => 'green',
                  'terlambat'   => 'amber',
                  default       => 'muted'
                };
                $statusText = match($p->status_masuk ?? '') {
                  'tepat_waktu' => 'Tepat Waktu',
                  'terlambat'   => 'Terlambat',
                  default       => '—'
                };
              @endphp
              <span class="badge badge-{{ $statusColor }}">{{ $statusText }}</span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" style="text-align:center;">Belum ada data presensi hari ini</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Sidebar Kanan --}}
  <div style="display:flex; flex-direction:column; gap:16px;">

    {{-- Alpa --}}
    <div class="card" style="border-left: 4px solid var(--red); background: linear-gradient(to right, rgba(255,71,87,0.05), transparent);">
      <div class="card-header">
        <i class="fa-solid fa-user-xmark" style="color:var(--red);"></i>
        <h3>Alpa ({{ $totalAlpa ?? 0 }})</h3>
      </div>
      <div class="card-body-sm" style="max-height:240px; overflow-y:auto;">
        @forelse($belumPresensiList ?? [] as $k)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border-color);">
          <div>
            <div style="font-weight:600; font-size:.85rem;">{{ $k->nama_lengkap }}</div>
            <div style="font-size:.7rem; color:var(--text-secondary);">{{ $k->kode_karyawan ?? '-' }}</div>
          </div>
          <span class="badge badge-red" style="font-size:.65rem;">Alpa</span>
        </div>
        @empty
        <div style="text-align:center; padding:20px; color:var(--text-secondary);">
          <i class="fa-solid fa-circle-check" style="font-size:1.2rem; color:var(--green);"></i>
          <div style="margin-top:6px; font-size:.8rem;">Tidak ada yang alpa!</div>
        </div>
        @endforelse
      </div>
    </div>

    {{-- Izin Pending --}}
    <div class="card" style="border-left: 4px solid var(--amber); background: linear-gradient(to right, rgba(255,171,64,0.05), transparent);">
      <div class="card-header">
        <i class="fa-solid fa-file-circle-check" style="color:var(--amber);"></i>
        <h3>Pengajuan Izin Pending</h3>
        <div class="card-actions">
          <a href="{{ route('hrd.izin') }}" class="btn btn-ghost btn-sm">Proses</a>
        </div>
      </div>
      <div class="card-body-sm">
        @forelse($izinMenunggu ?? [] as $izin)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border-color);">
          <div>
            <div style="font-weight:600;">{{ $izin->karyawan->nama_lengkap ?? '-' }}</div>
            <div style="font-size:0.7rem; color:var(--text-secondary);">{{ ucfirst(str_replace('_',' ',$izin->jenis_izin ?? 'izin')) }} • {{ $izin->tanggal_mulai?->format('d M Y') ?? '—' }}</div>
          </div>
          <a href="{{ route('hrd.izin') }}" class="badge badge-amber" style="text-decoration:none;">Proses</a>
        </div>
        @empty
        <div style="text-align:center; padding:20px; color:var(--text-secondary);">
          <i class="fa-solid fa-check-circle"></i> Tidak ada izin pending
        </div>
        @endforelse
      </div>
    </div>

    {{-- Aksi Cepat --}}
    <div class="card" style="border-left: 4px solid var(--blue-light); background: linear-gradient(to right, rgba(37,99,235,0.05), transparent);">
      <div class="card-header">
        <i class="fa-solid fa-bolt" style="color:var(--blue-light);"></i>
        <h3>Aksi Cepat</h3>
      </div>
      <div class="card-body-sm">
        <div style="display:flex; flex-direction:column; gap:10px;">
          <a href="{{ route('hrd.laporan') }}" class="btn btn-primary" style="width:100%; text-align:center;">
            <i class="fa-solid fa-file-chart-column"></i> Buat Laporan
          </a>
          <a href="{{ route('hrd.karyawan') }}" class="btn btn-outline" style="width:100%; text-align:center;">
            <i class="fa-solid fa-users"></i> Data Karyawan
          </a>
        </div>
      </div>
    </div>

  </div>
</div>
</div>
@endsection
