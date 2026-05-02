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
      <div style="font-size:.72rem; text-transform:uppercase; letter-spacing:.1em; color:var(--muted); margin-bottom:6px;">Presensi Masuk</div>
      @if($presensiHariIni?->jam_datang)
        <div style="font-family:'Syne',sans-serif; font-size:1.9rem; font-weight:800; color:var(--teal);">
          {{ \Carbon\Carbon::parse($presensiHariIni->jam_datang)->format('H:i') }}
        </div>
        <div class="badge badge-{{ $presensiHariIni->status === 'tepat_waktu' ? 'green' : 'amber' }}" style="margin-top:8px;">
          {{ $presensiHariIni->status === 'tepat_waktu' ? '✓ Tepat Waktu' : '⚠ Terlambat' }}
        </div>
      @else
        <div style="font-family:'Syne',sans-serif; font-size:1.4rem; font-weight:700; color:var(--muted);">Belum Presensi</div>
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
      <div style="font-size:.72rem; text-transform:uppercase; letter-spacing:.1em; color:var(--muted); margin-bottom:6px;">Presensi Pulang</div>
      @if($presensiHariIni?->jam_pulang)
        <div style="font-family:'Syne',sans-serif; font-size:1.9rem; font-weight:800; color:var(--green);">
          {{ \Carbon\Carbon::parse($presensiHariIni->jam_pulang)->format('H:i') }}
        </div>
        @if($presensiHariIni->status === 'pulang_awal')
          <div class="badge badge-amber" style="margin-top:8px;">⚠ Pulang Lebih Awal</div>
        @else
          <div class="badge badge-green" style="margin-top:8px;">✓ Selesai</div>
        @endif
      @else
        <div style="font-family:'Syne',sans-serif; font-size:1.4rem; font-weight:700; color:var(--muted);">Belum Presensi</div>
        <div class="badge badge-muted" style="margin-top:8px;">Menunggu</div>
      @endif
    </div>
  </div>
</div>

{{-- ── Tombol Presensi QR ───────────────────────────────────── --}}
<div class="card mb-6" style="background:linear-gradient(135deg, var(--navy-mid), var(--navy-light)); border-color: rgba(0,201,167,.2);">
  <div class="card-body" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
    <div>
      <h3 style="margin-bottom:4px;">Lakukan Presensi Sekarang</h3>
      <p class="text-muted text-sm">
        @if(!$presensiHariIni?->jam_datang)
          Waktu presensi masuk: <strong style="color:var(--teal);">{{ $jadwal?->jam_masuk ?? '08:00' }} – {{ \Carbon\Carbon::parse($jadwal?->jam_masuk ?? '08:00')->addMinutes($jadwal?->toleransi_menit ?? 5)->format('H:i') }}</strong>
        @elseif(!$presensiHariIni?->jam_pulang)
          Waktu presensi pulang: <strong style="color:var(--green);">{{ $jadwal?->jam_pulang ?? '17:00' }}</strong>
        @else
          Presensi hari ini sudah lengkap 🎉
        @endif
      </p>
    </div>
    @if(!($presensiHariIni?->jam_datang && $presensiHariIni?->jam_pulang))
      <a href="{{ route('karyawan.presensi') }}" class="btn btn-primary btn-lg">
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
    <div class="stat-icon teal"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-info">
      <div class="stat-label">Hadir Bulan Ini</div>
      <div class="stat-value">{{ $statsHadir ?? 0 }}</div>
      <div class="stat-delta pos">hari kerja</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
    <div class="stat-info">
      <div class="stat-label">Terlambat</div>
      <div class="stat-value">{{ $statsTerlambat ?? 0 }}</div>
      <div class="stat-delta neg">kali bulan ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-file-medical"></i></div>
    <div class="stat-info">
      <div class="stat-label">Izin Disetujui</div>
      <div class="stat-value">{{ $statsIzin ?? 0 }}</div>
      <div class="stat-delta pos">hari bulan ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-xmark"></i></div>
    <div class="stat-info">
      <div class="stat-label">Alpha</div>
      <div class="stat-value">{{ $statsAlpha ?? 0 }}</div>
      <div class="stat-delta neg">hari bulan ini</div>
    </div>
  </div>
