@extends('layouts.app')
@section('title', 'Panduan Instalasi & Database')
@section('page-title', 'Panduan Instalasi')

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

  /* ── Steps ── */
  .setup-step {
    position: relative;
    padding-left: 44px;
    padding-bottom: 28px;
    margin-bottom: 0;
  }
  .setup-step::before {
    content: '';
    position: absolute; left: 15px; top: 32px; bottom: 0;
    width: 2px;
    background: var(--border);
  }
  .setup-step:last-child::before { display: none; }
  .setup-step:last-child { padding-bottom: 0; }

  .step-badge {
    position: absolute; left: 0; top: 2px;
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--teal); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,201,167,0.3);
  }
  .step-title {
    font-weight: 700; font-size: 1rem; color: var(--text-primary);
    margin: 0 0 10px; padding-top: 4px;
  }
  .step-body {
    font-size: .88rem; line-height: 1.6;
  }
  .step-body ul { padding-left: 18px; margin: 8px 0 0; }
  .step-body li { margin-bottom: 6px; }
  .step-body li:last-child { margin-bottom: 0; }

  /* ── Code Block ── */
  .code-box {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 10px;
    margin-top: 10px;
    overflow: hidden;
  }
  .code-box-header {
    padding: 8px 14px;
    background: rgba(0,201,167,0.06);
    border-bottom: 1px solid var(--border);
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .5px;
    color: var(--teal);
    display: flex; align-items: center; gap: 6px;
  }
  .code-box-body {
    padding: 14px;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: .82rem; line-height: 1.8;
    color: var(--text-primary);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    white-space: pre;
  }
  .code-box-body .k { color: var(--teal); font-weight: 600; }
  .code-box-body .v { color: var(--blue-light); }
  .code-box-body .eq { color: var(--text-secondary); }

  /* ── Command Cards ── */
  .cmd-list {
    display: flex; flex-direction: column; gap: 10px;
    margin-top: 12px;
  }
  .cmd-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 14px; border-radius: 10px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    transition: border-color .2s, box-shadow .2s;
  }
  .cmd-item:hover {
    border-color: var(--teal);
    box-shadow: 0 2px 8px rgba(0,201,167,0.08);
  }
  .cmd-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
  }
  .cmd-content { flex: 1; min-width: 0; }
  .cmd-label {
    font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .4px;
    margin-bottom: 2px;
  }
  .cmd-code {
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: .85rem; color: var(--text-primary);
    word-break: break-all;
  }
  .cmd-icon {
    color: var(--text-secondary); font-size: .9rem; flex-shrink: 0;
  }

  /* ── Inline Code ── */
  .ic {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    color: var(--teal);
    padding: 1px 7px; border-radius: 5px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: .8rem;
    word-break: break-all;
  }

  /* ── Warning ── */
  .warn-box {
    display: flex; gap: 12px; align-items: flex-start;
    padding: 14px 16px; border-radius: 10px;
    background: rgba(255,171,64,0.06);
    border: 1px solid rgba(255,171,64,0.2);
    margin-top: 20px;
  }
  html.lm .warn-box {
    background: rgba(255,171,64,0.08);
    border-color: rgba(255,171,64,0.3);
  }
  .warn-box i { color: #d97706; font-size: 1rem; margin-top: 2px; flex-shrink: 0; }
  .warn-box strong { color: #d97706; }
  .warn-box .ic { color: #d97706; border-color: rgba(255,171,64,0.3); background: rgba(255,171,64,0.08); }

  @media (max-width: 600px) {
    .setup-status { flex-direction: column; text-align: center; }
    .setup-step { padding-left: 36px; }
    .step-badge { width: 28px; height: 28px; font-size: .7rem; }
    .setup-step::before { left: 13px; }
  }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Panduan Instalasi</span></div>
  <h1>Panduan Instalasi &amp; Database</h1>
  <p class="text-muted">Instruksi teknis untuk memindahkan atau mengatur ulang database aplikasi.</p>
</div>

<div class="animate-slideup">

  {{-- Status Koneksi --}}
  <div class="setup-status">
    <div class="setup-status-icon"><i class="fa-solid fa-database"></i></div>
    <div class="setup-status-text">
      <h4>Status Koneksi Database</h4>
      <p class="text-muted">Terhubung ke: <strong style="color:var(--teal);">{{ strtoupper($dbConnection) }}</strong> ({{ $dbName }})</p>
    </div>
  </div>

  {{-- Langkah Instalasi --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-gears text-teal"></i>
      <h3>Langkah Instalasi Baru</h3>
    </div>
    <div class="card-body">

      {{-- Step 1 --}}
      <div class="setup-step">
        <div class="step-badge">1</div>
        <h4 class="step-title">Persiapan Database</h4>
        <div class="step-body text-muted">
          <p style="margin:0;">Jika menggunakan <strong style="color:var(--text-primary);">MySQL (XAMPP)</strong>:</p>
          <ul>
            <li>Buka <strong>phpMyAdmin</strong> di <span class="ic">http://localhost/phpmyadmin</span></li>
            <li>Buat database baru bernama <span class="ic">{{ $dbName }}</span></li>
            <li>Pastikan Apache &amp; MySQL sudah <strong style="color:var(--green);">Running</strong></li>
          </ul>
        </div>
      </div>

      {{-- Step 2 --}}
      <div class="setup-step">
        <div class="step-badge">2</div>
        <h4 class="step-title">Konfigurasi File .env</h4>
        <div class="step-body text-muted">
          <p style="margin:0;">Buka file <span class="ic">.env</span> di folder utama aplikasi, lalu sesuaikan:</p>
          <div class="code-box">
            <div class="code-box-header"><i class="fa-solid fa-file-code"></i> Konfigurasi Database</div>
            <div class="code-box-body"><span class="k">DB_CONNECTION</span><span class="eq">=</span><span class="v">mysql</span>
<span class="k">DB_HOST</span><span class="eq">=</span><span class="v">127.0.0.1</span>
<span class="k">DB_PORT</span><span class="eq">=</span><span class="v">3306</span>
<span class="k">DB_DATABASE</span><span class="eq">=</span><span class="v">{{ $dbName }}</span>
<span class="k">DB_USERNAME</span><span class="eq">=</span><span class="v">root</span>
<span class="k">DB_PASSWORD</span><span class="eq">=</span></div>
          </div>
        </div>
      </div>

      {{-- Step 3 --}}
      <div class="setup-step">
        <div class="step-badge">3</div>
        <h4 class="step-title">Perintah Inisialisasi</h4>
        <div class="step-body text-muted">
          <p style="margin:0;">Buka Terminal / CMD di folder proyek, lalu jalankan secara berurutan:</p>
          <div class="cmd-list">
            <div class="cmd-item">
              <div class="cmd-dot" style="background:var(--teal);"></div>
              <div class="cmd-content">
                <div class="cmd-label" style="color:var(--teal);">A. Migrasi Tabel (Wajib)</div>
                <div class="cmd-code">php artisan migrate</div>
              </div>
              <i class="fa-solid fa-terminal cmd-icon"></i>
            </div>
            <div class="cmd-item">
              <div class="cmd-dot" style="background:var(--blue-light);"></div>
              <div class="cmd-content">
                <div class="cmd-label" style="color:var(--blue-light);">B. Seed Data Admin (Rekomendasi)</div>
                <div class="cmd-code">php artisan db:seed</div>
              </div>
              <i class="fa-solid fa-terminal cmd-icon"></i>
            </div>
            <div class="cmd-item">
              <div class="cmd-dot" style="background:var(--amber);"></div>
              <div class="cmd-content">
                <div class="cmd-label" style="color:var(--amber);">C. Symbolic Link (Wajib)</div>
                <div class="cmd-code">php artisan storage:link</div>
              </div>
              <i class="fa-solid fa-terminal cmd-icon"></i>
            </div>
            <div class="cmd-item">
              <div class="cmd-dot" style="background:#8B5CF6;"></div>
              <div class="cmd-content">
                <div class="cmd-label" style="color:#8B5CF6;">D. Bersihkan Cache (Opsional)</div>
                <div class="cmd-code">php artisan optimize:clear</div>
              </div>
              <i class="fa-solid fa-terminal cmd-icon"></i>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- Warning --}}
  <div class="warn-box">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div class="text-sm">
      <strong style="display:block; margin-bottom:4px;">Peringatan Keamanan</strong>
      Gunakan perintah <span class="ic">migrate:fresh</span> dengan sangat hati-hati karena akan menghapus seluruh data. Lakukan backup database secara rutin.
    </div>
  </div>

</div>
@endsection
