{{-- ===================================================
   LAPORAN PRESENSI — resources/views/shared/laporan.blade.php
   Dipakai oleh HRD & Operator
   =================================================== --}}
@extends('layouts.app')
@section('title','Laporan Presensi')
@section('page-title','Laporan Presensi')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Laporan Presensi</span></div>
  <h1>Laporan Presensi Karyawan</h1>
  <p class="text-muted">Generate dan unduh laporan kehadiran karyawan dalam format Excel atau PDF.</p>
</div>

<div class="animate-slideup">
{{-- Filter --}}
<div class="card mb-6">
  <div class="card-header">
    <i class="fa-solid fa-filter text-teal"></i>
    <h3>Filter Laporan</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route(auth()->user()->role . '.laporan') }}" id="filterForm">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;align-items:end;">

        <div class="form-group" style="margin:0;">
          <label class="form-label">Bulan</label>
          <select name="bulan" class="form-control" style="cursor: pointer;">
            @php
              $currentBulan = request('bulan', now()->format('Y-m'));
            @endphp
            @for($i = 0; $i < 12; $i++)
              @php
                $date = now()->subMonths($i);
                $val  = $date->format('Y-m');
                $label = $date->translatedFormat('F Y');
              @endphp
              <option value="{{ $val }}" {{ $currentBulan == $val ? 'selected' : '' }}>
                {{ $label }}
              </option>
            @endfor
          </select>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Karyawan</label>
          @if(auth()->user()->role === 'karyawan')
            <input type="text" class="form-control" value="{{ auth()->user()->karyawan->nama_lengkap }}" readonly style="background:var(--bg-input);cursor: pointer;">
            <input type="hidden" name="karyawan_id" value="{{ auth()->user()->karyawan->id }}">
          @else
            <select name="karyawan_id" class="form-control" style="cursor: pointer;">
              <option value="">— Semua Karyawan —</option>
              @foreach($listKaryawan ?? [] as $k)
              <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>
                {{ $k->nama_lengkap }}
              </option>
              @endforeach
            </select>
          @endif
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Status</label>
          <select name="status" class="form-control" style="cursor: pointer;">
            <option value="">— Semua Status —</option>
            <option value="tepat_waktu" {{ request('status') === 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
            <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
            <option value="pulang_awal" {{ request('status') === 'pulang_awal' ? 'selected' : '' }}>Pulang Awal</option>
            <option value="alpa" {{ request('status') === 'alpa' ? 'selected' : '' }}>Alpa</option>
            <option value="izin" {{ request('status') === 'izin' ? 'selected' : '' }}>Izin</option>
            <option value="sakit" {{ request('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
            <option value="cuti" {{ request('status') === 'cuti' ? 'selected' : '' }}>Cuti</option>
          </select>
        </div>

        <div style="display:flex;gap:8px;align-self:end;flex-wrap:wrap;">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i> Filter
          </button>
          <a href="{{ route(auth()->user()->role . '.laporan') }}" class="btn btn-outline">
            <i class="fa-solid fa-xmark"></i>
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Export Buttons --}}
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
  @php
    $exportRoute = auth()->user()->role . '.laporan.export';
  @endphp
  <a href="{{ route($exportRoute, array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="btn btn-outline">
    <i class="fa-solid fa-file-excel" style="color:#1D6F42;"></i> Export Excel
  </a>
  <a href="{{ route($exportRoute, array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-outline">
    <i class="fa-solid fa-file-pdf" style="color:#F40F02;"></i> Export PDF
  </a>
  <div style="margin-left:auto" class="text-muted text-sm">
    Menampilkan <strong>{{ $laporan->total() ?? 0 }}</strong> data
  </div>
</div>

{{-- Summary Cards --}}
<div class="stat-grid stagger" style="margin-bottom:24px;">
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
      <div class="stat-delta">karyawan</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon teal"><i class="fa-solid fa-list"></i></div>
    <div class="stat-info">
      <div class="stat-label">Total Catatan</div>
      <div class="stat-value">{{ $summary['total'] ?? 0 }}</div>
      <div class="stat-delta">hari kerja</div>
    </div>
  </div>
</div>

{{-- TABLE VIEW (Desktop Only) --}}
<div class="card desktop-table">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Karyawan</th>
          <th>Tanggal</th>
          <th>Hari</th>
          <th>Jam Masuk</th>
          <th>Jam Pulang</th>
          <th>Durasi</th>
          <th>Status</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($laporan ?? [] as $i => $p)
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
          <td class="text-muted text-xs">{{ $laporan->firstItem() + $i }}</td>
          <td>
            <div style="font-weight:600;font-size:.85rem;">{{ $p->karyawan?->nama_lengkap }}</div>
          </td>
          <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
          <td class="text-muted">{{ $p->hari ?? \Carbon\Carbon::parse($p->tanggal)->translatedFormat('l') }}</td>
          <td>{{ $p->jam_datang ? \Carbon\Carbon::parse($p->jam_datang)->format('H:i') : '—' }}</td>
          <td>{{ $p->jam_pulang ? \Carbon\Carbon::parse($p->jam_pulang)->format('H:i') : '—' }}</td>
          <td class="font-mono text-sm">{{ $durasi }}</td>
          <td><span class="badge badge-{{ $sc }}">{{ $sl }}</span></td>
          <td class="text-muted text-xs">{{ $p->keterangan ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--muted);">
          <i class="fa-solid fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
          Tidak ada data untuk filter yang dipilih
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if(isset($laporan) && $laporan->hasPages())
  <div class="card-footer">
    <div class="text-muted text-sm">{{ $laporan->firstItem() }}–{{ $laporan->lastItem() }} dari {{ $laporan->total() }}</div>
    <div style="margin-left:auto;">{{ $laporan->appends(request()->query())->links() }}</div>
  </div>
  @endif
</div>

{{-- MOBILE CARD VIEW --}}
<div class="mobile-cards">
  @forelse($laporan ?? [] as $i => $p)
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
          @if(auth()->user()->role !== 'karyawan')
            <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 4px;">{{ $p->karyawan?->nama_lengkap }}</div>
          @endif
          <div style="font-weight: 600;">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</div>
          <div class="text-muted text-sm">{{ $p->hari ?? \Carbon\Carbon::parse($p->tanggal)->translatedFormat('l') }}</div>
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
      Tidak ada data untuk filter yang dipilih
    </div>
  </div>
  @endforelse
  @if(isset($laporan) && $laporan->hasPages())
  <div class="card" style="margin-top: 16px;">
    <div class="card-footer">
      <div class="text-muted text-sm">{{ $laporan->firstItem() }}–{{ $laporan->lastItem() }} dari {{ $laporan->total() }}</div>
      <div style="margin-left:auto;">{{ $laporan->appends(request()->query())->links() }}</div>
    </div>
  </div>
  @endif
</div>
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