</div>

{{-- ── Grid: Riwayat + Izin Pending ────────────────────────── --}}
<div style="display:grid; grid-template-columns:1fr 380px; gap:20px; margin-top:24px;" class="stagger">

  {{-- Riwayat Presensi Terakhir --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-clock-rotate-left text-teal"></i>
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
          @forelse($riwayatTerbaru ?? [] as $r)
          <tr>
            <td>
              <div style="font-weight:600;">{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</div>
              <div class="text-xs text-muted">{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('l') }}</div>
            </td>
            <td>{{ $r->jam_datang ? \Carbon\Carbon::parse($r->jam_datang)->format('H:i') : '—' }}</td>
            <td>{{ $r->jam_pulang ? \Carbon\Carbon::parse($r->jam_pulang)->format('H:i') : '—' }}</td>
            <td>
              @php
                $statusMap = [
                  'tepat_waktu'  => ['green', 'Tepat Waktu'],
                  'terlambat'    => ['amber', 'Terlambat'],
                  'pulang_awal'  => ['red',   'Pulang Awal'],
                ];
                [$bc, $bl] = $statusMap[$r->status] ?? ['muted', ucfirst($r->status)];
              @endphp
              <span class="badge badge-{{ $bc }}">{{ $bl }}</span>
            </td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center; padding:28px; color:var(--muted);">Belum ada data presensi</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Izin / Notifikasi --}}
  <div style="display:flex; flex-direction:column; gap:16px;">

    {{-- Pengajuan Izin Terakhir --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-file-medical text-teal"></i>
        <h3>Izin Terbaru</h3>
        <div class="card-actions">
          <a href="{{ route('karyawan.izin') }}" class="btn btn-ghost btn-sm">+ Ajukan</a>
        </div>
      </div>
      <div class="card-body-sm">
        @forelse($izinTerbaru ?? [] as $izin)
        <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
          <div style="width:36px;height:36px;border-radius:8px;background:var(--card-bg);display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;">
            {{ ['izin'=>'🏖','sakit'=>'🤒','cuti'=>'🌴','tugas_luar'=>'💼','alpa'=>'❌'][$izin->jenis_izin] ?? '📄' }}
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:.85rem;">{{ ucfirst(str_replace('_',' ', $izin->jenis_izin)) }}</div>
            <div class="text-xs text-muted">
              {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M') }}
              @if($izin->tanggal_mulai !== $izin->tanggal_selesai)
                — {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d M Y') }}
              @else
                , {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('Y') }}
              @endif
            </div>
          </div>
          @php
            $sc = ['pending'=>'amber','disetujui'=>'green','ditolak'=>'red'][$izin->status] ?? 'muted';
          @endphp
          <span class="badge badge-{{ $sc }}">{{ ucfirst($izin->status) }}</span>
        </div>
        @empty
        <div style="text-align:center; padding:20px; color:var(--muted); font-size:.85rem;">Belum ada pengajuan izin</div>
        @endforelse
      </div>
    </div>

    {{-- Notifikasi Terbaru --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-bell text-teal"></i>
        <h3>Notifikasi</h3>
      </div>
      <div class="card-body-sm">
        @forelse(auth()->user()->notifikasi()->latest()->take(4)->get() as $notif)
        <div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);{{ $notif->is_read ? '' : 'background:rgba(0,201,167,.03);border-radius:6px;padding:10px 8px;' }}">
          <div style="width:8px;height:8px;border-radius:50%;background:{{ $notif->is_read ? 'var(--muted)' : 'var(--teal)' }};margin-top:6px;flex-shrink:0;"></div>
          <div>
            <div style="font-size:.83rem; {{ $notif->is_read ? 'color:var(--muted)' : 'font-weight:500;' }}">{{ $notif->pesan }}</div>
            <div class="text-xs text-muted" style="margin-top:3px;">{{ $notif->created_at->diffForHumans() }}</div>
          </div>
        </div>
        @empty
        <div style="text-align:center; padding:20px; color:var(--muted); font-size:.85rem;">Tidak ada notifikasi</div>
        @endforelse
      </div>
    </div>

  </div>
</div>

@endsection
