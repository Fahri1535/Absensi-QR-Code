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
          <label class="form-label">Dari Tanggal</label>
          <input type="date" name="dari" class="form-control" value="{{ request('dari', now()->startOfMonth()->toDateString()) }}">
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Sampai Tanggal</label>
          <input type="date" name="sampai" class="form-control" value="{{ request('sampai', now()->toDateString()) }}">
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Karyawan</label>
          <select name="karyawan_id" class="form-control">
            <option value="">— Semua Karyawan —</option>
            @foreach($listKaryawan ?? [] as $k)
            <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>
              {{ $k->nama_lengkap }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="">— Semua Status —</option>
            <option value="tepat_waktu"  {{ request('status') === 'tepat_waktu'  ? 'selected' : '' }}>Tepat Waktu</option>
            <option value="terlambat"    {{ request('status') === 'terlambat'    ? 'selected' : '' }}>Terlambat</option>
            <option value="pulang_awal"  {{ request('status') === 'pulang_awal'  ? 'selected' : '' }}>Pulang Awal</option>
          </select>
        </div>

        <div style="display:flex;gap:8px;align-self:end;">
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
  <a href="{{ route(auth()->user()->role . '.laporan.export', array_merge(request()->query(), ['format'=>'xlsx'])) }}"
     class="btn btn-outline">
    <i class="fa-solid fa-file-excel" style="color:#1D6F42;"></i> Export Excel
  </a>
  <a href="{{ route(auth()->user()->role . '.laporan.export', array_merge(request()->query(), ['format'=>'pdf'])) }}"
     class="btn btn-outline">
    <i class="fa-solid fa-file-pdf" style="color:#F40F02;"></i> Export PDF
  </a>
  <div style="margin-left:auto;" class="text-muted text-sm" style="line-height:2.2rem;">
    Menampilkan <strong>{{ $laporan->total() ?? 0 }}</strong> data
  </div>
</div>

{{-- Summary Cards --}}
<div class="stat-grid stagger" style="margin-bottom:20px;">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-info">
      <div class="stat-label">Tepat Waktu</div>
      <div class="stat-value">{{ $summary['tepat_waktu'] ?? 0 }}</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
    <div class="stat-info">
      <div class="stat-label">Terlambat</div>
      <div class="stat-value">{{ $summary['terlambat'] ?? 0 }}</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-arrow-left"></i></div>
    <div class="stat-info">
      <div class="stat-label">Pulang Awal</div>
      <div class="stat-value">{{ $summary['pulang_awal'] ?? 0 }}</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon teal"><i class="fa-solid fa-list"></i></div>
    <div class="stat-info">
      <div class="stat-label">Total Catatan</div>
      <div class="stat-value">{{ $summary['total'] ?? 0 }}</div>
    </div>
  </div>
</div>

{{-- Table --}}
<div class="card">
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
          $sc = ['tepat_waktu'=>'green','terlambat'=>'amber','pulang_awal'=>'red'][$p->status] ?? 'muted';
          $sl = ['tepat_waktu'=>'Tepat Waktu','terlambat'=>'Terlambat','pulang_awal'=>'Pulang Awal'][$p->status] ?? ucfirst($p->status);
          $durasi = ($p->jam_datang && $p->jam_pulang)
            ? \Carbon\Carbon::parse($p->jam_datang)->diff(\Carbon\Carbon::parse($p->jam_pulang))->format('%H:%I')
            : '—';
        @endphp
        <tr>
          <td class="text-muted text-xs">{{ $laporan->firstItem() + $i }}</td>
          <td>
            <div style="font-weight:600;font-size:.88rem;">{{ $p->karyawan?->nama_lengkap }}</div>
          </td>
          <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
          <td class="text-muted">{{ $p->hari }}</td>
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

@endsection
