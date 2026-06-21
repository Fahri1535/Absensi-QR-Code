@extends('layouts.app')
@section('title', 'Format Presensi')
@section('page-title', 'Format Presensi')

@push('styles')
<style>
  .scope-options { display: flex; flex-direction: column; gap: 10px; }
  .scope-option {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 14px 16px; border-radius: var(--radius-sm);
    border: 1px solid var(--border); cursor: pointer;
    background: var(--bg-card);
    transition: border-color var(--transition), background var(--transition);
  }
  .scope-option:hover,
  .scope-option:has(input:checked) {
    border-color: var(--teal);
    background: var(--teal-glow);
  }
  .scope-option input { margin-top: 3px; accent-color: var(--teal); flex-shrink: 0; }
  .scope-option-title { font-weight: 600; font-size: .9rem; }
  .scope-option-desc { font-size: .8rem; color: var(--text-secondary); margin-top: 3px; line-height: 1.45; }
  .setup-meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
  }
  .setup-meta-item {
    padding: 14px 16px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: var(--bg-card);
  }
  .setup-meta-item .label { font-size: .75rem; color: var(--text-secondary); margin-bottom: 4px; }
  .setup-meta-item .value { font-size: 1.25rem; font-weight: 800; font-family: 'DM Sans', sans-serif; }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Format Presensi</span></div>
  <h1>Format Presensi</h1>
  <p class="text-muted">Kelola dan hapus data riwayat kehadiran karyawan &amp; HRD — {{ now()->translatedFormat('l, d F Y') }}</p>
</div>

