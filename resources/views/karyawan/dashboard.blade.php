@extends('layouts.app')

@section('title', 'Dashboard Karyawan')
@section('page-title', 'Dashboard')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Dashboard</span></div>
  <h1>Halo, {{ auth()->user()->karyawan?->nama_lengkap ?? auth()->user()->username }} 👋</h1>
  <p class="text-muted">Berikut ringkasan presensi Anda hari ini — {{ now()->translatedFormat('l, d F Y') }}</p>
</div>

{{-- ── Status Presensi Hari Ini ─────────────────────────────── --}}
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:24px;" class="stagger">

  {{-- Masuk --}}
  <div class="card">
    <div class="card-body" style="text-align:center; padding:28px;">
      <div style="font-size:2.2rem; margin-bottom:10px;">
        {{ $presensiHariIni?->jam_datang ? '✅' : '⬜' }}
      </div>
      <div style="font-size:.72rem; text-transform:uppercase; letter-spacing:normal; color:var(--text-secondary); margin-bottom:6px;">Presensi Masuk</div>
      @if($presensiHariIni?->jam_datang)
        <div style="font-family:'Syne',sans-serif; font-size:1.9rem; font-weight:800; color:var(--blue-light); letter-spacing:normal;">
          {{ \Carbon\Carbon::parse($presensiHariIni->jam_datang)->format('H:i') }}
        </div>
        <div class="badge badge-{{ $presensiHariIni->status_masuk === 'tepat_waktu' ? 'green' : 'amber' }}" style="margin-top:8px;">
          {{ $presensiHariIni->status_masuk === 'tepat_waktu' ? '✓ Tepat Waktu' : '⚠ Terlambat' }}
        </div>
      @else
        <div style="font-size:1.4rem; font-weight:700; color:var(--text-secondary); letter-spacing:normal;">Belum Presensi</div>
        <div class="badge badge-muted" style="margin-top:8px;">Menunggu</div>
      @endif
    </div>
  </div>

  {{-- Pulang --}}
  <div class="card">
    <div class="card-body" style="text-align:center; padding:28px;">
      <div style="font-size:2.2rem; margin-bottom:10px;">
        {{ $presensiHariIni?->jam_pulang ? '🏠' : '⬜' }}
      </div>
      <div style="font-size:.72rem; text-transform:uppercase; letter-spacing:normal; color:var(--text-secondary); margin-bottom:6px;">Presensi Pulang</div>
      @if($presensiHariIni?->jam_pulang)
        <div style="font-family:'Syne',sans-serif; font-size:1.9rem; font-weight:800; color:var(--green); letter-spacing:normal;">
          {{ \Carbon\Carbon::parse($presensiHariIni->jam_pulang)->format('H:i') }}
        </div>
        @if($presensiHariIni->status_pulang === 'lebih_awal')
          <div class="badge badge-amber" style="margin-top:8px;">⚠ Pulang Lebih Awal</div>
        @else
          <div class="badge badge-green" style="margin-top:8px;">✓ Selesai</div>
        @endif
      @else
        <div style="font-size:1.4rem; font-weight:700; color:var(--text-secondary); letter-spacing:normal;">Belum Presensi</div>
        <div class="badge badge-muted" style="margin-top:8px;">Menunggu</div>
      @endif
    </div>
  </div>
</div>

{{-- ── Tombol Presensi QR ───────────────────────────────────── --}}
<div class="card mb-6" style="background:linear-gradient(135deg, var(--blue-secondary), var(--blue-primary)); border-color: rgba(59,130,246,.3);">
  <div class="card-body" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
    <div>
      <h3 style="margin-bottom:4px; color:white;">Lakukan Presensi Sekarang</h3>
      <p class="text-sm" style="color:rgba(255,255,255,0.8);">
        @if(!$presensiHariIni?->jam_datang)
          Waktu presensi masuk: <strong style="color:#FFD700;">{{ $jadwal->jam_masuk }} – {{ \Carbon\Carbon::parse($jadwal->jam_masuk)->addMinutes($jadwal->toleransi_menit)->format('H:i') }}</strong>
        @elseif(!$presensiHariIni?->jam_pulang)
          Waktu presensi pulang: <strong style="color:#FFD700;">{{ $jadwal->jam_pulang }}</strong>
        @else
          Presensi hari ini sudah lengkap 🎉
        @endif
      </p>
    </div>
    @if(!($presensiHariIni?->jam_datang && $presensiHariIni?->jam_pulang))
      <a href="{{ route('karyawan.presensi') }}" class="btn" style="background:white; color:var(--blue-primary); border-radius:12px; padding:12px 24px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px; letter-spacing:normal;">
        <i class="fa-solid fa-qrcode"></i>
        {{ !$presensiHariIni?->jam_datang ? 'Presensi Masuk' : 'Presensi Pulang' }}
      </a>
    @else
      <div class="badge badge-green" style="padding:12px 20px; font-size:.85rem;">
        <i class="fa-solid fa-circle-check"></i> Presensi Lengkap
      </div>
    @endif
  </div>
