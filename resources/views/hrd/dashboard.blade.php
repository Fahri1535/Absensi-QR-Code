@extends('layouts.app')

@section('title', 'Dashboard HRD')
@section('page-title', 'Dashboard HRD')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Dashboard</span></div>
  <h1>Dashboard HRD</h1>
  <p class="text-muted">Monitoring presensi dan izin karyawan — {{ now()->translatedFormat('l, d F Y') }}</p>
</div>

{{-- Stats --}}
<div class="stat-grid stagger">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
    <div class="stat-info">
      <div class="stat-label">Total Karyawan</div>
      <div class="stat-value">{{ $totalKaryawan ?? 0 }}</div>
      <div class="stat-delta pos">karyawan aktif</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-info">
      <div class="stat-label">Hadir Hari Ini</div>
      <div class="stat-value">{{ $hadirHariIni ?? 0 }}</div>
      <div class="stat-delta pos">karyawan</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
    <div class="stat-info">
      <div class="stat-label">Terlambat Hari Ini</div>
      <div class="stat-value">{{ $terlambatHariIni ?? 0 }}</div>
      <div class="stat-delta neg">karyawan</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-file-medical"></i></div>
    <div class="stat-info">
      <div class="stat-label">Izin Pending</div>
      <div class="stat-value">{{ $izinPending ?? 0 }}</div>
      <div class="stat-delta pos">menunggu</div>
    </div>
  </div>
</div>

<div style="display:grid; grid-template-columns:1fr 340px; gap:20px;" class="stagger">

  {{-- Presensi Terbaru --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-calendar-check" style="color:var(--blue-light);"></i>
      <h3>Presensi Terbaru Hari Ini</h3>
      <div class="card-actions">
        {{-- Karena tidak ada route presensi khusus untuk HRD, bisa ke laporan atau disable dulu --}}
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
          @forelse($presensiHariIni ?? [] as $p)
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

    {{-- Izin Pending --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-file-circle-check" style="color:var(--blue-light);"></i>
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
    <div class="card">
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
@endsection