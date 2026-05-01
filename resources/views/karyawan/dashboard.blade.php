@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Dashboard</span></div>
  <h1>Halo, {{ auth()->user()->karyawan?->nama_lengkap ?? auth()->user()->username }} 👋</h1>
  <p class="text-muted">Berikut ringkasan presensi Anda hari ini — {{ now()->translatedFormat('l, d F Y') }}</p>
</div>

{{-- ── Status Presensi Hari Ini ─────────────────────────────── --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:22px;">

  {{-- Masuk --}}
  <div class="card">
    <div class="card-body" style="text-align:center; padding:26px 20px;">
      <div style="font-size:2rem; margin-bottom:10px;">
        {{ $presensiHariIni?->jam_datang ? '✅' : '⬜' }}
      </div>
      <div style="font-size:.7rem; text-transform:uppercase; letter-spacing:.06em; color:var(--text-secondary); margin-bottom:6px;">
        Presensi Masuk
      </div>
      @if($presensiHariIni?->jam_datang)
        <div style="font-family:'Syne',sans-serif; font-size:1.8rem; font-weight:800; color:var(--blue-light);">
          {{ \Carbon\Carbon::parse($presensiHariIni->jam_datang)->format('H:i') }}
        </div>
        <div style="margin-top:8px;">
          <span class="badge badge-{{ $presensiHariIni->status_masuk === 'tepat_waktu' ? 'green' : 'amber' }}">
            {{ $presensiHariIni->status_masuk === 'tepat_waktu' ? '✓ Tepat Waktu' : '⚠ Terlambat' }}
          </span>
        </div>
      @else
        <div style="font-size:1.25rem; font-weight:700; color:var(--text-secondary);">Belum Presensi</div>
        <div style="margin-top:8px;"><span class="badge badge-muted">Menunggu</span></div>
      @endif
    </div>
  </div>

  {{-- Pulang --}}
  <div class="card">
    <div class="card-body" style="text-align:center; padding:26px 20px;">
      <div style="font-size:2rem; margin-bottom:10px;">
        {{ $presensiHariIni?->jam_pulang ? '🏠' : '⬜' }}
      </div>
      <div style="font-size:.7rem; text-transform:uppercase; letter-spacing:.06em; color:var(--text-secondary); margin-bottom:6px;">
        Presensi Pulang
      </div>
      @if($presensiHariIni?->jam_pulang)
        <div style="font-family:'Syne',sans-serif; font-size:1.8rem; font-weight:800; color:var(--green);">
          {{ \Carbon\Carbon::parse($presensiHariIni->jam_pulang)->format('H:i') }}
        </div>
        <div style="margin-top:8px;">
          @if($presensiHariIni->status_pulang === 'lebih_awal')
            <span class="badge badge-amber">⚠ Pulang Lebih Awal</span>
          @else
            <span class="badge badge-green">✓ Selesai</span>
          @endif
        </div>
      @else
        <div style="font-size:1.25rem; font-weight:700; color:var(--text-secondary);">Belum Presensi</div>
        <div style="margin-top:8px;"><span class="badge badge-muted">Menunggu</span></div>
      @endif
    </div>
  </div>

</div>

{{-- ── Banner Presensi Sekarang ─────────────────────────────── --}}
<div class="card mb-6"
     style="background:linear-gradient(135deg, var(--blue-secondary), var(--blue-primary));
            border-color:rgba(59,130,246,.35); margin-bottom:22px;">
  <div class="card-body"
       style="display:flex; align-items:center; justify-content:space-between;
              flex-wrap:wrap; gap:16px; padding:22px;">
    <div>
      <h3 style="margin-bottom:4px; color:#fff;">Lakukan Presensi Sekarang</h3>
      <p style="font-size:.85rem; color:rgba(255,255,255,.8); margin:0;">
        @if(!$presensiHariIni?->jam_datang)
          Waktu masuk: <strong style="color:#FFD700;">
            {{ $jadwal->jam_masuk }} –
            {{ \Carbon\Carbon::parse($jadwal->jam_masuk)->addMinutes($jadwal->toleransi_menit)->format('H:i') }}
          </strong>
        @elseif(!$presensiHariIni?->jam_pulang)
          Waktu pulang: <strong style="color:#FFD700;">{{ $jadwal->jam_pulang }}</strong>
        @else
          Presensi hari ini sudah lengkap 🎉
        @endif
      </p>
    </div>
    @if(!($presensiHariIni?->jam_datang && $presensiHariIni?->jam_pulang))
      <a href="{{ route('karyawan.presensi') }}"
         style="display:inline-flex; align-items:center; gap:8px;
                background:#fff; color:var(--blue-primary);
                padding:10px 22px; border-radius:10px;
                font-weight:600; font-size:.875rem;
                text-decoration:none; white-space:nowrap;
                transition:opacity .15s ease;"
         onmouseover="this.style.opacity='.88'"
         onmouseout="this.style.opacity='1'">
        <i class="fa-solid fa-qrcode"></i>
        {{ !$presensiHariIni?->jam_datang ? 'Presensi Masuk' : 'Presensi Pulang' }}
      </a>
    @else
      <span class="badge badge-green" style="padding:10px 18px; font-size:.82rem;">
        <i class="fa-solid fa-circle-check"></i> Presensi Lengkap
      </span>
    @endif
  </div>
</div>

{{-- ── Statistik Bulan Ini ──────────────────────────────────── --}}
<div class="stat-grid">
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
      <div class="stat-label">Izin Pending</div>
      <div class="stat-value">{{ $izinPending ?? 0 }}</div>
      <div class="stat-delta pos">menunggu</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-calendar-check"></i></div>
    <div class="stat-info">
      <div class="stat-label">Presensi Lengkap</div>
      <div class="stat-value">{{ $stat->total_lengkap ?? 0 }}</div>
      <div class="stat-delta pos">masuk + pulang</div>
    </div>
  </div>
</div>

{{-- ── Riwayat Presensi Terbaru ─────────────────────────────── --}}
<div class="card" style="margin-top:22px;">
  <div class="card-header">
    <i class="fa-solid fa-clock-rotate-left" style="color:var(--blue-light);"></i>
    <h3>Riwayat Presensi Terbaru</h3>
    <div class="card-actions">
      <a href="{{ route('karyawan.riwayat') }}" class="btn btn-ghost btn-sm">Lihat Semua</a>
    </div>
  </div>
  <div class="table-wrap">
    <table>
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
          </td>
          <td>{{ $r->jam_datang ? \Carbon\Carbon::parse($r->jam_datang)->format('H:i') : '—' }}</td>
          <td>{{ $r->jam_pulang ? \Carbon\Carbon::parse($r->jam_pulang)->format('H:i') : '—' }}</td>
          <td>
            @php
              $sc = ['tepat_waktu' => 'green', 'terlambat' => 'amber'][$r->status_masuk] ?? 'muted';
              $sl = ['tepat_waktu' => 'Tepat Waktu', 'terlambat' => 'Terlambat'][$r->status_masuk] ?? '—';
            @endphp
            <span class="badge badge-{{ $sc }}">{{ $sl }}</span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="4" style="text-align:center; padding:28px; color:var(--text-secondary);">
            Belum ada data presensi
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
