@extends('layouts.app')
@section('title', 'Format Presensi')
@section('page-title', 'Format Presensi')

@push('styles')
<style>
  /* ── Status Bar ── */
  .setup-status {
    display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
    padding: 16px 20px; border-radius: 12px;
    background: linear-gradient(135deg, rgba(0,201,167,0.08), rgba(0,201,167,0.02));
    border: 1px solid rgba(0,201,167,0.2);
    margin-bottom: 20px;
  }
  .setup-status-icon {
    width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
    background: var(--teal); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 4px 12px rgba(0,201,167,0.25);
  }
  .setup-status-text h4 { margin: 0; font-weight: 700; font-size: .95rem; }
  .setup-status-text p { margin: 2px 0 0; font-size: .8rem; }

  /* ── Warning Box ── */
  .warn-box {
    display: flex; gap: 12px; align-items: flex-start;
    padding: 14px 16px; border-radius: 10px;
    background: rgba(255,171,64,0.06);
    border: 1px solid rgba(255,171,64,0.2);
    margin-bottom: 20px;
  }
  html.lm .warn-box {
    background: rgba(255,171,64,0.08);
    border-color: rgba(255,171,64,0.3);
  }
  .warn-box i { color: #d97706; font-size: 1rem; margin-top: 2px; flex-shrink: 0; }
  .warn-box strong { color: #d97706; }

  /* ── Action Card ── */
  .action-card {
    display: flex; align-items: center; gap: 16px;
    padding: 20px;
    border-radius: 12px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
  }
  .action-icon {
    width: 56px; height: 56px; border-radius: 12px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
  }
  .action-content { flex: 1; min-width: 0; }
  .action-title { margin: 0; font-weight: 700; font-size: 1rem; }
  .action-desc { margin: 4px 0 0; font-size: .88rem; color: var(--text-secondary); }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Format Presensi</span></div>
  <h1>Format Presensi</h1>
  <p class="text-muted">Hapus seluruh data riwayat presensi karyawan (tidak mempengaruhi data lain).</p>
</div>

<div class="animate-slideup">

  {{-- Status Presensi --}}
  <div class="setup-status">
    <div class="setup-status-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
    <div class="setup-status-text">
      <h4>Jumlah Data Riwayat Presensi</h4>
      <p class="text-muted">Terdapat <strong style="color:var(--teal);">{{ number_format($presensiCount) }}</strong> record data presensi di database.</p>
    </div>
  </div>

  {{-- Warning Box --}}
  <div class="warn-box">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div class="text-sm">
      <strong style="display:block; margin-bottom:4px;">Peringatan Penting</strong>
      Tindakan ini akan menghapus SEMUA data riwayat presensi secara permanen dan tidak dapat dikembalikan. Pastikan Anda sudah melakukan backup data terlebih dahulu!
    </div>
  </div>

  {{-- Action Card --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-trash-can text-red"></i>
      <h3>Hapus Semua Riwayat Presensi</h3>
    </div>
    <div class="card-body">
      <div class="action-card">
        <div class="action-icon">
          <i class="fa-solid fa-database"></i>
        </div>
        <div class="action-content">
          <h4 class="action-title">Hapus Seluruh Data Presensi</h4>
          <p class="action-desc">Hapus semua riwayat presensi karyawan (tidak mempengaruhi data karyawan, izin, dll).</p>
        </div>
        <form method="POST" action="{{ route('operator.setup.delete-presensi') }}" onsubmit="return confirm('Yakin ingin menghapus SEMUA data riwayat presensi? Tindakan ini tidak dapat dibatalkan!')">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger btn-lg">
            <i class="fa-solid fa-trash-can"></i> Hapus Data Presensi
          </button>
        </form>
      </div>
    </div>
  </div>

</div>
@endsection