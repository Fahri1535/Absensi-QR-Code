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
      <div style="display:flex;gap:8px;align-self:end;flex-wrap:wrap;">
        @php 
          $exportParams = array_merge(request()->query(), ['format' => 'xlsx']);
          $exportRoute  = auth()->user()->role . '.riwayat.export';
        @endphp
        <a href="{{ route($exportRoute, $exportParams) }}" class="btn btn-outline">
          <i class="fa-solid fa-file-excel" style="color:#1D6F42;"></i> Excel
        </a>
        @php $exportParams['format'] = 'pdf'; @endphp
        <a href="{{ route($exportRoute, $exportParams) }}" class="btn btn-outline">
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
    <div class="stat-info">
      <div class="stat-label">Hadir</div>
      <div class="stat-value">{{ $summary['hadir'] ?? 0 }}</div>
      <div class="stat-delta pos">presensi fisik</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
    <div class="stat-info">
      <div class="stat-label">Terlambat</div>
      <div class="stat-value">{{ $summary['terlambat'] ?? 0 }}</div>
      <div class="stat-delta">dari total hadir</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-file-medical"></i></div>
    <div class="stat-info">
      <div class="stat-label">Izin</div>
      <div class="stat-value">{{ $summary['izin'] ?? 0 }}</div>
      <div class="stat-delta">sudah disetujui</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-user-xmark"></i></div>
    <div class="stat-info">
      <div class="stat-label">Alpa</div>
      <div class="stat-value">{{ $summary['alpa'] ?? 0 }}</div>
      <div class="stat-delta neg">tanpa keterangan</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red" style="opacity:.7;"><i class="fa-solid fa-arrow-left"></i></div>
    <div class="stat-info">
      <div class="stat-label">Pulang Awal</div>
      <div class="stat-value">{{ $summary['pulang_awal'] ?? 0 }}</div>
      <div class="stat-delta">kali</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon teal"><i class="fa-solid fa-calendar-days"></i></div>
    <div class="stat-info">
      <div class="stat-label">Total</div>
      <div class="stat-value">{{ $summary['total'] ?? 0 }}</div>
      <div class="stat-delta">hari kerja</div>
    </div>
  </div>
</div>

{{-- TABLE VIEW (Desktop Only) --}}
<div class="card animate-slideup desktop-table">
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
              $sc = ['tepat_waktu' => 'green', 'terlambat' => 'amber', 'pulang_awal' => 'red'][$status] ?? 'muted';
              $sl = ['tepat_waktu' => 'Tepat Waktu', 'terlambat' => 'Terlambat', 'pulang_awal' => 'Pulang Awal'][$status] ?? ucfirst($status);
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

{{-- MOBILE CARD VIEW --}}
<div class="mobile-cards">
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
        $sc = ['tepat_waktu' => 'green', 'terlambat' => 'amber', 'pulang_awal' => 'red'][$status] ?? 'muted';
        $sl = ['tepat_waktu' => 'Tepat Waktu', 'terlambat' => 'Terlambat', 'pulang_awal' => 'Pulang Awal'][$status] ?? ucfirst($status);
    }

    $durasi = ($p->jam_datang && $p->jam_pulang)
      ? \Carbon\Carbon::parse($p->jam_datang)->diff(\Carbon\Carbon::parse($p->jam_pulang))->format('%H:%I')
      : '—';
  @endphp
  <div class="card animate-slideup" style="margin-bottom: 16px;">
    <div class="card-body-sm">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
        <div>
          <div style="font-weight: 700; font-size: 1rem;">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</div>
          <div class="text-muted text-sm">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('l') }}</div>
        </div>
        <span class="badge badge-{{ $sc }}">{{ $sl }}</span>
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <div>
          <div class="text-xs text-muted" style="text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Jam Masuk</div>
          <div style="font-weight: 600;">{{ $p->jam_datang ? \Carbon\Carbon::parse($p->jam_datang)->format('H:i') : '—' }}</div>
        </div>
        <div>
          <div class="text-xs text-muted" style="text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Jam Pulang</div>
          <div style="font-weight: 600;">{{ $p->jam_pulang ? \Carbon\Carbon::parse($p->jam_pulang)->format('H:i') : '—' }}</div>
        </div>
        <div>
          <div class="text-xs text-muted" style="text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Durasi</div>
          <div style="font-weight: 600;" class="font-mono">{{ $durasi }}</div>
        </div>
      </div>
      @if($p->keterangan)
      <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border);">
        <div class="text-xs text-muted" style="text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Keterangan</div>
        <div class="text-sm">{{ $p->keterangan }}</div>
      </div>
      @endif
    </div>
  </div>
  @empty
  <div class="card animate-slideup">
    <div class="card-body" style="text-align: center; padding: 40px; color: var(--muted);">
      <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
      Tidak ada data presensi bulan ini
    </div>
  </div>
  @endforelse
  @if(isset($riwayat) && $riwayat->hasPages())
  <div class="card" style="margin-top: 16px;">
    <div class="card-footer">{{ $riwayat->links() }}</div>
  </div>
  @endif
</div>
@endsection

<style>
/* Desktop: show table, hide cards */
@media (min-width: 769px) {
  .mobile-cards { display: none; }
}
/* Mobile: show cards, hide table */
@media (max-width: 768px) {
  .desktop-table { display: none; }
}
</style>