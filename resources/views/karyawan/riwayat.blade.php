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

{{-- Filter Bulan & Status --}}
<div class="card mb-6">
  <div class="card-body-sm">
    <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;align-items:end;">
      <div class="form-group" style="margin:0;">
        <label class="form-label">Bulan</label>
        <select name="bulan" class="form-control" onchange="this.form.submit()" style="cursor: pointer;">
          @for($m = 1; $m <= 12; $m++)
          <option value="{{ $m }}" {{ request('bulan', now()->month) == $m ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
          </option>
          @endfor
        </select>
      </div>
      <div class="form-group" style="margin:0;">
        <label class="form-label">Tahun</label>
        <select name="tahun" class="form-control" onchange="this.form.submit()" style="cursor: pointer;">
          @for($y = now()->year; $y >= now()->year - 2; $y--)
          <option value="{{ $y }}" {{ request('tahun', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>
      <div class="form-group" style="margin:0;">
        <label class="form-label">Status</label>
        <select name="status" class="form-control" onchange="this.form.submit()" style="cursor: pointer;">
          <option value="">— Semua Status —</option>
          <option value="tepat_waktu" {{ request('status') === 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
          <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
          <option value="alpa" {{ request('status') === 'alpa' ? 'selected' : '' }}>Alpa</option>
          <option value="sakit" {{ request('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
          <option value="cuti" {{ request('status') === 'cuti' ? 'selected' : '' }}>Cuti</option>
        </select>
      </div>
      <div style="display:flex;gap:8px;align-self:end;">
        @php 
          $exportParams = array_merge(request()->query(), ['format' => 'xlsx']);
          $exportRoute  = auth()->user()->role . '.riwayat.export';
        @endphp
        <a href="{{ route($exportRoute, $exportParams) }}"
           class="btn btn-outline">
          <i class="fa-solid fa-file-excel" style="color:#1D6F42;"></i> Excel
        </a>
        @php $exportParams['format'] = 'pdf'; @endphp
        <a href="{{ route($exportRoute, $exportParams) }}"
           class="btn btn-outline">
          <i class="fa-solid fa-file-pdf" style="color:#F40F02;"></i> PDF
        </a>
      </div>
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
    <div class="stat-icon blue"><i class="fa-solid fa-file-medical"></i></div>
    <div class="stat-info"><div class="stat-label">Izin</div><div class="stat-value">{{ $summary['izin'] ?? 0 }}</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-user-xmark"></i></div>
    <div class="stat-info"><div class="stat-label">Alpa</div><div class="stat-value">{{ $summary['alpa'] ?? 0 }}</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon teal"><i class="fa-solid fa-calendar-days"></i></div>
    <div class="stat-info"><div class="stat-label">Total</div><div class="stat-value">{{ $summary['total'] ?? 0 }}</div><div class="stat-delta">hari</div></div>
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
          $isIzin = $p->is_izin ?? false;
          $status = $p->status ?? '—';
          
          if ($isIzin) {
              $sc = match($status) {
                  'sakit' => 'blue',
                  'cuti'  => 'indigo',
                  'pulang_cepat' => 'orange',
                  'lembur' => 'teal',
                  default => 'purple'
              };
              $sl = ucfirst(str_replace('_', ' ', $status));
          } elseif ($status === 'alpa') {
              $sc = 'red';
              $sl = 'Alpa';
          } else {
              $sc = ['tepat_waktu'=>'green','terlambat'=>'amber','pulang_awal'=>'red'][$status] ?? 'muted';
              $sl = ['tepat_waktu'=>'Tepat Waktu','terlambat'=>'Terlambat','pulang_awal'=>'Pulang Awal'][$status] ?? ucfirst($status);
          }

          $durasi = ($p->jam_datang && $p->jam_pulang)
            ? \Carbon\Carbon::parse($p->jam_datang)->diff(\Carbon\Carbon::parse($p->jam_pulang))->format('%H:%I')
            : '—';
        @endphp
        <tr>
          <td style="font-weight:600;">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
          <td class="text-muted">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('l') }}</td>
          <td>{{ $p->jam_datang ? \Carbon\Carbon::parse($p->jam_datang)->format('H:i') : '—' }}</td>
          <td>{{ $p->jam_pulang ? \Carbon\Carbon::parse($p->jam_pulang)->format('H:i') : '—' }}</td>
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
@endsection