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

  /* ── Stat Mini Grid ── */
  .stat-mini-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
    margin-top: 14px;
  }
  .stat-mini {
    padding: 10px 14px;
    border-radius: 8px;
    background: var(--bg-card);
    border: 1px solid var(--border);
  }
  .stat-mini-val { font-weight: 800; font-size: 1.15rem; font-family: 'DM Sans', sans-serif; }
  .stat-mini-label { font-size: .75rem; color: var(--text-secondary); }

  /* ── Scope Radio ── */
  .scope-options { display: flex; flex-direction: column; gap: 8px; }
  .scope-option {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 10px 12px; border-radius: 8px;
    border: 1px solid var(--border); cursor: pointer;
    transition: all var(--transition);
  }
  .scope-option:hover { border-color: var(--teal); background: var(--teal-glow); }
  .scope-option input { margin-top: 3px; }
  .scope-option-title { font-weight: 600; font-size: .88rem; }
  .scope-option-desc { font-size: .78rem; color: var(--text-secondary); margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Format Presensi</span></div>
  <h1>Format Presensi</h1>
  <p class="text-muted">Hapus data riwayat presensi karyawan — pilih hapus SEMUA atau per bulan tertentu.</p>
</div>

<div class="animate-slideup">

  @if(session('success'))
  <div class="alert alert-success" style="margin-bottom:20px;">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
  </div>
  @endif

  {{-- Status Presensi --}}
  <div class="setup-status">
    <div class="setup-status-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
    <div class="setup-status-text" style="flex:1;">
      <h4>Jumlah Data Riwayat</h4>
      <p class="text-muted">
        <strong style="color:var(--teal);">{{ number_format($presensiCount) }}</strong> record presensi ·
        <strong style="color:var(--blue);">{{ number_format($izinCount) }}</strong> record izin
      </p>
      {{-- Statistik ringkas --}}
      <div class="stat-mini-grid">
        <div class="stat-mini">
          <div class="stat-mini-val" style="color:var(--green);">{{ $statPresensi['tepat_waktu'] }}</div>
          <div class="stat-mini-label">Tepat Waktu</div>
        </div>
        <div class="stat-mini">
          <div class="stat-mini-val" style="color:var(--amber);">{{ $statPresensi['terlambat'] }}</div>
          <div class="stat-mini-label">Terlambat</div>
        </div>
        <div class="stat-mini">
          <div class="stat-mini-val" style="color:var(--purple);">{{ $statIzin['izin'] + $statIzin['sakit'] + $statIzin['cuti'] + $statIzin['lainnya'] }}</div>
          <div class="stat-mini-label">Total Izin</div>
        </div>
        <div class="stat-mini">
          <div class="stat-mini-val" style="color:var(--muted);">{{ $statPresensi['lainnya'] }}</div>
          <div class="stat-mini-label">Lainnya</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Warning Box --}}
  <div class="warn-box">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div class="text-sm">
      <strong style="display:block; margin-bottom:4px;">Peringatan Penting</strong>
      Tindakan ini akan menghapus data riwayat secara permanen dan tidak dapat dikembalikan. <strong>"Alpha" tidak perlu dihapus manual</strong> — alpha otomatis dihitung dari presensi yang kosong, jadi setelah presensi dihapus, alpha juga hilang. Pastikan Anda sudah backup data!
    </div>
  </div>

  {{-- ===== MODE 1: HAPUS SEMUA ===== --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header">
      <i class="fa-solid fa-trash-can text-red"></i>
      <h3>Hapus SEMUA Riwayat</h3>
    </div>
    <div class="card-body">
      <div class="action-card" style="flex-direction:column;align-items:stretch;">
        <div style="display:flex;align-items:center;gap:16px;">
          <div class="action-icon"><i class="fa-solid fa-database"></i></div>
          <div class="action-content">
            <h4 class="action-title">Hapus Seluruh Data Riwayat</h4>
            <p class="action-desc">Hapus SEMUA presensi sepanjang waktu. Pilih apakah izin/sakit/cuti juga ikut dihapus.</p>
          </div>
        </div>

        <form method="POST" action="{{ route('operator.setup.delete-presensi') }}" onsubmit="return confirm('Yakin ingin menghapus SEMUA data riwayat? Tindakan ini TIDAK DAPAT dibatalkan!')" style="margin-top:16px;">
          @csrf
          @method('DELETE')
          <label class="form-label">Pilih jenis data yang dihapus:</label>
          <div class="scope-options" style="margin-bottom:16px;">
            <label class="scope-option">
              <input type="radio" name="scope" value="presensi" checked required>
              <div>
                <div class="scope-option-title">Presensi saja</div>
                <div class="scope-option-desc">Hapus absen fisik (jam masuk/pulang/terlambat). Data izin, sakit, cuti tetap aman.</div>
              </div>
            </label>
            <label class="scope-option">
              <input type="radio" name="scope" value="semua">
              <div>
                <div class="scope-option-title">Presensi + Izin/Sakit/Cuti</div>
                <div class="scope-option-desc">Bersih total — semua jejak kehadiran hilang termasuk pengajuan izin.</div>
              </div>
            </label>
          </div>
          <button type="submit" class="btn btn-danger btn-lg">
            <i class="fa-solid fa-trash-can"></i> Hapus SEMUA Riwayat
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- ===== MODE 2: HAPUS PER BULAN ===== --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-calendar-xmark text-amber"></i>
      <h3>Hapus Per Bulan Spesifik</h3>
    </div>
    <div class="card-body">
      <div class="action-card" style="flex-direction:column;align-items:stretch;">
        <div style="display:flex;align-items:center;gap:16px;">
          <div class="action-icon" style="background:rgba(255,171,64,0.12);color:#d97706;">
            <i class="fa-solid fa-calendar-day"></i>
          </div>
          <div class="action-content">
            <h4 class="action-title">Hapus Riwayat Bulan Tertentu</h4>
            <p class="action-desc">Pilih bulan + tahun, hanya data bulan itu yang dihapus. Bulan lain tetap aman.</p>
          </div>
        </div>

        <form method="POST" action="{{ route('operator.setup.delete-presensi-bulan') }}" onsubmit="return confirm('Yakin ingin menghapus data riwayat untuk bulan yang dipilih? Tindakan ini TIDAK DAPAT dibatalkan!')" style="margin-top:16px;">
          @csrf
          @method('DELETE')
          <div class="form-group" style="margin-bottom:14px;">
            <label class="form-label">Pilih Bulan <span style="color:var(--red);">*</span></label>
            <input type="month" name="bulan" class="form-control"
                   value="{{ date('Y-m') }}" max="{{ date('Y-m') }}" required>
          </div>
          <label class="form-label">Pilih jenis data yang dihapus:</label>
          <div class="scope-options" style="margin-bottom:16px;">
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
                <div class="scope-option-title">Presensi + Izin/Sakit/Cuti</div>
                <div class="scope-option-desc">Hapus presensi + pengajuan izin yang mencakup bulan itu.</div>
              </div>
            </label>
          </div>
          <button type="submit" class="btn btn-danger btn-lg" style="background:#d97706;border-color:#d97706;">
            <i class="fa-solid fa-trash-can"></i> Hapus Riwayat Bulan Tersebut
          </button>
        </form>
      </div>
    </div>
  </div>

</div>
@endsection