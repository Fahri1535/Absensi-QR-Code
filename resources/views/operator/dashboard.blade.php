{{-- ===================================================
     OPERATOR DASHBOARD — resources/views/operator/dashboard.blade.php
     =================================================== --}}
@extends('layouts.app')
@section('title','Dashboard Operator')
@section('page-title','Dashboard Operator')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Dashboard</span></div>
  <h1>Dashboard Operator</h1>
  <p class="text-muted">Kelola data karyawan, QR Code, dan presensi — {{ now()->translatedFormat('l, d F Y') }}</p>
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
      <div class="stat-delta pos">sudah presensi</div>
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
    <div class="stat-icon red"><i class="fa-solid fa-xmark"></i></div>
    <div class="stat-info">
      <div class="stat-label">Belum Presensi</div>
      <div class="stat-value">{{ $belumPresensi ?? 0 }}</div>
      <div class="stat-delta neg">karyawan</div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;margin-top:4px;" class="stagger">

  {{-- Presensi --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-calendar-check" style="color: var(--blue-light);"></i>
      <h3>Presensi Terkini Hari Ini</h3>
      <div class="card-actions">
        <a href="{{ route('operator.presensi') }}" class="btn btn-ghost btn-sm">Lihat Semua</a>
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
        @forelse($presensiTerkini ?? [] as $p)
          <tr>
            <td>{{ $p->karyawan->nama_lengkap ?? '-' }}</td>
            <td>{{ $p->jam_datang ? \Carbon\Carbon::parse($p->jam_datang)->format('H:i') : '—' }}</td>
            <td>{{ $p->jam_pulang ? \Carbon\Carbon::parse($p->jam_pulang)->format('H:i') : '—' }}</td>
            <td>
              @php
                $statusColor = match($p->status_masuk ?? '') {
                  'tepat_waktu' => 'green',
                  'terlambat'   => 'amber',
                  default       => 'muted'
                };
                $statusLabel = match($p->status_masuk ?? '') {
                  'tepat_waktu' => 'Tepat Waktu',
                  'terlambat'   => 'Terlambat',
                  default       => '—'
                };
              @endphp
              <span class="badge badge-{{ $statusColor }}">{{ $statusLabel }}</span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" style="text-align:center;">Belum ada data</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Sidebar --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Quick Action --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-bolt" style="color: var(--blue-light);"></i>
        <h3>Aksi Cepat</h3>
      </div>

      <div class="card-body-sm">
        <div style="display:flex;flex-direction:column;gap:10px;">
          <a href="{{ route('operator.karyawan.create') }}" class="btn btn-primary btn-full">
            <i class="fa-solid fa-user-plus"></i> Tambah Karyawan
          </a>

          <a href="{{ route('operator.qrcode') }}" class="btn btn-outline btn-full">
            <i class="fa-solid fa-qrcode"></i> Kelola QR Code
          </a>

          <a href="{{ route('operator.laporan') }}" class="btn btn-outline btn-full">
            <i class="fa-solid fa-file-chart-column"></i> Buat Laporan
          </a>
        </div>
      </div>
    </div>

    {{-- QR Status --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-qrcode" style="color: var(--blue-light);"></i>
        <h3>Status QR Code</h3>
      </div>

      <div class="card-body-sm">
        <div style="display:flex; flex-direction:column; gap:12px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span>QR Code Masuk</span>
            <span class="badge badge-{{ ($qrMasukAktif ?? false) ? 'green' : 'red' }}">
              {{ ($qrMasukAktif ?? false) ? 'Aktif' : 'Nonaktif' }}
            </span>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span>QR Code Pulang</span>
            <span class="badge badge-{{ ($qrPulangAktif ?? false) ? 'green' : 'red' }}">
              {{ ($qrPulangAktif ?? false) ? 'Aktif' : 'Nonaktif' }}
            </span>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection