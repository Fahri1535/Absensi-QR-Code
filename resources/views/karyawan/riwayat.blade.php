{{-- ===================================================
     RIWAYAT PRESENSI — resources/views/karyawan/riwayat.blade.php
     =================================================== --}}
@extends('layouts.app')
@section('title','Riwayat Presensi')
@section('page-title','Riwayat Presensi')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Riwayat Presensi</span></div>
  <h1>Riwayat Presensi Saya</h1>
  <p class="text-muted">Catatan kehadiran Anda secara lengkap.</p>
</div>

{{-- Filter Bulan --}}
<div class="card mb-6">
  <div class="card-body-sm">
    <form method="GET" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
      <div class="form-group" style="margin:0;flex:1;min-width:140px;">
        <label class="form-label">Bulan</label>
        <select name="bulan" class="form-control" onchange="this.form.submit()">
          @for($m = 1; $m <= 12; $m++)
          <option value="{{ $m }}" {{ request('bulan', now()->month) == $m ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
          </option>
          @endfor
        </select>
      </div>
      <div class="form-group" style="margin:0;flex:1;min-width:100px;">
        <label class="form-label">Tahun</label>
        <select name="tahun" class="form-control" onchange="this.form.submit()">
          @for($y = now()->year; $y >= now()->year - 2; $y--)
          <option value="{{ $y }}" {{ request('tahun', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>
      <a href="{{ route('karyawan.riwayat.export', ['bulan'=>request('bulan',now()->month),'tahun'=>request('tahun',now()->year)]) }}"
         class="btn btn-outline" style="align-self:end;">
        <i class="fa-solid fa-file-excel" style="color:#1D6F42;"></i> Export
      </a>
    </form>
  </div>
</div>

{{-- Summary bulan --}}
<div class="stat-grid stagger mb-6">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-info"><div class="stat-label">Tepat Waktu</div><div class="stat-value">{{ $summary['tepat_waktu'] ?? 0 }}</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
    <div class="stat-info"><div class="stat-label">Terlambat</div><div class="stat-value">{{ $summary['terlambat'] ?? 0 }}</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-arrow-left"></i></div>
    <div class="stat-info"><div class="stat-label">Pulang Awal</div><div class="stat-value">{{ $summary['pulang_awal'] ?? 0 }}</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon teal"><i class="fa-solid fa-calendar-days"></i></div>
    <div class="stat-info"><div class="stat-label">Total Hadir</div><div class="stat-value">{{ $summary['total'] ?? 0 }}</div><div class="stat-delta">hari kerja</div></div>
  </div>
</div>

{{-- Table --}}
<div class="card animate-slideup">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Hari</th>
          <th>Masuk</th>
          <th>Pulang</th>
          <th>Durasi</th>
          <th>Status</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($riwayat ?? [] as $p)
        @php
          $sc = ['tepat_waktu'=>'green','terlambat'=>'amber','pulang_awal'=>'red'][$p->status] ?? 'muted';
          $sl = ['tepat_waktu'=>'Tepat Waktu','terlambat'=>'Terlambat','pulang_awal'=>'Pulang Awal'][$p->status] ?? ucfirst($p->status ?? '');
          $durasi = ($p->jam_datang && $p->jam_pulang)
            ? \Carbon\Carbon::parse($p->jam_datang)->diff(\Carbon\Carbon::parse($p->jam_pulang))->format('%H:%I jam')
            : '—';
        @endphp
        <tr>
          <td style="font-weight:600;">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
          <td class="text-muted">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('l') }}</td>
          <td>{{ $p->jam_datang ? \Carbon\Carbon::parse($p->jam_datang)->format('H:i') : '<span class="text-muted">—</span>' }}</td>
          <td>{{ $p->jam_pulang ? \Carbon\Carbon::parse($p->jam_pulang)->format('H:i') : '<span class="text-muted">—</span>' }}</td>
          <td class="font-mono text-sm">{{ $durasi }}</td>
          <td><span class="badge badge-{{ $sc }}">{{ $sl }}</span></td>
          <td class="text-muted text-xs">{{ $p->keterangan ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted);">Tidak ada data presensi bulan ini</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if(isset($riwayat) && $riwayat->hasPages())
  <div class="card-footer">{{ $riwayat->links() }}</div>
  @endif
</div>

<style>
  body, .card, .card-body, .page-content, 
  .table, .table th, .table td, .badge, .btn,
  h1, h2, h3, h4, p, span, div {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif !important;
    letter-spacing: normal !important;
  }
</style>

@endsection


{{-- ===================================================
     NOTIFIKASI — resources/views/notifikasi.blade.php
     =================================================== --}}
{{-- Save as separate file: notifikasi.blade.php --}}