</div>

{{-- ── Statistik Bulan Ini ──────────────────────────────────── --}}
<div class="stat-grid stagger">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-info">
      <div class="stat-label">Hadir Bulan Ini</div>
      <div class="stat-value">{{ $stat->total_hadir ?? 0 }}</div>
      <div class="stat-delta pos">hari kerja</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
    <div class="stat-info">
      <div class="stat-label">Terlambat</div>
      <div class="stat-value">{{ $stat->total_terlambat ?? 0 }}</div>
      <div class="stat-delta neg">kali bulan ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-file-medical"></i></div>
    <div class="stat-info">
      <div class="stat-label">Izin (Pending)</div>
      <div class="stat-value">{{ $izinPending ?? 0 }}</div>
      <div class="stat-delta pos">menunggu</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-calendar-check"></i></div>
    <div class="stat-info">
      <div class="stat-label">Presensi Lengkap</div>
      <div class="stat-value">{{ $stat->total_lengkap ?? 0 }}</div>
      <div class="stat-delta pos">hari masuk+pulang</div>
    </div>
  </div>
</div>

{{-- ── Riwayat Terakhir ─────────────────────────────────────── --}}
<div class="card mt-6 animate-slideup">
  <div class="card-header">
    <i class="fa-solid fa-clock-rotate-left" style="color:var(--blue-light);"></i>
    <h3>Riwayat Presensi Terbaru</h3>
    <div class="card-actions">
      <a href="{{ route('karyawan.riwayat') }}" class="btn btn-ghost btn-sm">Lihat Semua</a>
    </div>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Masuk</th>
          <th>Pulang</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($riwayatTerakhir ?? [] as $r)
        <tr>
          <td>
            <div style="font-weight:600;">{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</div>
            <div class="text-xs text-muted">{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('l') }}</div>
           </div>
          </div>
          </td>
          <td>{{ $r->jam_datang ? \Carbon\Carbon::parse($r->jam_datang)->format('H:i') : '—' }}</div>
          </td>
          <td>{{ $r->jam_pulang ? \Carbon\Carbon::parse($r->jam_pulang)->format('H:i') : '—' }}</div>
          </td>
          <td>
            @php
              $sc = ['tepat_waktu' => 'green', 'terlambat' => 'amber'][$r->status_masuk] ?? 'muted';
              $sl = ['tepat_waktu' => 'Tepat Waktu', 'terlambat' => 'Terlambat'][$r->status_masuk] ?? '—';
            @endphp
            <span class="badge badge-{{ $sc }}">{{ $sl }}</span>
          </div>
        </div>
        </tr>
        @empty
        <tr><td colspan="4" style="text-align:center; padding:28px; color:var(--text-secondary);">Belum ada data presensi</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<style>
  /* PAKSA SEMUA FONT JADI NORMAL */
  body, .card, .card-body, .card-header, .page-content, 
  .stat-card, .stat-info, .stat-label, .stat-value,
  .badge, .btn, .btn-ghost, .btn-sm, .text-muted,
  .breadcrumb, .table, .table th, .table td,
  h1, h2, h3, h4, h5, h6, p, span, div, a, li {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif !important;
    letter-spacing: 0px !important;
    letter-spacing: normal !important;
  }
  
  .stat-value {
    font-family: 'Syne', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif !important;
  }
</style>

@endsection