<div class="animate-slideup">

  @if(session('success'))
  <div class="alert alert-success mb-6">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
  </div>
  @endif

  @php
    $totalHariKerja = $stats['hadir'] + $stats['terlambat'] + $stats['alpha'] + $stats['sakit'] + $stats['lainnya'];
  @endphp

  {{-- Statistik Riwayat --}}
  <div class="stat-grid stagger mb-6">
    <div class="stat-card">
      <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
      <div class="stat-info">
        <div class="stat-label">Hadir</div>
        <div class="stat-value">{{ number_format($stats['hadir']) }}</div>
        <div class="stat-delta pos">tepat waktu</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
      <div class="stat-info">
        <div class="stat-label">Terlambat</div>
        <div class="stat-value">{{ number_format($stats['terlambat']) }}</div>
        <div class="stat-delta">presensi terlambat</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red"><i class="fa-solid fa-user-xmark"></i></div>
      <div class="stat-info">
        <div class="stat-label">Alpha</div>
        <div class="stat-value">{{ number_format($stats['alpha']) }}</div>
        <div class="stat-delta neg">tanpa keterangan</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon blue"><i class="fa-solid fa-file-medical"></i></div>
      <div class="stat-info">
        <div class="stat-label">Sakit</div>
        <div class="stat-value">{{ number_format($stats['sakit']) }}</div>
        <div class="stat-delta">izin disetujui</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon teal"><i class="fa-solid fa-calendar-days"></i></div>
      <div class="stat-info">
        <div class="stat-label">Lainnya</div>
        <div class="stat-value">{{ number_format($stats['lainnya']) }}</div>
        <div class="stat-delta">izin, cuti, dll</div>
      </div>
    </div>
  </div>

  {{-- Ringkasan Data --}}
  <div class="card mb-6">
    <div class="card-header">
      <i class="fa-solid fa-database text-teal"></i>
      <h3>Ringkasan Data (Karyawan &amp; HRD)</h3>
    </div>
    <div class="card-body">
      <div class="setup-meta-grid">
        <div class="setup-meta-item">
          <div class="label">Record Presensi</div>
          <div class="value" style="color:var(--green);">{{ number_format($presensiCount) }}</div>
        </div>
        <div class="setup-meta-item">
          <div class="label">Record Izin</div>
          <div class="value" style="color:var(--blue-light);">{{ number_format($izinCount) }}</div>
        </div>
        <div class="setup-meta-item">
          <div class="label">Hari Kerja Tercatat</div>
          <div class="value" style="color:var(--teal);">{{ number_format($totalHariKerja) }}</div>
        </div>
      </div>
      <p class="text-muted text-sm" style="margin:14px 0 0;">
        <i class="fa-solid fa-circle-info" style="color:var(--teal);"></i>
        Alpha dihitung otomatis dari hari kerja tanpa presensi &amp; tanpa izin disetujui.
        <strong>Lainnya</strong> = semua jenis izin disetujui kecuali sakit (izin, cuti, tugas luar, dll).
      </p>
    </div>
  </div>

  {{-- Peringatan --}}
  <div class="alert alert-warning mb-6">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
      <strong>Peringatan Penting</strong><br>
      Tindakan hapus data bersifat permanen dan tidak dapat dikembalikan. Hanya data <strong>karyawan &amp; HRD</strong> yang terpengaruh.
      Alpha tidak perlu dihapus manual — setelah presensi dihapus, alpha otomatis hilang. Pastikan Anda sudah backup data!
    </div>
  </div>

  {{-- Form Hapus --}}
  <div class="main-sidebar-grid stagger">

    {{-- Hapus Semua --}}
    <div class="card" style="border-left:4px solid var(--red);">
      <div class="card-header">
        <i class="fa-solid fa-trash-can" style="color:var(--red);"></i>
        <h3>Hapus Semua Riwayat</h3>
      </div>
      <div class="card-body">
        <p class="text-muted text-sm" style="margin:0 0 18px;">
          Hapus seluruh data presensi karyawan &amp; HRD sepanjang waktu. Pilih apakah data izin juga ikut dihapus.
        </p>

        <form method="POST" action="{{ route('operator.setup.delete-presensi') }}"
              onsubmit="return confirm('Yakin ingin menghapus SEMUA data riwayat karyawan/HRD? Tindakan ini TIDAK DAPAT dibatalkan!')">
          @csrf
          @method('DELETE')

          <label class="form-label">Jenis data yang dihapus</label>
          <div class="scope-options mb-4">
            <label class="scope-option">
              <input type="radio" name="scope" value="presensi" checked required>
              <div>
                <div class="scope-option-title">Presensi saja</div>
                <div class="scope-option-desc">Hapus absen fisik (jam masuk/pulang). Data izin, sakit, cuti tetap aman.</div>
              </div>
            </label>
            <label class="scope-option">
              <input type="radio" name="scope" value="semua">
              <div>
                <div class="scope-option-title">Presensi + Izin</div>
                <div class="scope-option-desc">Bersih total — semua jejak kehadiran hilang termasuk pengajuan izin.</div>
              </div>
            </label>
          </div>

          <button type="submit" class="btn btn-danger btn-lg" style="width:100%;">
            <i class="fa-solid fa-trash-can"></i> Hapus Semua Riwayat
          </button>
        </form>
      </div>
    </div>

    {{-- Hapus Per Bulan --}}
    <div class="card" style="border-left:4px solid var(--amber);">
      <div class="card-header">
        <i class="fa-solid fa-calendar-xmark" style="color:var(--amber);"></i>
        <h3>Hapus Per Bulan</h3>
      </div>
      <div class="card-body">
        <p class="text-muted text-sm" style="margin:0 0 18px;">
          Pilih bulan tertentu — hanya data bulan itu yang dihapus. Bulan lain tetap aman.
        </p>

        <form method="POST" action="{{ route('operator.setup.delete-presensi-bulan') }}"
              onsubmit="return confirm('Yakin ingin menghapus data riwayat untuk bulan yang dipilih? Tindakan ini TIDAK DAPAT dibatalkan!')">
          @csrf
          @method('DELETE')

          <div class="form-group">
            <label class="form-label">Pilih Bulan <span style="color:var(--red);">*</span></label>
            <input type="month" name="bulan" class="form-control"
                   value="{{ date('Y-m') }}" max="{{ date('Y-m') }}" required>
          </div>

          <label class="form-label">Jenis data yang dihapus</label>
          <div class="scope-options mb-4">
            <label class="scope-option">
              <input type="radio" name="scope" value="presensi" checked required>
              <div>
                <div class="scope-option-title">Presensi saja</div>
                <div class="scope-option-desc">Hapus absen fisik bulan itu saja. Data izin tetap aman.</div>
              </div>
            </label>
            <label class="scope-option">
              <input type="radio" name="scope" value="semua">
              <div>
                <div class="scope-option-title">Presensi + Izin</div>
                <div class="scope-option-desc">Hapus presensi + pengajuan izin yang mencakup bulan itu.</div>
              </div>
            </label>
          </div>

          <button type="submit" class="btn btn-danger btn-lg" style="width:100%;background:var(--amber);border-color:var(--amber);">
            <i class="fa-solid fa-trash-can"></i> Hapus Riwayat Bulan Tersebut
          </button>
        </form>
      </div>
    </div>

  </div>

</div>
@endsection
