@extends('layouts.app')
@section('title', 'Panduan Instalasi & Database')
@section('page-title', 'Panduan Instalasi')

@push('styles')
<style>
  .setup-container { max-width: 900px; }

  .setup-status-card {
    border-left: 4px solid var(--teal);
    background: linear-gradient(to right, rgba(0,201,167,0.05), transparent);
  }
  .setup-status-icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: var(--teal); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,201,167,0.3);
  }

  .setup-step {
    margin-bottom: 28px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border-color);
  }
  .setup-step:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
  }

  .step-number {
    width: 26px; height: 26px; border-radius: 50%;
    background: var(--teal); color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.8rem; font-weight: 700; flex-shrink: 0;
  }

  .setup-code-block {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    padding: 16px; border-radius: 10px;
    font-size: 0.85rem;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    line-height: 1.7;
    overflow-x: auto;
    color: var(--text-primary);
    white-space: pre;
    margin-top: 10px;
  }
  .setup-code-block .code-key { color: var(--blue-light); font-weight: 500; }
  .setup-code-block .code-val { color: var(--text-primary); }

  .cmd-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    padding: 12px 16px; border-radius: 10px;
    display: flex; justify-content: space-between; align-items: center; gap: 12px;
    transition: border-color 0.2s;
  }
  .cmd-card:hover { border-color: var(--teal); }
  .cmd-card .cmd-label {
    font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 4px;
  }
  .cmd-card code {
    background: transparent; color: var(--text-primary);
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 0.9rem; padding: 0;
  }

  .setup-warning {
    background: rgba(255,171,64,0.06);
    border: 1px solid rgba(255,171,64,0.2);
    padding: 16px; border-radius: 10px;
    display: flex; gap: 12px;
  }
  html.lm .setup-warning {
    background: rgba(255,171,64,0.08);
    border-color: rgba(255,171,64,0.3);
  }
  .setup-warning .warning-icon {
    color: #d97706; font-size: 1.1rem; margin-top: 2px; flex-shrink: 0;
  }
  .setup-warning strong { color: #d97706; }
  .setup-warning code {
    background: rgba(255,171,64,0.1); color: #d97706;
    padding: 1px 6px; border-radius: 4px; font-size: 0.85rem;
  }

  .inline-code {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    color: var(--blue-light);
    padding: 2px 8px; border-radius: 5px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 0.85rem;
  }

  @media (max-width: 768px) {
    .setup-status-card .card-body-sm > div { flex-direction: column; text-align: center; }
    .cmd-card { flex-direction: column; align-items: flex-start; }
  }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Panduan Instalasi</span></div>
  <h1>Panduan Instalasi &amp; Database</h1>
  <p class="text-muted">Instruksi teknis untuk memindahkan atau mengatur ulang database aplikasi.</p>
</div>

<div class="setup-container animate-slideup">

  {{-- Status Koneksi --}}
  <div class="card mb-4 setup-status-card">
    <div class="card-body-sm">
      <div style="display:flex; align-items:center; gap:16px;">
        <div class="setup-status-icon">
          <i class="fa-solid fa-database"></i>
        </div>
        <div>
          <h4 style="margin:0; font-weight:700;">Status Koneksi Database</h4>
          <p class="text-sm text-muted" style="margin:4px 0 0;">
            Terhubung ke: <strong style="color:var(--teal);">{{ strtoupper($dbConnection) }}</strong> ({{ $dbName }})
          </p>
        </div>
      </div>
    </div>
  </div>

  {{-- Langkah-langkah --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-gears" style="color:var(--blue-light);"></i>
      <h3>Langkah Instalasi Baru</h3>
    </div>
    <div class="card-body">

      {{-- Step 1 --}}
      <div class="setup-step">
        <h4 style="color:var(--teal); margin-bottom:12px; display:flex; align-items:center; gap:10px;">
          <span class="step-number">1</span>
          Persiapan Database
        </h4>
        <div style="padding-left:36px;">
          <p class="text-sm">Jika ingin menggunakan <strong>MySQL (XAMPP)</strong>:</p>
          <ul style="padding-left:20px; margin:0;" class="text-sm text-muted">
            <li>Buka <strong>phpMyAdmin</strong> (<code class="inline-code">http://localhost/phpmyadmin</code>).</li>
            <li>Buat database baru dengan nama <code class="inline-code">{{ $dbName }}</code>.</li>
            <li>Pastikan Apache dan MySQL di XAMPP sudah dalam status <strong>Running</strong>.</li>
          </ul>
        </div>
      </div>

      {{-- Step 2 --}}
      <div class="setup-step">
        <h4 style="color:var(--teal); margin-bottom:12px; display:flex; align-items:center; gap:10px;">
          <span class="step-number">2</span>
          Konfigurasi File .env
        </h4>
        <div style="padding-left:36px;">
          <p class="text-sm">Buka file <code class="inline-code">.env</code> di folder utama aplikasi, lalu sesuaikan bagian berikut:</p>
          <div class="setup-code-block"><span class="code-key">DB_CONNECTION</span>=<span class="code-val">mysql</span>
<span class="code-key">DB_HOST</span>=<span class="code-val">127.0.0.1</span>
<span class="code-key">DB_PORT</span>=<span class="code-val">3306</span>
<span class="code-key">DB_DATABASE</span>=<span class="code-val">{{ $dbName }}</span>
<span class="code-key">DB_USERNAME</span>=<span class="code-val">root</span>
<span class="code-key">DB_PASSWORD</span>=</div>
        </div>
      </div>

      {{-- Step 3 --}}
      <div class="setup-step">
        <h4 style="color:var(--teal); margin-bottom:12px; display:flex; align-items:center; gap:10px;">
          <span class="step-number">3</span>
          Perintah Inisialisasi (Terminal/CMD)
        </h4>
        <div style="padding-left:36px;">
          <p class="text-sm">Buka Terminal atau Command Prompt di folder utama aplikasi, lalu jalankan perintah berikut secara berurutan:</p>

          <div style="display:flex; flex-direction:column; gap:12px; margin-top:14px;">
            <div class="cmd-card">
              <div style="flex:1;">
                <div class="cmd-label" style="color:var(--teal);">A. Migrasi Tabel (Wajib)</div>
                <code>php artisan migrate</code>
              </div>
              <i class="fa-solid fa-terminal text-muted"></i>
            </div>

            <div class="cmd-card">
              <div style="flex:1;">
                <div class="cmd-label" style="color:var(--blue-light);">B. Seed Data Admin (Rekomendasi)</div>
                <code>php artisan db:seed</code>
              </div>
              <i class="fa-solid fa-terminal text-muted"></i>
            </div>

            <div class="cmd-card">
              <div style="flex:1;">
                <div class="cmd-label" style="color:var(--amber);">C. Symbolic Link (Wajib)</div>
                <code>php artisan storage:link</code>
              </div>
              <i class="fa-solid fa-terminal text-muted"></i>
            </div>

            <div class="cmd-card">
              <div style="flex:1;">
                <div class="cmd-label" style="color:#8B5CF6;">D. Bersihkan Cache (Opsional)</div>
                <code>php artisan optimize:clear</code>
              </div>
              <i class="fa-solid fa-terminal text-muted"></i>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- Warning --}}
  <div class="setup-warning" style="margin-top:16px;">
    <i class="fa-solid fa-triangle-exclamation warning-icon"></i>
    <div class="text-sm">
      <strong style="display:block; margin-bottom:4px;">Peringatan Keamanan</strong>
      Gunakan perintah <code>migrate:fresh</code> dengan sangat hati-hati karena akan menghapus seluruh data yang ada. Lakukan backup database secara rutin untuk keamanan data perusahaan.
    </div>
  </div>

</div>
@endsection
